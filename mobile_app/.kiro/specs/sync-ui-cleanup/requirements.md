# Requirements Document

## Introduction

The HRMAttend Android app's Data Sync screen currently presents a cluttered and confusing interface. It shows separate synced/unsynced counts for staff and clock records in side-by-side cards, two separate progress bars, a raw log-style message list, and an expandable list of individual records — all on one screen. This spec defines requirements for simplifying the sync UI into a clean, glanceable experience that lets the user understand sync status at a glance and trigger a sync with minimal friction.

This spec focuses exclusively on the sync UI presentation layer. Backend sync logic, face embedding sync, fingerprint sync, and facility switching are out of scope and will be handled in separate specs.

## Glossary

- **Sync_Screen**: The Data Sync fragment and its associated layout that displays sync status and controls
- **Sync_Summary_Card**: A UI card that shows aggregated pending and completed counts for a data category
- **Overall_Progress_Indicator**: A single progress bar or indicator that represents the combined sync progress across all data categories
- **Status_Banner**: A prominent visual element at the top of the Sync_Screen that communicates the current sync state using color and iconography
- **Sync_Button**: The primary action button that initiates the data synchronization process
- **Sync_Log**: A collapsible section that displays detailed sync messages for troubleshooting
- **Staff_Records**: Employee records stored locally that need to be synchronized with the remote server
- **Clock_Records**: Attendance clock-in/clock-out entries stored locally that need to be synchronized with the remote server

## Requirements

### Requirement 1: Unified Sync Status Display

**User Story:** As a facility administrator, I want to see the overall sync status at a glance, so that I know whether my device data is up to date without reading multiple counters.

#### Acceptance Criteria

1. WHEN the Sync_Screen is displayed, THE Status_Banner SHALL show one of four states: "Up to Date", "Pending Sync", "Syncing", or "Sync Failed"
2. WHEN no unsynced Staff_Records and no unsynced Clock_Records exist, THE Status_Banner SHALL display the "Up to Date" state with a green color indicator
3. WHEN unsynced Staff_Records or unsynced Clock_Records exist and sync is not in progress, THE Status_Banner SHALL display the "Pending Sync" state with an amber color indicator
4. WHILE sync is in progress, THE Status_Banner SHALL display the "Syncing" state with a blue color indicator
5. IF a sync operation fails, THEN THE Status_Banner SHALL display the "Sync Failed" state with a red color indicator

### Requirement 2: Simplified Pending Counts

**User Story:** As a facility administrator, I want to see how many records are waiting to sync, so that I understand the scope of pending work without navigating expandable lists.

#### Acceptance Criteria

1. WHEN the Sync_Screen is displayed, THE Sync_Summary_Card for Staff_Records SHALL display the total number of unsynced Staff_Records as a single pending count
2. WHEN the Sync_Screen is displayed, THE Sync_Summary_Card for Clock_Records SHALL display the total number of unsynced Clock_Records as a single pending count
3. WHEN all Staff_Records are synced, THE Sync_Summary_Card for Staff_Records SHALL display a checkmark icon and the text "All synced"
4. WHEN all Clock_Records are synced, THE Sync_Summary_Card for Clock_Records SHALL display a checkmark icon and the text "All synced"

### Requirement 3: Single Combined Progress Indicator

**User Story:** As a facility administrator, I want to see one progress bar during sync, so that I can track overall progress without comparing two separate bars.

#### Acceptance Criteria

1. WHILE sync is in progress, THE Overall_Progress_Indicator SHALL be visible and reflect the combined progress of Staff_Records and Clock_Records sync operations
2. THE Overall_Progress_Indicator SHALL calculate progress as the number of successfully synced items divided by the total number of items to sync, expressed as a percentage
3. WHEN sync is not in progress, THE Overall_Progress_Indicator SHALL be hidden
4. WHILE sync is in progress, THE Sync_Screen SHALL display a text label below the Overall_Progress_Indicator showing the count of synced items out of total items (e.g. "12 of 34 records synced")

### Requirement 4: Clean Sync Action Button

**User Story:** As a facility administrator, I want a clear and responsive sync button, so that I can start syncing with confidence and know when it is working.

#### Acceptance Criteria

1. WHEN unsynced records exist and sync is not in progress, THE Sync_Button SHALL be enabled and display the text "Sync Now"
2. WHILE sync is in progress, THE Sync_Button SHALL be disabled and display the text "Syncing…"
3. WHEN no unsynced records exist, THE Sync_Button SHALL be enabled and display the text "Sync Now"
4. IF a sync operation completes successfully, THEN THE Sync_Button SHALL return to the enabled state and display the text "Sync Now"

### Requirement 5: Collapsible Sync Log

**User Story:** As a facility administrator, I want detailed sync messages available but not cluttering the main view, so that I can troubleshoot issues when needed without visual noise during normal use.

#### Acceptance Criteria

1. THE Sync_Screen SHALL display a "Show Details" toggle below the progress area
2. WHEN the user taps the "Show Details" toggle, THE Sync_Log SHALL expand to show the list of sync messages
3. WHEN the Sync_Log is expanded and the user taps the toggle, THE Sync_Log SHALL collapse and hide the sync messages
4. THE Sync_Log SHALL default to the collapsed state each time the Sync_Screen is opened
5. WHEN a sync operation fails, THE Sync_Log SHALL automatically expand to show the error details

### Requirement 6: Remove Expandable Record Lists from Main Sync View

**User Story:** As a facility administrator, I want the sync screen to focus on status and actions, so that I am not overwhelmed by lists of individual records.

#### Acceptance Criteria

1. THE Sync_Screen SHALL NOT display the expandable list of individual Staff_Records pending sync
2. THE Sync_Screen SHALL NOT display the expandable list of individual Clock_Records pending sync
3. THE Sync_Screen SHALL NOT display the expandable list of Staff_Records with missing information
