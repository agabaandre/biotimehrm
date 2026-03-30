package ug.go.health.ihrisbiometric.services;

import android.content.Context;
import android.util.Log;

import java.util.ArrayList;
import java.util.List;

import retrofit2.Call;
import retrofit2.Callback;
import retrofit2.Response;
import ug.go.health.ihrisbiometric.models.FaceEmbeddingDownloadResponse;
import ug.go.health.ihrisbiometric.models.FaceEmbeddingRecord;
import ug.go.health.ihrisbiometric.models.FaceEmbeddingUploadRequest;
import ug.go.health.ihrisbiometric.models.FaceUploadResponse;
import ug.go.health.ihrisbiometric.models.StaffRecord;
import ug.go.health.ihrisbiometric.utils.FaceEmbeddingFileHelper;

public class EmbeddingSyncService {

    private static final String TAG = "EmbeddingSyncService";

    private final Context context;
    private final ApiInterface apiService;
    private final DbService dbService;
    private final FaceScanner faceScanner;

    public EmbeddingSyncService(Context context, ApiInterface apiService,
                                 DbService dbService, FaceScanner faceScanner) {
        this.context = context;
        this.apiService = apiService;
        this.dbService = dbService;
        this.faceScanner = faceScanner;
    }

    public interface EmbeddingSyncCallback {
        void onProgress(int completed, int total, String message);
        void onComplete(int uploaded, int downloaded, List<String> errors);
        void onError(String errorMessage);
    }

    public void uploadEmbeddings(EmbeddingSyncCallback callback) {
        dbService.getStaffRecordsWithUnsyncedEmbeddingsAsync(records -> {
            if (records == null || records.isEmpty()) {
                callback.onComplete(0, 0, new ArrayList<>());
                return;
            }

            final int total = records.size();
            final int[] completed = {0};
            final int[] uploaded = {0};
            final List<String> errors = new ArrayList<>();

            for (StaffRecord record : records) {
                // Read the .face file (JPEG bytes) and base64-encode for transport
                String base64Face = FaceEmbeddingFileHelper.readAsBase64(record.getFacePath());
                if (base64Face == null) {
                    // Fallback: use faceImage if facePath not set yet
                    base64Face = record.getFaceImage();
                }
                if (base64Face == null) {
                    completed[0]++;
                    errors.add("No face file for " + record.getIhrisPid());
                    callback.onProgress(completed[0], total, "Skipped " + record.getIhrisPid() + ": no face file");
                    if (completed[0] == total) callback.onComplete(uploaded[0], 0, errors);
                    continue;
                }

                // face_data field carries the base64 face image (used for re-registration on other devices)
                FaceEmbeddingUploadRequest request = new FaceEmbeddingUploadRequest(
                        record.getIhrisPid(), base64Face, base64Face);

                apiService.uploadFaceEmbedding(request).enqueue(new Callback<FaceUploadResponse>() {
                    @Override
                    public void onResponse(Call<FaceUploadResponse> call, Response<FaceUploadResponse> response) {
                        completed[0]++;
                        if (response.isSuccessful()) {
                            record.setEmbeddingSynced(true);
                            dbService.updateStaffRecordAsync(record, success -> {
                                if (success) uploaded[0]++;
                                callback.onProgress(completed[0], total, "Uploaded face for " + record.getIhrisPid());
                                if (completed[0] == total) callback.onComplete(uploaded[0], 0, errors);
                            });
                        } else {
                            String error = "Upload failed for " + record.getIhrisPid() + ": HTTP " + response.code();
                            Log.e(TAG, error);
                            errors.add(error);
                            callback.onProgress(completed[0], total, error);
                            if (completed[0] == total) callback.onComplete(uploaded[0], 0, errors);
                        }
                    }

                    @Override
                    public void onFailure(Call<FaceUploadResponse> call, Throwable t) {
                        completed[0]++;
                        String error = "Upload failed for " + record.getIhrisPid() + ": " + t.getMessage();
                        Log.e(TAG, error, t);
                        errors.add(error);
                        callback.onProgress(completed[0], total, error);
                        if (completed[0] == total) callback.onComplete(uploaded[0], 0, errors);
                    }
                });
            }
        });
    }

