package ug.go.health.ihrisbiometric.viewmodels;

import android.app.Application;
import android.util.Log;

import androidx.annotation.NonNull;
import androidx.lifecycle.AndroidViewModel;
import androidx.lifecycle.LiveData;
import androidx.lifecycle.MutableLiveData;

import org.json.JSONObject;

import java.util.ArrayList;
import java.util.List;
import java.util.concurrent.ExecutorService;
import java.util.concurrent.Executors;
import java.util.concurrent.atomic.AtomicInteger;

import okhttp3.ResponseBody;
import retrofit2.Call;
import retrofit2.Callback;
import retrofit2.Response;
import ug.go.health.ihrisbiometric.models.ClockHistory;
import ug.go.health.ihrisbiometric.models.StaffRecord;
import ug.go.health.ihrisbiometric.services.ApiInterface;
import ug.go.health.ihrisbiometric.services.ApiService;
import ug.go.health.ihrisbiometric.services.DbService;
import ug.go.health.ihrisbiometric.services.EmbeddingSyncService;
import ug.go.health.ihrisbiometric.services.FaceScanner;
import ug.go.health.ihrisbiometric.services.FingerprintSyncService;
import ug.go.health.ihrisbiometric.services.SessionService;

public class DataSyncViewModel extends AndroidViewModel {
    private static final String TAG = "DataSyncViewModel";

    private final DbService dbService;
    private final ApiInterface apiService;
    private final ExecutorService executorService;

    private final MutableLiveData<SyncStatus> syncStatusLiveData = new MutableLiveData<>(SyncStatus.IDLE);

    private final MutableLiveData<Integer> staffSyncProgressLiveData = new MutableLiveData<>(0);
    private final MutableLiveData<Integer> clockSyncProgressLiveData = new MutableLiveData<>(0);
    private final MutableLiveData<Integer> syncProgressLiveData = new MutableLiveData<>(0);

    private final MutableLiveData<List<String>> syncMessagesLiveData = new MutableLiveData<>(new ArrayList<>());

    private final MutableLiveData<Integer> syncedStaffCountLiveData = new MutableLiveData<>(0);
    private final MutableLiveData<Integer> unsyncedStaffCountLiveData = new MutableLiveData<>(0);
    private final MutableLiveData<Integer> syncedClockCountLiveData = new MutableLiveData<>(0);
    private final MutableLiveData<Integer> unsyncedClockCountLiveData = new MutableLiveData<>(0);

    private final MutableLiveData<List<StaffRecord>> staffRecordsReadyForSyncLiveData = new MutableLiveData<>();
    private final MutableLiveData<List<StaffRecord>> staffRecordsMissingInfoLiveData = new MutableLiveData<>();
    private final MutableLiveData<List<ClockHistory>> clockHistoryReadyForSyncLiveData = new MutableLiveData<>();

    private final MutableLiveData<Integer> fingerprintSyncProgressLiveData = new MutableLiveData<>(0);
    private final MutableLiveData<Integer> fingerprintUploadCountLiveData = new MutableLiveData<>(0);
    private final MutableLiveData<Integer> fingerprintDownloadCountLiveData = new MutableLiveData<>(0);

    private final MutableLiveData<Integer> embeddingSyncProgressLiveData = new MutableLiveData<>(0);
    private final MutableLiveData<Integer> embeddingUploadCountLiveData = new MutableLiveData<>(0);
    private final MutableLiveData<Integer> embeddingDownloadCountLiveData = new MutableLiveData<>(0);
    private final MutableLiveData<Integer> embeddingSyncedCountLiveData = new MutableLiveData<>(0);
    private final MutableLiveData<Integer> unsyncedEmbeddingCountLiveData = new MutableLiveData<>(0);

    private final AtomicInteger totalItemsToSync = new AtomicInteger(0);
    private final AtomicInteger syncedItemsCount = new AtomicInteger(0);

    private final AtomicInteger syncedStaffCount = new AtomicInteger(0);
    private final AtomicInteger syncedClockCount = new AtomicInteger(0);

    private SessionService sessionService;
    private FingerprintSyncService fingerprintSyncService;
    private EmbeddingSyncService embeddingSyncService;

