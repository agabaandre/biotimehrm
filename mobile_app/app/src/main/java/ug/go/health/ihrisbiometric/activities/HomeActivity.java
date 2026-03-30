package ug.go.health.ihrisbiometric.activities;

import android.Manifest;
import android.content.Context;
import android.content.pm.PackageManager;
import android.location.Location;
import android.os.Build;
import android.os.Bundle;
import android.os.Handler;
import android.os.Looper;
import android.util.Log;
import android.widget.Toast;

import androidx.annotation.NonNull;
import androidx.annotation.RequiresApi;
import androidx.appcompat.app.AppCompatActivity;
import androidx.core.app.ActivityCompat;
import androidx.core.content.ContextCompat;
import androidx.lifecycle.Observer;
import androidx.lifecycle.ViewModel;
import androidx.lifecycle.ViewModelProvider;
import androidx.navigation.NavController;
import androidx.navigation.fragment.NavHostFragment;
import androidx.work.ExistingPeriodicWorkPolicy;
import androidx.work.PeriodicWorkRequest;
import androidx.work.WorkManager;

import com.google.android.gms.location.FusedLocationProviderClient;
import com.google.android.gms.location.LocationCallback;
import com.google.android.gms.location.LocationRequest;
import com.google.android.gms.location.LocationResult;
import com.google.android.gms.location.LocationServices;
import com.google.android.gms.tasks.OnFailureListener;
import com.google.android.gms.tasks.OnSuccessListener;

import java.io.File;
import java.io.FileWriter;
import java.io.IOException;
import java.nio.file.Files;
import java.util.Date;
import java.util.List;
import java.util.concurrent.ExecutorService;
import java.util.concurrent.Executors;
import java.util.concurrent.TimeUnit;
import java.util.regex.Matcher;
import java.util.regex.Pattern;

import retrofit2.Call;
import retrofit2.Callback;
import retrofit2.Response;
import ug.go.health.ihrisbiometric.R;
import ug.go.health.ihrisbiometric.models.ClockHistory;
import ug.go.health.ihrisbiometric.models.DeviceSettings;
import ug.go.health.ihrisbiometric.models.StaffListResponse;
import ug.go.health.ihrisbiometric.models.StaffRecord;
import ug.go.health.ihrisbiometric.services.ApiInterface;
import ug.go.health.ihrisbiometric.services.ApiService;
import ug.go.health.ihrisbiometric.services.DbService;
import ug.go.health.ihrisbiometric.services.SessionService;
import ug.go.health.ihrisbiometric.services.StaffPictureUploadService;
import ug.go.health.ihrisbiometric.viewmodels.HomeViewModel;
import ug.go.health.library.ScannerLibrary;

public class HomeActivity extends AppCompatActivity implements ug.go.health.ihrisbiometric.ScannerEventListener {

    private static final String TAG = "HomeActivity";
    private static final String RFID_POWER_PATH = "/proc/gpiocontrol/set_id";
    private NavController navController;

    private ScannerLibrary scanner;
    private SessionService sessionService;
    private DbService dbService;
    private HomeViewModel viewModel;
    private ApiInterface apiService;
    private Handler handler;

    private final ExecutorService executorService = Executors.newSingleThreadExecutor();

    private static final long DEBOUNCE_DELAY = 2000; // 2 seconds
    private long lastClockTime = 0;

