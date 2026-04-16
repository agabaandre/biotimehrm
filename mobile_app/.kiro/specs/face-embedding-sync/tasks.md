# Implementation Plan: Face Embedding Sync

## Overview

Add cross-device face embedding synchronization to the iHRIS Biometric app. The implementation proceeds bottom-up: data layer changes first (entity, DAO, DB version), then new API models and endpoints, then the core EmbeddingSyncService, then FaceScanner extension, then ViewModel integration, and finally UI wiring. Each step builds on the previous and is validated before moving on.

## Tasks

- [x] 1. Data layer — StaffRecord entity and database migration
  - [x] 1.1 Add `embedding_synced` column to StaffRecord
    - Add `@ColumnInfo(name = "embedding_synced") private boolean embeddingSynced = false;` field with `@SerializedName("embedding_synced")` and `@Expose`
    - Add getter `isEmbeddingSynced()` and setter `setEmbeddingSynced(boolean)`
    - _Requirements: 1.2, 5.1, 5.2_
  - [x] 1.2 Increment AppDatabase version to 2
    - Change `version = 1` to `version = 2` in the `@Database` annotation in `AppDatabase.java`
    - `fallbackToDestructiveMigration()` is already configured, so no migration class needed
    - _Requirements: 1.2_
  - [x] 1.3 Add new queries to StaffRecordDao
    - Add `getStaffRecordsWithUnsyncedEmbeddings()` query: `SELECT * FROM staff_records WHERE face_enrolled = 1 AND face_data IS NOT NULL AND embedding_synced = 0`
    - Add `countUnsyncedEmbeddings()` query: `SELECT COUNT(*) FROM staff_records WHERE face_enrolled = 1 AND face_data IS NOT NULL AND embedding_synced = 0`
    - Add `countSyncedEmbeddings()` query: `SELECT COUNT(*) FROM staff_records WHERE embedding_synced = 1`
    - _Requirements: 1.1, 5.1_
  - [x] 1.4 Add async wrapper methods to DbService
    - Add `getStaffRecordsWithUnsyncedEmbeddingsAsync(Callback<List<StaffRecord>>)` using the existing executor + mainHandler pattern
    - Add `countUnsyncedEmbeddingsAsync(Callback<Integer>)` and `countSyncedEmbeddingsAsync(Callback<Integer>)`
    - _Requirements: 1.1, 5.1, 6.1_

- [x] 2. API models and endpoints
  - [x] 2.1 Create FaceEmbeddingUploadRequest model
    - New file `models/FaceEmbeddingUploadRequest.java` with fields: `ihrisPid` (String), `faceData` (String — CSV), `faceImage` (String — Base64)
    - Use `@SerializedName` and `@Expose` annotations, add constructor and getters
    - _Requirements: 1.1_
  - [x] 2.2 Create FaceEmbeddingDownloadResponse and FaceEmbeddingRecord models
    - New file `models/FaceEmbeddingDownloadResponse.java` with fields: `status` (String), `embeddings` (List\<FaceEmbeddingRecord\>)
    - New file `models/FaceEmbeddingRecord.java` with fields: `ihrisPid` (String), `faceData` (String — CSV), `faceImage` (String — Base64)
    - Use `@SerializedName` and `@Expose` annotations, add getters
    - _Requirements: 2.1, 2.2_
  - [x] 2.3 Add upload and download endpoints to ApiInterface
    - Add `@POST("upload_face_embedding") Call<FaceUploadResponse> uploadFaceEmbedding(@Body FaceEmbeddingUploadRequest request);`
    - Add `@GET("face_embeddings") Call<FaceEmbeddingDownloadResponse> getFaceEmbeddings(@Query("facility_id") String facilityId);`
    - _Requirements: 1.1, 2.1_

- [x] 3. Checkpoint — Verify data layer and API models compile
  - Ensure all tests pass, ask the user if questions arise.

