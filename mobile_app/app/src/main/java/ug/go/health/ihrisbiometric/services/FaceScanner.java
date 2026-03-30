package ug.go.health.ihrisbiometric.services;

import android.content.ContentValues;
import android.content.Context;
import android.content.res.AssetManager;
import android.graphics.Bitmap;
import android.graphics.BitmapFactory;
import android.net.Uri;
import android.os.Build;
import android.os.Environment;
import android.provider.MediaStore;
import android.util.Base64;
import android.util.Log;

import org.json.JSONException;
import org.json.JSONObject;
import org.opencv.core.Mat;
import org.opencv.imgproc.Imgproc;

import java.io.File;
import java.io.FileOutputStream;
import java.io.IOException;
import java.io.InputStream;
import java.io.OutputStream;
import java.util.Arrays;

import ug.go.health.ihrisbiometric.JniHelper;
import ug.go.health.ihrisbiometric.models.FaceInfo;
import ug.go.health.ihrisbiometric.models.FaceScannerResult;
import ug.go.health.ihrisbiometric.utils.Constants;
import ug.go.health.ihrisbiometric.utils.ImageConverter;

public class FaceScanner {
    private static final String TAG = "FaceScanner";
    private Context context;
    private ImageConverter imageConverter;
    public static FaceScannerResult result;

    static {
        System.loadLibrary("opencv_java4");
    }

    public int processImage(Mat mRgbFrame, FaceScannerResult result) {

        float[] faceBoxes = new float[15];

        JniHelper.getInstance().DetectFace(mRgbFrame.getNativeObjAddr(), faceBoxes);
        Log.d(TAG, "processImage: Resulting Boxes ===> " + Arrays.toString(faceBoxes));

        if (faceBoxes[0] != 1.0f) {
            return 0;
        }

        String faceRecognitionResult = JniHelper.getInstance().FaceRecognition(mRgbFrame.getNativeObjAddr(), faceBoxes);
        Log.d(TAG, "Face detected: " + faceRecognitionResult);

        try {
            JSONObject jsonObject = new JSONObject(faceRecognitionResult);
            String status = jsonObject.getString("status");
            FaceInfo faceInfo = result.faceInfo;

            switch (status) {
                case "NO_FACE_DETECTED":
                    faceInfo.faceDetected = false;
                    break;
                case "NOT_ENROLLED":
                    faceInfo.isEnrolled = false;
                    break;
                case "USER_ENROLLED":
                    faceInfo.faceDetected = true;
                    faceInfo.isEnrolled = true;
                    faceInfo.ihrisPID = jsonObject.optString("name", null);
                    break;
            }

            float livelinessScore = JniHelper.getInstance().CheckLiveliness(mRgbFrame.getNativeObjAddr(), faceBoxes);
            faceInfo.isLive = livelinessScore > 0.5f;
            Log.d(TAG, "processImage: We have received a liveness score of ==> " + livelinessScore);
            Log.d(TAG, faceInfo.isLive ? "processImage: This face is a real stream" : "processImage: This face is a still image");

        } catch (JSONException e) {
            e.printStackTrace();
        }

        return 1;
    }


    public void initEngine(Context context) {
        this.context = context;
        this.imageConverter = new ImageConverter(context);
        AssetManager assetManager = context.getAssets();
        String modelDir = Constants.getModelDir(context);

        for (String modelName : Constants.getModelList()) {
            File modelFile = new File(modelDir, modelName);

            if (!modelFile.exists()) {
                try (InputStream inputStream = assetManager.open(modelName);
                     FileOutputStream outputStream = new FileOutputStream(modelFile)) {

                    byte[] buffer = new byte[4096];
                    int length;

                    while ((length = inputStream.read(buffer)) > 0) {
                        outputStream.write(buffer, 0, length);
                    }

                    Log.d(TAG, "File copied successfully: " + modelFile.getAbsolutePath());
                } catch (IOException e) {
                    Log.e(TAG, "Error copying file: " + modelName, e);
                }
            } else {
                Log.d(TAG, "File already exists: " + modelFile.getAbsolutePath());
            }
        }

        String workDir = Constants.getWorkDir(context) + File.separator + "FACE_DB";
        File faceDbDir = new File(workDir);

        if (!faceDbDir.exists()) {
            faceDbDir.mkdir();
        }

        // Log the face database directory path
        Log.d(TAG, "Face database directory: " + faceDbDir.getAbsolutePath());

        JniHelper.getInstance().InitFaceEngine(modelDir, workDir);
    }

