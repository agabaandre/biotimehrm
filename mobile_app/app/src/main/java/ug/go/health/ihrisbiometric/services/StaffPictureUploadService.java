package ug.go.health.ihrisbiometric.services;

import android.app.NotificationManager;
import android.content.Context;
import android.os.Build;
import android.os.Environment;
import android.util.Log;

import androidx.annotation.NonNull;
import androidx.work.Worker;
import androidx.work.WorkerParameters;

import net.gotev.uploadservice.data.UploadNotificationConfig;
import net.gotev.uploadservice.data.UploadNotificationStatusConfig;
import net.gotev.uploadservice.protocols.multipart.MultipartUploadRequest;

import java.io.File;
import java.util.ArrayList;
import java.util.List;
import java.util.UUID;
import java.util.concurrent.atomic.AtomicInteger;

import kotlin.jvm.functions.Function2;
import ug.go.health.ihrisbiometric.models.DeviceSettings;

public class StaffPictureUploadService extends Worker {

    private static final String TAG = "StaffPictureUploadService";
    private static final String IMAGE_DIR = Environment.getExternalStoragePublicDirectory(Environment.DIRECTORY_PICTURES)
            + File.separator + "iHRIS Biometric" + File.separator + "Staff Images";
    private static final int BATCH_SIZE = 10; // Number of images to upload in each batch

    public StaffPictureUploadService(@NonNull Context context, @NonNull WorkerParameters workerParams) {
        super(context, workerParams);
    }

    @NonNull
    @Override
    public Result doWork() {
        try {
            File staffImagesDir = new File(IMAGE_DIR);

            if (staffImagesDir.exists() && staffImagesDir.isDirectory()) {
                File[] files = staffImagesDir.listFiles((dir, name) -> name.toLowerCase().endsWith(".jpg") || name.toLowerCase().endsWith(".png"));

                if (files != null && files.length > 0) {
                    uploadImagesInBatches(files);
                } else {
                    Log.i(TAG, "No images found in the directory.");
                }
            } else {
                Log.e(TAG, "Staff images directory not found.");
                return Result.failure();
            }

        } catch (Exception e) {
            Log.e(TAG, "Error during image upload: " + e.getMessage(), e);
            return Result.failure();
        }

        return Result.success();
    }

    private void uploadImagesInBatches(File[] files) {
        List<List<File>> batches = new ArrayList<>();
        for (int i = 0; i < files.length; i += BATCH_SIZE) {
            batches.add(new ArrayList<>(List.of(files).subList(i, Math.min(files.length, i + BATCH_SIZE))));
        }

        AtomicInteger successfulUploads = new AtomicInteger(0);
        AtomicInteger failedUploads = new AtomicInteger(0);

        for (List<File> batch : batches) {
            uploadBatch(batch, successfulUploads, failedUploads);
        }

        Log.i(TAG, "Upload complete. Successful: " + successfulUploads.get() + ", Failed: " + failedUploads.get());
    }

    private void uploadBatch(List<File> batch, AtomicInteger successfulUploads, AtomicInteger failedUploads) {
        try {
            SessionService session = new SessionService(getApplicationContext());
            DeviceSettings deviceSettings = session.getDeviceSettings();
            String baseUrl = deviceSettings.getServerUrl();
            String uploadUrl = baseUrl + "/upload";

            String uploadId = UUID.randomUUID().toString();
            String notificationChannelId = "StaffImageUploadChannel";

            createNotificationChannel(notificationChannelId);

            UploadNotificationConfig notificationConfig = createNotificationConfig(notificationChannelId);

            MultipartUploadRequest request = new MultipartUploadRequest(getApplicationContext(), uploadUrl)
                    .setMethod("POST")
                    .setNotificationConfig((context, s) -> notificationConfig)
                    .setMaxRetries(2);

            for (File file : batch) {
                request.addFileToUpload(file.getAbsolutePath(), "files[]");
            }

            request.setAutoDeleteFilesAfterSuccessfulUpload(true);

            request.startUpload();

        } catch (Exception e) {
            Log.e(TAG, "Error uploading batch: " + e.getMessage(), e);
            failedUploads.addAndGet(batch.size());
        }
    }

    private void createNotificationChannel(String channelId) {
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.O) {
            NotificationManager notificationManager =
                    (NotificationManager) getApplicationContext().getSystemService(Context.NOTIFICATION_SERVICE);
            if (notificationManager != null) {
                android.app.NotificationChannel channel = new android.app.NotificationChannel(
                        channelId,
                        "Staff Image Uploads",
                        NotificationManager.IMPORTANCE_LOW
                );
                notificationManager.createNotificationChannel(channel);
            }
        }
    }

    private UploadNotificationConfig createNotificationConfig(String channelId) {
        UploadNotificationStatusConfig progressConfig = new UploadNotificationStatusConfig(
                "Uploading", "Uploading staff images...");
        UploadNotificationStatusConfig successConfig = new UploadNotificationStatusConfig(
                "Upload Successful", "Staff images uploaded successfully.");
        UploadNotificationStatusConfig errorConfig = new UploadNotificationStatusConfig(
                "Upload Failed", "Error during the image upload.");
        UploadNotificationStatusConfig cancelledConfig = new UploadNotificationStatusConfig(
                "Upload Cancelled", "Image upload has been cancelled.");

        return new UploadNotificationConfig(
                channelId,
                true,
                progressConfig,
                successConfig,
                errorConfig,
                cancelledConfig
        );
    }
}