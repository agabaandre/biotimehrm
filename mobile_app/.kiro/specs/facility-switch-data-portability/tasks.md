# Implementation Plan: Facility Switch Data Portability

## Overview

Transform the HRMAttend app from a single-facility-at-a-time model to a multi-facility data store. This involves adding facility-scoped DAO queries, a staff merge helper, a new API endpoint for biometric downloads, a facility switch confirmation dialog, and updating the UI/sync layers to work with multi-facility data. All changes use Java 1.8 and follow the existing MVVM architecture.

## Tasks

- [ ] 1. Add facility-scoped DAO queries
  - [ ] 1.1 Add facility-scoped queries to `StaffRecordDao`
    - Add `getStaffRecordsByFacility(String facilityId)` returning `List<StaffRecord>`
    - Add `getStaffRecordsWithEmbeddingsByFacility(String facilityId)` filtering on `face_data IS NOT NULL`
    - Add `getStaffRecordsWithFingerprintsByFacility(String facilityId)` filtering on `fingerprint_data IS NOT NULL AND fingerprint_enrolled = 1`
    - Add `countStaffRecordsByFacility(String facilityId)` returning `int`
    - File: `app/src/main/java/ug/go/health/ihrisbiometric/dao/StaffRecordDao.java`
    - _Requirements: 1.1, 5.1, 5.4_

  - [ ] 1.2 Add facility-scoped queries to `ClockHistoryDao`
    - Add `getClockHistoryByFacility(String facilityId)` ordered by `clock_time DESC`
    - Add `countUnsyncedClockRecordsByFacility(String facilityId)` filtering on `synced = 0`
    - File: `app/src/main/java/ug/go/health/ihrisbiometric/dao/ClockHistoryDao.java`
    - _Requirements: 2.1, 6.1_

  - [ ] 1.3 Add facility-scoped async wrappers to `DbService`
    - Add `getStaffRecordsByFacilityAsync(String facilityId, Callback<List<StaffRecord>> callback)`
    - Add `getStaffRecordsWithEmbeddingsByFacilityAsync(String facilityId, Callback<List<StaffRecord>> callback)`
    - Add `getStaffRecordsWithFingerprintsByFacilityAsync(String facilityId, Callback<List<StaffRecord>> callback)`
    - Add `countUnsyncedClockRecordsByFacilityAsync(String facilityId, Callback<Integer> callback)`
    - Add `getClockHistoryByFacilityAsync(String facilityId, Callback<List<ClockHistory>> callback)`
    - Follow the existing `ExecutorService` + `Callback<T>` pattern in `DbService`
    - File: `app/src/main/java/ug/go/health/ihrisbiometric/services/DbService.java`
    - _Requirements: 1.1, 5.1, 6.1_

- [ ] 2. Implement StaffMergeHelper and API endpoint
  - [ ] 2.1 Create `StaffMergeHelper` utility class
    - Create `helpers/StaffMergeHelper.java` with a static `merge(StaffRecord local, StaffRecord server)` method
    - If no local record exists (local is null): return server record as-is
    - If local record exists: update staff info fields (surname, firstname, othername, job, facility, facility_id) from server; preserve local biometric data (face_data, fingerprint_data, face_image, face_enrolled, fingerprint_enrolled, embedding_synced, fingerprint_synced, template_id) if non-null/non-default locally
    - If local biometric data is null/default, accept server biometric data
    - File: `app/src/main/java/ug/go/health/ihrisbiometric/helpers/StaffMergeHelper.java`
    - _Requirements: 1.2, 1.3, 1.4, 3.4, 3.5_

  - [ ]* 2.2 Write unit tests for `StaffMergeHelper`
    - Test merge when local is null (insert server record)
    - Test merge preserves local biometric data when present
    - Test merge accepts server biometric data when local is null
    - Test merge updates staff info fields from server
    - Test merge with both local and server biometric data (local wins)
    - _Requirements: 1.3, 3.4, 3.5_

  - [ ] 2.3 Add `getStaffListWithBiometrics` endpoint to `ApiInterface`
    - Add `@GET("staff_list_with_biometrics") Call<StaffListResponse> getStaffListWithBiometrics(@Query("facility_id") String facilityId)`
    - File: `app/src/main/java/ug/go/health/ihrisbiometric/services/ApiInterface.java`
    - _Requirements: 3.1_

