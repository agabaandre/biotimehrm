package ug.go.health.ihrisbiometric.database;


import android.content.Context;

import androidx.room.Database;
import androidx.room.Room;
import androidx.room.RoomDatabase;
import androidx.room.TypeConverters;

import ug.go.health.ihrisbiometric.dao.ClockHistoryDao;
import ug.go.health.ihrisbiometric.dao.StaffRecordDao;
import ug.go.health.ihrisbiometric.models.ClockHistory;
import ug.go.health.ihrisbiometric.models.StaffRecord;

@Database(entities = {ClockHistory.class, StaffRecord.class}, version = 4, exportSchema = false)
@TypeConverters({Converters.class})
public abstract class AppDatabase extends RoomDatabase {
    private static final String DATABASE_NAME = "ihris_biometric_v2";

    private static AppDatabase instance;

    public abstract ClockHistoryDao clockHistoryDao();
    public abstract StaffRecordDao staffRecordDao();

    public static synchronized AppDatabase getInstance(Context context) {
        if (instance == null) {
            instance = Room.databaseBuilder(context.getApplicationContext(),
                            AppDatabase.class, DATABASE_NAME)
                    .fallbackToDestructiveMigration()
                    .build();
        }
        return instance;
    }
}