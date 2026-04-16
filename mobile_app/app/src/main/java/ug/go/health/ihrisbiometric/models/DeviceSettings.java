package ug.go.health.ihrisbiometric.models;

import com.google.gson.annotations.SerializedName;

public class DeviceSettings {
    @SerializedName("serverUrl")
    private String serverUrl;

    @SerializedName("portNumber")
    private int portNumber;

    @SerializedName("useSSL")
    private boolean useSSL;

    @SerializedName("scanMethod")
    private String scanMethod;

    @SerializedName("deviceType")
    private String deviceType;

    @SerializedName("actionType")
    private String actionType;


    public DeviceSettings() {
    }

    public DeviceSettings(String serverUrl, int portNumber, boolean useSSL, String scanMethod, String deviceType, String actionType) {
        this.serverUrl = serverUrl;
        this.portNumber = portNumber;
        this.useSSL = useSSL;
        this.scanMethod = scanMethod;
        this.deviceType = deviceType;
        this.actionType = actionType;
    }

    public String getServerUrl() {
        return serverUrl;
    }

    public void setServerUrl(String serverUrl) {
        this.serverUrl = serverUrl;
    }

    public int getPortNumber() {
        return portNumber;
    }

    public void setPortNumber(int portNumber) {
        this.portNumber = portNumber;
    }

    public boolean isUseSSL() {
        return useSSL;
    }

    public void setUseSSL(boolean useSSL) {
        this.useSSL = useSSL;
    }

    public String getScanMethod() {
        return scanMethod;
    }

    public void setScanMethod(String scanMethod) {
        this.scanMethod = scanMethod;
    }

    public String getDeviceType() {
        return deviceType;
    }

    public void setDeviceType(String deviceType) {
        this.deviceType = deviceType;
    }

    public String getActionType() {
        return actionType;
    }

    public void setActionType(String actionType) {
        this.actionType = actionType;
    }

    @Override
    public String toString() {
        return "DeviceSettings{" +
                "serverUrl='" + serverUrl + '\'' +
                ", portNumber=" + portNumber +
                ", useSSL=" + useSSL +
                ", scanMethod='" + scanMethod + '\'' +
                ", deviceType='" + deviceType + '\'' +
                ", actionType='" + actionType + '\'' +
                '}';
    }
}