- [ ] 3. Checkpoint - Ensure all tests pass
  - Ensure all tests pass, ask the user if questions arise.

- [ ] 4. Implement facility switch flow
  - [ ] 4.1 Create `FacilitySwitchHelper` service class
    - Create `helpers/FacilitySwitchHelper.java` that orchestrates the facility switch
    - Constructor takes `Context`, initializes `DbService`, `SessionService`, `ApiInterface`
    - Implement `checkUnsyncedRecords(String currentFacilityId, DbService.Callback<Integer> callback)` — calls `countUnsyncedClockRecordsByFacilityAsync`
    - Implement `performSwitch(String newFacilityId, String newFacilityName, SwitchCallback callback)`:
      1. Update `SessionService` with new facility ID and name
      2. Call API `getStaffListWithBiometrics(newFacilityId)` via Retrofit `enqueue()`
      3. For each returned `StaffRecord`: look up local record by `ihris_pid`, call `StaffMergeHelper.merge()`, upsert via `DbService`
      4. Reload face engine: call `JniHelper.delete()` to clear, then load embeddings for new facility via `getStaffRecordsWithEmbeddingsByFacilityAsync` and register each with `JniHelper.register()`
      5. Invoke callback with success/error
    - Implement `syncThenSwitch(String newFacilityId, String newFacilityName, SwitchCallback callback)`:
      1. Get all unsynced clock records via `getUnsyncedClockHistoryAsync`
      2. Upload each via `ApiInterface.syncClockHistory()`
      3. Mark uploaded records as synced
      4. Call `performSwitch()`
    - Define `SwitchCallback` interface with `onProgress(String message)`, `onComplete()`, `onError(String error)`
    - File: `app/src/main/java/ug/go/health/ihrisbiometric/helpers/FacilitySwitchHelper.java`
    - _Requirements: 1.1, 1.2, 1.4, 3.1, 4.1, 5.2, 5.3, 6.3, 6.4_

  - [ ] 4.2 Create `FacilitySwitchDialog` for unsynced records warning
    - Create a static helper method or utility that builds an `AlertDialog` with:
      - Title: "Unsynced Attendance Records"
      - Message: "You have {count} unsynced attendance records for {facility_name}."
      - Positive button: "Sync & Switch" — triggers `syncThenSwitch()`
      - Negative button: "Switch Anyway" — triggers `performSwitch()`
      - Neutral button: "Cancel" — dismisses dialog
    - Can be a static method in `FacilitySwitchHelper` or a separate `FacilitySwitchDialog.java` in `helpers/`
    - _Requirements: 6.1, 6.2, 6.3, 6.4_

- [ ] 5. Integrate facility switch into LoginActivity
  - [ ] 5.1 Modify `LoginActivity` to use `FacilitySwitchHelper`
    - After successful login, before navigating to `HomeActivity`:
      - Compare new `facilityId` from login response with `sessionService.getFacilityId()`
      - If different (facility switch): call `FacilitySwitchHelper.checkUnsyncedRecords()`
      - If unsynced count > 0: show `FacilitySwitchDialog`
      - If unsynced count == 0 or first login: call `performSwitch()` directly
    - Remove any existing `deleteAll()` / `clearStaffListAsync()` calls during login/facility switch flow
    - File: `app/src/main/java/ug/go/health/ihrisbiometric/activities/LoginActivity.java`
    - _Requirements: 1.1, 1.4, 2.1, 6.1, 6.2_

