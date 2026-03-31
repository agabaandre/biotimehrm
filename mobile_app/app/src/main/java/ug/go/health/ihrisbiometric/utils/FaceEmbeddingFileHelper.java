package ug.go.health.ihrisbiometric.utils;

import android.content.Context;
import android.util.Base64;
import android.util.Log;

import java.io.File;
import java.io.FileInputStream;
import java.io.FileOutputStream;
import java.io.IOException;

/**
 * Handles .face files — stores the face image used for recognition engine registration.
 * Content is JPEG bytes with .face extension.
 *
 * The float[] embedding lives inside the face engine's FACE_DB directory.
 * To register on a new device, we re-run registerFaceFromBase64() using the stored image.
 *
 * Wire format (upload/download): base64-encoded .face file bytes.
 */
public class FaceEmbeddingFileHelper {

    private static final String TAG = "FaceEmbeddingFileHelper";
    private static final String FACE_DIR = "face_embeddings";

    public static File getFaceDir(Context context) {
        File dir = new File(context.getFilesDir(), FACE_DIR);
        if (!dir.exists()) dir.mkdirs();
        return dir;
    }

    /**
     * Save a base64 JPEG face image as a .face file.
     * Returns the file path, or null on failure.
     */
    public static String saveFaceImage(Context context, String ihrisPid, String base64Jpeg) {
        if (base64Jpeg == null || base64Jpeg.isEmpty()) return null;

        byte[] bytes = Base64.decode(base64Jpeg, Base64.DEFAULT);
        String safeId = ihrisPid.replaceAll("[^a-zA-Z0-9_-]", "_");
        File file = new File(getFaceDir(context), safeId + ".face");

        try (FileOutputStream fos = new FileOutputStream(file)) {
            fos.write(bytes);
            return file.getAbsolutePath();
        } catch (IOException e) {
            Log.e(TAG, "Failed to save .face file for " + ihrisPid, e);
            return null;
        }
    }

    /**
     * Read a .face file and return its content as base64 (for upload or re-registration).
     */
    public static String readAsBase64(String filePath) {
        if (filePath == null) return null;
        File file = new File(filePath);
        if (!file.exists()) return null;

        try (FileInputStream fis = new FileInputStream(file)) {
            byte[] bytes = new byte[(int) file.length()];
            fis.read(bytes);
            return Base64.encodeToString(bytes, Base64.NO_WRAP);
        } catch (IOException e) {
            Log.e(TAG, "Failed to read .face file: " + filePath, e);
            return null;
        }
    }

    /**
     * Decode base64 wire data → write .face file → return path.
     */
    public static String decodeFromBase64(Context context, String ihrisPid, String base64) {
        return saveFaceImage(context, ihrisPid, base64);
    }
}
