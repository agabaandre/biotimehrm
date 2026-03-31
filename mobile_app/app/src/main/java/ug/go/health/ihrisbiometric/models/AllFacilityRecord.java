package ug.go.health.ihrisbiometric.models;

import com.google.gson.annotations.Expose;
import com.google.gson.annotations.SerializedName;

public class AllFacilityRecord {

    @SerializedName("facility_id") @Expose private String facilityId;
    @SerializedName("facility") @Expose private String facility;

    public String getFacilityId() { return facilityId; }
    public String getFacility() { return facility; }

    @Override
    public String toString() { return facility; }
}
