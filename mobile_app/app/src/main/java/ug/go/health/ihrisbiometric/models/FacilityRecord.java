package ug.go.health.ihrisbiometric.models;

import com.google.gson.annotations.Expose;
import com.google.gson.annotations.SerializedName;

public class FacilityRecord {

    @SerializedName("facility_id")
    @Expose
    private String facilityId;
    @SerializedName("facility")
    @Expose
    private String facility;

    public String getFacilityId() {
        return facilityId;
    }

    public void setFacilityId(String facilityId) {
        this.facilityId = facilityId;
    }

    public String getFacility() {
        return facility;
    }

    public void setFacility(String facility) {
        this.facility = facility;
    }

    @Override
    public String toString() {
        return "Facility{" +
                "facilityId='" + facilityId + '\'' +
                ", facility='" + facility + '\'' +
                '}';
    }
}
