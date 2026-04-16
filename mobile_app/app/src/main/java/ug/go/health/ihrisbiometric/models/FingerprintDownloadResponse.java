package ug.go.health.ihrisbiometric.models;

import com.google.gson.annotations.Expose;
import com.google.gson.annotations.SerializedName;

import java.util.List;

public class FingerprintDownloadResponse {

    @SerializedName("status")
    @Expose
    private String status;

    @SerializedName("message")
    @Expose
    private String message;

    @SerializedName("fingerprints")
    @Expose
    private List<FingerprintRecord> fingerprints;

    public String getStatus() {
        return status;
    }

    public String getMessage() {
        return message;
    }

    public List<FingerprintRecord> getFingerprints() {
        return fingerprints;
    }
}
