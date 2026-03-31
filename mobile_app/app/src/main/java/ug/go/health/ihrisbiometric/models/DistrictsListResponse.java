package ug.go.health.ihrisbiometric.models;

import com.google.gson.annotations.Expose;
import com.google.gson.annotations.SerializedName;
import java.util.List;

public class DistrictsListResponse {
    @SerializedName("status") @Expose private String status;
    @SerializedName("districts") @Expose private List<String> districts;

    public String getStatus() { return status; }
    public List<String> getDistricts() { return districts; }
}
