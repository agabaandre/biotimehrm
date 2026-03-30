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

import retrofit2.Call;
import retrofit2.Callback;
import retrofit2.Response;
import ug.go.health.ihrisbiometric.R;
import ug.go.health.ihrisbiometric.models.LoginRequest;
import ug.go.health.ihrisbiometric.models.LoginResponse;
import ug.go.health.ihrisbiometric.models.User;
import ug.go.health.ihrisbiometric.services.ApiInterface;
import ug.go.health.ihrisbiometric.services.ApiService;
import ug.go.health.ihrisbiometric.services.DbService;
import ug.go.health.ihrisbiometric.services.SessionService;

public class LoginActivity extends AppCompatActivity {

    private SessionService session;

    ApiInterface apiService;
    DbService dbService;

    TextInputLayout tilUsername, tilPassword;
    TextInputEditText tieUsername, tiePassword;
    Button btnLogin;

    ProgressBar progressBar;

    TextView changeSettings;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_login);

        session = new SessionService(this);

        tilUsername = findViewById(R.id.username_layout);
        tilPassword = findViewById(R.id.password_layout);

        tieUsername = findViewById(R.id.username_input);
        tiePassword = findViewById(R.id.password_input);

        btnLogin = findViewById(R.id.login_button);

        progressBar = findViewById(R.id.progress_bar);

        apiService = ApiService.getApiInterface(this);

        btnLogin.setOnClickListener((v) -> {
            handleLogin();
        });

        changeSettings = findViewById(R.id.change_settings);
        changeSettings.setOnClickListener((v -> {
            Intent intent = new Intent(LoginActivity.this, DeviceSetupActivity.class);
            startActivity(intent);
        }));

    }

    private void handleLogin() {
        String username = tieUsername.getText().toString();
        String password = tiePassword.getText().toString();

        if (username.isEmpty()) {
            tilUsername.setError("Username is required");
            return;
        }

        if (password.isEmpty()) {
            tilPassword.setError("Password is required");
            return;
        }

        btnLogin.setVisibility(View.GONE);
        progressBar.setVisibility(View.VISIBLE);

        LoginRequest request = new LoginRequest();
        request.setUsername(username);
        request.setPassword(password);

        apiService.login(request).enqueue(new Callback<LoginResponse>() {
            @Override
            public void onResponse(Call<LoginResponse> call, Response<LoginResponse> response) {
                if(response.isSuccessful()) {
                    LoginResponse loginResponse = response.body();
                    Log.i("LOGIN_RESPONSE", loginResponse.toString());
                    if(loginResponse.successful()) {

                        User user = loginResponse.getUser();

                        String facilityId = user.getFacilityId();
                        String facilityName = user.getFacilityName();

                        session.setCurrentUser(user);
                        session.setFacilityId(facilityId);
                        session.setFacilityName(facilityName);

                        Intent intent1 = new Intent(LoginActivity.this, HomeActivity.class);
                        startActivity(intent1);
                        finish();

                    } else {

                        progressBar.setVisibility(View.GONE);
                        btnLogin.setVisibility(View.VISIBLE);

                        AlertDialog.Builder dialog = new AlertDialog.Builder(LoginActivity.this);
                        dialog.setTitle("Login Failed");
                        dialog.setMessage("Unable to login at the moment");
                        dialog.setPositiveButton("Retry", (dialog1, which) -> {
                            dialog1.dismiss();
                        });
                        dialog.setNegativeButton("Cancel", (dialog2, which) -> {
                            dialog2.dismiss();
                        });

                        dialog.show();
                    }
                } else {
                    progressBar.setVisibility(View.GONE);
                    btnLogin.setVisibility(View.VISIBLE);

                    AlertDialog.Builder dialog = new AlertDialog.Builder(LoginActivity.this);
                    dialog.setTitle("Login Failed");
                    dialog.setMessage("Unable to login at the moment");
                    dialog.show();
                }
            }

            @Override
            public void onFailure(Call<LoginResponse> call, Throwable t) {
                Toast.makeText(LoginActivity.this, "Login Failed", Toast.LENGTH_SHORT).show();
                progressBar.setVisibility(View.GONE);
                btnLogin.setVisibility(View.VISIBLE);
            }
        });
    }
}