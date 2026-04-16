package ug.go.health.ihrisbiometric.models;

import com.google.gson.annotations.SerializedName;

public class AppSettings {
    @SerializedName("app_name")
    private String appName;

    @SerializedName("welcome_message")
    private String welcomeMessage;

    @SerializedName("app_mode")
    private String appMode; // "health" or "education"

    public String getAppName() {
        return appName;
    }

    public void setAppName(String appName) {
        this.appName = appName;
    }

    public String getWelcomeMessage() {
        return welcomeMessage;
    }

    public void setWelcomeMessage(String welcomeMessage) {
        this.welcomeMessage = welcomeMessage;
    }

    public String getAppMode() {
        return appMode != null ? appMode : "health";
    }

    public void setAppMode(String appMode) {
        this.appMode = appMode;
    }

    public boolean isEducationMode() {
        return "education".equalsIgnoreCase(appMode);
    }

    public String getFacilityLabel() {
        return isEducationMode() ? "School" : "Facility";
    }
}
