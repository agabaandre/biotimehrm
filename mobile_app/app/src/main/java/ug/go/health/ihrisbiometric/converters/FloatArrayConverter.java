package ug.go.health.ihrisbiometric.converters;

import androidx.room.TypeConverter;

public class FloatArrayConverter {
    @TypeConverter
    public static float[] fromString(String value) {
        if (value == null) {
            return null;
        }
        String[] strings = value.split(",");
        float[] result = new float[strings.length];
        for (int i = 0; i < result.length; i++) {
            result[i] = Float.parseFloat(strings[i]);
        }
        return result;
    }

    @TypeConverter
    public static String toString(float[] array) {
        if (array == null) {
            return null;
        }
        StringBuilder sb = new StringBuilder();
        for (int i = 0; i < array.length; i++) {
            sb.append(array[i]);
            if (i < array.length - 1) {
                sb.append(",");
            }
        }
        return sb.toString();
    }
}
