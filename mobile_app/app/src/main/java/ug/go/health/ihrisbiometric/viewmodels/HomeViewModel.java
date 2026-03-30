package ug.go.health.ihrisbiometric.viewmodels;

import android.app.Application;
import android.util.Log;

import androidx.annotation.NonNull;
import androidx.lifecycle.AndroidViewModel;
import androidx.lifecycle.LiveData;
import androidx.lifecycle.MutableLiveData;

import java.util.List;

import ug.go.health.ihrisbiometric.models.DeviceSettings;
import ug.go.health.ihrisbiometric.models.StaffRecord;
import ug.go.health.ihrisbiometric.services.DbService;
import ug.go.health.ihrisbiometric.services.SessionService;

public class HomeViewModel extends AndroidViewModel {
    private static final String TAG = "HomeViewModel";

    private final MutableLiveData<StaffRecord> selectedStaff = new MutableLiveData<>();
    private final MutableLiveData<String> status = new MutableLiveData<>();
    private final MutableLiveData<String> actionType = new MutableLiveData<>();
    private final MutableLiveData<Integer> emptyId = new MutableLiveData<>();
    private final MutableLiveData<List<StaffRecord>> staffRecords = new MutableLiveData<>();
    private final MutableLiveData<String> scanMethod = new MutableLiveData<>();

    private final DbService dbService;
    private final SessionService sessionService;
    private final String token;

    public HomeViewModel(@NonNull Application application, String token) {
        super(application);
        dbService = new DbService(application);
        sessionService = new SessionService(application);
        this.token = token;
        loadStaffRecords();
        initializeScanMethod();
    }

    private void initializeScanMethod() {
        String method = sessionService.getDeviceSettings().getScanMethod();
        scanMethod.setValue(method);
    }

    public void updateScanMethod(String method) {
        scanMethod.setValue(method);
        SessionService sessionService = new SessionService(getApplication());
        DeviceSettings deviceSettings = sessionService.getDeviceSettings();
        deviceSettings.setScanMethod(method);
        sessionService.setDeviceSettings(deviceSettings);
    }

    public LiveData<String> getScanMethod() {
        return scanMethod;
    }


    public void setSelectedStaff(StaffRecord staff) {
        selectedStaff.setValue(staff);
    }

    public LiveData<StaffRecord> getSelectedStaff() {
        return selectedStaff;
    }

    public void setStatus(String newStatus) {
        status.setValue(newStatus);
    }

    public LiveData<String> getStatus() {
        return status;
    }

    public void setActionType(String type) {
        actionType.setValue(type);
    }

    public LiveData<String> getActionType() {
        return actionType;
    }

    public void setEmptyId(int id) {
        emptyId.setValue(id);
    }

    public int getEmptyId() {
        Integer value = emptyId.getValue();
        return value != null ? value : -1;
    }

    public LiveData<Integer> getEmptyIdLiveData() {
        return emptyId;
    }

    public void navigationEventHandled() {
        Log.d(TAG, "Navigation event handled");
    }

    public LiveData<List<StaffRecord>> getStaffRecords() {
        return staffRecords;
    }

    private void loadStaffRecords() {
        dbService.getStaffRecordsAsync(new DbService.Callback<List<StaffRecord>>() {
            @Override
            public void onResult(List<StaffRecord> records) {
                Log.d(TAG, "loadStaffRecords: " + records.size());
                staffRecords.postValue(records);
            }
        });
    }

    public void refreshStaffRecords() {
        loadStaffRecords();
    }

    public void incrementEmptyId() {
        Integer currentEmptyId = emptyId.getValue();
        if (currentEmptyId != null) {
            emptyId.setValue(currentEmptyId + 1);
        } else {
            // If emptyId is null, initialize it to 1
            emptyId.setValue(1);
        }
    }


    @Override
    protected void onCleared() {
        super.onCleared();
        // Cleanup any resources if needed
    }
}
