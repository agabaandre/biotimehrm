# Implementation Plan: Fingerprint Sync

## Overview

Add cross-device fingerprint template synchronization to the iHRIS Biometric app. The implementation proceeds bottom-up: data layer changes first (StaffRecord entity, DAO queries, DB version), then new API models and endpoints, then the core FingerprintSyncService, then ViewModel integration with the existing sync workflow, and finally UI wiring. Scanner registration is handled lazily as a separate pass. Each step builds on the previous and is validated before moving on.

## Tasks

- [x] 1. Data layer — StaffRecord entity and database migration
  - [x] 1.1 Add `fingerprint_synced` column to StaffRecord
    - Add `@ColumnInfo(name = "fingerprint_synced") private boolean fingerprintSynced = false;` field with `@SerializedName("fingerprint_synced")` and `@Expose`
    - Add getter `isFingerprintSynced()` and setter `setFingerprintSynced(boolean)`
    - _Requirements: 1.2, 5.1, 5.2_
  - [x] 1.2 Increment AppDatabase version
    - Change `version = 1` to `version = 2` in the `@Database` annotation in `AppDatabase.java`
    - `fallbackToDestructiveMigration()` is already configured, so no migration class needed
    - Coordinate with face-embedding-sync if both land together — only one version bump needed
    - _Requirements: 1.2_
  - [x] 1.3 Add new queries to StaffRecordDao
    - Add `getStaffRecordsWithUnsyncedFingerprints()`: `SELECT * FROM staff_records WHERE fingerprint_enrolled = 1 AND fingerprint_data IS NOT NULL AND fingerprint_synced = 0`
    - Add `getStaffRecordsWithUnregisteredFingerprints()`: `SELECT * FROM staff_records WHERE fingerprint_enrolled = 1 AND fingerprint_data IS NOT NULL AND template_id = 0`
    - Add `countUnsyncedFingerprints()`: `SELECT COUNT(*) FROM staff_records WHERE fingerprint_enrolled = 1 AND fingerprint_data IS NOT NULL AND fingerprint_synced = 0`
    - Add `countSyncedFingerprints()`: `SELECT COUNT(*) FROM staff_records WHERE fingerprint_synced = 1`
    - _Requirements: 1.1, 3.1, 5.1_
  - [x] 1.4 Add async wrapper methods to DbService
    - Add `getStaffRecordsWithUnsyncedFingerprintsAsync(Callback<List<StaffRecord>>)` using the existing executor + mainHandler pattern
    - Add `getStaffRecordsWithUnregisteredFingerprintsAsync(Callback<List<StaffRecord>>)`
    - Add `countUnsyncedFingerprintsAsync(Callback<Integer>)` and `countSyncedFingerprintsAsync(Callback<Integer>)`
    - _Requirements: 1.1, 3.1, 5.1, 6.1_

- [x] 2. API models and endpoints
  - [x] 2.1 Create FingerprintUploadRequest model
    - New file `models/FingerprintUploadRequest.java` with fields: `ihrisPid` (String, `@SerializedName("ihris_pid")`), `fingerprintData` (String — Base64, `@SerializedName("fingerprint_data")`)
    - Use `@Expose` annotations, add constructor and getters
    - _Requirements: 1.1, 8.1_
  - [x] 2.2 Create FingerprintDownloadResponse and FingerprintRecord models
    - New file `models/FingerprintDownloadResponse.java` with fields: `status` (String), `message` (String), `fingerprints` (List\<FingerprintRecord\>)
    - New file `models/FingerprintRecord.java` with fields: `ihrisPid` (String, `@SerializedName("ihris_pid")`), `fingerprintData` (String — Base64, `@SerializedName("fingerprint_data")`)
    - Use `@SerializedName` and `@Expose` annotations, add getters
    - _Requirements: 2.1, 2.2, 8.2_
  - [x] 2.3 Add upload and download endpoints to ApiInterface
    - Add `@POST("upload_fingerprint") Call<FingerprintUploadResponse> uploadFingerprint(@Body FingerprintUploadRequest request);`
    - Add `@GET("fingerprints") Call<FingerprintDownloadResponse> getFingerprints(@Query("facility_id") String facilityId);`
    - _Requirements: 8.1, 8.2_

- [x] 3. Checkpoint — Verify data layer and API models compile
  - Ensure all tests pass, ask the user if questions arise.