    public DataSyncViewModel(@NonNull Application application) {
        super(application);
        dbService = new DbService(application.getApplicationContext());
        sessionService = new SessionService(application.getApplicationContext());

        // Initialize ApiInterface
        apiService = ApiService.getApiInterface(application.getApplicationContext());

        executorService = Executors.newSingleThreadExecutor();

        fingerprintSyncService = new FingerprintSyncService(application.getApplicationContext(), apiService, dbService);

        // Initialize FaceScanner and EmbeddingSyncService
        FaceScanner faceScanner = new FaceScanner();
        faceScanner.initEngine(application.getApplicationContext());
        embeddingSyncService = new EmbeddingSyncService(application.getApplicationContext(), apiService, dbService, faceScanner);

        updateSyncCounts();
        updateSyncCategories();
    }

    public LiveData<SyncStatus> getSyncStatus() {
        return syncStatusLiveData;
    }

    public LiveData<Integer> getStaffSyncProgress() {
        return staffSyncProgressLiveData;
    }

    public LiveData<Integer> getClockSyncProgress() {
        return clockSyncProgressLiveData;
    }

    public LiveData<List<String>> getSyncMessages() {
        return syncMessagesLiveData;
    }

    public LiveData<Integer> getSyncedStaffCount() {
        return syncedStaffCountLiveData;
    }

    public LiveData<Integer> getUnsyncedStaffCount() {
        return unsyncedStaffCountLiveData;
    }

    public LiveData<Integer> getSyncedClockCount() {
        return syncedClockCountLiveData;
    }

    public LiveData<Integer> getUnsyncedClockCount() {
        return unsyncedClockCountLiveData;
    }

    public LiveData<List<StaffRecord>> getStaffRecordsReadyForSync() {
        return staffRecordsReadyForSyncLiveData;
    }

    public LiveData<List<StaffRecord>> getStaffRecordsMissingInfo() {
        return staffRecordsMissingInfoLiveData;
    }

    public LiveData<List<ClockHistory>> getClockHistoryReadyForSync() {
        return clockHistoryReadyForSyncLiveData;
    }

    public LiveData<Integer> getFingerprintSyncProgress() {
        return fingerprintSyncProgressLiveData;
    }

    public LiveData<Integer> getFingerprintUploadCount() {
        return fingerprintUploadCountLiveData;
    }

    public LiveData<Integer> getFingerprintDownloadCount() {
        return fingerprintDownloadCountLiveData;
    }

    public LiveData<Integer> getEmbeddingSyncProgress() {
        return embeddingSyncProgressLiveData;
    }

    public LiveData<Integer> getEmbeddingUploadCount() {
        return embeddingUploadCountLiveData;
    }

    public LiveData<Integer> getEmbeddingDownloadCount() {
        return embeddingDownloadCountLiveData;
    }

    public LiveData<Integer> getEmbeddingSyncedCount() {
        return embeddingSyncedCountLiveData;
    }

    public LiveData<Integer> getUnsyncedEmbeddingCount() {
        return unsyncedEmbeddingCountLiveData;
    }

    public void startSync() {
        if (syncStatusLiveData.getValue() == SyncStatus.IN_PROGRESS) {
            return; // Prevent multiple syncs
        }

        syncStatusLiveData.setValue(SyncStatus.IN_PROGRESS);
        syncedItemsCount.set(0);
        syncedStaffCount.set(0);
        syncedClockCount.set(0);
        syncProgressLiveData.setValue(0);
        List<String> messages = new ArrayList<>();
        messages.add("Starting sync...");
        syncMessagesLiveData.postValue(messages);

        executorService.execute(this::performSync);
    }

    private void performSync() {
        try {
            updateSyncMessage("Fetching unsynced records...");
            dbService.getUnsyncedStaffRecordsAsync(this::handleUnsyncedStaffRecords);
        } catch (Exception e) {
            Log.e(TAG, "Sync failed", e);
            syncStatusLiveData.postValue(SyncStatus.FAILED);
            updateSyncMessage("Sync failed: " + e.getMessage());
        }
    }

