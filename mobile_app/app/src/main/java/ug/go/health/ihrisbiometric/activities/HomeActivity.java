package ug.go.health.ihrisbiometric.activities;

import android.Manifest;
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
import com.google.android.gms.location.Priority;
import com.google.android.gms.tasks.OnFailureListener;
import com.google.android.gms.tasks.OnSuccessListener;

import java.io.File;
import java.io.FileOutputStream;
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
import ug.go.health.ihrisbiometric.services.FingerprintSyncService;
import ug.go.health.ihrisbiometric.services.SessionService;
import ug.go.health.ihrisbiometric.viewmodels.DataSyncViewModel;
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

        // On first login (fresh install or after logout), trigger sync automatically
        // so previously enrolled staff are downloaded and ready to clock immediately
        if (!sessionService.isInitialSyncDone()) {
            triggerInitialSync();
        }

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
                    // Give the sync ViewModel a reference so it can re-register downloaded templates
                    viewModel.setScanner(scanner);
                    // Register any templates that were downloaded but not yet registered
                    // (e.g. after fresh install + sync before scanner was ready)
                    registerUnregisteredTemplates();
                }
            } else if ("Mobile".equals(deviceSettings.getDeviceType())) {
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
        if (staffRecord == null) {
            updateStatus("No staff selected for enrollment");
            return;
        }

        staffRecord.setFingerprintEnrolled(true);
        staffRecord.setTemplateId(templateNumber);
        staffRecord.setFingerprintSynced(false);
        // Mark record as unsynced so the enrollment data gets pushed to server on next sync
        staffRecord.setSynced(false);

        // Read the template bytes from the scanner on a background thread,
        // save to disk, then update the DB — all before marking as enrolled.
        executorService.execute(() -> {
            byte[] templateBytes = scanner.readTemplateSync(templateNumber);

            if (templateBytes != null && templateBytes.length > 0) {
                // Save to internal storage
                File fpDir = new File(getFilesDir(), "fingerprints");
                if (!fpDir.exists()) fpDir.mkdirs();

                String safeId = staffRecord.getIhrisPid().replaceAll("[^a-zA-Z0-9_-]", "_");
                File destFile = new File(fpDir, safeId + "_" + templateNumber + ".fpt");

                try (FileOutputStream fos = new FileOutputStream(destFile)) {
                    fos.write(templateBytes);
                    staffRecord.setFingerprintPath(destFile.getAbsolutePath());
                    Log.d(TAG, "Fingerprint saved to " + destFile.getAbsolutePath());
                } catch (IOException e) {
                    Log.e(TAG, "Failed to save fingerprint file", e);
                    // Path stays null — upload will be skipped but enrollment still recorded
                }
            } else {
                Log.w(TAG, "readTemplateSync returned null for slot " + templateNumber + " — file not saved");
            }

            // Get location then save record
            if (ActivityCompat.checkSelfPermission(this, Manifest.permission.ACCESS_FINE_LOCATION)
                    == PackageManager.PERMISSION_GRANTED) {
                fusedLocationClient.getLastLocation().addOnSuccessListener(location -> {
                    if (location != null) {
                        staffRecord.setLocation(new ug.go.health.ihrisbiometric.models.Location(
                                location.getLatitude(), location.getLongitude()));
                    }
                    saveEnrolledStaff(staffRecord);
                }).addOnFailureListener(e -> saveEnrolledStaff(staffRecord));
            } else {
                saveEnrolledStaff(staffRecord);
            }
        });
    }

    /**
     * Triggered on first login — downloads staff list, biometrics, and reference data.
     * Face embeddings are registered with the engine immediately after download.
     * Fingerprint templates are registered with the scanner once it's ready.
     */
    private void triggerInitialSync() {
        updateStatus("Downloading data, please wait...");
        // Use a temporary DataSyncViewModel scoped to this activity
        DataSyncViewModel syncVm = new ViewModelProvider(this).get(DataSyncViewModel.class);
        syncVm.setHomeViewModel(viewModel);
        syncVm.startSync();
        syncVm.getSyncStatus().observe(this, status -> {
            if (status == DataSyncViewModel.SyncStatus.COMPLETED) {
                sessionService.setInitialSyncDone(true);
                updateStatus("Welcome. Ready.");
                viewModel.refreshStaffRecords();
            } else if (status == DataSyncViewModel.SyncStatus.FAILED) {
                updateStatus("Welcome. Sync failed — retry from menu.");
            }
        });
    }

    /** Registers any templates downloaded but not yet on this scanner. */
    private void registerUnregisteredTemplates() {        if (scanner == null) return;
        FingerprintSyncService fpSync = new FingerprintSyncService(this,
                ApiService.getApiInterface(this), dbService);
        fpSync.registerTemplatesOnScanner(scanner,
                new FingerprintSyncService.ScannerRegistrationCallback() {
                    @Override
                    public void onProgress(int completed, int total, String ihrisPid) {
                        Log.d(TAG, "Registering template " + completed + "/" + total + ": " + ihrisPid);
                    }
                    @Override
                    public void onComplete(int registered, List<String> failures) {
                        if (registered > 0) {
                            updateStatus("Registered " + registered + " fingerprint(s). Ready.");
                            viewModel.refreshStaffRecords();
                        }
                        if (!failures.isEmpty()) {
                            Log.w(TAG, "Template registration failures: " + failures);
                        }
                    }
                    @Override
                    public void onError(String errorMessage) {
                        Log.e(TAG, "Template registration error: " + errorMessage);
                    }
                });
    }

    private void saveEnrolledStaff(StaffRecord staffRecord) {        dbService.updateStaffRecordAsync(staffRecord, result -> {
            if (result) {
                updateStatus(staffRecord.getName() + " Enrolled");
                viewModel.incrementEmptyId();
            } else {
                Log.e(TAG, "Failed to update staff record");
                updateStatus("Failed to update staff record for " + staffRecord.getName());
            }
        });
    }

    @RequiresApi(api = Build.VERSION_CODES.O)
    private void handleTemplateSaved(int templateNumber, String filePath) {
        StaffRecord staffRecord = viewModel.getSelectedStaff().getValue();
        if (staffRecord != null && staffRecord.getTemplateId() == templateNumber) {
            String ihrisPid = staffRecord.getIhrisPid();
            // Store in internal app storage — no external storage permission needed
            File fpDir = new File(getFilesDir(), "fingerprints");
            if (!fpDir.exists()) fpDir.mkdirs();

            String safeId = ihrisPid.replaceAll("[^a-zA-Z0-9_-]", "_");
            File destFile = new File(fpDir, safeId + "_" + templateNumber + ".fpt");

            File originalFile = new File(filePath);
            try {
                // Copy to internal storage (rename across filesystems may fail)
                byte[] data = Files.readAllBytes(originalFile.toPath());
                try (FileOutputStream fos = new FileOutputStream(destFile)) {
                    fos.write(data);
                }
                // Delete the original scanner-written file
                originalFile.delete();

                // Store the path — not the raw bytes
                staffRecord.setFingerprintPath(destFile.getAbsolutePath());
                staffRecord.setFingerprintEnrolled(true);
                staffRecord.setFingerprintSynced(false);
                staffRecord.setTemplateId(templateNumber);

                dbService.updateStaffRecordAsync(staffRecord, success -> {
                    if (success) {
                        viewModel.setStatus(staffRecord.getName() + " enrolled. Template saved.");
                    } else {
                        viewModel.setStatus("Failed to update record for " + staffRecord.getName());
                    }
                });
            } catch (IOException e) {
                Log.e(TAG, "Error saving fingerprint file", e);
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
        return new LocationRequest.Builder(Priority.PRIORITY_HIGH_ACCURACY, 10000)
                .setMinUpdateIntervalMillis(5000)
                .build();
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
            public void onResponse(@NonNull Call<StaffListResponse> call, @NonNull Response<StaffListResponse> response) {
                if (response.isSuccessful() && response.body() != null) {
                    List<StaffRecord> staffRecords = response.body().getStaff();
                    saveStaffRecordsToDatabase(staffRecords);
                } else {
                    Log.e(TAG, "Error fetching staff list: " + response.message());
                    Toast.makeText(HomeActivity.this, "Failed to fetch staff list", Toast.LENGTH_SHORT).show();
                }
            }

            @Override
            public void onFailure(@NonNull Call<StaffListResponse> call, @NonNull Throwable t) {
                Log.e(TAG, "Error fetching staff list", t);
                Toast.makeText(HomeActivity.this, "Network error: " + t.getMessage(), Toast.LENGTH_SHORT).show();
            }
        });
    }

    private void saveStaffRecordsToDatabase(List<StaffRecord> staffRecords) {
        for (StaffRecord apiStaffRecord : staffRecords) {
            Log.d(TAG, "Received staff record from server ===> " + apiStaffRecord.toJson());

            dbService.getStaffRecordByihrisPIDAsync(apiStaffRecord.getIhrisPid(), existingRecord -> {
                StaffRecord dbStaffRecord = existingRecord != null ? existingRecord : new StaffRecord();

                // Update fields from server — but NEVER overwrite the local Room id
                // (server id is ihrisdata.id; local id is Room's auto-generated PK)
                dbStaffRecord.setIhrisPid(apiStaffRecord.getIhrisPid());
                dbStaffRecord.setSurname(apiStaffRecord.getSurname());
                dbStaffRecord.setFirstname(apiStaffRecord.getFirstname());
                dbStaffRecord.setOthername(apiStaffRecord.getOthername());
                dbStaffRecord.setJob(apiStaffRecord.getJob());
                dbStaffRecord.setFacilityId(apiStaffRecord.getFacilityId());
                dbStaffRecord.setFacility(apiStaffRecord.getFacility());

                if (existingRecord != null) {
                    // Preserve all local biometric and sync state
                    dbStaffRecord.setFingerprintEnrolled(existingRecord.isFingerprintEnrolled());
                    dbStaffRecord.setFingerprintPath(existingRecord.getFingerprintPath());
                    dbStaffRecord.setFingerprintSynced(existingRecord.isFingerprintSynced());
                    dbStaffRecord.setTemplateId(existingRecord.getTemplateId());
                    dbStaffRecord.setFaceEnrolled(existingRecord.isFaceEnrolled());
                    dbStaffRecord.setFacePath(existingRecord.getFacePath());
                    dbStaffRecord.setFaceImage(existingRecord.getFaceImage());
                    dbStaffRecord.setEmbeddingSynced(existingRecord.isEmbeddingSynced());                    dbStaffRecord.setSynced(existingRecord.isSynced());
                    dbService.updateStaffRecordAsync(dbStaffRecord, success -> {
                        if (!success) Log.e(TAG, "Failed to update staff record: " + dbStaffRecord.getIhrisPid());
                    });
                } else {
                    dbStaffRecord.setFingerprintEnrolled(false);
                    dbStaffRecord.setFaceEnrolled(false);
                    dbStaffRecord.setFingerprintSynced(false);
                    dbStaffRecord.setEmbeddingSynced(false);
                    dbStaffRecord.setSynced(true); // fresh from server = already synced
                    dbService.saveStaffRecordAsync(dbStaffRecord, success -> {
                        if (!success) Log.e(TAG, "Failed to save staff record: " + dbStaffRecord.getIhrisPid());
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
                if (result.getCommandCode() == ug.go.health.library.DevComm.CMD_ENROLL_CODE) {
                    updateStatus(result.getMessage()); // already human-friendly from ScannerLibrary
                } else {
                    updateStatus("Fingerprint not recognised. Please try again.");
                }
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
                } else if (result.getCommandCode() == ug.go.health.library.DevComm.CMD_CLEAR_TEMPLATE_CODE) {
                    updateStatus("Welcome");
                }
            }
        });
    }
}
