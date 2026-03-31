package ug.go.health.ihrisbiometric.dao;

import androidx.room.Dao;
import androidx.room.Insert;
import androidx.room.Query;
import androidx.room.Update;

import java.util.List;

import ug.go.health.ihrisbiometric.models.ClockHistory;

@Dao
public interface ClockHistoryDao {
    @Insert
    long insert(ClockHistory clockHistory);

    @Query("SELECT * FROM clock_history")
    List<ClockHistory> getAllClockHistory();

    @Query("SELECT * FROM clock_history WHERE ihris_pid = :ihrisPid ORDER BY clock_time DESC LIMIT 1")
    ClockHistory getLastClockHistory(String ihrisPid);

    @Query("SELECT * FROM clock_history WHERE synced = 0")
    List<ClockHistory> getUnsyncedClockHistory();

    @Query("SELECT COUNT(*) FROM clock_history WHERE synced = 0")
    int countUnsyncedClockRecords();

    @Query("SELECT COUNT(*) FROM clock_history WHERE synced = 1")
    int countSyncedClockRecords();

    @Update
    void update(ClockHistory clockHistory);

    @Query("SELECT * FROM clock_history WHERE (:name IS NULL OR name LIKE '%' || :name || '%') AND (:startDate IS NULL OR clock_time >= :startDate) AND (:endDate IS NULL OR clock_time <= :endDate) ORDER BY clock_time DESC")
    List<ClockHistory> getFilteredClockHistory(String name, Long startDate, Long endDate);
}