    private void handleUnsyncedStaffRecords(List<StaffRecord> unsyncedStaffRecords) {
        dbService.getUnsyncedClockHistoryAsync(unsyncedClockRecords -> {
            totalItemsToSync.set(unsyncedStaffRecords.size() + unsyncedClockRecords.size());

            if (totalItemsToSync.get() == 0 && unsyncedStaffRecords.isEmpty()) {
                // Still run fingerprint and embedding sync even if no staff/clock records to sync
                runFingerprintSync(() -> runEmbeddingSync(() -> {
                    syncStatusLiveData.postValue(SyncStatus.COMPLETED);
                    List<String> messages = syncMessagesLiveData.getValue();
                    messages.add("Sync completed");
                    syncMessagesLiveData.postValue(messages);
                    updateSyncCounts();
                    updateSyncCategories();
                }));
                return;
            }

            syncStaffRecords(unsyncedStaffRecords);
            // Fingerprint sync runs after staff sync, then embedding sync, then clock sync follows
            runFingerprintSync(() -> runEmbeddingSync(() -> syncClockRecords(unsyncedClockRecords)));
        });
    }

    private void runFingerprintSync(Runnable onComplete) {
        List<String> messages = syncMessagesLiveData.getValue();
        messages.add("Syncing fingerprint templates...");
        syncMessagesLiveData.postValue(messages);

        String facilityId = sessionService.getFacilityId();

        fingerprintSyncService.uploadFingerprints(new FingerprintSyncService.FingerprintSyncCallback() {
            @Override
            public void onProgress(int completed, int total, String message) {
                if (total > 0) {
                    int progress = (int) ((completed / (float) total) * 50); // Upload is 0-50%
                    fingerprintSyncProgressLiveData.postValue(progress);
                }
            }

            @Override
            public void onComplete(int uploaded, int downloaded, java.util.List<String> errors) {
                fingerprintUploadCountLiveData.postValue(uploaded);
                List<String> msgs = syncMessagesLiveData.getValue();
                if (uploaded > 0) {
                    msgs.add("Uploaded " + uploaded + " fingerprint template(s)");
                }
                if (!errors.isEmpty()) {
                    msgs.add("Fingerprint upload errors: " + errors.size());
                }
                syncMessagesLiveData.postValue(msgs);

                // Now download
                fingerprintSyncService.downloadFingerprints(facilityId, new FingerprintSyncService.FingerprintSyncCallback() {
                    @Override
                    public void onProgress(int completed, int total, String message) {
                        if (total > 0) {
                            int progress = 50 + (int) ((completed / (float) total) * 50); // Download is 50-100%
                            fingerprintSyncProgressLiveData.postValue(progress);
                        }
                    }

                    @Override
                    public void onComplete(int uploaded2, int downloaded2, java.util.List<String> errors2) {
                        fingerprintDownloadCountLiveData.postValue(downloaded2);
                        fingerprintSyncProgressLiveData.postValue(100);
                        List<String> msgs2 = syncMessagesLiveData.getValue();
                        if (downloaded2 > 0) {
                            msgs2.add("Downloaded " + downloaded2 + " fingerprint template(s)");
                        }
                        if (!errors2.isEmpty()) {
                            msgs2.add("Fingerprint download errors: " + errors2.size());
                        }
                        syncMessagesLiveData.postValue(msgs2);
                        updateSyncCounts();
                        onComplete.run();
                    }

                    @Override
                    public void onError(String errorMessage) {
                        Log.e(TAG, "Fingerprint download failed: " + errorMessage);
                        List<String> msgs2 = syncMessagesLiveData.getValue();
                        msgs2.add("Fingerprint download failed: " + errorMessage);
                        syncMessagesLiveData.postValue(msgs2);
                        onComplete.run(); // Continue to clock sync even on failure
                    }
                });
            }

            @Override
            public void onError(String errorMessage) {
                Log.e(TAG, "Fingerprint upload failed: " + errorMessage);
                List<String> msgs = syncMessagesLiveData.getValue();
                msgs.add("Fingerprint upload failed: " + errorMessage);
                syncMessagesLiveData.postValue(msgs);
                onComplete.run(); // Continue to clock sync even on failure
            }
        });
    }

