package ug.go.health.ihrisbiometric.helpers;

import android.content.Context;
import android.database.sqlite.SQLiteDatabase;
import android.database.sqlite.SQLiteOpenHelper;

public class DatabaseHelper extends SQLiteOpenHelper {
    private static final String DATABASE_NAME = "ihris_biometric_v2";
    private static final int DATABASE_VERSION = 1;

    // Table Names
    public static final String TABLE_CLOCK_HISTORY = "clock_history";
    public static final String TABLE_STAFF_RECORDS = "staff_records";

    // Common column names
    public static final String COLUMN_ID = "id";
    public static final String COLUMN_IHRISPID = "ihris_pid";

    // Columns for staff_records table
    public static final String COLUMN_SURNAME = "surname";
    public static final String COLUMN_FIRSTNAME = "firstname";
    public static final String COLUMN_OTHERNAME = "othername";
    public static final String COLUMN_JOB = "job";
    public static final String COLUMN_FACILITYID = "facility_id";
    public static final String COLUMN_FACILITY = "facility";
    public static final String COLUMN_FINGERPRINT_DATA = "fingerprint_data";
    public static final String COLUMN_FACE_DATA = "face_data";
    public static final String COLUMN_ENROLLED = "enrolled";
    public static final String COLUMN_TEMPLATE = "template";
    public static final String COLUMN_SYNCED = "synced";

    // Columns for clock_history table
    public static final String COLUMN_NAME = "name";
    public static final String COLUMN_CLOCK_TIME = "clock_time";
    public static final String COLUMN_CLOCK_STATUS = "clock_status";

    // Create table queries
    private static final String CREATE_TABLE_CLOCK_HISTORY =
            "CREATE TABLE " + TABLE_CLOCK_HISTORY + "(" +
                    COLUMN_ID + " INTEGER PRIMARY KEY AUTOINCREMENT," +
                    COLUMN_IHRISPID + " TEXT," +
                    COLUMN_NAME + " TEXT," +
                    COLUMN_CLOCK_TIME + " DATETIME," +
                    COLUMN_CLOCK_STATUS + " TEXT," +
                    COLUMN_SYNCED + " INTEGER DEFAULT 0" +
                    ")";

    private static final String CREATE_TABLE_STAFF_RECORDS =
            "CREATE TABLE " + TABLE_STAFF_RECORDS + "(" +
                    COLUMN_ID + " INTEGER PRIMARY KEY," +
                    COLUMN_IHRISPID + " TEXT," +
                    COLUMN_SURNAME + " TEXT," +
                    COLUMN_FIRSTNAME + " TEXT," +
                    COLUMN_OTHERNAME + " TEXT," +
                    COLUMN_JOB + " TEXT," +
                    COLUMN_FACILITYID + " INTEGER," +
                    COLUMN_FACILITY + " TEXT," +
                    COLUMN_FINGERPRINT_DATA + " BLOB," +
                    COLUMN_FACE_DATA + " BLOB," +
                    COLUMN_ENROLLED + " INTEGER DEFAULT 0," +
                    COLUMN_TEMPLATE + " INTEGER DEFAULT 0," +
                    COLUMN_SYNCED + " INTEGER DEFAULT 0" +
                    ")";

    public DatabaseHelper(Context context) {
        super(context, DATABASE_NAME, null, DATABASE_VERSION);
    }

    @Override
    public void onCreate(SQLiteDatabase db) {
        // Create the tables
        db.execSQL(CREATE_TABLE_CLOCK_HISTORY);
        db.execSQL(CREATE_TABLE_STAFF_RECORDS);
    }

    @Override
    public void onUpgrade(SQLiteDatabase db, int oldVersion, int newVersion) {
        // Drop the tables if they exist
        db.execSQL("DROP TABLE IF EXISTS " + TABLE_CLOCK_HISTORY);
        db.execSQL("DROP TABLE IF EXISTS " + TABLE_STAFF_RECORDS);

        // Create new tables
        onCreate(db);
    }
}