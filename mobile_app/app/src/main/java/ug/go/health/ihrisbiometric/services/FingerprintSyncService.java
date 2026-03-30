package ug.go.health.ihrisbiometric.services;

import android.content.Context;
import android.util.Log;

import java.util.ArrayList;
import java.util.List;

import retrofit2.Call;
import retrofit2.Callback;
import retrofit2.Response;
import ug.go.health.ihrisbiometric.converters.ByteArrayConverter;
import ug.go.health.ihrisbiometric.models.FingerprintDownloadResponse;
import ug.go.health.ihrisbiometric.models.FingerprintRecord;
import ug.go.health.ihrisbiometric.models.FingerprintUploadRequest;
import ug.go.health.ihrisbiometric.models.FingerprintUploadResponse;
import ug.go.health.ihrisbiometric.models.StaffRecord;
import ug.go.health.library.ScannerLibrary;

public class FingerprintSyncService {

    private static final String TAG = "FingerprintSyncService";

    private final Context context;
    private final ApiInterface apiService;
    private final DbService dbService;

    public FingerprintSyncService(Context context, ApiInterface apiService, DbService dbService) {
        this.context = context;
        this.apiService = apiService;
        this.dbService = dbService;
    }

    public interface FingerprintSyncCallback {
        void onProgress(int completed, int total, String message);
        void onComplete(int uploaded, int downloaded, List<String> errors);
        void onError(String errorMessage);
    }

