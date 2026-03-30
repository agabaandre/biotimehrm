# Implementation Plan: Sync UI Cleanup

## Overview

Simplify the Data Sync screen from a multi-card, multi-progress-bar layout into a clean, glanceable interface with a status banner, pending counts, combined progress, collapsible log, and no expandable record lists. All changes are in the presentation layer: ViewModel, Fragment, XML layout, and adapter removal.

## Tasks

- [ ] 1. Create SyncStatusBannerState enum and add new ViewModel LiveData
  - [ ] 1.1 Create `SyncStatusBannerState` enum in the models package
    - Create `SyncStatusBannerState.java` with UP_TO_DATE, PENDING_SYNC, SYNCING, SYNC_FAILED values
    - Include `getColorRes()`, `getIconRes()`, and `getLabel()` methods with switch statements
    - _Requirements: 1.1, 1.2, 1.3, 1.4, 1.5_

  - [ ] 1.2 Add new LiveData fields and `getStatusBannerState()` to `DataSyncViewModel`
    - Add `combinedProgressLiveData` (MediatorLiveData<Integer>) merging staff + clock progress
    - Add `syncedItemsCountLiveData` and `totalItemsToSyncLiveData` (MutableLiveData<Integer>)
    - Add `getStatusBannerState()` returning a MediatorLiveData<SyncStatusBannerState> that combines syncStatus + unsynced counts
    - Update `updateStaffSyncProgress()` and `updateClockSyncProgress()` to post to `syncedItemsCountLiveData` and `combinedProgressLiveData`
    - Post `totalItemsToSync` value to `totalItemsToSyncLiveData` in `handleUnsyncedStaffRecords()`
    - Add public getters: `getCombinedProgress()`, `getSyncedItemsCount()`, `getTotalItemsToSync()`
    - _Requirements: 1.1, 1.2, 1.3, 1.4, 1.5, 3.1, 3.2, 3.4_

  - [ ]* 1.3 Write property tests for SyncStatusBannerState mapping (jqwik)
    - **Property 1: Status banner state mapping is correct and exhaustive**
    - Generate random SyncStatus × unsyncedStaff (0..1000) × unsyncedClock (0..1000), assert derived state matches spec rules
    - **Validates: Requirements 1.1, 1.2, 1.3, 1.4, 1.5**

  - [ ]* 1.4 Write property tests for combined progress calculation (jqwik)
    - **Property 3: Combined progress calculation**
    - Generate random syncedCount (0..total) and totalCount (1..10000), assert result equals (synced * 100) / total and is in [0, 100]
    - **Validates: Requirements 3.2**

- [ ] 2. Rewrite fragment_data_sync.xml layout
  - [ ] 2.1 Replace the existing layout with the new simplified structure
    - Add status banner LinearLayout (ImageView icon + TextView status text) below the toolbar
    - Replace the two count cards with two MaterialCardView summary cards showing only pending count or "All synced" + checkmark
    - Replace two ProgressBars with a single combined ProgressBar (hidden by default)
    - Add progress text label TextView below the progress bar (hidden by default)
    - Add "Show Details" toggle TextView below the progress area
    - Wrap the existing syncMessagesListView in a syncLogContainer LinearLayout with visibility GONE
    - Update the Sync button text to "Sync Now"
    - Remove all expandable list view references from the layout
    - _Requirements: 1.1, 2.1, 2.2, 2.3, 2.4, 3.1, 3.3, 3.4, 4.1, 5.1, 5.4, 6.1, 6.2, 6.3_


