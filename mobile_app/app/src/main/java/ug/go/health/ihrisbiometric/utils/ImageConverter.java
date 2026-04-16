package ug.go.health.ihrisbiometric.utils;

import android.content.Context;
import android.graphics.Bitmap;
import android.graphics.BitmapFactory;
import android.graphics.ImageFormat;
import android.graphics.Matrix;
import android.graphics.Rect;
import android.graphics.YuvImage;
import android.media.Image;
import java.io.ByteArrayOutputStream;
import java.nio.ByteBuffer;

/* loaded from: C:\Users\WORK\Desktop\hrm\classes3.dex */
public class ImageConverter {
    private static final float HARDCODE_SCALE = 0.5f;
    private Context context;

    public ImageConverter(Context context) {
        this.context = context;
    }

    public Bitmap convert(Image image, int rotation) throws Exception {
        byte[] nv21 = yuv420toNV21(image);
        if (nv21 == null) {
            throw new Exception("Image converting failed");
        }
        return nv21ToBitmap(nv21, image.getWidth(), image.getHeight(), rotation);
    }

    private Bitmap nv21ToBitmap(byte[] nv21, int width, int height, int rotation) {
        YuvImage yuvImage = new YuvImage(nv21, ImageFormat.NV21, width, height, null);
        ByteArrayOutputStream byteArrayOutputStream = new ByteArrayOutputStream();
        yuvImage.compressToJpeg(new Rect(0, 0, width, height), 100, byteArrayOutputStream);
        byte[] jpegData = byteArrayOutputStream.toByteArray();
        BitmapFactory.Options options = new BitmapFactory.Options();
        options.inPreferredConfig = Bitmap.Config.ARGB_8888;
        return scaleAndRotate(BitmapFactory.decodeByteArray(jpegData, 0, jpegData.length, options), HARDCODE_SCALE, rotation);
    }

    private Bitmap scaleAndRotate(Bitmap bitmap, float scale, int rotation) {
        Matrix matrix = new Matrix();
        matrix.postScale(scale, scale);
        if (rotation != 0) {
            matrix.postRotate(rotation);
        }
        return Bitmap.createBitmap(bitmap, 0, 0, bitmap.getWidth(), bitmap.getHeight(), matrix, true);
    }

    private byte[] yuv420toNV21(Image image) {
        int width = image.getWidth();
        int height = image.getHeight();
        Image.Plane[] planes = image.getPlanes();
        int ySize = width * height;
        byte[] nv21 = new byte[(ySize * 3) / 2];
        ByteBuffer yBuffer = planes[0].getBuffer();
        ByteBuffer uBuffer = planes[1].getBuffer();
        ByteBuffer vBuffer = planes[2].getBuffer();
        int rowStride = planes[1].getRowStride();
        int pixelStride = planes[1].getPixelStride();
        yBuffer.get(nv21, 0, ySize);
        int uvIndex = ySize;
        int uIndex = 0;
        int vIndex = 0;
        for (int i = 0; i < height / 2; i++) {
            for (int j = 0; j < width / 2; j++) {
                nv21[uvIndex++] = vBuffer.get(vIndex);
                nv21[uvIndex++] = uBuffer.get(uIndex);
                uIndex += pixelStride;
                vIndex += pixelStride;
            }
            uIndex += rowStride - (width / 2 * pixelStride);
            vIndex += rowStride - (width / 2 * pixelStride);
        }
        return nv21;
    }

    public static byte[] YUV_420_888_to_NV21(Image image) {
        int width = image.getWidth();
        int height = image.getHeight();
        Image.Plane[] planes = image.getPlanes();
        int ySize = width * height;
        byte[] nv21 = new byte[(ySize * 3) / 2];
        ByteBuffer yBuffer = planes[0].getBuffer();
        ByteBuffer uBuffer = planes[1].getBuffer();
        ByteBuffer vBuffer = planes[2].getBuffer();
        int rowStride = planes[1].getRowStride();
        int pixelStride = planes[1].getPixelStride();
        yBuffer.get(nv21, 0, ySize);
        int uvIndex = ySize;
        int uIndex = 0;
        int vIndex = 0;
        for (int i = 0; i < height / 2; i++) {
            for (int j = 0; j < width / 2; j++) {
                nv21[uvIndex++] = vBuffer.get(vIndex);
                nv21[uvIndex++] = uBuffer.get(uIndex);
                uIndex += pixelStride;
                vIndex += pixelStride;
            }
            uIndex += rowStride - (width / 2 * pixelStride);
            vIndex += rowStride - (width / 2 * pixelStride);
        }
        return nv21;
    }
}