    public String registerFace(Mat mRgbFrame, String userId) {
        if (userId.isEmpty()) {
            return "ERROR: User ID is empty";
        }

        Imgproc.cvtColor(mRgbFrame, mRgbFrame, Imgproc.COLOR_BGR2RGB);

        float[] faceBoxes = new float[15];
        JniHelper.getInstance().DetectFace(mRgbFrame.getNativeObjAddr(), faceBoxes);

        // Check if the face is already registered
        String faceRecognitionResult = JniHelper.getInstance().FaceRecognition(mRgbFrame.getNativeObjAddr(), faceBoxes);
        try {
            JSONObject jsonObject = new JSONObject(faceRecognitionResult);
            String status = jsonObject.getString("status");
            if (status.equals("USER_ENROLLED")) {
                Log.d(TAG, "Face is already registered: " + userId);
                return "ERROR: Face is already registered";
            }
        } catch (JSONException e) {
            e.printStackTrace();
            return "ERROR: JSON parsing error";
        }

        // Register the face
        JniHelper.getInstance().FaceRegister(mRgbFrame.getNativeObjAddr(), userId, faceBoxes);

        return "SUCCESS: Face registered successfully";
    }

    public String registerFaceFromBase64(String base64Image, String userId) {
        if (base64Image == null || base64Image.isEmpty()) {
            return "ERROR: Base64 image is null or empty";
        }
        if (userId == null || userId.isEmpty()) {
            return "ERROR: User ID is null or empty";
        }

        try {
            byte[] imageBytes = Base64.decode(base64Image, Base64.DEFAULT);
            Bitmap bitmap = BitmapFactory.decodeByteArray(imageBytes, 0, imageBytes.length);
            if (bitmap == null) {
                return "ERROR: Failed to decode Base64 image to Bitmap";
            }

            Mat mat = new Mat();
            org.opencv.android.Utils.bitmapToMat(bitmap, mat);
            bitmap.recycle();

            return registerFace(mat, userId);
        } catch (IllegalArgumentException e) {
            Log.e(TAG, "Invalid Base64 string for user: " + userId, e);
            return "ERROR: Invalid Base64 string";
        }
    }


    public String saveEnrolledFaceImage(Mat mRgbFrame, String userId) {
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.Q) {
            Bitmap bitmap = Bitmap.createBitmap(mRgbFrame.cols(), mRgbFrame.rows(), Bitmap.Config.ARGB_8888);
            org.opencv.android.Utils.matToBitmap(mRgbFrame, bitmap);

            String fileName = userId + ".jpg";
            ContentValues values = new ContentValues();
            values.put(MediaStore.Images.Media.DISPLAY_NAME, fileName);
            values.put(MediaStore.Images.Media.MIME_TYPE, "image/jpeg");
            values.put(MediaStore.Images.Media.RELATIVE_PATH, Environment.DIRECTORY_PICTURES + File.separator + "iHRIS Biometric/Staff Images");

            Uri uri = context.getContentResolver().insert(MediaStore.Images.Media.EXTERNAL_CONTENT_URI, values);
            if (uri == null) {
                Log.e(TAG, "Failed to create new MediaStore record.");
                return null;
            }

            try (OutputStream out = context.getContentResolver().openOutputStream(uri)) {
                if (out != null) {
                    bitmap.compress(Bitmap.CompressFormat.JPEG, 100, out);
                    Log.d(TAG, "Face image saved successfully: " + uri.toString());
                    return uri.toString();
                } else {
                    Log.e(TAG, "Failed to get output stream.");
                    return null;
                }
            } catch (IOException e) {
                Log.e(TAG, "Error saving face image", e);
                return null;
            }
        } else {
            // Fallback for older versions
            String imageDirPath = Constants.getImageDir(context) + File.separator + "Staff Images";
            File imageDir = new File(imageDirPath);
            if (!imageDir.exists()) {
                imageDir.mkdirs();
            }

            String fileName = userId + ".jpg";
            File imageFile = new File(imageDir, fileName);

            try (FileOutputStream out = new FileOutputStream(imageFile)) {
                Bitmap bitmap = Bitmap.createBitmap(mRgbFrame.cols(), mRgbFrame.rows(), Bitmap.Config.ARGB_8888);
                org.opencv.android.Utils.matToBitmap(mRgbFrame, bitmap);
                bitmap.compress(Bitmap.CompressFormat.JPEG, 100, out);
                Log.d(TAG, "Face image saved successfully: " + imageFile.getAbsolutePath());
                return imageFile.getAbsolutePath();
            } catch (IOException e) {
                Log.e(TAG, "Error saving face image", e);
                return null;
            }
        }
    }
}