    public void downloadEmbeddings(String facilityId, EmbeddingSyncCallback callback) {
        apiService.getFaceEmbeddings(facilityId).enqueue(new Callback<FaceEmbeddingDownloadResponse>() {
            @Override
            public void onResponse(Call<FaceEmbeddingDownloadResponse> call,
                                   Response<FaceEmbeddingDownloadResponse> response) {
                if (!response.isSuccessful()) {
                    callback.onError("Download failed: HTTP " + response.code());
                    return;
                }
                FaceEmbeddingDownloadResponse body = response.body();
                if (body == null || body.getEmbeddings() == null || body.getEmbeddings().isEmpty()) {
                    callback.onComplete(0, 0, new ArrayList<>());
                    return;
                }

                List<FaceEmbeddingRecord> embeddings = body.getEmbeddings();
                final int total = embeddings.size();
                final int[] completed = {0};
                final int[] downloaded = {0};
                final List<String> errors = new ArrayList<>();

                for (FaceEmbeddingRecord embeddingRecord : embeddings) {
                    dbService.getStaffRecordByihrisPIDAsync(embeddingRecord.getIhrisPid(), localRecord -> {
                        completed[0]++;
                        if (localRecord == null) {
                            Log.w(TAG, "No local record for: " + embeddingRecord.getIhrisPid());
                            callback.onProgress(completed[0], total, "Skipped " + embeddingRecord.getIhrisPid() + ": not in local DB");
                            if (completed[0] == total) callback.onComplete(0, downloaded[0], errors);
                            return;
                        }

                        // Skip if already have a local .face file and it's synced
                        if (localRecord.getFacePath() != null
                                && new java.io.File(localRecord.getFacePath()).exists()
                                && localRecord.isEmbeddingSynced()) {
                            callback.onProgress(completed[0], total, "Skipped " + embeddingRecord.getIhrisPid() + ": local file exists");
                            if (completed[0] == total) callback.onComplete(0, downloaded[0], errors);
                            return;
                        }

                        // face_data from server is base64 JPEG — save as .face file
                        String base64Image = embeddingRecord.getFaceData();
                        if (base64Image == null || base64Image.isEmpty()) {
                            errors.add("Empty face data for " + embeddingRecord.getIhrisPid());
                            if (completed[0] == total) callback.onComplete(0, downloaded[0], errors);
                            return;
                        }

                        // Write .face file to disk
                        String facePath = FaceEmbeddingFileHelper.saveFaceImage(
                                context, embeddingRecord.getIhrisPid(), base64Image);
                        if (facePath == null) {
                            errors.add("Failed to save .face file for " + embeddingRecord.getIhrisPid());
                            if (completed[0] == total) callback.onComplete(0, downloaded[0], errors);
                            return;
                        }

                        // Re-register with the face engine using the downloaded image
                        // Use forceRegister so re-syncing the same device doesn't fail
                        String registerResult = faceScanner.forceRegisterFaceFromBase64(
                                base64Image, embeddingRecord.getIhrisPid());
                        if (registerResult == null || !registerResult.startsWith("SUCCESS")) {
                            Log.w(TAG, "Face engine registration failed for " + embeddingRecord.getIhrisPid() + ": " + registerResult);
                            // Don't block — still save the file so we can retry later
                        }

                        localRecord.setFacePath(facePath);
                        localRecord.setFaceImage(base64Image);
                        localRecord.setFaceEnrolled(true);
                        localRecord.setEmbeddingSynced(true);

                        dbService.updateStaffRecordAsync(localRecord, success -> {
                            if (success) downloaded[0]++;
                            else errors.add("DB update failed for " + embeddingRecord.getIhrisPid());
                            callback.onProgress(completed[0], total, "Downloaded face for " + embeddingRecord.getIhrisPid());
                            if (completed[0] == total) callback.onComplete(0, downloaded[0], errors);
                        });
                    });
                }
            }

            @Override
            public void onFailure(Call<FaceEmbeddingDownloadResponse> call, Throwable t) {
                Log.e(TAG, "Download failed: " + t.getMessage(), t);
                callback.onError("Download failed: " + t.getMessage());
            }
        });
    }
}

public class EmbeddingSyncService {

    private static final String TAG = "EmbeddingSyncService";

    private final Context context;
    private final ApiInterface apiService;
    private final DbService dbService;
    private final FaceScanner faceScanner;

    public EmbeddingSyncService(Context context, ApiInterface apiService,
                                 DbService dbService, FaceScanner faceScanner) {
        this.context = context;
        this.apiService = apiService;
        this.dbService = dbService;
        this.faceScanner = faceScanner;
    }

    public interface EmbeddingSyncCallback {
        void onProgress(int completed, int total, String message);
        void onComplete(int uploaded, int downloaded, List<String> errors);
        void onError(String errorMessage);
    }

