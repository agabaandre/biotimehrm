package ug.go.health.ihrisbiometric.converters;

import androidx.room.TypeConverter;

public class ByteArrayConverter {
    @TypeConverter
    public static byte[] fromString(String value) {
        if (value == null) {
            return null;
        }
        return android.util.Base64.decode(value, android.util.Base64.DEFAULT);
    }

    @TypeConverter
    public static String toString(byte[] bytes) {
        if (bytes == null) {
            return null;
        }
        return android.util.Base64.encodeToString(bytes, android.util.Base64.DEFAULT);
    }
}
