package ug.go.health.ihrisbiometric.utils;

import android.graphics.Bitmap;
import android.graphics.BitmapFactory;
import android.graphics.ImageFormat;
import android.graphics.Matrix;
import android.graphics.Rect;
import android.graphics.YuvImage;
import android.media.Image;
import android.util.Log;

import androidx.annotation.OptIn;
import androidx.camera.core.ExperimentalGetImage;
import androidx.camera.core.ImageProxy;

import java.io.ByteArrayOutputStream;
import java.nio.ByteBuffer;

public class BitmapUtils {
    private static final String TAG = "BitmapUtils";

    public static Bitmap getBitmap(ImageProxy image) {
        Log.d(TAG, "Image format: " + image.getFormat());
        Log.d(TAG, "Image dimensions: " + image.getWidth() + "x" + image.getHeight());
        Log.d(TAG, "Number of planes: " + image.getPlanes().length);

        ByteBuffer buffer = image.getPlanes()[0].getBuffer();
        byte[] bytes = new byte[buffer.capacity()];
        buffer.get(bytes);

        if (image.getFormat() == ImageFormat.JPEG) {
            return BitmapFactory.decodeByteArray(bytes, 0, bytes.length, null);
        } else if (image.getFormat() == ImageFormat.YUV_420_888) {
            return yuv420ToBitmap(image);
        } else {
            Log.e(TAG, "Unsupported image format: " + image.getFormat());
            return null;
        }
    }

    @OptIn(markerClass = ExperimentalGetImage.class)
    private static Bitmap yuv420ToBitmap(ImageProxy image) {
        Image.Plane[] planes = image.getImage().getPlanes();
        ByteBuffer yBuffer = planes[0].getBuffer();
        ByteBuffer uBuffer = planes[1].getBuffer();
        ByteBuffer vBuffer = planes[2].getBuffer();

        int ySize = yBuffer.remaining();
        int uSize = uBuffer.remaining();
        int vSize = vBuffer.remaining();

        byte[] nv21 = new byte[ySize + uSize + vSize];

        yBuffer.get(nv21, 0, ySize);
        vBuffer.get(nv21, ySize, vSize);
        uBuffer.get(nv21, ySize + vSize, uSize);

        YuvImage yuvImage = new YuvImage(nv21, ImageFormat.NV21, image.getWidth(), image.getHeight(), null);
        ByteArrayOutputStream out = new ByteArrayOutputStream();
        yuvImage.compressToJpeg(new Rect(0, 0, yuvImage.getWidth(), yuvImage.getHeight()), 100, out);

        byte[] imageBytes = out.toByteArray();
        return BitmapFactory.decodeByteArray(imageBytes, 0, imageBytes.length);
    }

    public static Bitmap rotateBitmap(Bitmap bitmap, int rotation) {
        Matrix matrix = new Matrix();
        matrix.postRotate(rotation);
        return Bitmap.createBitmap(bitmap, 0, 0, bitmap.getWidth(), bitmap.getHeight(), matrix, true);
    }

    private static byte[] yuv420ThreePlanesToNV21(Image.Plane[] yuv420888planes, int width, int height) {
        int imageSize = width * height;
        byte[] out = new byte[imageSize + 2 * (imageSize / 4)];

        if (areUVPlanesNV21(yuv420888planes[1].getBuffer(), yuv420888planes[2].getBuffer())) {
            // Copy Y plane
            yuv420888planes[0].getBuffer().get(out, 0, imageSize);

            // Copy UV planes
            ByteBuffer uBuffer = yuv420888planes[1].getBuffer();
            ByteBuffer vBuffer = yuv420888planes[2].getBuffer();
            int uvSize = imageSize / 4;
            uBuffer.get(out, imageSize, uvSize);
            vBuffer.get(out, imageSize + uvSize, uvSize);
        } else {
            // Fallback to copying planes manually
            ByteBuffer yBuffer = yuv420888planes[0].getBuffer();
            ByteBuffer uBuffer = yuv420888planes[1].getBuffer();
            ByteBuffer vBuffer = yuv420888planes[2].getBuffer();

            int ySize = yBuffer.remaining();
            int uSize = uBuffer.remaining();
            int vSize = vBuffer.remaining();

            yBuffer.get(out, 0, ySize);
            int uvSize = imageSize / 4;
            for (int i = 0; i < uvSize; i++) {
                out[ySize + i * 2] = vBuffer.get(i);
                out[ySize + i * 2 + 1] = uBuffer.get(i);
            }
        }

        return out;
    }

    private static boolean areUVPlanesNV21(ByteBuffer uBuffer, ByteBuffer vBuffer) {
        if (uBuffer == null || vBuffer == null) {
            return false;
        }

        int uCapacity = uBuffer.capacity();
        int vCapacity = vBuffer.capacity();

        if (uCapacity == 0 || vCapacity == 0 || uCapacity != vCapacity) {
            return false;
        }

        // Check a sample of values
        int sampleSize = Math.min(4, uCapacity);
        for (int i = 0; i < sampleSize; i++) {
            if (uBuffer.get(i) != vBuffer.get(vCapacity - 1 - i)) {
                return false;
            }
        }

        return true;
    }
    /**
     * Converts a raw grayscale byte array to a PNG byte array.
     */
    public static byte[] grayscaleToPng(byte[] grayscaleData, int width, int height) {
        if (grayscaleData == null || width <= 0 || height <= 0) return null;

        Bitmap bitmap = Bitmap.createBitmap(width, height, Bitmap.Config.ARGB_8888);
        int[] pixels = new int[width * height];

        for (int i = 0; i < grayscaleData.length && i < pixels.length; i++) {
            int grey = grayscaleData[i] & 0xFF;
            pixels[i] = 0xFF000000 | (grey << 16) | (grey << 8) | grey;
        }

        bitmap.setPixels(pixels, 0, width, 0, 0, width, height);

        ByteArrayOutputStream out = new ByteArrayOutputStream();
        bitmap.compress(Bitmap.CompressFormat.PNG, 100, out);
        return out.toByteArray();
    }
}