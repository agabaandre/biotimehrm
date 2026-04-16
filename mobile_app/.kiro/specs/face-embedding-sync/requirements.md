# Requirements Document

## Introduction

The iHRIS Biometric (HRMAttend) app currently generates and stores face embeddings locally on individual devices. A staff member enrolled on one device cannot be recognized on another device because the face embedding data (float arrays) is never uploaded to the server or downloaded by other devices. This feature adds the ability to upload face embeddings to the server and download them onto any device, enabling cross-device face recognition for biometric attendance.

## Glossary

- **App**: The iHRIS Biometric (HRMAttend) Android application running on a device.
- **Server**: The remote iHRIS backend that exposes REST API endpoints for data synchronization.
- **Face_Embedding**: A float array (float[]) representing a staff member's facial features, extracted by the ONNX face recognition model via JNI.
- **Staff_Record**: A local Room database entity containing staff information, enrollment status, and biometric data including Face_Embedding and face image.
- **Embedding_Sync_Service**: The component responsible for uploading and downloading Face_Embeddings between the App and the Server.
- **Face_Engine**: The JNI-based face recognition engine (JniHelper) that registers and recognizes faces using Face_Embeddings.
- **ihris_pid**: A unique identifier for a staff member, used to associate Face_Embeddings with the correct person across devices.
- **FloatArrayConverter**: The Room TypeConverter that serializes float[] to comma-separated strings and deserializes them back.
- **Embedding_Sync_Status**: A per-record flag tracking whether a Staff_Record's Face_Embedding has been synchronized with the Server.

## Requirements

### Requirement 1: Upload Face Embeddings to Server

**User Story:** As a facility administrator, I want face embeddings captured during enrollment to be uploaded to the server, so that the biometric data is available for other devices to download.

#### Acceptance Criteria

1. WHEN a Staff_Record has face_enrolled set to true and face_data is not null and Embedding_Sync_Status is unsynced, THE Embedding_Sync_Service SHALL upload the Face_Embedding as a comma-separated string along with the ihris_pid and face_image to the Server.
2. WHEN the Server responds with a success status, THE Embedding_Sync_Service SHALL update the Embedding_Sync_Status of the Staff_Record to synced in the local database.
3. IF the Server responds with an error status, THEN THE Embedding_Sync_Service SHALL retain the Embedding_Sync_Status as unsynced and log the error message.
4. IF a network failure occurs during upload, THEN THE Embedding_Sync_Service SHALL retain the Embedding_Sync_Status as unsynced and queue the record for retry on the next sync cycle.

### Requirement 2: Download Face Embeddings from Server

**User Story:** As a facility administrator, I want to download face embeddings from the server onto a new device, so that staff enrolled on other devices can be recognized locally.

#### Acceptance Criteria

1. WHEN the App initiates an embedding download, THE Embedding_Sync_Service SHALL request all Face_Embeddings associated with the facility from the Server.
2. WHEN the Server returns Face_Embedding records, THE Embedding_Sync_Service SHALL deserialize each comma-separated string back into a float[] using FloatArrayConverter and store the result in the corresponding Staff_Record's face_data column.
3. WHEN a downloaded Face_Embedding is stored in the local database, THE Embedding_Sync_Service SHALL set face_enrolled to true and Embedding_Sync_Status to synced on the corresponding Staff_Record.
4. IF the Server returns an empty list of Face_Embeddings for the facility, THEN THE Embedding_Sync_Service SHALL report that no embeddings are available for download.
5. IF a network failure occurs during download, THEN THE Embedding_Sync_Service SHALL display an error message to the user and allow retry.

### Requirement 3: Register Downloaded Embeddings with Face Engine

**User Story:** As a facility administrator, I want downloaded face embeddings to be registered with the on-device face recognition engine, so that staff can be recognized for clock-in and clock-out.

#### Acceptance Criteria

1. WHEN Face_Embeddings are downloaded and stored in the local database, THE App SHALL register each Face_Embedding with the Face_Engine using the staff member's ihris_pid as the identifier.
2. WHEN the Face_Engine registration completes for a Staff_Record, THE App SHALL make that staff member available for face recognition during clock-in and clock-out.
3. IF the Face_Engine fails to register a downloaded Face_Embedding, THEN THE App SHALL log the failure with the ihris_pid and skip to the next record without interrupting the remaining registrations.

### Requirement 4: Embedding Serialization Round-Trip Integrity

**User Story:** As a developer, I want face embeddings to survive serialization and deserialization without data loss, so that recognition accuracy is preserved across devices.

#### Acceptance Criteria

1. THE FloatArrayConverter SHALL convert a float[] to a comma-separated string and back to a float[] that is element-wise equal to the original array.
2. FOR ALL valid Face_Embeddings, serializing to a comma-separated string then deserializing back to a float[] then serializing again SHALL produce an identical string (round-trip property).
3. WHEN a Face_Embedding contains special float values (NaN, positive infinity, negative infinity), THE FloatArrayConverter SHALL preserve those values through a round-trip conversion.
4. WHEN a null Face_Embedding is provided, THE FloatArrayConverter SHALL return null from both toString and fromString operations.

### Requirement 5: Incremental Sync and Conflict Resolution

**User Story:** As a facility administrator, I want the sync process to only transfer new or updated embeddings, so that bandwidth usage is minimized and sync is efficient.

#### Acceptance Criteria

1. WHEN the Embedding_Sync_Service uploads Face_Embeddings, THE Embedding_Sync_Service SHALL upload only Staff_Records where Embedding_Sync_Status is unsynced and face_data is not null.
2. WHEN the Embedding_Sync_Service downloads Face_Embeddings, THE Embedding_Sync_Service SHALL skip Staff_Records that already have face_data stored locally with Embedding_Sync_Status set to synced.
3. WHEN the Server holds a Face_Embedding for an ihris_pid that also has a locally enrolled Face_Embedding, THE Embedding_Sync_Service SHALL keep the local Face_Embedding and skip the server version.
4. WHEN a Staff_Record exists locally without face_data and the Server holds a Face_Embedding for the same ihris_pid, THE Embedding_Sync_Service SHALL download and store the server Face_Embedding.

### Requirement 6: Sync Progress and Status Reporting

**User Story:** As a facility administrator, I want to see the progress and result of embedding sync operations, so that I know whether the sync completed and how many records were transferred.

#### Acceptance Criteria

1. WHILE the Embedding_Sync_Service is uploading or downloading Face_Embeddings, THE App SHALL display the current sync progress as a count of completed records out of total records.
2. WHEN the embedding sync completes, THE App SHALL display a summary showing the number of embeddings uploaded and the number of embeddings downloaded.
3. IF the embedding sync fails partway through, THEN THE App SHALL display which records failed and allow the user to retry the sync.
4. THE App SHALL integrate the embedding sync progress into the existing DataSyncFragment sync UI alongside staff record and clock history sync categories.

### Requirement 7: Embedding Sync Triggered During Data Sync

**User Story:** As a facility administrator, I want embedding sync to happen automatically as part of the existing data sync process, so that I do not need to perform a separate action.

#### Acceptance Criteria

1. WHEN the user initiates a data sync from the DataSyncFragment, THE Embedding_Sync_Service SHALL execute embedding upload and download as part of the sync workflow after staff record sync and before clock history sync.
2. WHEN the embedding sync phase completes within the data sync workflow, THE DataSyncViewModel SHALL proceed to the next sync phase.
3. IF the embedding sync phase fails within the data sync workflow, THEN THE DataSyncViewModel SHALL report the failure and continue with the remaining sync phases.
