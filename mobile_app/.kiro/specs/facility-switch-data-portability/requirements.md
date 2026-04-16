# Requirements Document

## Introduction

The iHRIS Biometric (HRMAttend) app currently ties downloaded staff records and biometric data to a single facility context. When a user switches facilities (by logging in with a different facility assignment or changing the active facility), the app either clears existing data or overwrites it with the new facility's staff list. This results in loss of previously downloaded staff information, face embeddings, and fingerprint templates. Additionally, the current staff download only fetches basic staff information without biometric data, requiring re-enrollment on each device.

This feature ensures that facility switching preserves all previously downloaded data across facilities, and that the data sync process downloads complete biometric data (face embeddings and fingerprint templates) alongside staff information. Any supported device should be able to use the downloaded biometric data for attendance recording without requiring local re-enrollment.

## Glossary

- **App**: The iHRIS Biometric (HRMAttend) Android application running on a device.
- **Server**: The remote iHRIS backend that exposes REST API endpoints for data synchronization.
- **Staff_Record**: A local Room database entity containing staff information, enrollment status, and biometric data including Face_Embedding and Fingerprint_Template.
- **Face_Embedding**: A float array (float[]) representing a staff member's facial features, stored in the face_data column of Staff_Record.
- **Fingerprint_Template**: A byte array (byte[]) representing a staff member's fingerprint data, stored in the fingerprint_data column of Staff_Record.
- **Clock_History**: A local Room database entity containing attendance clock-in/clock-out entries, each associated with a facility_id.
- **Active_Facility**: The facility currently selected by the user for attendance operations, identified by facility_id stored in SessionService.
- **Facility_Switch**: The action of changing the Active_Facility from one facility to another, either through re-login or a facility selection UI.
- **Biometric_Data**: The combination of Face_Embedding, face_image, and Fingerprint_Template associated with a Staff_Record.
- **Face_Engine**: The JNI-based face recognition engine (JniHelper) that registers and recognizes faces using Face_Embeddings.
- **Scanner_Device**: The serial port fingerprint scanner hardware used to capture and recognize fingerprints.
- **SessionService**: The SharedPreferences-based service that stores the current user session, Active_Facility, and device settings.
- **Data_Sync**: The process of downloading staff records and biometric data from the Server and uploading local attendance records to the Server.
- **ihris_pid**: A unique identifier for a staff member, used to associate biometric data with the correct person across devices and facilities.
- **facility_id**: An identifier for a health facility, used to scope staff records and attendance data.

## Requirements

### Requirement 1: Preserve Staff Records Across Facility Switches

**User Story:** As a facility administrator, I want previously downloaded staff records to remain in the local database when I switch to a different facility, so that I do not lose data that has already been downloaded.

#### Acceptance Criteria

1. WHEN a Facility_Switch occurs, THE App SHALL retain all existing Staff_Records in the local Room database regardless of their facility_id.
2. WHEN a Facility_Switch occurs, THE App SHALL download Staff_Records for the new Active_Facility and merge them into the local database using ihris_pid as the unique key.
3. WHEN a Staff_Record with the same ihris_pid already exists locally, THE App SHALL update the staff information fields (surname, firstname, othername, job, facility, facility_id) from the Server and preserve locally stored Biometric_Data.
4. WHEN a Facility_Switch occurs, THE App SHALL retain all locally enrolled Biometric_Data (Face_Embedding, face_image, Fingerprint_Template) for Staff_Records from the previous facility.

### Requirement 2: Preserve Clock History Across Facility Switches

**User Story:** As a facility administrator, I want attendance records from previous facilities to remain on the device after switching, so that unsynced clock records are not lost.

#### Acceptance Criteria

1. WHEN a Facility_Switch occurs, THE App SHALL retain all existing Clock_History records in the local Room database regardless of their facility_id.
2. WHEN a Facility_Switch occurs, THE App SHALL retain all unsynced Clock_History records and continue to include them in subsequent Data_Sync operations.
3. THE App SHALL associate each new Clock_History record with the Active_Facility's facility_id at the time of clock-in or clock-out.

### Requirement 3: Download Complete Biometric Data with Staff Records

**User Story:** As a facility administrator, I want the data sync process to download face embeddings and fingerprint templates alongside staff information, so that any supported device can use the biometric data for attendance without re-enrollment.

#### Acceptance Criteria

1. WHEN the App initiates a staff data download for the Active_Facility, THE Data_Sync process SHALL request staff records including Face_Embedding data and Fingerprint_Template data from the Server.
2. WHEN the Server returns Staff_Records with Face_Embedding data, THE App SHALL deserialize the Face_Embedding and store it in the face_data column of the corresponding Staff_Record and set face_enrolled to true.
3. WHEN the Server returns Staff_Records with Fingerprint_Template data, THE App SHALL decode the Fingerprint_Template and store it in the fingerprint_data column of the corresponding Staff_Record and set fingerprint_enrolled to true.
4. WHEN a Staff_Record already has locally enrolled Biometric_Data, THE App SHALL preserve the local Biometric_Data and skip the server version for that record.
5. IF the Server returns a Staff_Record without Biometric_Data, THEN THE App SHALL store the staff information and leave the biometric fields unchanged.

