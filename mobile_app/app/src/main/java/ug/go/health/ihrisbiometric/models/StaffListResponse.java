package ug.go.health.ihrisbiometric.models;

import com.google.gson.annotations.Expose;
import com.google.gson.annotations.SerializedName;
import java.util.List;

public class StaffListResponse {

    @SerializedName("status")
    @Expose
    private String status;
    @SerializedName("message")
    @Expose
    private String message;
    @SerializedName("staff")
    @Expose
    private List<StaffRecord> staff;

    /**
     * No args constructor for use in serialization
     *
     */
    public StaffListResponse() {
    }

    /**
     *
     * @param staff
     * @param message
     * @param status
     */
    public StaffListResponse(String status, String message, List<StaffRecord> staff) {
        super();
        this.status = status;
        this.message = message;
        this.staff = staff;
    }

    public String getStatus() {
        return status;
    }

    public void setStatus(String status) {
        this.status = status;
    }

    public String getMessage() {
        return message;
    }

    public void setMessage(String message) {
        this.message = message;
    }

    public List<StaffRecord> getStaff() {
        return staff;
    }

    public void setStaff(List<StaffRecord> staff) {
        this.staff = staff;
    }

    @Override
    public String toString() {
        return "StaffListResponse{" +
                "status='" + status + '\'' +
                ", message='" + message + '\'' +
                ", staff=" + staff.toString() +
                '}';
    }
}
