package ug.go.health.ihrisbiometric.models;

import androidx.room.ColumnInfo;
import androidx.room.Entity;
import androidx.room.Ignore;
import androidx.room.PrimaryKey;
import androidx.room.TypeConverters;

import com.google.gson.annotations.Expose;
import com.google.gson.annotations.SerializedName;

import java.util.Date;

import ug.go.health.ihrisbiometric.converters.DateConverter;
import ug.go.health.ihrisbiometric.converters.LocationConverter;

@Entity(tableName = "clock_history")
@TypeConverters({DateConverter.class, LocationConverter.class})
public class ClockHistory {

    @PrimaryKey(autoGenerate = true)
    @ColumnInfo(name = "id")
    private int id;

    @SerializedName("ihris_pid")
    @Expose
    @ColumnInfo(name = "ihris_pid")
    private String ihrisPID;

    @SerializedName("name")
    @Expose
    private String name;

    @SerializedName("clock_time")
    @Expose
    @ColumnInfo(name = "clock_time")
    private Date clockTime;

    @SerializedName("clock_status")
    @Expose
    @ColumnInfo(name = "clock_status")
    private String clockStatus;

    @SerializedName("synced")
    @Expose
    private boolean synced;

    @SerializedName("location")
    @Expose
    private Location location;

    @SerializedName("facility_id")
    @Expose
    @ColumnInfo(name = "facility_id")
    private String facilityId;

    @SerializedName("latitude")
    @Expose
    @ColumnInfo(name = "latitude")
    private double latitude;

    @SerializedName("longitude")
    @Expose
    @ColumnInfo(name = "longitude")
    private double longitude;

    public ClockHistory() {
    }

    @Ignore
    public ClockHistory(String ihrisPID, String name, Date clockTime, String clockStatus, boolean synced, Location location, String facilityId, double latitude, double longitude) {
        this.ihrisPID = ihrisPID;
        this.name = name;
        this.clockTime = clockTime;
        this.clockStatus = clockStatus;
        this.synced = synced;
        this.location = location;
        this.facilityId = facilityId;
        this.latitude = latitude;
        this.longitude = longitude;
    }

    public int getId() {
        return id;
    }

    public void setId(int id) {
        this.id = id;
    }

    public String getIhrisPID() {
        return ihrisPID;
    }

    public void setIhrisPID(String ihrisPID) {
        this.ihrisPID = ihrisPID;
    }

    public String getName() {
        return name;
    }

    public void setName(String name) {
        this.name = name;
    }

    public Date getClockTime() {
        return clockTime;
    }

    public void setClockTime(Date clockTime) {
        this.clockTime = clockTime;
    }

    public String getClockStatus() {
        return clockStatus;
    }

    public void setClockStatus(String clockStatus) {
        this.clockStatus = clockStatus;
    }

    public boolean isSynced() {
        return synced;
    }

    public void setSynced(boolean synced) {
        this.synced = synced;
    }

    public Location getLocation() {
        return location;
    }

    public void setLocation(Location location) {
        this.location = location;
    }

    public String getFacilityId() {
        return facilityId;
    }

    public void setFacilityId(String facilityId) {
        this.facilityId = facilityId;
    }

    public double getLatitude() {
        return latitude;
    }

    public void setLatitude(double latitude) {
        this.latitude = latitude;
    }

    public double getLongitude() {
        return longitude;
    }

    public void setLongitude(double longitude) {
        this.longitude = longitude;
    }

    @Override
    public String toString() {
        return "ClockHistory{" +
                "id=" + id +
                ", ihrisPID='" + ihrisPID + '\'' +
                ", name='" + name + '\'' +
                ", clockTime=" + clockTime +
                ", clockStatus='" + clockStatus + '\'' +
                ", synced=" + synced +
                ", location=" + location +
                ", facilityId='" + facilityId + '\'' +
                ", latitude=" + latitude +
                ", longitude=" + longitude +
                '}';
    }
}