    private FusedLocationProviderClient fusedLocationClient;
    private LocationCallback locationCallback;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_home);

        handler = new Handler(); // Initialize the Handler instance

        viewModel = new ViewModelProvider(this, new ViewModelProvider.Factory() {
            @NonNull
            @Override
            public <T extends ViewModel> T create(@NonNull Class<T> modelClass) {
                if (modelClass.isAssignableFrom(HomeViewModel.class)) {
                    return (T) new HomeViewModel(getApplication(), sessionService.getToken());
                }
                throw new IllegalArgumentException("Unknown ViewModel class");
            }
        }).get(HomeViewModel.class);
        dbService = new DbService(this);
        sessionService = new SessionService(this);
        apiService = ApiService.getApiInterface(this);

        grantPermissions();
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.O) {
            handler.postDelayed(this::initializeScanner, 1000); // Use the Handler instance
        }

        NavHostFragment navHostFragment = (NavHostFragment) getSupportFragmentManager()
                .findFragmentById(R.id.nav_host_fragment);
        navController = navHostFragment.getNavController();

        observeViewModel();
        fetchFacilitiesAndStaff();

        // Schedule the periodic staff picture upload task
        PeriodicWorkRequest uploadWorkRequest;
        uploadWorkRequest = new PeriodicWorkRequest.Builder(StaffPictureUploadService.class, 1, TimeUnit.HOURS)
                .build();
        WorkManager.getInstance(this).enqueueUniquePeriodicWork("StaffPictureUploadWork", ExistingPeriodicWorkPolicy.REPLACE, uploadWorkRequest);

        // Initialize Fused Location Provider Client
        fusedLocationClient = LocationServices.getFusedLocationProviderClient(this);
        locationCallback = new LocationCallback() {
            @Override
            public void onLocationResult(LocationResult locationResult) {
                if (locationResult == null) {
                    return;
                }
                for (Location location : locationResult.getLocations()) {
                    // Update your location here
                    updateLocation(location);
                }
            }
        };
    }

    private void observeViewModel() {
        viewModel.getActionType().observe(this, this::handleActionType);
        viewModel.getSelectedStaff().observe(this, new Observer<StaffRecord>() {
            @Override
            public void onChanged(StaffRecord staffRecord) {
                if(staffRecord != null) {
                    String scanMethod = sessionService.getDeviceSettings().getScanMethod();
                    if ("fingerprint".equals(scanMethod)) {
                        if(scanner != null) {
                            scanner.Run_CmdEnroll(viewModel.getEmptyId());
                        }
                    }
                }
            }
        });
    }

    private void handleActionType(String actionType) {
        if ("clock".equals(actionType)) {
            String scanMethod = sessionService.getDeviceSettings().getScanMethod();
            if ("fingerprint".equals(scanMethod)) {
                if (scanner != null) {
                    scanner.Run_CmdIdentify();
                }
            } else if ("face".equals(scanMethod)) {
                // Navigate to CameraFragment
                navController.navigate(R.id.action_homeFragment_to_cameraFragment);
            }
        } else if("enroll".equals(actionType)) {
            navController.navigate(R.id.action_homeFragment_to_enrollUserFragment);
        }
    }

    private void initializeScanner() {
        DeviceSettings deviceSettings = sessionService.getDeviceSettings();
        if (deviceSettings != null) {
            if ("Scanner".equals(deviceSettings.getDeviceType())) {
                if ("fingerprint".equals(deviceSettings.getScanMethod())) {
                    if (scanner != null) {
                        scanner.init(this, this);
                    } else {
                        scanner = new ScannerLibrary(this, this);
                        String deviceModel = Build.MODEL;
                        if ("U9100S".equals(deviceModel)) {
                            scanner.OpenDevice("/dev/ttyMT3", 115200);
                        } else if ("U9000".equals(deviceModel)) {
                            scanner.OpenDevice("/dev/ttyS3", 115200);
                        }
                        new Handler().postDelayed(() -> scanner.Run_CmdGetEmptyID(), 2000);
                    }
                }
            } else if ("Mobile".equals(deviceSettings.getDeviceType())) {
                // Mobile devices will only use face recognition by default
                deviceSettings.setScanMethod("face");
                sessionService.setDeviceSettings(deviceSettings);
            }
        }
    }

    public void handleScannerEvent(String event) {

        String cleanedEvent = event.replaceAll("\\s+", " ").replaceAll("(\r\n|\n)", " ").trim();
        Log.d(TAG, "CLEANED_EVENT " + cleanedEvent);

        if (event.contains("EMPTY_ID")) {
            handleEmptyIdEvent(event);
        } else if (event.contains("Input your finger")) {
            updateStatus("Place finger on scanner");
        } else {
            handleScannerResult(cleanedEvent);
        }
    }

    private void handleScannerResult(String cleanedEvent) {
        Pattern successPattern = Pattern.compile("Result : Success Template No : (\\d+)");
        Pattern deletedPattern = Pattern.compile("Result : Success Template Deleted No : (\\d+)");
        Pattern notEnrolledPattern = Pattern.compile("Result : Fail Identify NG");
        Pattern templateSavedPattern = Pattern.compile("Result : Success Template No : (\\d+) Saved file path = (.+)");

        Matcher successMatcher = successPattern.matcher(cleanedEvent);
        Matcher deletedMatcher = deletedPattern.matcher(cleanedEvent);
        Matcher notEnrolledMatcher = notEnrolledPattern.matcher(cleanedEvent);
        Matcher templateSavedMatcher = templateSavedPattern.matcher(cleanedEvent);

        if (templateSavedMatcher.find()) {
            int templateNumber = Integer.parseInt(templateSavedMatcher.group(1));
            String filePath = templateSavedMatcher.group(2);
            handleTemplateSaved(templateNumber, filePath);
        } else if (successMatcher.find()) {
            int templateNumber = Integer.parseInt(successMatcher.group(1));
            long currentTime = System.currentTimeMillis();
            if (currentTime - lastClockTime > DEBOUNCE_DELAY) {
                lastClockTime = currentTime;
                handleSuccessfulScan(templateNumber);
            } else {
                updateStatus("Please wait before clocking again.");
            }
        } else if (deletedMatcher.find()) {
            int templateNumber = Integer.parseInt(deletedMatcher.group(1));
            updateStatus("Removed orphaned template id: " + templateNumber);
        } else if (notEnrolledMatcher.find()) {
            updateStatus("Fingerprint not enrolled.");
        } else {
            updateStatus(cleanedEvent);
        }
    }

    private void handleSuccessfulScan(int templateNumber) {
        Log.d(TAG, "Success Template No " + templateNumber + " detected.");

        String actionType = viewModel.getActionType().getValue();
        if ("clock".equals(actionType)) {
            handleClockAction(templateNumber);
        } else if ("enroll".equals(actionType)) {
            handleEnrollAction(templateNumber);
        }
    }

    private void handleClockAction(int templateId) {
        executorService.execute(() -> {
            Log.d(TAG, "Perform clock in action for user with template id " + templateId);

            dbService.getStaffRecordByTemplateAsync(templateId, staffRecord -> {
				if (staffRecord != null) {
					Log.d(TAG, "onResult: Ready to clock user " + staffRecord.toJson());

					// Create a new ClockHistory object
					ClockHistory clockHistory = new ClockHistory();
					clockHistory.setIhrisPID(staffRecord.getIhrisPid());
					clockHistory.setName(staffRecord.getName());
					clockHistory.setClockTime(new Date());
					clockHistory.setFacilityId(staffRecord.getFacilityId());

					// Determine clock status (IN or OUT)
					dbService.getLastClockHistoryAsync(staffRecord.getIhrisPid(), lastClockHistory -> {
						String clockStatus = (lastClockHistory == null || "OUT".equals(lastClockHistory.getClockStatus())) ? "IN" : "OUT";
						clockHistory.setClockStatus(clockStatus);

						// Get current location
						if (ActivityCompat.checkSelfPermission(HomeActivity.this, Manifest.permission.ACCESS_FINE_LOCATION) == PackageManager.PERMISSION_GRANTED) {
							fusedLocationClient.getLastLocation().addOnSuccessListener(location -> {
								if (location != null) {
									clockHistory.setLocation(new ug.go.health.ihrisbiometric.models.Location(
											location.getLatitude(),
											location.getLongitude()
									));
									clockHistory.setLatitude(location.getLatitude());
									clockHistory.setLongitude(location.getLongitude());
								}

								// Save clock history
								dbService.saveClockHistoryAsync(clockHistory, result -> {
									if(result) {
										updateStatus(staffRecord.getName() + " CLOCKED " + clockStatus);
									} else {
										updateStatus("Failed to clock " + staffRecord.getName());
									}
								});
							});
						}
					});
				} else {
					Log.d(TAG, "onResult: This template does not exist we can delete it");
					scanner.Run_CmdDeleteID(templateId);
					viewModel.setStatus("Deleted orphaned template: " + templateId);
				}
			});
        });
    }

    private void handleEnrollAction(int templateNumber) {
        StaffRecord staffRecord = viewModel.getSelectedStaff().getValue();
        if (staffRecord != null) {
            staffRecord.setFingerprintEnrolled(true);
            staffRecord.setTemplateId(templateNumber);

            // Fetch the current location and update the staff record
            if (ActivityCompat.checkSelfPermission(this, Manifest.permission.ACCESS_FINE_LOCATION) == PackageManager.PERMISSION_GRANTED) {
                fusedLocationClient.getLastLocation().addOnSuccessListener(new OnSuccessListener<Location>() {
                    @Override
                    public void onSuccess(Location location) {
                        if (location != null) {

                            staffRecord.setLocation(new ug.go.health.ihrisbiometric.models.Location(
                                    location.getLatitude(),
                                    location.getLongitude()
                            ));

//                            staffRecord.setLatitude(location.getLatitude());
//                            staffRecord.setLongitude(location.getLongitude());
                        }

                        // Save the updated staff record
                        dbService.updateStaffRecordAsync(staffRecord, result -> {
                            if (result) {
                                Log.d(TAG, "Staff record updated successfully");


                                Log.d(TAG, "Empty ID incremented to " + viewModel.getEmptyId());

                                updateStatus(staffRecord.getName() + " Enrolled");

                                scanner.Run_CmdReadTemplate(templateNumber);

                                viewModel.incrementEmptyId();


                            } else {
                                Log.e(TAG, "Failed to update staff record");
                                updateStatus("Failed to update staff record for " + staffRecord.getName());
                            }
                        });

//                        updateStatus("Fingerprint enrolled. Waiting for template to be saved...");
                    }
                }).addOnFailureListener(new OnFailureListener() {
                    @Override
                    public void onFailure(@NonNull Exception e) {
                        Log.e(TAG, "Failed to get location", e);
                        updateStatus("Failed to get location for enrollment");
                    }
                });
            } else {
                updateStatus("Location permission not granted");
            }
        } else {
            updateStatus("No staff selected for enrollment");
        }
    }

    @RequiresApi(api = Build.VERSION_CODES.O)
    private void handleTemplateSaved(int templateNumber, String filePath) {
        StaffRecord staffRecord = viewModel.getSelectedStaff().getValue();
        if (staffRecord != null && staffRecord.getTemplateId() == templateNumber) {
            String ihrisPid = staffRecord.getIhrisPid();
            String newFileName = ihrisPid + "_" + templateNumber + ".fpt";
            File originalFile = new File(filePath);
            File newFile = new File(originalFile.getParent(), newFileName);

            try {
                // Rename the file
                if (originalFile.renameTo(newFile)) {
                    Log.d(TAG, "File renamed successfully to: " + newFileName);
                } else {
                    Log.e(TAG, "Failed to rename file");
                    // If renaming fails, we'll continue with the original file
                    newFile = originalFile;
                }

                // Read the file content
                byte[] fingerprintData = Files.readAllBytes(newFile.toPath());

                // Update the staff record
                staffRecord.setFingerprintData(fingerprintData);
                staffRecord.setFingerprintEnrolled(true);
                staffRecord.setTemplateId(templateNumber);

                // Save the updated staff record
                dbService.updateStaffRecordAsync(staffRecord, success -> {
                    if (success) {
                        viewModel.setStatus(staffRecord.getName() + " Enrolled. Template saved as: " + newFileName);
                    } else {
                        viewModel.setStatus("Failed to update record for " + staffRecord.getName());
                    }
                });
            } catch (IOException e) {
                Log.e(TAG, "Error handling template file", e);
                viewModel.setStatus("Error saving template for " + staffRecord.getName());
            }
        } else {
            Log.e(TAG, "Unexpected template save or no staff selected");
        }
    }

    private void handleEmptyIdEvent(String event) {
        // Format: "EMPTY_ID :: <number>"
        String[] parts = event.split("::");
        if (parts.length >= 2) {
            try {
                int emptyId = Integer.parseInt(parts[1].trim());
                viewModel.setEmptyId(emptyId);
                updateStatus("Welcome. Scanner is ready.");
            } catch (NumberFormatException e) {
                Log.e(TAG, "Failed to parse empty ID from: " + event);
            }
        } else {
            Log.e(TAG, "Invalid Empty ID event format: " + event);
        }
    }

    private void updateStatus(String status) {
        new Handler(Looper.getMainLooper()).post(() -> {
            viewModel.setStatus(status);
            Log.d(TAG, status);
        });
    }

    private void grantPermissions() {
        String[] permissions = {
                Manifest.permission.CAMERA,
                Manifest.permission.WRITE_EXTERNAL_STORAGE,
                Manifest.permission.READ_EXTERNAL_STORAGE,
                Manifest.permission.ACCESS_FINE_LOCATION,
                Manifest.permission.ACCESS_COARSE_LOCATION
        };

        for (String permission : permissions) {
            if (ContextCompat.checkSelfPermission(this, permission) != PackageManager.PERMISSION_GRANTED) {
                ActivityCompat.requestPermissions(this, permissions, 0);
                break;
            }
        }
    }

    public void PowerControl(int state) {
        try {
            FileWriter localFileWriterOn = new FileWriter(new File(RFID_POWER_PATH));
            localFileWriterOn.write(state == 1 ? "1" : "0");
            localFileWriterOn.close();
        } catch (IOException e) {
            Log.e(TAG, "Error in PowerControl", e);
        }
    }

    @Override
    protected void onResume() {
        super.onResume();
        if ("Scanner".equals(sessionService.getDeviceSettings().getDeviceType())) {
            PowerControl(1);
        }
        startLocationUpdates();
    }

    @Override
    protected void onPause() {
        super.onPause();
        stopLocationUpdates();
    }

    private void startLocationUpdates() {
        if (ActivityCompat.checkSelfPermission(this, Manifest.permission.ACCESS_FINE_LOCATION) != PackageManager.PERMISSION_GRANTED && ActivityCompat.checkSelfPermission(this, Manifest.permission.ACCESS_COARSE_LOCATION) != PackageManager.PERMISSION_GRANTED) {
            return;
        }
        fusedLocationClient.requestLocationUpdates(createLocationRequest(), locationCallback, null);
    }

    private void stopLocationUpdates() {
        fusedLocationClient.removeLocationUpdates(locationCallback);
    }

    private LocationRequest createLocationRequest() {
        LocationRequest locationRequest = LocationRequest.create();
        locationRequest.setInterval(10000);
        locationRequest.setFastestInterval(5000);
        locationRequest.setPriority(LocationRequest.PRIORITY_HIGH_ACCURACY);
        return locationRequest;
    }

    private void updateLocation(Location location) {
        if (location != null) {
            Log.d(TAG, "Latitude: " + location.getLatitude() + ", Longitude: " + location.getLongitude());
            // Update your UI or save the location as needed
        }
    }

    private void fetchFacilitiesAndStaff() {
        apiService.getStaffList().enqueue(new Callback<StaffListResponse>() {
            @Override
            public void onResponse(Call<StaffListResponse> call, Response<StaffListResponse> response) {
                if (response.isSuccessful() && response.body() != null) {
                    List<StaffRecord> staffRecords = response.body().getStaff();
                    saveStaffRecordsToDatabase(staffRecords);
                } else {
                    Log.e(TAG, "Error fetching staff list: " + response.message());
                    Toast.makeText(HomeActivity.this, "Failed to fetch staff list", Toast.LENGTH_SHORT).show();
                }
            }

            @Override
            public void onFailure(Call<StaffListResponse> call, Throwable t) {
                Log.e(TAG, "Error fetching staff list", t);
                Toast.makeText(HomeActivity.this, "Network error: " + t.getMessage(), Toast.LENGTH_SHORT).show();
            }
        });
    }

    private void saveStaffRecordsToDatabase(List<StaffRecord> staffRecords) {
        for (StaffRecord apiStaffRecord : staffRecords) {
            Log.d(TAG, "Received staff record from server ===> " + apiStaffRecord.toJson());

            // Check if a staff record with the same ihris_pid already exists
            dbService.getStaffRecordByihrisPIDAsync(apiStaffRecord.getIhrisPid(), new DbService.Callback<StaffRecord>() {
                @Override
                public void onResult(StaffRecord existingRecord) {
                    StaffRecord dbStaffRecord = existingRecord != null ? existingRecord : new StaffRecord();

                    // Update fields that can be changed from the server
                    dbStaffRecord.setId(apiStaffRecord.getId());
                    dbStaffRecord.setIhrisPid(apiStaffRecord.getIhrisPid());
                    dbStaffRecord.setSurname(apiStaffRecord.getSurname());
                    dbStaffRecord.setFirstname(apiStaffRecord.getFirstname());
                    dbStaffRecord.setOthername(apiStaffRecord.getOthername());
                    dbStaffRecord.setJob(apiStaffRecord.getJob());
                    dbStaffRecord.setFacilityId(apiStaffRecord.getFacilityId());
                    dbStaffRecord.setFacility(apiStaffRecord.getFacility());

                    // Preserve local data if it exists
                    if (existingRecord != null) {
                        dbStaffRecord.setFingerprintEnrolled(existingRecord.isFingerprintEnrolled());
                        dbStaffRecord.setFaceEnrolled(existingRecord.isFaceEnrolled());
                        dbStaffRecord.setSynced(existingRecord.isSynced());
                        dbStaffRecord.setFingerprintData(existingRecord.getFingerprintData());
                        dbStaffRecord.setFaceData(existingRecord.getFaceData());
                        dbStaffRecord.setTemplateId(existingRecord.getTemplateId());
                    } else {
                        // Set default values for new records
                        dbStaffRecord.setFingerprintEnrolled(false);
                        dbStaffRecord.setFaceEnrolled(false);
                        dbStaffRecord.setSynced(false); // Assuming new records from server are not synced
                    }

                    Log.d(TAG, "Saving staff record to database ===> " + dbStaffRecord.toJson());

                    dbService.saveStaffRecordAsync(dbStaffRecord, new DbService.Callback<Boolean>() {
                        @Override
                        public void onResult(Boolean success) {
                            if (!success) {
                                Log.e(TAG, "Failed to save staff record: " + dbStaffRecord.getIhrisPid());
                            }
                        }
                    });
                }
            });
        }
        Log.d(TAG, "Finished saving staff records to database");
        Toast.makeText(HomeActivity.this, "Staff records updated", Toast.LENGTH_SHORT).show();
        viewModel.refreshStaffRecords();
    }

    @Override
    protected void onDestroy() {
        super.onDestroy();
        if (scanner != null && "fingerprint".equals(sessionService.getDeviceSettings().getScanMethod())) {
            scanner.CloseDevice();
            scanner = null;
            PowerControl(0);
        }
        executorService.shutdown();
    }
    @Override
    public void onEvent(String message) {
        handleScannerEvent(message);
    }

    private static final int MAX_RECONNECT_ATTEMPTS = 3;
    private int reconnectAttempts = 0;
    private boolean isReconnecting = false;

    @Override
    public void onScannerEvent(ug.go.health.library.ScannerResult result) {
        runOnUiThread(() -> {
            Log.d(TAG, "Scanner Result: " + result.getMessage());

            if (result.getType() == ug.go.health.library.ScannerResult.Type.ERROR
                    && "Comm Failure".equals(result.getMessage())) {
                updateStatus(result.getMessage());
                if (!isReconnecting) {
                    if (reconnectAttempts < MAX_RECONNECT_ATTEMPTS) {
                        reconnectAttempts++;
                        isReconnecting = true;
                        Log.w(TAG, "Comm Failure detected. Reconnect attempt " + reconnectAttempts + "/" + MAX_RECONNECT_ATTEMPTS);
                        updateStatus("Scanner disconnected. Reconnecting (" + reconnectAttempts + "/" + MAX_RECONNECT_ATTEMPTS + ")...");
                        handler.postDelayed(() -> {
                            if (scanner != null) {
                                scanner.CloseDevice();
                                scanner = null;
                            }
                            isReconnecting = false;
                            initializeScanner();
                        }, 2000);
                    } else {
                        Log.e(TAG, "Scanner failed after " + MAX_RECONNECT_ATTEMPTS + " reconnect attempts.");
                        updateStatus("Scanner unavailable. Please check the device connection.");
                    }
                }
                return;
            }

            if (result.getType() == ug.go.health.library.ScannerResult.Type.IN_PROGRESS) {
                // Show prompts like "Place your finger on the sensor", "Checking slot..."
                updateStatus(result.getMessage());
                return;
            }

            if (result.getType() == ug.go.health.library.ScannerResult.Type.WAITING_FOR_FINGER) {
                // Intermediate enrollment sweep prompt
                updateStatus(result.getMessage());
                return;
            }

            if (result.getType() == ug.go.health.library.ScannerResult.Type.FAILURE) {
                updateStatus("Fingerprint not recognised. Please try again.");
                return;
            }

            if (result.getType() == ug.go.health.library.ScannerResult.Type.ERROR) {
                updateStatus("Scanner error. Please try again.");
                return;
            }

            // SUCCESS — reset reconnect state
            if (result.getType() == ug.go.health.library.ScannerResult.Type.SUCCESS) {
                reconnectAttempts = 0;
                isReconnecting = false;

                if (result.getCommandCode() == ug.go.health.library.DevComm.CMD_GET_EMPTY_ID_CODE) {
                    // Handled via onEvent("EMPTY_ID :: N") — nothing to do here
                } else if (result.getCommandCode() == ug.go.health.library.DevComm.CMD_IDENTIFY_CODE ||
                           result.getCommandCode() == ug.go.health.library.DevComm.CMD_VERIFY_CODE) {
                    long currentTime = System.currentTimeMillis();
                    if (currentTime - lastClockTime > DEBOUNCE_DELAY) {
                        lastClockTime = currentTime;
                        handleSuccessfulScan(result.getValue());
                    } else {
                        updateStatus("Please wait before clocking again.");
                    }
                } else if (result.getCommandCode() == ug.go.health.library.DevComm.CMD_ENROLL_CODE) {
                    handleSuccessfulScan(result.getValue());
                }
            }
        });
    }
}
