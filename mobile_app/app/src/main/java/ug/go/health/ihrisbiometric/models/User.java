package ug.go.health.ihrisbiometric.models;

import com.google.gson.annotations.Expose;
import com.google.gson.annotations.SerializedName;

import java.io.Serializable;

public class User implements Serializable
{

    @SerializedName("user_id")
    @Expose
    private String userId;

    @SerializedName("ihris_pid")
    @Expose
    private String ihrisPid;

    @SerializedName("email")
    @Expose
    private String email;
    @SerializedName("username")
    @Expose
    private String username;
    @SerializedName("name")
    @Expose
    private String name;
    @SerializedName("role_id")
    @Expose
    private String roleId;
    @SerializedName("role_name")
    @Expose
    private String roleName;
    @SerializedName("facility_id")
    @Expose
    private String facilityId;
    @SerializedName("facility_name")
    @Expose
    private String facilityName;

    @SerializedName("token")
    @Expose
    private String token;

    public User() {
    }

    public User(String userId, String ihrisPid, String email, String username, String name, String roleId, String roleName, String facilityId, String facilityName, String token) {
        this.userId = userId;
        this.ihrisPid = ihrisPid;
        this.email = email;
        this.username = username;
        this.name = name;
        this.roleId = roleId;
        this.roleName = roleName;
        this.facilityId = facilityId;
        this.facilityName = facilityName;
        this.token = token;
    }

    public String getUserId() {
        return userId;
    }

    public void setUserId(String userId) {
        this.userId = userId;
    }

    public String getIhrisPid() {
        return ihrisPid;
    }

    public void setIhrisPid(String ihrisPid) {
        this.ihrisPid = ihrisPid;
    }

    public String getEmail() {
        return email;
    }

    public void setEmail(String email) {
        this.email = email;
    }

    public String getUsername() {
        return username;
    }

    public void setUsername(String username) {
        this.username = username;
    }

    public String getName() {
        return name;
    }

    public void setName(String name) {
        this.name = name;
    }

    public String getRoleId() {
        return roleId;
    }

    public void setRoleId(String roleId) {
        this.roleId = roleId;
    }

    public String getRoleName() {
        return roleName;
    }

    public void setRoleName(String roleName) {
        this.roleName = roleName;
    }

    public String getFacilityId() {
        return facilityId;
    }

    public void setFacilityId(String facilityId) {
        this.facilityId = facilityId;
    }

    public String getFacilityName() {
        return facilityName;
    }

    public void setFacilityName(String facilityName) {
        this.facilityName = facilityName;
    }

    public String getToken() {
        return token;
    }

    public void setToken(String token) {
        this.token = token;
    }

    @Override
    public String toString() {
        return "User{" +
                "userId='" + userId + '\'' +
                ", ihrisPid='" + ihrisPid + '\'' +
                ", email='" + email + '\'' +
                ", username='" + username + '\'' +
                ", name='" + name + '\'' +
                ", roleId='" + roleId + '\'' +
                ", roleName='" + roleName + '\'' +
                ", facilityId='" + facilityId + '\'' +
                ", facilityName='" + facilityName + '\'' +
                ", token='" + token + '\'' +
                '}';
    }
}
