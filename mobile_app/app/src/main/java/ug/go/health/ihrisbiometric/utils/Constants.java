package ug.go.health.ihrisbiometric.utils;

import android.content.ContentResolver;
import android.content.ContentValues;
import android.content.Context;
import android.net.Uri;
import android.os.Build;
import android.os.Environment;
import android.provider.MediaStore;

import java.io.File;

public final class Constants {
    public static final String WORK_DIR_NAME = "ihris_biometric";
    public static final String WORK_DIR = Environment.getExternalStorageDirectory().getAbsolutePath() + File.separator + WORK_DIR_NAME;

    private Constants() {
    }

    public static String getWorkDir(Context context) {
        File externalFilesDir = context.getExternalFilesDir(WORK_DIR_NAME);
        if (externalFilesDir == null) {
            return null;
        }
        if (!externalFilesDir.exists()) {
            externalFilesDir.mkdirs();
        }
        return externalFilesDir.getAbsolutePath();
    }

    public static String getModelDir(Context context) {
        String modelDirPath = getWorkDir(context) + File.separator + "model";
        File modelDir = new File(modelDirPath);
        if (!modelDir.exists()) {
            modelDir.mkdirs();
        }
        return modelDirPath;
    }

    public static String getImageDir(Context context) {
        // If Android version is 10 (Q) or above, use MediaStore for scoped storage
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.Q) {
            // Using the MediaStore API for saving files in a public directory
            ContentResolver resolver = context.getContentResolver();
            ContentValues contentValues = new ContentValues();
            contentValues.put(MediaStore.MediaColumns.DISPLAY_NAME, "HRMAttend");
            contentValues.put(MediaStore.MediaColumns.MIME_TYPE, "image/jpeg");
            contentValues.put(MediaStore.MediaColumns.RELATIVE_PATH, Environment.DIRECTORY_PICTURES + File.separator + "HRMAttend");

            Uri uri = resolver.insert(MediaStore.Images.Media.EXTERNAL_CONTENT_URI, contentValues);
            if (uri != null) {
                return uri.toString();
            } else {
                return null; // Handle the error accordingly
            }
        } else {
            // Fallback for Android versions below 10 (Q)
            String imageDirPath = getWorkDir(context) + File.separator + "HRMAttend";
            File imageDir = new File(imageDirPath);
            if (!imageDir.exists()) {
                imageDir.mkdirs();
            }
            return imageDirPath;
        }
    }

    public static String getImageListPath(Context context) {
        return getImageDir(context) + File.separator + "imagelist.dat";
    }

    public static String[] getModelList() {
        return new String[]{"det_500m_480.onnx", "mbf.onnx", "27_80.onnx", "40_80.onnx"};
    }
}