- [x] 4. FingerprintSyncService — core sync logic
  - [x] 4.1 Create FingerprintSyncService class with constructor and callback interfaces
    - New file `services/FingerprintSyncService.java`
    - Constructor takes `Context`, `ApiInterface`, `DbService`
    - Define inner `FingerprintSyncCallback` interface with `onProgress(int completed, int total, String message)`, `onComplete(int uploaded, int downloaded, List<String> errors)`, `onError(String errorMessage)`
    - Define inner `ScannerRegistrationCallback` interface with `onProgress(int completed, int total, String ihrisPid)`, `onComplete(int registered, List<String> failures)`, `onError(String errorMessage)`
    - _Requirements: 1.1, 2.1, 6.1_
  - [x] 4.2 Implement `uploadFingerprints(FingerprintSyncCallback)` method
    - Query unsynced fingerprints via `DbService.getStaffRecordsWithUnsyncedFingerprintsAsync()`
    - For each record: build `FingerprintUploadRequest` with `ihrisPid` and Base64 fingerprint_data from `ByteArrayConverter.toString()`
    - Call `ApiInterface.uploadFingerprint()` with `enqueue()` and on success set `fingerprintSynced = true` via `DbService.updateStaffRecordAsync()`
    - On failure (server error or network): log error, keep `fingerprintSynced = false`, add to error list
    - Report progress via callback after each record
    - _Requirements: 1.1, 1.2, 1.3, 1.4, 5.1_
  - [x] 4.3 Implement `downloadFingerprints(String facilityId, FingerprintSyncCallback)` method
    - Call `ApiInterface.getFingerprints(facilityId)` with `enqueue()`
    - For each `FingerprintRecord` in response: look up local `StaffRecord` by `ihrisPid` via `DbService.getStaffRecordByihrisPIDAsync()`
    - Skip if local record already has `fingerprintData != null` and `fingerprintSynced == true` (local takes precedence per Req 5.3)
    - If local record has no fingerprint_data: decode Base64 via `ByteArrayConverter.fromString()`, set `fingerprintData`, `fingerprintEnrolled = true`, `fingerprintSynced = true`
    - Update StaffRecord via `DbService.updateStaffRecordAsync()`
    - Handle empty list: report "No fingerprint templates available for download"
    - Handle network errors: report error via callback
    - Report progress via callback after each record
    - _Requirements: 2.1, 2.2, 2.3, 2.4, 2.5, 5.2, 5.3, 5.4_
  - [x] 4.4 Implement `registerTemplatesOnScanner(ScannerRegistrationCallback)` method
    - Query unregistered fingerprints via `DbService.getStaffRecordsWithUnregisteredFingerprintsAsync()`
    - For each record: attempt to register the fingerprint_data on the scanner device and assign the returned `template_id`
    - On success: update `template_id` on StaffRecord via `DbService.updateStaffRecordAsync()`
    - On failure: log with ihris_pid, add to failures list, continue with next record (non-blocking)
    - Report progress via callback after each record
    - _Requirements: 3.1, 3.2, 3.3, 3.4_

  - [ ]* 4.5 Write property test: P1 — ByteArrayConverter round-trip
    - **Property 1: ByteArrayConverter round-trip**
    - Use jqwik to generate arbitrary byte arrays (0–10000 bytes), convert via `ByteArrayConverter.toString()` then `ByteArrayConverter.fromString()`, assert element-wise equality
    - Test null input returns null in both directions
    - Test empty byte array round-trips correctly
    - **Validates: Requirements 4.1, 4.2, 4.3, 4.4**
  - [ ]* 4.6 Write property test: P2 — Upload filter selects only eligible records
    - **Property 2: Upload filter selects only eligible records**
    - Use jqwik to generate arbitrary StaffRecord lists with random combinations of `fingerprint_enrolled`, `fingerprint_data`, and `fingerprint_synced`
    - Apply the upload filter logic and verify the result set contains exactly those records where `fingerprint_enrolled == true` AND `fingerprint_data != null` AND `fingerprint_synced == false`
    - **Validates: Requirements 1.1, 5.1**
  - [ ]* 4.7 Write property test: P5 — Incremental download preserves local data and fills gaps
    - **Property 5: Incremental download preserves local data and fills gaps**
    - Use jqwik to generate arbitrary local StaffRecord state and server FingerprintRecord lists
    - Verify: (a) local records with existing `fingerprint_data` and `fingerprint_synced == true` are unchanged after download, (b) local records without `fingerprint_data` that have a matching server record get the server's data with `fingerprint_enrolled = true` and `fingerprint_synced = true`
    - **Validates: Requirements 2.3, 5.2, 5.3, 5.4**

- [x] 5. Checkpoint — Verify FingerprintSyncService compiles and property tests pass
  - Ensure all tests pass, ask the user if questions arise.

