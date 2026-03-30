package ug.go.health.ihrisbiometric.activities;

import android.content.Intent;
import android.os.Build;
import android.os.Bundle;
import android.os.Handler;
import android.util.Log;

import androidx.appcompat.app.AppCompatActivity;

import ug.go.health.ihrisbiometric.R;
import ug.go.health.ihrisbiometric.models.DeviceSettings;
import ug.go.health.ihrisbiometric.services.ApiInterface;
import ug.go.health.ihrisbiometric.services.DbService;
import ug.go.health.ihrisbiometric.services.SessionService;

public class SplashActivity extends AppCompatActivity {

    private SessionService session;
    private static final int SPLASH_SCREEN_TIMEOUT = 3000; // 3 seconds
    private DbService dbService;
    private boolean isLoggedIn;
    private ApiInterface apiService;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_splash);

        session = new SessionService(this);
        isLoggedIn = session.isLoggedIn();

        // Initialize database service
        dbService = new DbService(this);

        // Delay showing the next screen for SPLASH_SCREEN_TIMEOUT
        new Handler().postDelayed(() -> {
            Intent intent = null;
            DeviceSettings deviceSettings = session.getDeviceSettings();

            if (deviceSettings != null) {
                Log.i("YISSH", deviceSettings.toString());

                // Check if user is logged in
                if (isLoggedIn) {
                    // Proceed to main screen
                    Intent intent1 = new Intent(SplashActivity.this, HomeActivity.class);
                    SplashActivity.this.startActivity(intent1);
                    SplashActivity.this.finish();
                } else {
                    intent = new Intent(SplashActivity.this, LoginActivity.class);
                    SplashActivity.this.startActivity(intent);
                    SplashActivity.this.finish();
                }
            } else {
                Log.i("YISSH", "Device Settings Not Found");
                //                intent = new Intent(SplashActivity.this, DeviceSetupActivity.class);
                //                SplashActivity.this.startActivity(intent);
                //                SplashActivity.this.finish();

                // Set Defaults and go to login screen

                // Check Device Model Here. i.e check for U9100S or U9000
                String deviceModel = Build.MODEL;

                String serverURL = "https://attend.health.go.ug/demo/api/";

                DeviceSettings deviceSettings1 = new DeviceSettings();
                deviceSettings1.setServerUrl(serverURL);
                deviceSettings1.setPortNumber(80);
                deviceSettings1.setUseSSL(true);
                deviceSettings1.setActionType("clock");
                if (deviceModel.equals("U9100S") || deviceModel.equals("U9000")) {
                    deviceSettings1.setDeviceType("Scanner");
                } else {
                    deviceSettings1.setDeviceType("Mobile");
                }
                deviceSettings1.setScanMethod("fingerprint");
                session.setDeviceSettings(deviceSettings1);

                Intent intent1 = new Intent(SplashActivity.this, LoginActivity.class);
                startActivity(intent1);
                finish();

            }

        }, SPLASH_SCREEN_TIMEOUT);
    }

}
