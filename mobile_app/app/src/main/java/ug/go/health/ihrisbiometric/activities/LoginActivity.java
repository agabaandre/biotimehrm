package ug.go.health.ihrisbiometric.activities;

import android.content.Intent;
import android.os.Bundle;
import android.util.Log;
import android.view.View;
import android.widget.Button;
import android.widget.ProgressBar;
import android.widget.TextView;
import android.widget.Toast;

import androidx.appcompat.app.AlertDialog;
import androidx.appcompat.app.AppCompatActivity;

import com.google.android.material.textfield.TextInputEditText;
import com.google.android.material.textfield.TextInputLayout;

import java.io.File;
import java.io.FileOutputStream;
import java.io.IOException;
import java.util.List;

import retrofit2.Call;
import retrofit2.Callback;
import retrofit2.Response;
import ug.go.health.ihrisbiometric.R;
import ug.go.health.ihrisbiometric.models.FaceEmbeddingDownloadResponse;
import ug.go.health.ihrisbiometric.models.FaceEmbeddingRecord;
import ug.go.health.ihrisbiometric.models.FingerprintDownloadResponse;
import ug.go.health.ihrisbiometric.models.FingerprintRecord;
import ug.go.health.ihrisbiometric.models.LoginRequest;
import ug.go.health.ihrisbiometric.models.LoginResponse;
import ug.go.health.ihrisbiometric.models.StaffListResponse;
import ug.go.health.ihrisbiometric.models.StaffRecord;
import ug.go.health.ihrisbiometric.models.User;
import ug.go.health.ihrisbiometric.services.ApiInterface;
import ug.go.health.ihrisbiometric.services.ApiService;
import ug.go.health.ihrisbiometric.services.DbService;
import ug.go.health.ihrisbiometric.services.FaceScanner;
import ug.go.health.ihrisbiometric.services.SessionService;
import ug.go.health.ihrisbiometric.utils.FaceEmbeddingFileHelper;
import ug.go.health.ihrisbiometric.converters.ByteArrayConverter;

public class LoginActivity extends AppCompatActivity {

    private static final String TAG = "LoginActivity";

    private SessionService session;
    private ApiInterface apiService;
    private DbService dbService;
    private FaceScanner faceScanner;