- [ ] 6. Update UI to be facility-scoped
  - [ ] 6.1 Modify `HomeViewModel` to load staff by active facility
    - Change staff record loading to use `dbService.getStaffRecordsByFacilityAsync(facilityId, ...)`
    - Get `facilityId` from `SessionService`
    - File: `app/src/main/java/ug/go/health/ihrisbiometric/viewmodels/HomeViewModel.java`
    - _Requirements: 5.1, 5.4_

  - [ ] 6.2 Scope face engine initialization to active facility
    - On app startup / facility switch, load only the active facility's embeddings into `JniHelper`
    - Use `getStaffRecordsWithEmbeddingsByFacilityAsync(facilityId, ...)` instead of `getStaffRecordsWithEmbeddingsAsync()`
    - Ensure `JniHelper.delete()` is called before re-registering to clear previous facility's faces
    - Update relevant code in `HomeActivity`, `CameraFragment`, or wherever face engine is initialized
    - _Requirements: 4.1, 5.2, 5.3_

  - [ ] 6.3 Scope `ClockHistoryFragment` to active facility
    - Filter displayed clock history by active facility using `getClockHistoryByFacilityAsync(facilityId, ...)`
    - File: `app/src/main/java/ug/go/health/ihrisbiometric/fragments/ClockHistoryFragment.java`
    - _Requirements: 2.3, 5.1_

- [ ] 7. Checkpoint - Ensure all tests pass
  - Ensure all tests pass, ask the user if questions arise.

- [ ] 8. Update DataSync for multi-facility and biometric download
  - [ ] 8.1 Ensure clock history sync uploads records from all facilities
    - Verify that `DataSyncViewModel.syncClockRecords()` uses `getUnsyncedClockHistoryAsync()` (which already returns all unsynced records regardless of facility_id)
    - Ensure each `ClockHistory` record sent to the server includes its `facility_id` field
    - If the existing sync already does this, no code change needed — just verify and document
    - _Requirements: 7.1, 7.2, 7.3_

  - [ ] 8.2 Add biometric data handling to staff download in `DataSyncViewModel`
    - When staff records are downloaded during sync, use `StaffMergeHelper.merge()` to upsert
    - After merge, for records with new face embeddings: register with `JniHelper` if they belong to the active facility
    - After merge, for records with new fingerprint templates: store locally (registration happens when scanner is available per Requirement 4.4)
    - Set `face_enrolled = true` when face_data is populated from server, `fingerprint_enrolled = true` when fingerprint_data is populated
    - Log failures for individual biometric registrations with `ihris_pid` and continue processing remaining records
    - _Requirements: 3.1, 3.2, 3.3, 3.4, 3.5, 4.1, 4.2, 4.3, 4.4, 4.5_

  - [ ]* 8.3 Write unit tests for multi-facility clock sync behavior
    - Test that unsynced records from multiple facility_ids are all included in sync
    - Test that facility_id is preserved on each uploaded record
    - Test that synced flag is set on records from all facilities after successful upload
    - _Requirements: 7.1, 7.2, 7.3_

- [ ] 9. Handle fingerprint template registration on scanner connect
  - [ ] 9.1 Register downloaded fingerprint templates when scanner becomes available
    - Use existing `getStaffRecordsWithUnregisteredFingerprintsAsync()` or add facility-scoped variant
    - When `Scanner_Device` connects, iterate unregistered templates for the active facility and register them
    - Assign `template_id` to each successfully registered `StaffRecord` and update via `DbService`
    - _Requirements: 4.2, 4.4, 4.5_

- [ ] 10. Final checkpoint - Ensure all tests pass
  - Ensure all tests pass, ask the user if questions arise.

## Notes

- Tasks marked with `*` are optional and can be skipped for faster MVP
- No database schema changes are needed — existing `StaffRecord` and `ClockHistory` entities already have all required columns including `facility_id`
- Since `fallbackToDestructiveMigration()` is used, no migration is required
- The existing `clearStaffListAsync()` / `deleteAll()` method is preserved for a manual "clear all data" action in settings but is no longer called during facility switch
- All code must be Java 1.8 compatible per project conventions
- Each task references specific requirements for traceability
