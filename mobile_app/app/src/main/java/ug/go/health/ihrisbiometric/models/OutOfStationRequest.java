package ug.go.health.ihrisbiometric.models;

public class OutOfStationRequest {
    private String startDate;
    private String endDate;
    private String reason;
    private String comments;

    public OutOfStationRequest(String startDate, String endDate, String reason, String comments) {
        this.startDate = startDate;
        this.endDate = endDate;
        this.reason = reason;
        this.comments = comments;
    }

    public String getStartDate() {
        return startDate;
    }

    public void setStartDate(String startDate) {
        this.startDate = startDate;
    }

    public String getEndDate() {
        return endDate;
    }

    public void setEndDate(String endDate) {
        this.endDate = endDate;
    }

    public String getReason() {
        return reason;
    }

    public void setReason(String reason) {
        this.reason = reason;
    }

    public String getComments() {
        return comments;
    }

    public void setComments(String comments) {
        this.comments = comments;
    }
}