    TextInputLayout tilUsername, tilPassword;
    TextInputEditText tieUsername, tiePassword;
    Button btnLogin;
    ProgressBar progressBar;
    TextView tvStatus;
    TextView changeSettings;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_login);

        session = new SessionService(this);
        apiService = ApiService.getApiInterface(this);
        dbService = new DbService(this);

        tilUsername = findViewById(R.id.username_layout);
        tilPassword = findViewById(R.id.password_layout);
        tieUsername = findViewById(R.id.username_input);
        tiePassword = findViewById(R.id.password_input);
        btnLogin = findViewById(R.id.login_button);
        progressBar = findViewById(R.id.progress_bar);

        // Reuse the existing progress bar label if present, otherwise fall back gracefully
        tvStatus = findViewById(R.id.tv_loading_status);

        btnLogin.setOnClickListener(v -> handleLogin());

        changeSettings = findViewById(R.id.change_settings);
        changeSettings.setOnClickListener(v -> startActivity(
                new Intent(LoginActivity.this, DeviceSetupActivity.class)));
    }

    private void handleLogin() {
        String username = tieUsername.getText().toString().trim();
        String password = tiePassword.getText().toString().trim();

        if (username.isEmpty()) { tilUsername.setError("Username is required"); return; }
        if (password.isEmpty()) { tilPassword.setError("Password is required"); return; }

        btnLogin.setVisibility(View.GONE);
        progressBar.setVisibility(View.VISIBLE);
        setStatus("Logging in...");

        LoginRequest request = new LoginRequest();
        request.setUsername(username);
        request.setPassword(password);

        apiService.login(request).enqueue(new Callback<LoginResponse>() {
            @Override
            public void onResponse(Call<LoginResponse> call, Response<LoginResponse> response) {
                if (response.isSuccessful() && response.body() != null && response.body().successful()) {
                    User user = response.body().getUser();
                    session.setCurrentUser(user);
                    session.setFacilityId(user.getFacilityId());
                    session.setFacilityName(user.getFacilityName());

                    // Refresh API service with the new token
                    apiService = ApiService.getApiInterface(LoginActivity.this);

                    // Download all required data before entering the app
                    downloadStaffList(user.getFacilityId());
                } else {
                    showLoginError();
                }
            }

            @Override
            public void onFailure(Call<LoginResponse> call, Throwable t) {
                Log.e(TAG, "Login failed", t);
                showLoginError();
            }
        });
    }

    // -------------------------------------------------------------------------
    // Step 1: Download staff list
    // -------------------------------------------------------------------------
    private void downloadStaffList(String facilityId) {
        setStatus("Downloading staff list...");
        apiService.getStaffList().enqueue(new Callback<StaffListResponse>() {
            @Override
            public void onResponse(Call<StaffListResponse> call, Response<StaffListResponse> response) {
                if (response.isSuccessful() && response.body() != null
                        && response.body().getStaff() != null) {
                    saveStaffToDb(response.body().getStaff(), facilityId);
                } else {
                    Log.w(TAG, "Staff list empty or error — continuing");
                    downloadFingerprints(facilityId);
                }
            }

            @Override
            public void onFailure(Call<StaffListResponse> call, Throwable t) {
                Log.e(TAG, "Staff list download failed: " + t.getMessage());
                downloadFingerprints(facilityId); // continue even on failure
            }
        });
    }

    private void saveStaffToDb(List<StaffRecord> staffList, String facilityId) {
        final int[] saved = {0};
        final int total = staffList.size();
        if (total == 0) { downloadFingerprints(facilityId); return; }

        for (StaffRecord serverRecord : staffList) {
            dbService.getStaffRecordByihrisPIDAsync(serverRecord.getIhrisPid(), existing -> {
                if (existing == null) {
                    serverRecord.setSynced(true);
                    dbService.saveStaffRecordAsync(serverRecord, success -> {
                        saved[0]++;
                        if (saved[0] == total) downloadFingerprints(facilityId);
                    });
                } else {
                    // Preserve local biometric data
                    if (existing.isSynced()) {
                        serverRecord.setId(existing.getId());
                        serverRecord.setSynced(true);
                        serverRecord.setFingerprintPath(existing.getFingerprintPath());
                        serverRecord.setFingerprintEnrolled(existing.isFingerprintEnrolled());
                        serverRecord.setFingerprintSynced(existing.isFingerprintSynced());
                        serverRecord.setTemplateId(existing.getTemplateId());
                        serverRecord.setFacePath(existing.getFacePath());
                        serverRecord.setFaceEnrolled(existing.isFaceEnrolled());
                        serverRecord.setEmbeddingSynced(existing.isEmbeddingSynced());
                        serverRecord.setFaceImage(existing.getFaceImage());
                        dbService.updateStaffRecordAsync(serverRecord, success -> {
                            saved[0]++;
                            if (saved[0] == total) downloadFingerprints(facilityId);
                        });
                    } else {
                        saved[0]++;
                        if (saved[0] == total) downloadFingerprints(facilityId);
                    }
                }
            });
        }
    }

    // -------------------------------------------------------------------------
    // Step 2: Download fingerprints
    // -------------------------------------------------------------------------
    private void downloadFingerprints(String facilityId) {
        setStatus("Downloading fingerprints...");
        apiService.getFingerprints(facilityId).enqueue(new Callback<FingerprintDownloadResponse>() {
            @Override
            public void onResponse(Call<FingerprintDownloadResponse> call,
                                   Response<FingerprintDownloadResponse> response) {
                List<FingerprintRecord> list = (response.isSuccessful() && response.body() != null)
                        ? response.body().getFingerprints() : null;
                if (list != null && !list.isEmpty()) {
                    saveFingerprintsToDb(list, facilityId);
                } else {
                    downloadFaceEmbeddings(facilityId);
                }
            }

            @Override
            public void onFailure(Call<FingerprintDownloadResponse> call, Throwable t) {
                Log.e(TAG, "Fingerprint download failed: " + t.getMessage());
                downloadFaceEmbeddings(facilityId);
            }
        });
    }

    private void saveFingerprintsToDb(List<FingerprintRecord> records, String facilityId) {
        File fpDir = new File(getFilesDir(), "fingerprints");
        if (!fpDir.exists()) fpDir.mkdirs();

        final int total = records.size();
        final int[] done = {0};

        for (FingerprintRecord fp : records) {
            dbService.getStaffRecordByihrisPIDAsync(fp.getIhrisPid(), localRecord -> {
                if (localRecord != null
                        && localRecord.getFingerprintPath() != null
                        && new File(localRecord.getFingerprintPath()).exists()
                        && localRecord.isFingerprintSynced()) {
                    // Already have it locally — skip
                    done[0]++;
                    if (done[0] == total) downloadFaceEmbeddings(facilityId);
                    return;
                }

                byte[] bytes = ByteArrayConverter.fromString(fp.getFingerprintData());
                if (bytes == null || bytes.length == 0) {
                    done[0]++;
                    if (done[0] == total) downloadFaceEmbeddings(facilityId);
                    return;
                }

                String safeId = fp.getIhrisPid().replaceAll("[^a-zA-Z0-9_-]", "_");
                File destFile = new File(fpDir, safeId + ".fpt");
                try (FileOutputStream fos = new FileOutputStream(destFile)) {
                    fos.write(bytes);
                } catch (IOException e) {
                    Log.e(TAG, "Failed to write .fpt for " + fp.getIhrisPid(), e);
                    done[0]++;
                    if (done[0] == total) downloadFaceEmbeddings(facilityId);
                    return;
                }

                if (localRecord != null) {
                    localRecord.setFingerprintPath(destFile.getAbsolutePath());
                    localRecord.setFingerprintEnrolled(true);
                    localRecord.setFingerprintSynced(true);
                    localRecord.setTemplateId(0); // needs scanner registration
                    dbService.updateStaffRecordAsync(localRecord, success -> {
                        done[0]++;
                        if (done[0] == total) downloadFaceEmbeddings(facilityId);
                    });
                } else {
                    done[0]++;
                    if (done[0] == total) downloadFaceEmbeddings(facilityId);
                }
            });
        }
    }

    // -------------------------------------------------------------------------
    // Step 3: Download face embeddings
    // -------------------------------------------------------------------------
    private void downloadFaceEmbeddings(String facilityId) {
        setStatus("Downloading face data...");
        apiService.getFaceEmbeddings(facilityId).enqueue(new Callback<FaceEmbeddingDownloadResponse>() {
            @Override
            public void onResponse(Call<FaceEmbeddingDownloadResponse> call,
                                   Response<FaceEmbeddingDownloadResponse> response) {
                List<FaceEmbeddingRecord> list = (response.isSuccessful() && response.body() != null)
                        ? response.body().getEmbeddings() : null;
                if (list != null && !list.isEmpty()) {
                    saveFaceEmbeddingsAndRegister(list);
                } else {
                    navigateToHome();
                }
            }

            @Override
            public void onFailure(Call<FaceEmbeddingDownloadResponse> call, Throwable t) {
                Log.e(TAG, "Face embeddings download failed: " + t.getMessage());
                navigateToHome();
            }
        });
    }

    private void saveFaceEmbeddingsAndRegister(List<FaceEmbeddingRecord> records) {
        setStatus("Registering face data...");

        // Init face scanner once for all registrations
        if (faceScanner == null) {
            faceScanner = new FaceScanner();
            faceScanner.initEngine(this);
        }

        final int total = records.size();
        final int[] done = {0};

        for (FaceEmbeddingRecord rec : records) {
            dbService.getStaffRecordByihrisPIDAsync(rec.getIhrisPid(), localRecord -> {
                if (localRecord != null
                        && localRecord.getFacePath() != null
                        && new File(localRecord.getFacePath()).exists()
                        && localRecord.isEmbeddingSynced()) {
                    // Already registered — still force-register in case engine was wiped
                    faceScanner.forceRegisterFaceFromBase64(
                            FaceEmbeddingFileHelper.readAsBase64(localRecord.getFacePath()),
                            rec.getIhrisPid());
                    done[0]++;
                    if (done[0] == total) navigateToHome();
                    return;
                }

                String base64 = rec.getFaceData();
                if (base64 == null || base64.isEmpty()) {
                    done[0]++;
                    if (done[0] == total) navigateToHome();
                    return;
                }

                // Save .face file
                String facePath = FaceEmbeddingFileHelper.saveFaceImage(
                        LoginActivity.this, rec.getIhrisPid(), base64);

                // Register with face engine
                faceScanner.forceRegisterFaceFromBase64(base64, rec.getIhrisPid());

                if (localRecord != null && facePath != null) {
                    localRecord.setFacePath(facePath);
                    localRecord.setFaceImage(base64);
                    localRecord.setFaceEnrolled(true);
                    localRecord.setEmbeddingSynced(true);
                    dbService.updateStaffRecordAsync(localRecord, success -> {
                        done[0]++;
                        if (done[0] == total) navigateToHome();
                    });
                } else {
                    done[0]++;
                    if (done[0] == total) navigateToHome();
                }
            });
        }
    }

    // -------------------------------------------------------------------------
    // Done — go to HomeActivity
    // -------------------------------------------------------------------------
    private void navigateToHome() {
        session.setInitialSyncDone(true);
        runOnUiThread(() -> {
            startActivity(new Intent(LoginActivity.this, HomeActivity.class));
            finish();
        });
    }

    private void setStatus(String msg) {
        runOnUiThread(() -> {
            if (tvStatus != null) tvStatus.setText(msg);
            Log.d(TAG, msg);
        });
    }

    private void showLoginError() {
        runOnUiThread(() -> {
            progressBar.setVisibility(View.GONE);
            btnLogin.setVisibility(View.VISIBLE);
            setStatus("");
            new AlertDialog.Builder(LoginActivity.this)
                    .setTitle("Login Failed")
                    .setMessage("Invalid username or password")
                    .setPositiveButton("Retry", (d, w) -> d.dismiss())
                    .show();
        });
    }
}
