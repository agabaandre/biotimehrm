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

    Spinner deviceType;

    String selectedDeviceType;

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

        loadingDialog = new LoadingDialog(this);

        // Populate the device type dropdown
        List<String> deviceTypes;
        deviceTypes = Arrays.asList("Select Device Type", "Mobile", "Scanner");


        DropdownAdapter adapter = new DropdownAdapter(this, deviceTypes);
        deviceType.setAdapter(adapter);
        deviceType.setOnItemSelectedListener(new AdapterView.OnItemSelectedListener() {
            @Override
            public void onItemSelected(AdapterView<?> parent, View view, int position, long id) {
                selectedDeviceType = parent.getItemAtPosition(position).toString();
            }

            @Override
            public void onNothingSelected(AdapterView<?> parent) {

            }
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
        } else {
            deviceSettings = new DeviceSettings();
        }


        setupBtn = findViewById(R.id.device_setup_btn);
        DeviceSettings finalDeviceSettings = deviceSettings;
        setupBtn.setOnClickListener((v) -> {
            loadingDialog.show("Saving device settings...");

            String serverURL = etServerURL.getText().toString().trim();
            String portNumber = etPortNumber.getText().toString().trim();
            boolean useSSL = cbUseSSL.isChecked();

            // Remove http or https from the serverURL if present
            serverURL = serverURL.replaceFirst("^(http[s]?://)", "");

            // Remove the API path from the serverURL if present at the end
            serverURL = serverURL.replaceFirst("(/api)?$", "");

            // extract protocol, host and api path from the server URL
            String protocol = "http://";
            String host = serverURL;
            String apiPath = "";
            int apiIndex = serverURL.indexOf("/api");
            if (serverURL.startsWith("https://")) {
                protocol = "https://";
                host = serverURL.substring(8);
            } else if (serverURL.startsWith("http://")) {
                host = serverURL.substring(7);
            }

            if (apiIndex != -1) {
                host = serverURL.substring(0, apiIndex);
                apiPath = serverURL.substring(apiIndex);
            } else {
                apiPath = "/api";
            }

            // concatenate host and port number
            String urlWithPort = host;
            if (!portNumber.isEmpty() && !portNumber.equals("80")) {
                urlWithPort += ":" + portNumber;
            }

            // prepend protocol and concatenate host/port and api path
            String finalUrl = protocol + urlWithPort + apiPath;

            if (useSSL && finalUrl.startsWith("http://")) {
                finalUrl = finalUrl.replaceFirst("http://", "https://");
            } else if (!useSSL && finalUrl.startsWith("https://")) {
                finalUrl = finalUrl.replaceFirst("https://", "http://");
            }

            // append trailing slash if not already present
            if (!finalUrl.endsWith("/")) {
                finalUrl += "/";
            }

            finalDeviceSettings.setServerUrl(finalUrl);
            finalDeviceSettings.setPortNumber(Integer.parseInt(portNumber));
            finalDeviceSettings.setUseSSL(useSSL);
            finalDeviceSettings.setScanMethod("fingerprint");
            finalDeviceSettings.setDeviceType(selectedDeviceType);
            finalDeviceSettings.setActionType("clock");

            // Log the data we are going to save
            Log.d("Device Settings", finalDeviceSettings.toString());

            if(session.setDeviceSettings(finalDeviceSettings)) {

                loadingDialog.close();

                AlertDialog.Builder builder = new AlertDialog.Builder(this);
                builder.setTitle("Settings Saved");
                builder.setMessage("Device settings have been saved.");
                builder.setPositiveButton("Close Setup", (dialog, which) -> {
                    // Do something when OK button is clicked
                    Intent intent = new Intent(DeviceSetupActivity.this, SplashActivity.class);
                    startActivity(intent);
                    finish();
                });
                AlertDialog dialog = builder.create();
                dialog.show();
            } else {
                AlertDialog.Builder builder = new AlertDialog.Builder(this);
                builder.setTitle("Settings not saved");
                builder.setMessage("Device settings was unable to complete. Please restart app and try again.");
                builder.setPositiveButton("Restart App", (dialog, which) -> {
                    // Do something when OK button is clicked
                    dialog.dismiss();
                });
                builder.setNegativeButton("Retry", (dialog, which) -> {
                    dialog.dismiss();
                });
                AlertDialog dialog = builder.create();
                dialog.show();
            }

        });

    }
}