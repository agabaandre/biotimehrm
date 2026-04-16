package ug.go.health.ihrisbiometric.models;

import com.google.gson.annotations.Expose;
import com.google.gson.annotations.SerializedName;

public class FingerprintRecord {

    @SerializedName("ihris_pid")
    @Expose
    private String ihrisPid;

    @SerializedName("fingerprint_data")
    @Expose
    private String fingerprintData;

    public String getIhrisPid() {
        return ihrisPid;
    }

    public String getFingerprintData() {
        return fingerprintData;
    }
}