- [x] 4. FaceScanner — registerFaceFromBase64 method
  - [x] 4.1 Add `registerFaceFromBase64(String base64Image, String userId)` to FaceScanner
    - Decode Base64 string to byte array, create Bitmap from bytes
    - Convert Bitmap to OpenCV Mat using `org.opencv.android.Utils.bitmapToMat()`
    - Delegate to existing `registerFace(Mat, String)` method
    - Return the result string from `registerFace`
    - _Requirements: 3.1, 3.2, 3.3_

- [x] 5. EmbeddingSyncService — core sync logic
  - [x] 5.1 Create EmbeddingSyncService class with constructor and callback interface
    - New file `services/EmbeddingSyncService.java`
    - Constructor takes `Context`, `ApiInterface`, `DbService`, `FaceScanner`
    - Define inner `EmbeddingSyncCallback` interface with `onProgress(int, int, String)`, `onComplete(int, int, List<String>)`, `onError(String)`
    - _Requirements: 1.1, 2.1, 6.1_
  - [x] 5.2 Implement `uploadEmbeddings(EmbeddingSyncCallback)` method
    - Query unsynced embeddings via `DbService.getStaffRecordsWithUnsyncedEmbeddingsAsync()`
    - For each record: build `FaceEmbeddingUploadRequest` with `ihrisPid`, CSV face_data from `FloatArrayConverter.toString()`, and `faceImage`
    - Call `ApiInterface.uploadFaceEmbedding()` and on success set `embeddingSynced = true` via `DbService.updateStaffRecordAsync()`
    - On failure: log error, keep `embeddingSynced = false`, add to error list
    - Report progress via callback after each record
    - _Requirements: 1.1, 1.2, 1.3, 1.4, 5.1_
  - [x] 5.3 Implement `downloadEmbeddings(String facilityId, EmbeddingSyncCallback)` method
    - Call `ApiInterface.getFaceEmbeddings(facilityId)`
    - For each `FaceEmbeddingRecord` in response: look up local `StaffRecord` by `ihrisPid`
    - Skip if local record already has `face_data != null` and `embeddingSynced == true` (local takes precedence)
    - Otherwise: deserialize CSV to float[] via `FloatArrayConverter.fromString()`, set `faceData`, `faceImage`, `faceEnrolled = true`, `embeddingSynced = true`
    - Call `FaceScanner.registerFaceFromBase64()` to register with face engine; log and skip on failure
    - Update StaffRecord via `DbService.updateStaffRecordAsync()`
    - Report progress via callback; handle empty list and network errors
    - _Requirements: 2.1, 2.2, 2.3, 2.4, 2.5, 3.1, 3.2, 3.3, 5.2, 5.3, 5.4_
  - [ ]* 5.4 Write property test: P5 — Incremental sync upload filter
    - **Property 5: Upload filter only selects records with face_enrolled=true AND face_data!=null AND embedding_synced=false**
    - Use jqwik to generate arbitrary StaffRecord lists with random combinations of face_enrolled, face_data, and embedding_synced
    - Verify the DAO query `getStaffRecordsWithUnsyncedEmbeddings()` returns exactly the records matching all three conditions
    - **Validates: Requirements 1.1, 5.1**
  - [ ]* 5.5 Write property test: P6 — Incremental sync download skip
    - **Property 6: Download skips records with local face_data AND embedding_synced=true**
    - Use jqwik to generate arbitrary local StaffRecord state and server FaceEmbeddingRecord lists
    - Verify that downloadEmbeddings never overwrites a record where face_data != null and embeddingSynced == true
    - **Validates: Requirements 5.2**
  - [ ]* 5.6 Write property test: P7 — Conflict resolution (local takes precedence)
    - **Property 7: When both local and server have embeddings for the same ihris_pid, local embedding is preserved**
    - Use jqwik to generate StaffRecords with existing face_data and server records with different face_data for the same ihris_pid
    - Verify the local face_data is unchanged after download
    - **Validates: Requirements 5.3**

- [x] 6. Checkpoint — Verify EmbeddingSyncService compiles and property tests pass
  - Ensure all tests pass, ask the user if questions arise.