    private void runEmbeddingSync(Runnable onComplete) {
        List<String> messages = syncMessagesLiveData.getValue();
        messages.add("Syncing face embeddings...");
        syncMessagesLiveData.postValue(messages);

        String facilityId = sessionService.getFacilityId();

        embeddingSyncService.uploadEmbeddings(new EmbeddingSyncService.EmbeddingSyncCallback() {
            @Override
            public void onProgress(int completed, int total, String message) {
                if (total > 0) {
                    int progress = (int) ((completed / (float) total) * 50); // Upload is 0-50%
                    embeddingSyncProgressLiveData.postValue(progress);
                }
            }

            @Override
            public void onComplete(int uploaded, int downloaded, java.util.List<String> errors) {
                embeddingUploadCountLiveData.postValue(uploaded);
                List<String> msgs = syncMessagesLiveData.getValue();
                if (uploaded > 0) {
                    msgs.add("Uploaded " + uploaded + " face embedding(s)");
                }
                if (!errors.isEmpty()) {
                    msgs.add("Embedding upload errors: " + errors.size());
                }
                syncMessagesLiveData.postValue(msgs);

                // Now download
                embeddingSyncService.downloadEmbeddings(facilityId, new EmbeddingSyncService.EmbeddingSyncCallback() {
                    @Override
                    public void onProgress(int completed, int total, String message) {
                        if (total > 0) {
                            int progress = 50 + (int) ((completed / (float) total) * 50); // Download is 50-100%
                            embeddingSyncProgressLiveData.postValue(progress);
                        }
                    }

                    @Override
                    public void onComplete(int uploaded2, int downloaded2, java.util.List<String> errors2) {
                        embeddingDownloadCountLiveData.postValue(downloaded2);
                        embeddingSyncProgressLiveData.postValue(100);
                        List<String> msgs2 = syncMessagesLiveData.getValue();
                        if (downloaded2 > 0) {
                            msgs2.add("Downloaded " + downloaded2 + " face embedding(s)");
                        }
                        if (!errors2.isEmpty()) {
                            msgs2.add("Embedding download errors: " + errors2.size());
                        }
                        syncMessagesLiveData.postValue(msgs2);
                        updateSyncCounts();
                        onComplete.run();
                    }

                    @Override
                    public void onError(String errorMessage) {
                        Log.e(TAG, "Embedding download failed: " + errorMessage);
                        List<String> msgs2 = syncMessagesLiveData.getValue();
                        msgs2.add("Embedding download failed: " + errorMessage);
                        syncMessagesLiveData.postValue(msgs2);
                        onComplete.run(); // Continue to clock sync even on failure
                    }
                });
            }

            @Override
            public void onError(String errorMessage) {
                Log.e(TAG, "Embedding upload failed: " + errorMessage);
                List<String> msgs = syncMessagesLiveData.getValue();
                msgs.add("Embedding upload failed: " + errorMessage);
                syncMessagesLiveData.postValue(msgs);
                onComplete.run(); // Continue to clock sync even on failure
            }
        });
    }

    private void syncStaffRecords(List<StaffRecord> unsyncedStaffRecords) {
        updateSyncMessage("Syncing staff records...");
        for (StaffRecord staffRecord : unsyncedStaffRecords) {
            if (staffRecord.isDeleted()) {
                syncDeletedStaff(staffRecord);
            } else if (staffRecord.getIhrisPid() != null && staffRecord.getIhrisPid().startsWith("LOCAL_")) {
                syncNewStaff(staffRecord);
            } else {
                syncUpdatedStaff(staffRecord);
            }
        }
    }

    private void syncNewStaff(StaffRecord staffRecord) {
        apiService.createStaff(staffRecord).enqueue(new StaffSyncCallback(staffRecord));
    }

    private void syncUpdatedStaff(StaffRecord staffRecord) {
        apiService.updateStaff(staffRecord.getId(), staffRecord).enqueue(new StaffSyncCallback(staffRecord));
    }

    private void syncDeletedStaff(StaffRecord staffRecord) {
        if (staffRecord.getIhrisPid() != null && staffRecord.getIhrisPid().startsWith("LOCAL_")) {
            dbService.deleteStaffRecordLocallyAsync(staffRecord.getId(), success -> updateStaffSyncProgress());
        } else {
            apiService.deleteStaff(staffRecord.getId()).enqueue(new Callback<ResponseBody>() {
                @Override
                public void onResponse(Call<ResponseBody> call, Response<ResponseBody> response) {
                    if (response.isSuccessful()) {
                        dbService.deleteStaffRecordLocallyAsync(staffRecord.getId(), success -> updateStaffSyncProgress());
                    } else {
                        handleSyncError("Failed to delete staff on server: " + staffRecord.getName());
                    }
                }

                @Override
                public void onFailure(Call<ResponseBody> call, Throwable t) {
                    handleSyncError("Failed to delete staff: " + t.getMessage());
                }
            });
        }
    }

    private class StaffSyncCallback implements Callback<StaffRecord> {
        private final StaffRecord staffRecord;

        StaffSyncCallback(StaffRecord staffRecord) {
            this.staffRecord = staffRecord;
        }

