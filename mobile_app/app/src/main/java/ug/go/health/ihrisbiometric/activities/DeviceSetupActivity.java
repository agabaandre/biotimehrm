package ug.go.health.ihrisbiometric.activities;

import androidx.appcompat.app.AlertDialog;
import androidx.appcompat.app.AppCompatActivity;
import androidx.appcompat.widget.Toolbar;

import android.content.Intent;
import android.os.Bundle;
import android.util.Log;
import android.view.View;
import android.widget.AdapterView;
import android.widget.Button;
import android.widget.CheckBox;
import android.widget.ProgressBar;
import android.widget.Spinner;

import com.google.android.material.textfield.TextInputEditText;

import java.util.Arrays;
import java.util.List;

import ug.go.health.ihrisbiometric.R;
import ug.go.health.ihrisbiometric.models.AppSettings;
import ug.go.health.ihrisbiometric.models.DeviceSettings;
import ug.go.health.ihrisbiometric.services.DbService;
import ug.go.health.ihrisbiometric.services.SessionService;
import ug.go.health.ihrisbiometric.utils.DropdownAdapter;
import ug.go.health.ihrisbiometric.utils.LoadingDialog;

public class DeviceSetupActivity extends AppCompatActivity {

    Toolbar toolbar;
    ProgressBar progressBar;
    Button setupBtn;
    DbService dbService;

    TextInputEditText etServerURL, etPortNumber;
    CheckBox cbUseSSL;

    Spinner deviceType, appMode;

    String selectedDeviceType, selectedAppMode;

    private LoadingDialog loadingDialog;
    private SessionService session;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_device_setup);

        dbService = new DbService(this);
        session = new SessionService(this);

        toolbar = findViewById(R.id.device_setup_toolbar);
        toolbar.setTitle("Device Setup");

        // if there is a previous activity, show the back button
        if (getSupportActionBar() != null) {
            getSupportActionBar().setDisplayHomeAsUpEnabled(true);

            // handle the back button click
            toolbar.setNavigationOnClickListener((v) -> {
                onBackPressed();
            });
        }

        progressBar = findViewById(R.id.device_setup_progressbar);

        etServerURL = findViewById(R.id.et_server_url);
        etPortNumber = findViewById(R.id.et_port_number);

        cbUseSSL = findViewById(R.id.cb_use_ssl);

        deviceType = findViewById(R.id.device_type_dropdown);
        appMode = findViewById(R.id.app_mode_dropdown);

        loadingDialog = new LoadingDialog(this);

        // Populate the device type dropdown
        List<String> deviceTypes = Arrays.asList("Select Device Type", "Mobile", "Scanner");
        DropdownAdapter deviceAdapter = new DropdownAdapter(this, deviceTypes);
        deviceType.setAdapter(deviceAdapter);
        deviceType.setOnItemSelectedListener(new AdapterView.OnItemSelectedListener() {
            @Override
            public void onItemSelected(AdapterView<?> parent, View view, int position, long id) {
                selectedDeviceType = parent.getItemAtPosition(position).toString();
            }
            @Override
            public void onNothingSelected(AdapterView<?> parent) {}
        });

        // Populate the app mode dropdown
        List<String> appModes = Arrays.asList("Select App Mode", "Health", "Education");
        DropdownAdapter modeAdapter = new DropdownAdapter(this, appModes);
        appMode.setAdapter(modeAdapter);
        appMode.setOnItemSelectedListener(new AdapterView.OnItemSelectedListener() {
            @Override
            public void onItemSelected(AdapterView<?> parent, View view, int position, long id) {
                selectedAppMode = parent.getItemAtPosition(position).toString().toLowerCase();
            }
            @Override
            public void onNothingSelected(AdapterView<?> parent) {}
        });

        // Check if device settings exist and populate the form
        DeviceSettings deviceSettings = session.getDeviceSettings();
        if (deviceSettings != null) {
            etServerURL.setText(deviceSettings.getServerUrl());
            etPortNumber.setText(String.valueOf(deviceSettings.getPortNumber()));
            cbUseSSL.setChecked(deviceSettings.isUseSSL());

            String selectedType = deviceSettings.getDeviceType();
            for (int i = 0; i < deviceTypes.size(); i++) {
                if (deviceTypes.get(i).equals(selectedType)) {
                    deviceType.setSelection(i);
                    break;
                }
            }
        }

        AppSettings currentAppSettings = session.getAppSettings();
        if (currentAppSettings != null) {
            String mode = currentAppSettings.getAppMode();
            for (int i = 0; i < appModes.size(); i++) {
                if (appModes.get(i).equalsIgnoreCase(mode)) {
                    appMode.setSelection(i);
                    break;
                }
            }
        }


        setupBtn = findViewById(R.id.device_setup_btn);
        setupBtn.setOnClickListener((v) -> {
            loadingDialog.show("Saving settings...");

            String serverURL = etServerURL.getText().toString().trim();
            String portNumber = etPortNumber.getText().toString().trim();
            boolean useSSL = cbUseSSL.isChecked();

            if (serverURL.isEmpty()) {
                loadingDialog.close();
                etServerURL.setError("Server URL is required");
                return;
            }

            // Simple URL normalization
            if (!serverURL.startsWith("http")) {
                serverURL = (useSSL ? "https://" : "http://") + serverURL;
            }
            if (!serverURL.endsWith("/")) {
                serverURL += "/";
            }

            DeviceSettings ds = session.getDeviceSettings();
            if (ds == null) ds = new DeviceSettings();
            
            ds.setServerUrl(serverURL);
            ds.setPortNumber(portNumber.isEmpty() ? 80 : Integer.parseInt(portNumber));
            ds.setUseSSL(useSSL);
            ds.setDeviceType(selectedDeviceType);
            ds.setScanMethod("fingerprint");
            ds.setActionType("clock");

            AppSettings as = session.getAppSettings();
            if (as == null) as = new AppSettings();
            as.setAppMode(selectedAppMode);

            session.setDeviceSettings(ds);
            session.setAppSettings(as);

            loadingDialog.close();

            AlertDialog.Builder builder = new AlertDialog.Builder(this);
            builder.setTitle("Settings Saved");
            builder.setMessage("Settings have been saved successfully.");
            builder.setPositiveButton("Close", (dialog, which) -> {
                Intent intent = new Intent(DeviceSetupActivity.this, SplashActivity.class);
                intent.addFlags(Intent.FLAG_ACTIVITY_CLEAR_TASK | Intent.FLAG_ACTIVITY_NEW_TASK);
                startActivity(intent);
                finish();
            });
            builder.show();
        });

    }
}
