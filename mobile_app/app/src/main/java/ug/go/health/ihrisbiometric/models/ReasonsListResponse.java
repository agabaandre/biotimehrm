package ug.go.health.ihrisbiometric.models;

import com.google.gson.annotations.Expose;
import com.google.gson.annotations.SerializedName;

import java.util.List;

public class ReasonsListResponse {

    @SerializedName("status")
    @Expose
    private String status;

    @SerializedName("message")
    @Expose
    private String message;

    @SerializedName("reasons")
    @Expose
    private List<ReasonRecord> reasons;

    public String getStatus() {
        return status;
    }

    public String getMessage() {
        return message;
    }

    public List<ReasonRecord> getReasons() {
        return reasons;
    }
}
