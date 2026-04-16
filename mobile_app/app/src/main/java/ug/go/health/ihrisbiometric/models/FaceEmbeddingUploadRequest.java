package ug.go.health.ihrisbiometric.models;

import com.google.gson.annotations.Expose;
import com.google.gson.annotations.SerializedName;

public class FaceEmbeddingUploadRequest {

    @SerializedName("ihris_pid")
    @Expose
    private String ihrisPid;

    @SerializedName("face_data")
    @Expose
    private String faceData;

    @SerializedName("face_image")
    @Expose
    private String faceImage;

    public FaceEmbeddingUploadRequest(String ihrisPid, String faceData, String faceImage) {
        this.ihrisPid = ihrisPid;
        this.faceData = faceData;
        this.faceImage = faceImage;
    }

    public String getIhrisPid() {
        return ihrisPid;
    }

    public String getFaceData() {
        return faceData;
    }

    public String getFaceImage() {
        return faceImage;
    }
}