### Requirement 4: Register Downloaded Biometric Data for Attendance

**User Story:** As a facility administrator, I want downloaded biometric data to be automatically registered with the on-device recognition engines, so that staff can be recognized for attendance on any supported device.

#### Acceptance Criteria

1. WHEN Face_Embeddings are downloaded and stored in the local database, THE App SHALL register each Face_Embedding with the Face_Engine using the staff member's ihris_pid as the identifier.
2. WHEN Fingerprint_Templates are downloaded and stored in the local database, THE App SHALL register each Fingerprint_Template on the Scanner_Device and assign a local template_id to the Staff_Record.
3. IF the Face_Engine fails to register a downloaded Face_Embedding, THEN THE App SHALL log the failure with the ihris_pid and continue registering the remaining records.
4. IF the Scanner_Device is not connected when Fingerprint_Templates are downloaded, THEN THE App SHALL store the templates locally and register them when the Scanner_Device becomes available.
5. WHEN biometric registration completes for a Staff_Record, THE App SHALL make that staff member available for attendance recognition using the registered biometric modality.

### Requirement 5: Facility-Scoped Staff List Display

**User Story:** As a facility administrator, I want the staff list and attendance UI to show only staff from the active facility, so that I work with the relevant staff while data from other facilities remains safely stored.

#### Acceptance Criteria

1. WHILE an Active_Facility is set, THE App SHALL display only Staff_Records matching the Active_Facility's facility_id in the staff list and attendance UI.
2. WHILE an Active_Facility is set, THE App SHALL load Face_Embeddings into the Face_Engine only for Staff_Records matching the Active_Facility's facility_id.
3. WHEN a Facility_Switch occurs, THE App SHALL unload Face_Embeddings for the previous facility from the Face_Engine and load Face_Embeddings for the new Active_Facility.
4. THE App SHALL retain Staff_Records from all facilities in the local database regardless of which facility_id is displayed in the UI.

### Requirement 6: Facility Switch Confirmation and Data Safety

**User Story:** As a facility administrator, I want to be warned before switching facilities if there are unsynced records, so that I can sync pending data before switching and avoid data loss.

#### Acceptance Criteria

1. WHEN the user initiates a Facility_Switch and unsynced Clock_History records exist for the current Active_Facility, THE App SHALL display a confirmation dialog warning that unsynced attendance records exist.
2. WHEN the confirmation dialog is displayed, THE App SHALL offer the user the option to sync pending records before switching, proceed without syncing, or cancel the switch.
3. IF the user chooses to sync before switching, THEN THE App SHALL execute a Data_Sync for the current Active_Facility and proceed with the Facility_Switch after sync completes.
4. IF the user chooses to proceed without syncing, THEN THE App SHALL perform the Facility_Switch and retain all unsynced records for later sync.

### Requirement 7: Multi-Facility Data Sync

**User Story:** As a facility administrator, I want the sync process to upload attendance records for all facilities stored on the device, so that records from previously active facilities are not stranded.

#### Acceptance Criteria

1. WHEN the user initiates a Data_Sync, THE App SHALL upload all unsynced Clock_History records regardless of their facility_id.
2. WHEN uploading Clock_History records, THE App SHALL include the facility_id field in each record sent to the Server so the Server can associate the record with the correct facility.
3. WHEN the Data_Sync completes, THE App SHALL mark all successfully uploaded Clock_History records as synced regardless of their facility_id.

### Requirement 8: Biometric Data Serialization Round-Trip Integrity

**User Story:** As a developer, I want biometric data to survive the download-store-register cycle without data loss, so that recognition accuracy is preserved when biometric data is used on a different device than where it was originally enrolled.

#### Acceptance Criteria

1. FOR ALL valid Face_Embeddings, downloading from the Server then storing in the local database then loading for Face_Engine registration SHALL produce a float[] that is element-wise equal to the original enrolled Face_Embedding.
2. FOR ALL valid Fingerprint_Templates, downloading from the Server then storing in the local database then loading for Scanner_Device registration SHALL produce a byte[] that is element-wise equal to the original enrolled Fingerprint_Template.
3. THE FloatArrayConverter SHALL convert a float[] to a comma-separated string and back to a float[] that is element-wise equal to the original array (round-trip property).
4. THE ByteArrayConverter SHALL convert a byte[] to a Base64 string and back to a byte[] that is element-wise equal to the original array (round-trip property).
