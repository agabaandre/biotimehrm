package ug.go.health.ihrisbiometric.models;

import com.google.gson.Gson;

public class FaceInfo {
    public boolean faceDetected;
    public String ihrisPID;
    public boolean isEnrolled;
    public boolean isLive;

    public FaceInfo() {
    }

    public FaceInfo(boolean faceDetected, boolean isLive, boolean isEnrolled, String ihrisPID) {
        this.faceDetected = faceDetected;
        this.isLive = isLive;
        this.isEnrolled = isEnrolled;
        this.ihrisPID = ihrisPID;
    }

    public boolean isFaceDetected() {
        return faceDetected;
    }

    public void setFaceDetected(boolean faceDetected) {
        this.faceDetected = faceDetected;
    }

    public boolean isLive() {
        return isLive;
    }

    public void setLive(boolean isLive) {
        this.isLive = isLive;
    }

    public boolean isEnrolled() {
        return isEnrolled;
    }

    public void setEnrolled(boolean isEnrolled) {
        this.isEnrolled = isEnrolled;
    }

    public String getIhrisPID() {
        return ihrisPID;
    }

    public void setIhrisPID(String ihrisPID) {
        this.ihrisPID = ihrisPID;
    }

    public String toJson() {
        return new Gson().toJson(this);
    }

    public static FaceInfo fromJson(String json) {
        return new Gson().fromJson(json, FaceInfo.class);
    }
}
