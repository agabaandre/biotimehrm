package ug.go.health.ihrisbiometric.models;

import com.google.gson.annotations.Expose;
import com.google.gson.annotations.SerializedName;
import java.util.List;

public class CadresListResponse {
    @SerializedName("status") @Expose private String status;
    @SerializedName("cadres") @Expose private List<String> cadres;

    public String getStatus() { return status; }
    public List<String> getCadres() { return cadres; }
}
