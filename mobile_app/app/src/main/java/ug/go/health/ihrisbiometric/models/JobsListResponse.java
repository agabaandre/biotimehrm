package ug.go.health.ihrisbiometric.models;

import com.google.gson.annotations.Expose;
import com.google.gson.annotations.SerializedName;
import java.util.List;

public class JobsListResponse {
    @SerializedName("status") @Expose private String status;
    @SerializedName("jobs") @Expose private List<String> jobs;

    public String getStatus() { return status; }
    public List<String> getJobs() { return jobs; }
}
