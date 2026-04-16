# Requirements Document

## Introduction

The iHRIS Biometric (HRMAttend) app captures fingerprint templates locally on devices equipped with serial port fingerprint scanners. A staff member enrolled on one device cannot be recognized on another device because the fingerprint template data (byte arrays) is never uploaded to the server or downloaded by other devices. This feature adds the ability to upload fingerprint templates to the server and download them onto any supported device, enabling cross-device fingerprint recognition for biometric attendance.

## Glossary

- **App**: The iHRIS Biometric (HRMAttend) Android application running on a device.
- **Server**: The remote iHRIS backend that exposes REST API endpoints for data synchronization.
- **Fingerprint_Template**: A byte array (byte[]) representing a staff member's fingerprint data, captured by the serial port fingerprint scanner hardware.
- **Staff_Record**: A local Room database entity containing staff information, enrollment status, and biometric data including Fingerprint_Template.
- **Fingerprint_Sync_Service**: The component responsible for uploading and downloading Fingerprint_Templates between the App and the Server.
- **Scanner_Device**: The serial port fingerprint scanner hardware (DeviceType.SCANNER) used to capture and recognize fingerprints.
- **ByteArrayConverter**: The Room TypeConverter that serializes byte[] to Base64 strings and deserializes them back.
- **Fingerprint_Sync_Status**: A per-record flag tracking whether a Staff_Record's Fingerprint_Template has been synchronized with the Server.
- **ihris_pid**: A unique identifier for a staff member, used to associate Fingerprint_Templates with the correct person across devices.
- **template_id**: An integer identifier assigned to a fingerprint template on the local Scanner_Device, used for on-device recognition lookups.
- **facility_id**: An identifier for the health facility, used to scope fingerprint downloads to the relevant staff.

## Requirements

### Requirement 1: Upload Fingerprint Templates to Server

**User Story:** As a facility administrator, I want fingerprint templates captured during enrollment to be uploaded to the server, so that the biometric data is available for other devices to download.

#### Acceptance Criteria

1. WHEN a Staff_Record has fingerprint_enrolled set to true and fingerprint_data is not null and Fingerprint_Sync_Status is unsynced, THE Fingerprint_Sync_Service SHALL upload the Fingerprint_Template as a Base64-encoded string along with the ihris_pid to the Server.
2. WHEN the Server responds with a success status, THE Fingerprint_Sync_Service SHALL update the Fingerprint_Sync_Status of the Staff_Record to synced in the local database.
3. IF the Server responds with an error status, THEN THE Fingerprint_Sync_Service SHALL retain the Fingerprint_Sync_Status as unsynced and log the error message.
4. IF a network failure occurs during upload, THEN THE Fingerprint_Sync_Service SHALL retain the Fingerprint_Sync_Status as unsynced and queue the record for retry on the next sync cycle.

### Requirement 2: Download Fingerprint Templates from Server

**User Story:** As a facility administrator, I want to download fingerprint templates from the server onto a new device, so that staff enrolled on other devices can be recognized locally.

#### Acceptance Criteria

1. WHEN the App initiates a fingerprint download, THE Fingerprint_Sync_Service SHALL request all Fingerprint_Templates associated with the facility_id from the Server.
2. WHEN the Server returns Fingerprint_Template records, THE Fingerprint_Sync_Service SHALL decode each Base64 string back into a byte[] using ByteArrayConverter and store the result in the corresponding Staff_Record's fingerprint_data column.
3. WHEN a downloaded Fingerprint_Template is stored in the local database, THE Fingerprint_Sync_Service SHALL set fingerprint_enrolled to true and Fingerprint_Sync_Status to synced on the corresponding Staff_Record.
4. IF the Server returns an empty list of Fingerprint_Templates for the facility, THEN THE Fingerprint_Sync_Service SHALL report that no fingerprint templates are available for download.
5. IF a network failure occurs during download, THEN THE Fingerprint_Sync_Service SHALL display an error message to the user and allow retry.

### Requirement 3: Register Downloaded Templates on Scanner Device

**User Story:** As a facility administrator, I want downloaded fingerprint templates to be registered on the local scanner device, so that staff can be recognized for clock-in and clock-out via fingerprint.

#### Acceptance Criteria

1. WHEN Fingerprint_Templates are downloaded and stored in the local database, THE App SHALL register each Fingerprint_Template on the Scanner_Device and assign a local template_id to the Staff_Record.
2. WHEN the Scanner_Device registration completes for a Staff_Record, THE App SHALL make that staff member available for fingerprint recognition during clock-in and clock-out.
3. IF the Scanner_Device fails to register a downloaded Fingerprint_Template, THEN THE App SHALL log the failure with the ihris_pid and skip to the next record without interrupting the remaining registrations.
4. IF the Scanner_Device is not connected when templates are downloaded, THEN THE App SHALL store the templates locally and register them when the Scanner_Device becomes available.

