package ug.go.health.ihrisbiometric;

public class JniHelper {
    private static final String TAG = "JniHelper";
    private static JniHelper instance;

    public static native void jniInitFaceEngine(String str, String str2);

    public native float jniCheckLiveliness(long j, float[] fArr);

    public native boolean jniCheckfit(float[] fArr);

    public native void jniDetectFace(long j, float[] fArr);

    public native String jniFaceRecognition(long j, float[] fArr);

    public native void jniFaceRegister(long j, String str, float[] fArr);

    static {
        System.loadLibrary("face-scanner");
    }

    public static JniHelper getInstance() {
        if (instance == null) {
            instance = new JniHelper();
        }
        return instance;
    }

    private JniHelper() {
    }

    public void InitFaceEngine(String str, String str2) {
        jniInitFaceEngine(str, str2);
    }

    public void DetectFace(long j, float[] fArr) {
        jniDetectFace(j, fArr);
    }

    public boolean Checkfit(float[] fArr) {
        return jniCheckfit(fArr);
    }

    public String FaceRecognition(long j, float[] fArr) {
        return jniFaceRecognition(j, fArr);
    }

    public void FaceRegister(long j, String str, float[] fArr) {
        jniFaceRegister(j, str, fArr);
    }

    public float CheckLiveliness(long j, float[] fArr) {
        return jniCheckLiveliness(j, fArr);
    }
}