    public void uploadEmbeddings(EmbeddingSyncCallback callback) {
        dbService.getStaffRecordsWithUnsyncedEmbeddingsAsync(records -> {
            if (records == null || records.isEmpty()) {
                callback.onComplete(0, 0, new ArrayList<>());
                return;
            }

            final int total = records.size();
            final int[] completed = {0};
            final int[] uploaded = {0};
            final List<String> errors = new ArrayList<>();

            for (StaffRecord record : records) {
                String csvFaceData = FloatArrayConverter.toString(record.getFaceData());
                FaceEmbeddingUploadRequest request = new FaceEmbeddingUploadRequest(
                        record.getIhrisPid(), csvFaceData, record.getFaceImage());

                apiService.uploadFaceEmbedding(request).enqueue(new Callback<FaceUploadResponse>() {
                    @Override
                    public void onResponse(Call<FaceUploadResponse> call, Response<FaceUploadResponse> response) {
                        completed[0]++;
                        if (response.isSuccessful()) {
                            record.setEmbeddingSynced(true);
                            dbService.updateStaffRecordAsync(record, success -> {
                                if (success) {
                                    uploaded[0]++;
                                }
                                callback.onProgress(completed[0], total,
                                        "Uploaded embedding for " + record.getIhrisPid());
                                if (completed[0] == total) {
                                    callback.onComplete(uploaded[0], 0, errors);
                                }
                            });
                        } else {
                            String error = "Upload failed for " + record.getIhrisPid()
                                    + ": server error " + response.code();
                            Log.e(TAG, error);
                            errors.add(error);
                            callback.onProgress(completed[0], total, error);
                            if (completed[0] == total) {
                                callback.onComplete(uploaded[0], 0, errors);
                            }
                        }
                    }

                    @Override
                    public void onFailure(Call<FaceUploadResponse> call, Throwable t) {
                        completed[0]++;
                        String error = "Upload failed for " + record.getIhrisPid()
                                + ": " + t.getMessage();
                        Log.e(TAG, error, t);
                        errors.add(error);
                        callback.onProgress(completed[0], total, error);
                        if (completed[0] == total) {
                            callback.onComplete(uploaded[0], 0, errors);
                        }
                    }
                });
            }
        });
    }

    public void downloadEmbeddings(String facilityId, EmbeddingSyncCallback callback) {
        apiService.getFaceEmbeddings(facilityId).enqueue(new Callback<FaceEmbeddingDownloadResponse>() {
            @Override
            public void onResponse(Call<FaceEmbeddingDownloadResponse> call,
                                   Response<FaceEmbeddingDownloadResponse> response) {
                if (!response.isSuccessful()) {
                    callback.onError("Download failed: server error " + response.code());
                    return;
                }
                FaceEmbeddingDownloadResponse body = response.body();
                if (body == null || body.getEmbeddings() == null || body.getEmbeddings().isEmpty()) {
                    callback.onComplete(0, 0, new ArrayList<>());
                    return;
                }

                List<FaceEmbeddingRecord> embeddings = body.getEmbeddings();
                final int total = embeddings.size();
                final int[] completed = {0};
                final int[] downloaded = {0};
                final List<String> errors = new ArrayList<>();

                for (FaceEmbeddingRecord embeddingRecord : embeddings) {
                    dbService.getStaffRecordByihrisPIDAsync(embeddingRecord.getIhrisPid(), localRecord -> {
                        completed[0]++;
                        if (localRecord == null) {
                            Log.w(TAG, "No local record for ihris_pid: " + embeddingRecord.getIhrisPid());
                            callback.onProgress(completed[0], total,
                                    "Skipped " + embeddingRecord.getIhrisPid() + ": not in local DB");
                            if (completed[0] == total) {
                                callback.onComplete(0, downloaded[0], errors);
                            }
                            return;
                        }

                        // Skip if local record already has face_data and is synced (local takes precedence)
                        if (localRecord.getFaceData() != null && localRecord.isEmbeddingSynced()) {
                            callback.onProgress(completed[0], total,
                                    "Skipped " + embeddingRecord.getIhrisPid() + ": local data exists");
                            if (completed[0] == total) {
                                callback.onComplete(0, downloaded[0], errors);
                            }
                            return;
                        }

                        // Deserialize CSV to float[] and store on local record
                        float[] faceData = FloatArrayConverter.fromString(embeddingRecord.getFaceData());
                        localRecord.setFaceData(faceData);
                        localRecord.setFaceImage(embeddingRecord.getFaceImage());
                        localRecord.setFaceEnrolled(true);
                        localRecord.setEmbeddingSynced(true);

                        // Register with face engine
                        String registerResult = faceScanner.registerFaceFromBase64(
                                embeddingRecord.getFaceImage(), embeddingRecord.getIhrisPid());
                        if (registerResult == null || !registerResult.startsWith("SUCCESS")) {
                            String error = "Face engine registration failed for "
                                    + embeddingRecord.getIhrisPid() + ": " + registerResult;
                            Log.e(TAG, error);
                            errors.add(error);
                        }

                        dbService.updateStaffRecordAsync(localRecord, success -> {
                            if (success) {
                                downloaded[0]++;
                            } else {
                                errors.add("Failed to save embedding for " + embeddingRecord.getIhrisPid());
                            }
                            callback.onProgress(completed[0], total,
                                    "Downloaded embedding for " + embeddingRecord.getIhrisPid());
                            if (completed[0] == total) {
                                callback.onComplete(0, downloaded[0], errors);
                            }
                        });
                    });
                }
            }

            @Override
            public void onFailure(Call<FaceEmbeddingDownloadResponse> call, Throwable t) {
                Log.e(TAG, "Download failed: " + t.getMessage(), t);
                callback.onError("Download failed: " + t.getMessage());
            }
        });
    }
}