        @Override
        public void onResponse(Call<StaffRecord> call, Response<StaffRecord> response) {
            if (response.isSuccessful() && response.body() != null) {
                StaffRecord syncedStaff = response.body();
                syncedStaff.setSynced(true);
                dbService.updateStaffRecordAsync(syncedStaff, success -> updateStaffSyncProgress());
            } else {
                handleSyncError("Sync failed for staff record: " + staffRecord.getName());
            }
        }

        @Override
        public void onFailure(Call<StaffRecord> call, Throwable t) {
            handleSyncError("Failed to sync staff record: " + t.getMessage());
        }
    }

    private void syncClockRecords(List<ClockHistory> unsyncedClockRecords) {
        updateSyncMessage("Syncing clock records...");
        for (ClockHistory clockHistory : unsyncedClockRecords) {
            apiService.syncClockHistory(clockHistory).enqueue(new Callback<ClockHistory>() {
                @Override
                public void onResponse(Call<ClockHistory> call, Response<ClockHistory> response) {
                    if (response.isSuccessful()) {
                        clockHistory.setSynced(true);
                        dbService.updateClockHistoryAsync(clockHistory, (result) -> updateClockSyncProgress());
                    } else {
                        handleSyncError("Sync failed for clock history");
                    }
                }

                @Override
                public void onFailure(Call<ClockHistory> call, Throwable t) {
                    handleSyncError("Failed to sync clock record: " + t.getMessage());
                }
            });
        }
    }

    private void updateSyncMessage(String message) {
        List<String> messages = syncMessagesLiveData.getValue();
        if (messages == null) messages = new ArrayList<>();
        messages.add(message);
        syncMessagesLiveData.postValue(messages);
    }

    private void handleSyncError(String errorMessage) {
        Log.e(TAG, errorMessage);
        syncStatusLiveData.postValue(SyncStatus.FAILED);
        updateSyncMessage(errorMessage);
    }

    private void updateStaffSyncProgress() {
        int synced = syncedItemsCount.incrementAndGet();
        int progress = (int) ((synced / (float) totalItemsToSync.get()) * 100);
        staffSyncProgressLiveData.postValue(progress);
        syncProgressLiveData.postValue(progress);
        checkSyncCompletion();
    }

    private void updateClockSyncProgress() {
        int synced = syncedItemsCount.incrementAndGet();
        int progress = (int) ((synced / (float) totalItemsToSync.get()) * 100);
        clockSyncProgressLiveData.postValue(progress);
        syncProgressLiveData.postValue(progress);
        checkSyncCompletion();
    }

    private void checkSyncCompletion() {
        if (syncedItemsCount.get() == totalItemsToSync.get()) {
            syncStatusLiveData.postValue(SyncStatus.COMPLETED);
            updateSyncMessage("Sync completed successfully");
            updateSyncCounts();
            updateSyncCategories();
        }
    }

    public void updateSyncCounts() {
        dbService.countSyncedStaffRecordsAsync(syncedStaffCountLiveData::postValue);
        dbService.countUnsyncedStaffRecordsAsync(unsyncedStaffCountLiveData::postValue);
        dbService.countSyncedClockRecordsAsync(syncedClockCountLiveData::postValue);
        dbService.countUnsyncedClockRecordsAsync(unsyncedClockCountLiveData::postValue);
        dbService.countUnsyncedFingerprintsAsync(count -> fingerprintUploadCountLiveData.postValue(count));
        dbService.countSyncedFingerprintsAsync(count -> fingerprintDownloadCountLiveData.postValue(count));
        dbService.countSyncedEmbeddingsAsync(embeddingSyncedCountLiveData::postValue);
        dbService.countUnsyncedEmbeddingsAsync(unsyncedEmbeddingCountLiveData::postValue);
    }

    public void updateSyncCategories() {
        dbService.getStaffRecordsReadyForSyncAsync(staffRecordsReadyForSyncLiveData::postValue);
        dbService.getStaffRecordsMissingInfoAsync(staffRecordsMissingInfoLiveData::postValue);
        dbService.getUnsyncedClockHistoryAsync(clockHistoryReadyForSyncLiveData::postValue);
    }

    public enum SyncStatus {
        IDLE, IN_PROGRESS, COMPLETED, FAILED
    }

    @Override
    protected void onCleared() {
        super.onCleared();
        executorService.shutdown();
    }
}