### Requirement 4: Fingerprint Template Serialization Round-Trip Integrity

**User Story:** As a developer, I want fingerprint templates to survive serialization and deserialization without data loss, so that recognition accuracy is preserved across devices.

#### Acceptance Criteria

1. THE ByteArrayConverter SHALL convert a byte[] to a Base64 string and back to a byte[] that is element-wise equal to the original array.
2. FOR ALL valid Fingerprint_Templates, encoding to a Base64 string then decoding back to a byte[] then encoding again SHALL produce an identical string (round-trip property).
3. WHEN a null Fingerprint_Template is provided, THE ByteArrayConverter SHALL return null from both toString and fromString operations.
4. WHEN an empty byte array is provided, THE ByteArrayConverter SHALL produce a valid Base64 string that decodes back to an empty byte array.

### Requirement 5: Incremental Sync and Conflict Resolution

**User Story:** As a facility administrator, I want the sync process to only transfer new or updated templates, so that bandwidth usage is minimized and sync is efficient.

#### Acceptance Criteria

1. WHEN the Fingerprint_Sync_Service uploads Fingerprint_Templates, THE Fingerprint_Sync_Service SHALL upload only Staff_Records where Fingerprint_Sync_Status is unsynced and fingerprint_data is not null.
2. WHEN the Fingerprint_Sync_Service downloads Fingerprint_Templates, THE Fingerprint_Sync_Service SHALL skip Staff_Records that already have fingerprint_data stored locally with Fingerprint_Sync_Status set to synced.
3. WHEN the Server holds a Fingerprint_Template for an ihris_pid that also has a locally enrolled Fingerprint_Template, THE Fingerprint_Sync_Service SHALL keep the local Fingerprint_Template and skip the server version.
4. WHEN a Staff_Record exists locally without fingerprint_data and the Server holds a Fingerprint_Template for the same ihris_pid, THE Fingerprint_Sync_Service SHALL download and store the server Fingerprint_Template.

### Requirement 6: Sync Progress and Status Reporting

**User Story:** As a facility administrator, I want to see the progress and result of fingerprint sync operations, so that I know whether the sync completed and how many records were transferred.

#### Acceptance Criteria

1. WHILE the Fingerprint_Sync_Service is uploading or downloading Fingerprint_Templates, THE App SHALL display the current sync progress as a count of completed records out of total records.
2. WHEN the fingerprint sync completes, THE App SHALL display a summary showing the number of templates uploaded and the number of templates downloaded.
3. IF the fingerprint sync fails partway through, THEN THE App SHALL display which records failed and allow the user to retry the sync.
4. THE App SHALL integrate the fingerprint sync progress into the existing DataSyncFragment sync UI alongside staff record and clock history sync categories.

### Requirement 7: Fingerprint Sync Triggered During Data Sync

**User Story:** As a facility administrator, I want fingerprint sync to happen automatically as part of the existing data sync process, so that I do not need to perform a separate action.

#### Acceptance Criteria

1. WHEN the user initiates a data sync from the DataSyncFragment, THE Fingerprint_Sync_Service SHALL execute fingerprint upload and download as part of the sync workflow after staff record sync and before clock history sync.
2. WHEN the fingerprint sync phase completes within the data sync workflow, THE DataSyncViewModel SHALL proceed to the next sync phase.
3. IF the fingerprint sync phase fails within the data sync workflow, THEN THE DataSyncViewModel SHALL report the failure and continue with the remaining sync phases.

### Requirement 8: API Endpoints for Fingerprint Sync

**User Story:** As a developer, I want dedicated API endpoints for fingerprint template upload and download, so that the App can exchange fingerprint data with the Server.

#### Acceptance Criteria

1. THE ApiInterface SHALL define a POST endpoint that accepts a JSON body containing ihris_pid and a Base64-encoded Fingerprint_Template string and returns a FingerprintUploadResponse.
2. THE ApiInterface SHALL define a GET endpoint that accepts a facility_id parameter and returns a list of Staff_Records with their ihris_pid and Base64-encoded Fingerprint_Template data.
3. WHEN the upload endpoint receives a valid request, THE Server SHALL store the Fingerprint_Template and respond with a success status.
4. WHEN the download endpoint receives a valid facility_id, THE Server SHALL return all Fingerprint_Templates associated with staff in that facility.
