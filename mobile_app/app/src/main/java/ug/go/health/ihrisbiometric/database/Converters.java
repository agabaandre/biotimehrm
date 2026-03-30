package ug.go.health.ihrisbiometric.database;

import androidx.room.TypeConverter;

public class Converters {
    @TypeConverter
    public static byte[] fromString(String value) {
        return value == null ? null : value.getBytes();
    }

    @TypeConverter
    public static String fromByteArray(byte[] bytes) {
        return bytes == null ? null : new String(bytes);
    }
}