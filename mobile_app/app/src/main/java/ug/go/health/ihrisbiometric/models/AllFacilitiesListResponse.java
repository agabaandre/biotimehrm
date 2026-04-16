package ug.go.health.ihrisbiometric.models;

import com.google.gson.annotations.Expose;
import com.google.gson.annotations.SerializedName;
import java.util.List;

public class AllFacilitiesListResponse {
    @SerializedName("status") @Expose private String status;
    @SerializedName("facilities") @Expose private List<AllFacilityRecord> facilities;

    public String getStatus() { return status; }
    public List<AllFacilityRecord> getFacilities() { return facilities; }
}
