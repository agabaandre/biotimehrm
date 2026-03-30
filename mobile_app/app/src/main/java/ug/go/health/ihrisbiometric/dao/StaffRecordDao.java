package ug.go.health.ihrisbiometric.dao;

import androidx.room.Dao;
import androidx.room.Insert;
import androidx.room.OnConflictStrategy;
import androidx.room.Query;
import androidx.room.Update;

import java.util.List;

import ug.go.health.ihrisbiometric.models.StaffRecord;

@Dao
public interface StaffRecordDao {
    @Insert(onConflict = OnConflictStrategy.REPLACE)
    long insert(StaffRecord staffRecord);

    @Query("SELECT * FROM staff_records WHERE is_deleted = 0")
    List<StaffRecord> getAllStaffRecords();

    @Query("SELECT * FROM staff_records WHERE ihris_pid = :ihrisPid AND is_deleted = 0")
    StaffRecord getStaffRecordByIhrisPid(String ihrisPid);

    @Query("SELECT * FROM staff_records WHERE template_id = :templateId AND is_deleted = 0")
    StaffRecord getStaffRecordByTemplate(int templateId);

    @Query("SELECT * FROM staff_records WHERE face_data IS NOT NULL AND is_deleted = 0")
    List<StaffRecord> getStaffRecordsWithEmbeddings();

    @Query("SELECT * FROM staff_records WHERE synced = 0")
    List<StaffRecord> getUnsyncedStaffRecords();

    @Query("SELECT COUNT(*) FROM staff_records WHERE synced = 0")
    int countUnsyncedStaffRecords();

    // Records that need to push enrollment data to server:
    // either metadata unsynced, OR has local biometric data not yet uploaded
    @Query("SELECT * FROM staff_records WHERE is_deleted = 0 AND (" +
           "synced = 0 OR " +
           "(fingerprint_enrolled = 1 AND fingerprint_path IS NOT NULL AND fingerprint_synced = 0) OR " +
           "(face_enrolled = 1 AND face_data IS NOT NULL AND embedding_synced = 0)" +
           ")")
    List<StaffRecord> getRecordsNeedingServerSync();

    @Query("SELECT * FROM staff_records WHERE synced = 0 AND is_deleted = 0")
    List<StaffRecord> getStaffRecordsReadyForSync();

    @Query("SELECT * FROM staff_records WHERE synced = 0 AND is_deleted = 0")
    List<StaffRecord> getStaffRecordsMissingInfo();

    @Query("SELECT COUNT(*) FROM staff_records WHERE synced = 1")
    int countSyncedStaffRecords();

    @Query("SELECT COUNT(*) FROM staff_records WHERE is_deleted = 0")
    int countStaffRecords();

    @Update
    void update(StaffRecord staffRecord);

    @Query("DELETE FROM staff_records")
    void deleteAll();

    @Query("SELECT * FROM staff_records WHERE (ihris_pid LIKE :filter OR template_id LIKE :filter) AND enrolled_at BETWEEN :startTimestamp AND :endTimestamp AND is_deleted = 0")
    List<StaffRecord> getFilteredStaffRecords(String filter, Long startTimestamp, Long endTimestamp);

    @Query("SELECT * FROM staff_records WHERE is_deleted = 1")
    List<StaffRecord> getDeletedStaffRecords();

    @Query("SELECT * FROM staff_records WHERE id = :id")
    StaffRecord getStaffRecordById(int id);

    @Query("DELETE FROM staff_records WHERE id = :id")
    void deleteById(int id);

    @Query("SELECT * FROM staff_records WHERE fingerprint_enrolled = 1 AND fingerprint_path IS NOT NULL AND fingerprint_synced = 0")
    List<StaffRecord> getStaffRecordsWithUnsyncedFingerprints();

    @Query("SELECT * FROM staff_records WHERE fingerprint_enrolled = 1 AND fingerprint_path IS NOT NULL AND template_id = 0")
    List<StaffRecord> getStaffRecordsWithUnregisteredFingerprints();

    @Query("SELECT COUNT(*) FROM staff_records WHERE fingerprint_enrolled = 1 AND fingerprint_path IS NOT NULL AND fingerprint_synced = 0")
    int countUnsyncedFingerprints();

    @Query("SELECT COUNT(*) FROM staff_records WHERE fingerprint_synced = 1")
    int countSyncedFingerprints();

    @Query("SELECT * FROM staff_records WHERE face_enrolled = 1 AND face_data IS NOT NULL AND embedding_synced = 0")
    List<StaffRecord> getStaffRecordsWithUnsyncedEmbeddings();

    @Query("SELECT COUNT(*) FROM staff_records WHERE face_enrolled = 1 AND face_data IS NOT NULL AND embedding_synced = 0")
    int countUnsyncedEmbeddings();

    @Query("SELECT COUNT(*) FROM staff_records WHERE embedding_synced = 1")
    int countSyncedEmbeddings();
}