- [x] 6. ViewModel integration — wire FingerprintSyncService into DataSyncViewModel
  - [x] 6.1 Add fingerprint sync LiveData fields to DataSyncViewModel
    - Add `fingerprintSyncProgressLiveData` as `MutableLiveData<Integer>` (0–100 progress)
    - Add `fingerprintUploadCountLiveData` and `fingerprintDownloadCountLiveData` as `MutableLiveData<Integer>`
    - Add public getter methods for each
    - Update `updateSyncCounts()` to also query fingerprint sync counts via `DbService.countUnsyncedFingerprintsAsync()` and `DbService.countSyncedFingerprintsAsync()`
    - _Requirements: 6.1, 6.2, 6.4_
  - [x] 6.2 Instantiate FingerprintSyncService in DataSyncViewModel constructor
    - Create `FingerprintSyncService` with the existing `apiService`, `dbService`, and application context
    - _Requirements: 7.1_
  - [x] 6.3 Modify `performSync()` to insert fingerprint sync between staff sync and clock sync
    - After `handleUnsyncedStaffRecords` completes staff sync, call `fingerprintSyncService.uploadFingerprints()`
    - On upload complete, call `fingerprintSyncService.downloadFingerprints(facilityId)` using facility ID from `SessionService`
    - On download complete, attempt `fingerprintSyncService.registerTemplatesOnScanner()` if scanner is connected
    - On fingerprint sync complete (or on error), proceed to `syncClockRecords()`
    - Fingerprint sync failure must not block clock sync — log error and continue
    - Update `fingerprintSyncProgressLiveData` from the callback's `onProgress`
    - Update `fingerprintUploadCountLiveData` and `fingerprintDownloadCountLiveData` from `onComplete`
    - Post fingerprint sync messages to `syncMessagesLiveData`
    - _Requirements: 7.1, 7.2, 7.3, 6.1, 6.2, 6.3_
  - [ ]* 6.4 Write property test: P3 — Successful upload transitions sync status
    - **Property 3: Successful upload transitions sync status**
    - Generate random StaffRecords, simulate successful upload, verify `fingerprintSynced` becomes true
    - **Validates: Requirements 1.2**
  - [ ]* 6.5 Write property test: P4 — Failed upload preserves unsynced status
    - **Property 4: Failed upload preserves unsynced status**
    - Generate random StaffRecords, simulate failed upload (server error or network failure), verify `fingerprintSynced` remains false
    - **Validates: Requirements 1.3, 1.4**
  - [ ]* 6.6 Write property test: P8 — Progress callbacks report accurate counts
    - **Property 8: Progress callbacks report accurate counts**
    - Generate random batch sizes N, simulate sync operations, verify progress callbacks have monotonically increasing `completed` values from 0 to N and constant `total` parameter
    - **Validates: Requirements 6.1**
  - [ ]* 6.7 Write property test: P9 — Fingerprint sync failure does not block remaining sync phases
    - **Property 9: Fingerprint sync failure does not block remaining sync phases**
    - Simulate fingerprint sync failure within the data sync workflow, verify clock history sync still executes
    - **Validates: Requirements 7.3**

- [x] 7. Checkpoint — Verify ViewModel integration and property tests pass
  - Ensure all tests pass, ask the user if questions arise.

- [x] 8. UI layer — DataSyncFragment observes fingerprint sync LiveData
  - [x] 8.1 Add fingerprint sync progress bar and count display to DataSyncFragment
    - Add `fingerprintProgressBar` ProgressBar to `fragment_data_sync.xml` layout
    - Bind `fingerprintProgressBar` in `initializeViews()`
    - Observe `fingerprintSyncProgressLiveData` to update the progress bar
    - Show/hide `fingerprintProgressBar` based on `SyncStatus` (visible during IN_PROGRESS, gone otherwise)
    - _Requirements: 6.1, 6.4_
  - [x] 8.2 Display fingerprint sync counts and messages
    - Observe `fingerprintUploadCountLiveData` and `fingerprintDownloadCountLiveData` to display upload/download counts in the UI
    - Fingerprint sync messages are already integrated via `syncMessagesLiveData` from task 6.3
    - _Requirements: 6.2, 6.3, 6.4_
  - [ ]* 8.3 Write property test: P6 — Scanner registration assigns template_id
    - **Property 6: Scanner registration assigns template_id**
    - Generate random StaffRecords with `fingerprint_data` present and `template_id == 0`, simulate successful scanner registration, verify `template_id` becomes non-zero
    - **Validates: Requirements 3.1**
  - [ ]* 8.4 Write property test: P7 — Scanner registration failure is non-blocking
    - **Property 7: Scanner registration failure is non-blocking**
    - Generate random batches with some records configured to fail registration, verify all records are attempted and `registered + failed == total`
    - **Validates: Requirements 3.3**

- [x] 9. Final checkpoint — Full integration verification
  - Ensure all tests pass, ask the user if questions arise.

## Notes

- Tasks marked with `*` are optional and can be skipped for faster MVP
- Each task references specific requirements for traceability
- Checkpoints ensure incremental validation
- Property tests validate universal correctness properties from the design document (P1–P9)
- The implementation language is Java (Java 1.8 source compatibility) matching the existing codebase
- Database version increment with `fallbackToDestructiveMigration()` means existing local data will be cleared on upgrade — coordinate with face-embedding-sync if both land together
- Scanner registration is lazy — templates are stored in Room on download and registered on the scanner device separately when hardware is available
