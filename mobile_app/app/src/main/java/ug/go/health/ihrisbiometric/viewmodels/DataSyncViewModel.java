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
import ug.go.health.ihrisbiometric.models.AllFacilitiesListResponse;
import ug.go.health.ihrisbiometric.models.AllFacilityRecord;
import ug.go.health.ihrisbiometric.models.CadresListResponse;
import ug.go.health.ihrisbiometric.models.ClockHistory;
import ug.go.health.ihrisbiometric.models.DistrictsListResponse;
import ug.go.health.ihrisbiometric.models.FacilityListResponse;
import ug.go.health.ihrisbiometric.models.FacilityRecord;
import ug.go.health.ihrisbiometric.models.JobsListResponse;
import ug.go.health.ihrisbiometric.models.ReasonRecord;
import ug.go.health.ihrisbiometric.models.ReasonsListResponse;
import ug.go.health.ihrisbiometric.models.StaffListResponse;
import ug.go.health.ihrisbiometric.models.StaffRecord;
import ug.go.health.ihrisbiometric.services.ApiInterface;
import ug.go.health.ihrisbiometric.services.ApiService;
import ug.go.health.ihrisbiometric.services.DbService;
import ug.go.health.ihrisbiometric.services.EmbeddingSyncService;
import ug.go.health.ihrisbiometric.services.FaceScanner;
import ug.go.health.ihrisbiometric.services.FingerprintSyncService;
import ug.go.health.ihrisbiometric.services.SessionService;
import ug.go.health.library.ScannerLibrary;
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
    private HomeViewModel homeViewModel; // source of the scanner reference

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

    /** Call from DataSyncFragment to give access to the shared HomeViewModel scanner reference. */
    public void setHomeViewModel(HomeViewModel homeViewModel) {
        this.homeViewModel = homeViewModel;
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
            updateSyncMessage("Downloading reference data...");
            downloadReferenceData(() -> {
                updateSyncMessage("Fetching unsynced records...");
                dbService.getUnsyncedStaffRecordsAsync(this::handleUnsyncedStaffRecords);
            });
        } catch (Exception e) {
            Log.e(TAG, "Sync failed", e);
            syncStatusLiveData.postValue(SyncStatus.FAILED);
            updateSyncMessage("Sync failed: " + e.getMessage());
        }
    }

    /**
     * Download staff list, facilities, reasons, cadres, districts, all facilities, and jobs
     * from server before uploading local changes.
     */
    private void downloadReferenceData(Runnable onComplete) {
        downloadStaffList(() ->
            downloadFacilities(() ->
                downloadReasons(() ->
                    downloadCadres(() ->
                        downloadDistricts(() ->
                            downloadAllFacilities(() ->
                                downloadJobs(onComplete)
                            )
                        )
                    )
                )
            )
        );
    }

    private void downloadStaffList(Runnable onComplete) {
        updateSyncMessage("Downloading staff list...");
        apiService.getStaffList().enqueue(new Callback<StaffListResponse>() {
            @Override
            public void onResponse(Call<StaffListResponse> call, Response<StaffListResponse> response) {
                if (response.isSuccessful() && response.body() != null && response.body().getStaff() != null) {
                    List<StaffRecord> serverStaff = response.body().getStaff();
                    mergeServerStaff(serverStaff, () -> {
                        updateSyncMessage("Downloaded " + serverStaff.size() + " staff records");
                        onComplete.run();
                    });
                } else {
                    updateSyncMessage("Staff download skipped (no data or error)");
                    onComplete.run();
                }
            }

            @Override
            public void onFailure(Call<StaffListResponse> call, Throwable t) {
                Log.e(TAG, "Staff list download failed: " + t.getMessage());
                updateSyncMessage("Staff download failed: " + t.getMessage());
                onComplete.run(); // Continue sync even on failure
            }
        });
    }

    private void mergeServerStaff(List<StaffRecord> serverStaff, Runnable onComplete) {
        if (serverStaff == null || serverStaff.isEmpty()) {
            onComplete.run();
            return;
        }

        final int[] processed = {0};
        final int total = serverStaff.size();

        for (StaffRecord serverRecord : serverStaff) {
            dbService.getStaffRecordByihrisPIDAsync(serverRecord.getIhrisPid(), localRecord -> {
                if (localRecord == null) {
                    // New record from server — insert locally as synced
                    serverRecord.setSynced(true);
                    dbService.saveStaffRecordAsync(serverRecord, success -> {
                        processed[0]++;
                        if (processed[0] == total) onComplete.run();
                    });
                } else {
                    // Record exists locally — only update if local is already synced
                    // (don't overwrite local unsynced changes)
                    if (localRecord.isSynced()) {
                        serverRecord.setId(localRecord.getId());
                        serverRecord.setSynced(true);
                        // Always preserve local biometric data and sync flags —
                        // local data takes precedence over server data
                        serverRecord.setFingerprintPath(localRecord.getFingerprintPath());
                        serverRecord.setFingerprintEnrolled(localRecord.isFingerprintEnrolled());
                        serverRecord.setFingerprintSynced(localRecord.isFingerprintSynced());
                        serverRecord.setTemplateId(localRecord.getTemplateId());
                        serverRecord.setFaceData(localRecord.getFaceData());
                        serverRecord.setFaceEnrolled(localRecord.isFaceEnrolled());
                        serverRecord.setEmbeddingSynced(localRecord.isEmbeddingSynced());
                        serverRecord.setFaceImage(localRecord.getFaceImage());
                        dbService.updateStaffRecordAsync(serverRecord, success -> {
                            processed[0]++;
                            if (processed[0] == total) onComplete.run();
                        });
                    } else {
                        // Local has unsynced changes — skip server update
                        processed[0]++;
                        if (processed[0] == total) onComplete.run();
                    }
                }
            });
        }
    }

    private void downloadFacilities(Runnable onComplete) {
        apiService.getFacilities().enqueue(new Callback<FacilityListResponse>() {
            @Override
            public void onResponse(Call<FacilityListResponse> call, Response<FacilityListResponse> response) {
                if (response.isSuccessful() && response.body() != null && response.body().getFacilities() != null) {
                    List<FacilityRecord> facilities = new ArrayList<>();
                    // Add current user's facility first
                    if (sessionService.getCurrentUser() != null) {
                        FacilityRecord currentFacility = new FacilityRecord();
                        currentFacility.setFacility(sessionService.getCurrentUser().getFacilityName());
                        currentFacility.setFacilityId(sessionService.getCurrentUser().getFacilityId());
                        facilities.add(currentFacility);
                    }
                    facilities.addAll(response.body().getFacilities());
                    sessionService.setFacilities(facilities);
                    updateSyncMessage("Downloaded " + facilities.size() + " facilities");
                }
                onComplete.run();
            }

            @Override
            public void onFailure(Call<FacilityListResponse> call, Throwable t) {
                Log.e(TAG, "Facilities download failed: " + t.getMessage());
                onComplete.run();
            }
        });
    }

    private void downloadReasons(Runnable onComplete) {
        apiService.getReasons().enqueue(new Callback<ReasonsListResponse>() {
            @Override
            public void onResponse(Call<ReasonsListResponse> call, Response<ReasonsListResponse> response) {
                if (response.isSuccessful() && response.body() != null && response.body().getReasons() != null) {
                    List<String> reasonNames = new ArrayList<>();
                    for (ReasonRecord reason : response.body().getReasons()) {
                        reasonNames.add(reason.getReason());
                    }
                    sessionService.setReasonList(reasonNames);
                    updateSyncMessage("Downloaded " + reasonNames.size() + " reasons");
                }
                onComplete.run();
            }

            @Override
            public void onFailure(Call<ReasonsListResponse> call, Throwable t) {
                Log.e(TAG, "Reasons download failed: " + t.getMessage());
                onComplete.run();
            }
        });
    }

    private void downloadCadres(Runnable onComplete) {
        apiService.getCadres().enqueue(new Callback<CadresListResponse>() {
            @Override
            public void onResponse(Call<CadresListResponse> call, Response<CadresListResponse> response) {
                if (response.isSuccessful() && response.body() != null && response.body().getCadres() != null) {
                    sessionService.setCadreList(response.body().getCadres());
                    updateSyncMessage("Downloaded " + response.body().getCadres().size() + " cadres");
                }
                onComplete.run();
            }
            @Override
            public void onFailure(Call<CadresListResponse> call, Throwable t) {
                Log.e(TAG, "Cadres download failed: " + t.getMessage());
                onComplete.run();
            }
        });
    }

    private void downloadDistricts(Runnable onComplete) {
        apiService.getDistricts().enqueue(new Callback<DistrictsListResponse>() {
            @Override
            public void onResponse(Call<DistrictsListResponse> call, Response<DistrictsListResponse> response) {
                if (response.isSuccessful() && response.body() != null && response.body().getDistricts() != null) {
                    sessionService.setDistrictList(response.body().getDistricts());
                    updateSyncMessage("Downloaded " + response.body().getDistricts().size() + " districts");
                }
                onComplete.run();
            }
            @Override
            public void onFailure(Call<DistrictsListResponse> call, Throwable t) {
                Log.e(TAG, "Districts download failed: " + t.getMessage());
                onComplete.run();
            }
        });
    }

    private void downloadAllFacilities(Runnable onComplete) {
        apiService.getAllFacilities().enqueue(new Callback<AllFacilitiesListResponse>() {
            @Override
            public void onResponse(Call<AllFacilitiesListResponse> call, Response<AllFacilitiesListResponse> response) {
                if (response.isSuccessful() && response.body() != null && response.body().getFacilities() != null) {
                    List<String> facilityNames = new ArrayList<>();
                    for (AllFacilityRecord f : response.body().getFacilities()) {
                        facilityNames.add(f.getFacility());
                    }
                    sessionService.setAllFacilityList(facilityNames);
                    updateSyncMessage("Downloaded " + facilityNames.size() + " facilities");
                }
                onComplete.run();
            }
            @Override
            public void onFailure(Call<AllFacilitiesListResponse> call, Throwable t) {
                Log.e(TAG, "All facilities download failed: " + t.getMessage());
                onComplete.run();
            }
        });
    }

    private void downloadJobs(Runnable onComplete) {
        apiService.getJobs().enqueue(new Callback<JobsListResponse>() {
            @Override
            public void onResponse(Call<JobsListResponse> call, Response<JobsListResponse> response) {
                if (response.isSuccessful() && response.body() != null && response.body().getJobs() != null) {
                    sessionService.setJobList(response.body().getJobs());
                    updateSyncMessage("Downloaded " + response.body().getJobs().size() + " jobs");
                }
                onComplete.run();
            }
            @Override
            public void onFailure(Call<JobsListResponse> call, Throwable t) {
                Log.e(TAG, "Jobs download failed: " + t.getMessage());
                onComplete.run();
            }
        });
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

                        // Re-register downloaded templates on the scanner if one is connected
                        if (homeViewModel != null && homeViewModel.getScanner() != null && downloaded2 > 0) {
                            registerDownloadedTemplates(onComplete);
                        } else {
                            onComplete.run();
                        }
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

    private void registerDownloadedTemplates(Runnable onComplete) {
        ScannerLibrary scanner = homeViewModel != null ? homeViewModel.getScanner() : null;
        if (scanner == null) {
            updateSyncMessage("Scanner not connected — skipping template registration");
            onComplete.run();
            return;
        }
        updateSyncMessage("Registering downloaded templates on scanner...");
        fingerprintSyncService.registerTemplatesOnScanner(scanner,                new FingerprintSyncService.ScannerRegistrationCallback() {
                    @Override
                    public void onProgress(int completed, int total, String ihrisPid) {
                        updateSyncMessage("Registered " + completed + "/" + total + ": " + ihrisPid);
                    }

                    @Override
                    public void onComplete(int registered, List<String> failures) {
                        List<String> msgs = syncMessagesLiveData.getValue();
                        msgs.add("Registered " + registered + " template(s) on scanner");
                        if (!failures.isEmpty()) {
                            msgs.add("Registration failures: " + failures.size());
                            for (String f : failures) Log.w(TAG, "Registration failure: " + f);
                        }
                        syncMessagesLiveData.postValue(msgs);
                        onComplete.run();
                    }

                    @Override
                    public void onError(String errorMessage) {
                        updateSyncMessage("Scanner registration error: " + errorMessage);
                        onComplete.run(); // don't block the rest of sync
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
