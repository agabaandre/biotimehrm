package ug.go.health.ihrisbiometric.services;

import android.content.Context;
import android.util.Log;

import java.io.File;
import java.io.FileOutputStream;
import java.io.IOException;
import java.nio.file.Files;
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

    /** Returns the directory where fingerprint .fpt files are stored. */
    private File getFingerprintDir() {
        File dir = new File(context.getFilesDir(), "fingerprints");
        if (!dir.exists()) dir.mkdirs();
        return dir;
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
                // Read the .fpt file from disk
                String path = record.getFingerprintPath();
                if (path == null || path.isEmpty()) {
                    completed[0]++;
                    errors.add("No file path for " + record.getIhrisPid());
                    callback.onProgress(completed[0], total, "Skipped " + record.getIhrisPid() + ": no file path");
                    if (completed[0] == total) callback.onComplete(uploaded[0], 0, errors);
                    continue;
                }

                File file = new File(path);
                if (!file.exists()) {
                    completed[0]++;
                    errors.add("File missing for " + record.getIhrisPid() + ": " + path);
                    callback.onProgress(completed[0], total, "Skipped " + record.getIhrisPid() + ": file not found");
                    if (completed[0] == total) callback.onComplete(uploaded[0], 0, errors);
                    continue;
                }

                byte[] fileBytes;
                try {
                    fileBytes = Files.readAllBytes(file.toPath());
                } catch (IOException e) {
                    completed[0]++;
                    errors.add("Read error for " + record.getIhrisPid() + ": " + e.getMessage());
                    callback.onProgress(completed[0], total, "Read error for " + record.getIhrisPid());
                    if (completed[0] == total) callback.onComplete(uploaded[0], 0, errors);
                    continue;
                }

                // Base64-encode the file bytes for JSON transport
                String base64 = ByteArrayConverter.toString(fileBytes);
                FingerprintUploadRequest request = new FingerprintUploadRequest(record.getIhrisPid(), base64);

                apiService.uploadFingerprint(request).enqueue(new Callback<FingerprintUploadResponse>() {
                    @Override
                    public void onResponse(Call<FingerprintUploadResponse> call, Response<FingerprintUploadResponse> response) {
                        completed[0]++;
                        if (response.isSuccessful()) {
                            record.setFingerprintSynced(true);
                            dbService.updateStaffRecordAsync(record, success -> {
                                if (success) uploaded[0]++;
                                callback.onProgress(completed[0], total, "Uploaded fingerprint for " + record.getIhrisPid());
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
                    public void onFailure(Call<FingerprintUploadResponse> call, Throwable t) {
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

    public void downloadFingerprints(String facilityId, FingerprintSyncCallback callback) {
        apiService.getFingerprints(facilityId).enqueue(new Callback<FingerprintDownloadResponse>() {
            @Override
            public void onResponse(Call<FingerprintDownloadResponse> call, Response<FingerprintDownloadResponse> response) {
                if (!response.isSuccessful()) {
                    callback.onError("Download failed: HTTP " + response.code());
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
                            Log.w(TAG, "No local record for: " + fpRecord.getIhrisPid());
                            callback.onProgress(completed[0], total, "Skipped " + fpRecord.getIhrisPid() + ": not in local DB");
                            if (completed[0] == total) callback.onComplete(0, downloaded[0], errors);
                            return;
                        }

                        // Skip if already have a local file and it's synced
                        if (localRecord.getFingerprintPath() != null
                                && new File(localRecord.getFingerprintPath()).exists()
                                && localRecord.isFingerprintSynced()) {
                            callback.onProgress(completed[0], total, "Skipped " + fpRecord.getIhrisPid() + ": local file exists");
                            if (completed[0] == total) callback.onComplete(0, downloaded[0], errors);
                            return;
                        }

                        // Decode base64 → bytes → write to file
                        byte[] fileBytes = ByteArrayConverter.fromString(fpRecord.getFingerprintData());
                        if (fileBytes == null || fileBytes.length == 0) {
                            errors.add("Empty data for " + fpRecord.getIhrisPid());
                            if (completed[0] == total) callback.onComplete(0, downloaded[0], errors);
                            return;
                        }

                        String safeId = fpRecord.getIhrisPid().replaceAll("[^a-zA-Z0-9_-]", "_");
                        File destFile = new File(getFingerprintDir(), safeId + ".fpt");
                        try (FileOutputStream fos = new FileOutputStream(destFile)) {
                            fos.write(fileBytes);
                        } catch (IOException e) {
                            errors.add("Write error for " + fpRecord.getIhrisPid() + ": " + e.getMessage());
                            if (completed[0] == total) callback.onComplete(0, downloaded[0], errors);
                            return;
                        }

                        localRecord.setFingerprintPath(destFile.getAbsolutePath());
                        localRecord.setFingerprintEnrolled(true);
                        localRecord.setFingerprintSynced(true);
                        // Reset template_id to 0 so registerTemplatesOnScanner will
                        // re-register this fingerprint on this device's scanner
                        localRecord.setTemplateId(0);

                        dbService.updateStaffRecordAsync(localRecord, success -> {
                            if (success) downloaded[0]++;
                            else errors.add("DB update failed for " + fpRecord.getIhrisPid());
                            callback.onProgress(completed[0], total, "Downloaded fingerprint for " + fpRecord.getIhrisPid());
                            if (completed[0] == total) callback.onComplete(0, downloaded[0], errors);
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
            final int[] registered = {0};
            final List<String> failures = new ArrayList<>();

            // Process sequentially — each registration waits for the previous to finish
            registerNext(scanner, records, 0, total, registered, failures, callback);
        });
    }

    private void registerNext(ScannerLibrary scanner, List<StaffRecord> records,
                              int index, int total, int[] registered,
                              List<String> failures, ScannerRegistrationCallback callback) {
        if (index >= total) {
            callback.onComplete(registered[0], failures);
            return;
        }

        StaffRecord record = records.get(index);
        String path = record.getFingerprintPath();

        if (path == null || !new File(path).exists()) {
            failures.add("No file for " + record.getIhrisPid());
            callback.onProgress(index + 1, total, record.getIhrisPid());
            registerNext(scanner, records, index + 1, total, registered, failures, callback);
            return;
        }

        byte[] fileBytes;
        try {
            fileBytes = Files.readAllBytes(new File(path).toPath());
        } catch (IOException e) {
            failures.add("Read error for " + record.getIhrisPid() + ": " + e.getMessage());
            callback.onProgress(index + 1, total, record.getIhrisPid());
            registerNext(scanner, records, index + 1, total, registered, failures, callback);
            return;
        }

        // Use the new async method that runs entirely on the scanner executor
        scanner.registerTemplateAsync(fileBytes, (assignedSlot, error) -> {
            if (assignedSlot > 0) {
                record.setTemplateId(assignedSlot);
                dbService.updateStaffRecordAsync(record, success -> {
                    if (success) registered[0]++;
                    else failures.add("DB update failed for " + record.getIhrisPid());
                    callback.onProgress(index + 1, total, record.getIhrisPid());
                    registerNext(scanner, records, index + 1, total, registered, failures, callback);
                });
            } else {
                failures.add("Scanner registration failed for " + record.getIhrisPid() + ": " + error);
                callback.onProgress(index + 1, total, record.getIhrisPid());
                registerNext(scanner, records, index + 1, total, registered, failures, callback);
            }
        });
    }

    public interface ScannerRegistrationCallback {
        void onProgress(int completed, int total, String ihrisPid);
        void onComplete(int registered, List<String> failures);
        void onError(String errorMessage);
    }
}
