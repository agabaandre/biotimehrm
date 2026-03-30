package ug.go.health.ihrisbiometric.services;

import android.content.Context;
import android.content.SharedPreferences;

import com.google.gson.Gson;
import com.google.gson.reflect.TypeToken;

import java.util.ArrayList;
import java.util.List;

import ug.go.health.ihrisbiometric.models.DeviceSettings;
import ug.go.health.ihrisbiometric.models.FacilityRecord;
import ug.go.health.ihrisbiometric.models.User;

public class SessionService {

    private SharedPreferences preferences;
    private SharedPreferences.Editor editor;
    private Context context;

    public static final String KEY_USER = "user";

    public static final String KEY_FACILITY_ID = "facilityId";
    public static final String KEY_FACILITY_NAME = "facilityName";


    public SessionService(Context context) {
        this.context = context;
        preferences = context.getSharedPreferences("session", Context.MODE_PRIVATE);
        editor = preferences.edit();
    }

    public DeviceSettings getDeviceSettings() {
        String deviceSettings = preferences.getString("device_settings", null);
        if (deviceSettings != null) {
            return new Gson().fromJson(deviceSettings, DeviceSettings.class);
        }
        return null;
    };

    public boolean setDeviceSettings(DeviceSettings deviceSettings) {
        editor.putString("device_settings", new Gson().toJson(deviceSettings));
        return editor.commit();
    };

    public void setFacilities(List<FacilityRecord> facilityRecords) {
        editor.putString("facilities", new Gson().toJson(facilityRecords));
        editor.commit();
    }

    public List<FacilityRecord> getFacilities() {
        String facilityRecordsJson = preferences.getString("facilities", null);
        if (facilityRecordsJson != null) {
            Gson gson = new Gson();
            TypeToken<List<FacilityRecord>> token = new TypeToken<List<FacilityRecord>>() {};
            List<FacilityRecord> facilityRecords = gson.fromJson(facilityRecordsJson, token.getType());
            return facilityRecords;
        }
        return new ArrayList<>();
    }

    public User getCurrentUser() {
        String userJson = preferences.getString(KEY_USER, null);
        if (userJson != null) {
            Gson gson = new Gson();
            return gson.fromJson(userJson, User.class);
        }
        return null;
    }

    public String getToken() {
        User user = getCurrentUser();
        if (user != null) {
            return user.getToken();
        }
        return null;
    }

    public void setCurrentUser(User user) {
        Gson gson = new Gson();
        String userJson = gson.toJson(user);
        editor.putString(KEY_USER, userJson);
        editor.apply();
    }

    public String getFacilityId() {
        return preferences.getString(KEY_FACILITY_ID, null);
    }

    public void setFacilityId(String facilityId) {
        editor.putString(KEY_FACILITY_ID, facilityId);
        editor.apply();
    }

    public void setFacilityName(String facilityName) {
        editor.putString(KEY_FACILITY_NAME, facilityName);
        editor.apply();;
    }

    public void logout() {
        // Clear user session and navigate to the login screen
        editor.remove(KEY_USER);
        editor.remove(KEY_FACILITY_ID);
        editor.remove(KEY_FACILITY_NAME);
        editor.remove("facilities");
        editor.apply();
    }

    public boolean isLoggedIn() {
        return getCurrentUser() != null;
    }

    public List<String> getReasonList() {
        return new ArrayList<>();
    }
}