- [ ] 3. Rewrite DataSyncFragment to use new layout and ViewModel
  - [ ] 3.1 Update view references and remove old code in `DataSyncFragment`
    - Remove old view references: `tvStaffSyncedCount`, `tvStaffUnsyncedCount`, `tvClockSyncedCount`, `tvClockUnsyncedCount`, `staffProgressBar`, `clockProgressBar`, `expandableListView`, `syncCategoryAdapter`, `categories`, `categoryItems`
    - Remove `initializeExpandableListView()` method entirely
    - Remove `updateStaffRecordsReadyForSync()`, `updateStaffRecordsMissingInfo()`, `updateClockHistoryReadyForSync()` methods
    - Remove `updateStaffProgressBar()`, `updateClockProgressBar()` methods
    - Add new view references: `statusBanner`, `statusBannerIcon`, `statusBannerText`, `tvStaffPending`, `tvClockPending`, `combinedProgressBar`, `tvProgressLabel`, `toggleSyncLog`, `syncLogContainer`
    - Add `isSyncLogExpanded` boolean field (default false)
    - Remove SyncCategoryAdapter import
    - _Requirements: 6.1, 6.2, 6.3_

  - [ ] 3.2 Rewrite `observeViewModel()` to observe new LiveData
    - Observe `getStatusBannerState()` to set banner background color, icon, and text based on SyncStatusBannerState
    - Observe `unsyncedStaffCountLiveData` to show pending count or "All synced" with checkmark icon on staff card
    - Observe `unsyncedClockCountLiveData` to show pending count or "All synced" with checkmark icon on clock card
    - Observe `combinedProgressLiveData` for the single progress bar value
    - Observe `syncedItemsCountLiveData` + `totalItemsToSyncLiveData` to format "X of Y records synced" label
    - Observe `syncStatusLiveData` to show/hide progress bar and label (visible only during IN_PROGRESS)
    - _Requirements: 1.1, 1.2, 1.3, 1.4, 1.5, 2.1, 2.2, 2.3, 2.4, 3.1, 3.2, 3.3, 3.4_

  - [ ] 3.3 Rewrite `updateSyncStatus()` for new button and auto-expand behavior
    - On IN_PROGRESS: disable button, set text "Syncing…"
    - On all other states: enable button, set text "Sync Now"
    - On FAILED: auto-expand sync log by setting `isSyncLogExpanded = true` and syncLogContainer visibility to VISIBLE
    - _Requirements: 4.1, 4.2, 4.3, 4.4, 5.5_

  - [ ] 3.4 Add sync log toggle click listener
    - Set click listener on `toggleSyncLog` that flips `isSyncLogExpanded` and sets syncLogContainer visibility accordingly
    - Update toggle text between "Show Details" and "Hide Details"
    - _Requirements: 5.1, 5.2, 5.3, 5.4_

  - [ ]* 3.5 Write property tests for pending count display (jqwik)
    - **Property 2: Pending count display shows count or "All synced"**
    - Generate random int count (0..10000), assert formatted string contains count when > 0, or "All synced" when 0
    - **Validates: Requirements 2.1, 2.2, 2.3, 2.4**

  - [ ]* 3.6 Write property tests for progress visibility and button state (jqwik)
    - **Property 4: Progress indicator visibility matches sync status**
    - Generate random SyncStatus, assert visibility == VISIBLE iff IN_PROGRESS
    - **Property 6: Sync button state follows sync status**
    - Generate random SyncStatus, assert disabled + "Syncing…" iff IN_PROGRESS, else enabled + "Sync Now"
    - **Validates: Requirements 3.1, 3.3, 4.1, 4.2, 4.3, 4.4**

  - [ ]* 3.7 Write property tests for progress label and log toggle (jqwik)
    - **Property 5: Progress label formatting**
    - Generate random syncedCount and totalCount, assert string matches "{synced} of {total} records synced"
    - **Property 7: Sync log toggle is a round-trip**
    - Generate random boolean initial state, toggle twice, assert final == initial
    - **Property 8: Sync log auto-expands on failure**
    - Generate random sync message list, transition to FAILED, assert log is expanded
    - **Validates: Requirements 3.4, 5.2, 5.3, 5.5**

- [ ] 4. Checkpoint - Ensure all tests pass
  - Ensure all tests pass, ask the user if questions arise.

- [ ] 5. Delete SyncCategoryAdapter and clean up unused references
  - [ ] 5.1 Delete `SyncCategoryAdapter.java`
    - Remove `app/src/main/java/ug/go/health/ihrisbiometric/adapters/SyncCategoryAdapter.java`
    - Verify no other files import or reference `SyncCategoryAdapter`
    - _Requirements: 6.1, 6.2, 6.3_

  - [ ] 5.2 Remove unused LiveData getters from `DataSyncViewModel` (if safe)
    - Remove `getSyncedStaffCount()`, `getSyncedClockCount()` getters if no other fragment observes them
    - Remove `getStaffSyncProgress()`, `getClockSyncProgress()` getters if no other fragment observes them
    - Remove `getStaffRecordsReadyForSync()`, `getStaffRecordsMissingInfo()`, `getClockHistoryReadyForSync()` getters if no other fragment observes them
    - Remove `updateSyncCategories()` call from constructor if the LiveData fields are removed
    - _Requirements: 6.1, 6.2, 6.3_

  - [ ]* 5.3 Write unit tests for edge cases
    - Test sync log defaults to collapsed on fragment creation (Req 5.4)
    - Test progress calculation edge case: 0 synced of 1 total = 0%
    - Test progress calculation edge case: total equals synced = 100%
    - Test banner state after full sync cycle: IDLE → IN_PROGRESS → COMPLETED with 0 remaining → UP_TO_DATE
    - _Requirements: 5.4, 3.2, 1.1, 1.2_

- [ ] 6. Final checkpoint - Ensure all tests pass
  - Ensure all tests pass, ask the user if questions arise.

## Notes

- Tasks marked with `*` are optional and can be skipped for faster MVP
- Each task references specific requirements for traceability
- Checkpoints ensure incremental validation
- Property tests use jqwik (JUnit 5 PBT engine) and validate universal correctness properties from the design document
- No backend sync logic, Room entities, or API calls are modified — all changes are presentation layer only
