package ug.go.health.ihrisbiometric.services;

import android.content.Context;
import android.content.SharedPreferences;

import com.google.gson.Gson;
import com.google.gson.reflect.TypeToken;

import java.util.ArrayList;
import java.util.List;

import ug.go.health.ihrisbiometric.models.AllFacilityRecord;
import ug.go.health.ihrisbiometric.models.AppSettings;
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
    public static final String KEY_APP_SETTINGS = "app_settings";


    public SessionService(Context context) {
        this.context = context;
        preferences = context.getSharedPreferences("session", Context.MODE_PRIVATE);
        editor = preferences.edit();
    }

    public AppSettings getAppSettings() {
        String appSettingsJson = preferences.getString(KEY_APP_SETTINGS, null);
        if (appSettingsJson != null) {
            return new Gson().fromJson(appSettingsJson, AppSettings.class);
        }
        return null;
    }

    public void setAppSettings(AppSettings appSettings) {
        editor.putString(KEY_APP_SETTINGS, new Gson().toJson(appSettings));
        editor.apply();
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
        editor.remove(KEY_USER);
        editor.remove(KEY_FACILITY_ID);
        editor.remove(KEY_FACILITY_NAME);
        editor.remove("facilities");
        editor.remove("initial_sync_done");
        editor.apply();
    }

    public boolean isLoggedIn() {
        return getCurrentUser() != null;
    }

    public List<String> getReasonList() {
        String reasonsJson = preferences.getString("reasons", null);
        if (reasonsJson != null) {
            Gson gson = new Gson();
            TypeToken<List<String>> token = new TypeToken<List<String>>() {};
            return gson.fromJson(reasonsJson, token.getType());
        }
        return new ArrayList<>();
    }

    public void setReasonList(List<String> reasons) {
        editor.putString("reasons", new Gson().toJson(reasons));
        editor.commit();
    }

    public boolean isInitialSyncDone() {
        return preferences.getBoolean("initial_sync_done", false);
    }

    public void setInitialSyncDone(boolean done) {
        editor.putBoolean("initial_sync_done", done);
        editor.commit();
    }

    public List<String> getCadreList() {
        String json = preferences.getString("cadres", null);
        if (json != null) {
            return new Gson().fromJson(json, new TypeToken<List<String>>() {}.getType());
        }
        return new ArrayList<>();
    }

    public void setCadreList(List<String> cadres) {
        editor.putString("cadres", new Gson().toJson(cadres));
        editor.commit();
    }

    public List<String> getDistrictList() {
        String json = preferences.getString("districts", null);
        if (json != null) {
            return new Gson().fromJson(json, new TypeToken<List<String>>() {}.getType());
        }
        return new ArrayList<>();
    }

    public void setDistrictList(List<String> districts) {
        editor.putString("districts", new Gson().toJson(districts));
        editor.commit();
    }

    public List<String> getAllFacilityList() {
        String json = preferences.getString("all_facilities", null);
        if (json != null) {
            return new Gson().fromJson(json, new TypeToken<List<String>>() {}.getType());
        }
        return new ArrayList<>();
    }

    public void setAllFacilityList(List<String> facilities) {
        editor.putString("all_facilities", new Gson().toJson(facilities));
        editor.commit();
    }

    public List<AllFacilityRecord> getAllFacilityRecords() {
        String json = preferences.getString("all_facility_records", null);
        if (json != null) {
            return new Gson().fromJson(json, new TypeToken<List<AllFacilityRecord>>() {}.getType());
        }
        return new ArrayList<>();
    }

    public void setAllFacilityRecords(List<AllFacilityRecord> facilities) {
        editor.putString("all_facility_records", new Gson().toJson(facilities));
        editor.commit();
    }

    public List<String> getJobList() {
        String json = preferences.getString("jobs", null);
        if (json != null) {
            return new Gson().fromJson(json, new TypeToken<List<String>>() {}.getType());
        }
        return new ArrayList<>();
    }

    public void setJobList(List<String> jobs) {
        editor.putString("jobs", new Gson().toJson(jobs));
        editor.commit();
    }
}