- [ ] 7. FloatArrayConverter property tests
  - [ ]* 7.1 Write property test: P1 — Round-trip consistency
    - **Property 1: FloatArrayConverter round-trip (float[] → CSV → float[] element-wise equal)**
    - Use jqwik to generate arbitrary float arrays, convert to string and back, assert element-wise equality
    - **Validates: Requirements 4.1**
  - [ ]* 7.2 Write property test: P2 — Idempotent serialization
    - **Property 2: FloatArrayConverter idempotent serialization (serialize → deserialize → serialize = same string)**
    - Use jqwik to generate arbitrary float arrays, serialize → deserialize → serialize, assert string equality
    - **Validates: Requirements 4.2**
  - [ ]* 7.3 Write property test: P3 — Null handling
    - **Property 3: FloatArrayConverter null handling (null in → null out)**
    - Verify `FloatArrayConverter.toString(null)` returns null and `FloatArrayConverter.fromString(null)` returns null
    - **Validates: Requirements 4.4**
  - [ ]* 7.4 Write property test: P4 — Special float values preserved
    - **Property 4: FloatArrayConverter special values (NaN, ±Infinity preserved)**
    - Use jqwik to generate float arrays containing NaN, Float.POSITIVE_INFINITY, Float.NEGATIVE_INFINITY
    - Verify these values survive round-trip through toString/fromString
    - **Validates: Requirements 4.3**

- [x] 8. ViewModel integration — wire EmbeddingSyncService into DataSyncViewModel
  - [x] 8.1 Add embedding sync LiveData fields to DataSyncViewModel
    - Add `embeddingSyncProgressLiveData`, `embeddingUploadCountLiveData`, `embeddingDownloadCountLiveData` as `MutableLiveData<Integer>`
    - Add public getter methods for each
    - Add `embeddingSyncedCountLiveData` and `unsyncedEmbeddingCountLiveData` for sync counts
    - Update `updateSyncCounts()` to also query embedding sync counts via DbService
    - _Requirements: 6.1, 6.2, 6.4_
  - [x] 8.2 Instantiate EmbeddingSyncService and FaceScanner in DataSyncViewModel constructor
    - Create `FaceScanner` instance and call `initEngine(context)` 
    - Create `EmbeddingSyncService` with the existing `apiService`, `dbService`, and new `faceScanner`
    - _Requirements: 7.1_
  - [x] 8.3 Modify `performSync()` to insert embedding sync between staff sync and clock sync
    - After `handleUnsyncedStaffRecords` completes staff sync, call `embeddingSyncService.uploadEmbeddings()` 
    - On upload complete, call `embeddingSyncService.downloadEmbeddings(facilityId)` using facility ID from `SessionService`
    - On download complete (or on embedding sync error), proceed to `syncClockRecords()`
    - Embedding sync failure must not block clock sync — log error and continue
    - Update `embeddingSyncProgressLiveData` from the callback's `onProgress`
    - Update `embeddingUploadCountLiveData` and `embeddingDownloadCountLiveData` from `onComplete`
    - _Requirements: 7.1, 7.2, 7.3, 6.1, 6.2, 6.3_

- [x] 9. UI layer — DataSyncFragment observes embedding sync LiveData
  - [x] 9.1 Add embedding sync progress and count display to DataSyncFragment
    - Observe `embeddingSyncProgressLiveData` to show a progress bar or progress text during embedding sync
    - Observe `embeddingUploadCountLiveData` and `embeddingDownloadCountLiveData` to display upload/download counts
    - Add embedding sync messages to the existing sync messages list
    - _Requirements: 6.1, 6.2, 6.3, 6.4_

- [x] 10. Final checkpoint — Full integration verification
  - Ensure all tests pass, ask the user if questions arise.

## Notes

- Tasks marked with `*` are optional and can be skipped for faster MVP
- Each task references specific requirements for traceability
- Checkpoints ensure incremental validation
- Property tests validate universal correctness properties from the design document (P1–P7)
- The implementation language is Java (Java 1.8 source compatibility) matching the existing codebase
- Database version increment with `fallbackToDestructiveMigration()` means existing local data will be cleared on upgrade — this is acceptable per the design