    public void uploadFingerprints(FingerprintSyncCallback callback) {
        dbService.getStaffRecordsWithUnsyncedFingerprintsAsync(records -> {
            if (records == null || records.isEmpty()) {
                callback.onComplete(0, 0, new ArrayList<>());
                return;
            }
            final int total = records.size();
            final int[] completed = {0};
            final int[] uploaded = {0};
            final List<String> errors = new ArrayList<>();

            for (StaffRecord record : records) {
                String base64 = ByteArrayConverter.toString(record.getFingerprintData());
                FingerprintUploadRequest request = new FingerprintUploadRequest(record.getIhrisPid(), base64);

                apiService.uploadFingerprint(request).enqueue(new Callback<FingerprintUploadResponse>() {
                    @Override
                    public void onResponse(Call<FingerprintUploadResponse> call, Response<FingerprintUploadResponse> response) {
                        completed[0]++;
                        if (response.isSuccessful()) {
                            record.setFingerprintSynced(true);
                            dbService.updateStaffRecordAsync(record, success -> {
                                if (success) {
                                    uploaded[0]++;
                                }
                                callback.onProgress(completed[0], total, "Uploaded fingerprint for " + record.getIhrisPid());
                                if (completed[0] == total) {
                                    callback.onComplete(uploaded[0], 0, errors);
                                }
                            });
                        } else {
                            String error = "Upload failed for " + record.getIhrisPid() + ": server error " + response.code();
                            Log.e(TAG, error);
                            errors.add(error);
                            callback.onProgress(completed[0], total, error);
                            if (completed[0] == total) {
                                callback.onComplete(uploaded[0], 0, errors);
                            }
                        }
                    }

                    @Override
                    public void onFailure(Call<FingerprintUploadResponse> call, Throwable t) {
                        completed[0]++;
                        String error = "Upload failed for " + record.getIhrisPid() + ": " + t.getMessage();
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

    public void downloadFingerprints(String facilityId, FingerprintSyncCallback callback) {
        apiService.getFingerprints(facilityId).enqueue(new Callback<FingerprintDownloadResponse>() {
            @Override
            public void onResponse(Call<FingerprintDownloadResponse> call, Response<FingerprintDownloadResponse> response) {
                if (!response.isSuccessful()) {
                    callback.onError("Download failed: server error " + response.code());
                    return;
                }
                FingerprintDownloadResponse body = response.body();
                if (body == null || body.getFingerprints() == null || body.getFingerprints().isEmpty()) {
                    callback.onComplete(0, 0, new ArrayList<>());
                    return;
                }

                List<FingerprintRecord> fingerprints = body.getFingerprints();
                final int total = fingerprints.size();
                final int[] completed = {0};
                final int[] downloaded = {0};
                final List<String> errors = new ArrayList<>();

                for (FingerprintRecord fpRecord : fingerprints) {
                    dbService.getStaffRecordByihrisPIDAsync(fpRecord.getIhrisPid(), localRecord -> {
                        completed[0]++;
                        if (localRecord == null) {
                            Log.w(TAG, "No local record for ihris_pid: " + fpRecord.getIhrisPid());
                            callback.onProgress(completed[0], total, "Skipped " + fpRecord.getIhrisPid() + ": not in local DB");
                            if (completed[0] == total) {
                                callback.onComplete(0, downloaded[0], errors);
                            }
                            return;
                        }

                        // Skip if local record already has fingerprint data and is synced (local takes precedence)
                        if (localRecord.getFingerprintData() != null && localRecord.isFingerprintSynced()) {
                            callback.onProgress(completed[0], total, "Skipped " + fpRecord.getIhrisPid() + ": local data exists");
                            if (completed[0] == total) {
                                callback.onComplete(0, downloaded[0], errors);
                            }
                            return;
                        }

                        // Store server fingerprint data locally
                        byte[] fingerprintBytes = ByteArrayConverter.fromString(fpRecord.getFingerprintData());
                        localRecord.setFingerprintData(fingerprintBytes);
                        localRecord.setFingerprintEnrolled(true);
                        localRecord.setFingerprintSynced(true);

                        dbService.updateStaffRecordAsync(localRecord, success -> {
                            if (success) {
                                downloaded[0]++;
                            } else {
                                errors.add("Failed to save fingerprint for " + fpRecord.getIhrisPid());
                            }
                            callback.onProgress(completed[0], total, "Downloaded fingerprint for " + fpRecord.getIhrisPid());
                            if (completed[0] == total) {
                                callback.onComplete(0, downloaded[0], errors);
                            }
                        });
                    });
                }
            }

            @Override
            public void onFailure(Call<FingerprintDownloadResponse> call, Throwable t) {
                Log.e(TAG, "Download failed: " + t.getMessage(), t);
                callback.onError("Download failed: " + t.getMessage());
            }
        });
    }

    public void registerTemplatesOnScanner(ScannerLibrary scanner, ScannerRegistrationCallback callback) {
        dbService.getStaffRecordsWithUnregisteredFingerprintsAsync(records -> {
            if (records == null || records.isEmpty()) {
                callback.onComplete(0, new ArrayList<>());
                return;
            }

            final int total = records.size();
            final int[] completed = {0};
            final int[] registered = {0};
            final List<String> failures = new ArrayList<>();

            for (StaffRecord record : records) {
                try {
                    // Get next available template ID on the scanner
                    int templateId = record.getId(); // Use staff record ID as template slot
                    boolean written = scanner.WriteTemplateFile(templateId, record.getFingerprintData());
                    if (!written) {
                        String error = "Failed to write template file for " + record.getIhrisPid();
                        Log.e(TAG, error);
                        failures.add(error);
                        completed[0]++;
                        callback.onProgress(completed[0], total, record.getIhrisPid());
                        continue;
                    }

                    int result = scanner.Run_CmdWriteTemplate(templateId);
                    if (result == 0) {
                        record.setTemplateId(templateId);
                        dbService.updateStaffRecordAsync(record, success -> {
                            completed[0]++;
                            if (success) {
                                registered[0]++;
                            } else {
                                failures.add("DB update failed for " + record.getIhrisPid());
                            }
                            callback.onProgress(completed[0], total, record.getIhrisPid());
                            if (completed[0] == total) {
                                callback.onComplete(registered[0], failures);
                            }
                        });
                    } else {
                        String error = "Scanner registration failed for " + record.getIhrisPid() + " (code: " + result + ")";
                        Log.e(TAG, error);
                        failures.add(error);
                        completed[0]++;
                        callback.onProgress(completed[0], total, record.getIhrisPid());
                    }
                } catch (Exception e) {
                    String error = "Exception registering " + record.getIhrisPid() + ": " + e.getMessage();
                    Log.e(TAG, error, e);
                    failures.add(error);
                    completed[0]++;
                    callback.onProgress(completed[0], total, record.getIhrisPid());
                }
            }

            // If all completed synchronously (no async DB updates pending)
            if (completed[0] == total) {
                callback.onComplete(registered[0], failures);
            }
        });
    }

    public interface ScannerRegistrationCallback {
        void onProgress(int completed, int total, String ihrisPid);
        void onComplete(int registered, List<String> failures);
        void onError(String errorMessage);
    }
}
