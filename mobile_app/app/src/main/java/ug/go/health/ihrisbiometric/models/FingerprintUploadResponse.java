package ug.go.health.ihrisbiometric.models;

import com.google.gson.annotations.Expose;
import com.google.gson.annotations.SerializedName;

public class FingerprintUploadResponse {
    @SerializedName("status")
    @Expose
    private String Status;

    @SerializedName("message")
    @Expose
    private String message;

    public FingerprintUploadResponse() {
    }

    public FingerprintUploadResponse(String status, String message) {
        Status = status;
        this.message = message;
    }

    public String getStatus() {
        return Status;
    }

    public void setStatus(String status) {
        Status = status;
    }

    public String getMessage() {
        return message;
    }

    public void setMessage(String message) {
        this.message = message;
    }
}
