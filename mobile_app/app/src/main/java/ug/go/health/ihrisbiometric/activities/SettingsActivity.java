package ug.go.health.ihrisbiometric.activities;

import android.content.Intent;
import android.graphics.Color;
import android.os.Bundle;
import android.util.Log;
import android.view.MenuItem;
import android.widget.Button;
import android.widget.ListView;

import androidx.appcompat.app.AppCompatActivity;
import androidx.appcompat.widget.Toolbar;

import java.util.ArrayList;
import java.util.List;

import ug.go.health.ihrisbiometric.R;
import ug.go.health.ihrisbiometric.ScannerEventListener;
import ug.go.health.ihrisbiometric.adapters.SettingsAdapter;
import ug.go.health.ihrisbiometric.models.SettingsItem;
import ug.go.health.ihrisbiometric.services.SessionService;
import ug.go.health.library.ScannerLibrary;

public class SettingsActivity extends AppCompatActivity implements SettingsAdapter.OnItemClickListener  {

    private ListView mSettingsListView;
    private Toolbar mToolbar;
    private Button mLogoutButton;

    private SessionService sessionService;

    private ScannerLibrary scannerLibrary;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_settings);

        sessionService = new SessionService(this);

        mToolbar = findViewById(R.id.settings_toolbar);
        setSupportActionBar(mToolbar);
        getSupportActionBar().setDisplayHomeAsUpEnabled(true);
        getSupportActionBar().setTitle("Settings");

        mToolbar.setTitleTextColor(Color.WHITE);

        mSettingsListView = findViewById(R.id.settings_list_view);

        List<SettingsItem> settingsItemList = new ArrayList<>();
        settingsItemList.add(new SettingsItem(R.drawable.ic_staff, "View Staff List", "View a list of all staff members"));
        settingsItemList.add(new SettingsItem(R.drawable.ic_device_settings, "Device Settings", "Configure device settings"));
        settingsItemList.add(new SettingsItem(R.drawable.ic_sync, "Sync Data", "Sync data with the server"));
        settingsItemList.add(new SettingsItem(R.drawable.ic_about, "About Project", "Learn more about this project"));
        settingsItemList.add(new SettingsItem(R.drawable.ic_clear, "Clear Fingerprints", "Clear all in memory fingerprints"));

        SettingsAdapter adapter = new SettingsAdapter(this, settingsItemList);
        adapter.setOnItemClickListener(this);
        mSettingsListView.setAdapter(adapter);


        mLogoutButton = findViewById(R.id.logout_button);
        mLogoutButton.setOnClickListener(v -> {
            // Perform logout actions here
            // For example, you can clear the user session and navigate to the login screen
            logout();
        });
    }

    @Override
    public boolean onOptionsItemSelected(MenuItem item) {
        if (item.getItemId() == android.R.id.home) {
            onBackPressed();
            return true;
        }
        return super.onOptionsItemSelected(item);
    }

    private void logout() {
        sessionService.logout();
        Intent intent = new Intent(SettingsActivity.this, LoginActivity.class);
        intent.addFlags(Intent.FLAG_ACTIVITY_CLEAR_TASK | Intent.FLAG_ACTIVITY_NEW_TASK);
        startActivity(intent);
        finish(); // Finish the SettingsActivity to prevent going back to it after logout
    }

    @Override
    public void onItemClick(SettingsItem item) {
        // Handle item click here
        // Example implementation:
        if (item.getTitle().equals("View Staff List")) {

        } else if (item.getTitle().equals("Device Settings")) {
            startActivity(new Intent(SettingsActivity.this, DeviceSetupActivity.class));
        } else if (item.getTitle().equals("About Project")) {
            startActivity(new Intent(SettingsActivity.this, AboutProjectActivity.class));
        } else if(item.getTitle().equals("Clear Fingerprints")) {
            ScannerLibrary scannerLibrary = new ScannerLibrary(SettingsActivity.this, new ScannerEventListener() {
                @Override
                public void onScannerEvent(ug.go.health.library.ScannerResult result) {
                    Log.d("YISSH", "onScannerEvent: " + result.getMessage());
                }
                @Override
                public void onEvent(String message) {
                    Log.d("YISSH", "onEvent: " + message);
                }
            });
            scannerLibrary.Run_CmdDeleteAll();
        }
    }

}

