package ug.go.health.ihrisbiometric.models;

import com.google.gson.annotations.Expose;
import com.google.gson.annotations.SerializedName;

public class FingerprintUploadRequest {

    @SerializedName("ihris_pid")
    @Expose
    private String ihrisPid;

    @SerializedName("fingerprint_data")
    @Expose
    private String fingerprintData;

    public FingerprintUploadRequest(String ihrisPid, String fingerprintData) {
        this.ihrisPid = ihrisPid;
        this.fingerprintData = fingerprintData;
    }

    public String getIhrisPid() {
        return ihrisPid;
    }

    public String getFingerprintData() {
        return fingerprintData;
    }
}
