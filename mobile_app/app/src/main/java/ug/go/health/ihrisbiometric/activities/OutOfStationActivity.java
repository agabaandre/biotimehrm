package ug.go.health.ihrisbiometric.activities;

import android.annotation.SuppressLint;
import android.content.Intent;
import android.database.Cursor;
import android.net.Uri;
import android.os.Bundle;
import android.provider.OpenableColumns;
import android.widget.ArrayAdapter;
import android.widget.Button;
import android.widget.EditText;
import android.widget.Spinner;
import android.widget.TextView;
import android.widget.Toast;

import androidx.appcompat.app.AlertDialog;
import androidx.appcompat.app.AppCompatActivity;
import androidx.appcompat.widget.Toolbar;

import java.io.File;
import java.io.FileOutputStream;
import java.io.InputStream;
import java.io.OutputStream;
import java.util.List;

import okhttp3.MediaType;
import okhttp3.MultipartBody;
import okhttp3.RequestBody;
import retrofit2.Call;
import retrofit2.Callback;
import retrofit2.Response;
import ug.go.health.ihrisbiometric.R;
import ug.go.health.ihrisbiometric.fragments.DatePickerFragment;
import ug.go.health.ihrisbiometric.models.OutOfStationResponse;
import ug.go.health.ihrisbiometric.services.ApiService;
import ug.go.health.ihrisbiometric.services.SessionService;
import ug.go.health.ihrisbiometric.utils.LoadingDialog;

public class OutOfStationActivity extends AppCompatActivity {

    private static final int PICK_FILE_REQUEST_CODE = 1;

    private SessionService sessionService;
    private LoadingDialog loadingDialog;

    private EditText requestStartDate;
    private EditText requestEndDate;
    private TextView selectedFileNameTextView;
    private Uri selectedFileUri;
    private Spinner reasonSpinner;
    private Button submitButton;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_out_of_station);

        Toolbar toolbar = findViewById(R.id.toolbar);
        setSupportActionBar(toolbar);
        if (getSupportActionBar() != null) {
            getSupportActionBar().setDisplayHomeAsUpEnabled(true);
            getSupportActionBar().setDisplayShowHomeEnabled(true);
        }

        sessionService = new SessionService(this);
        loadingDialog = new LoadingDialog(this);

        requestStartDate = findViewById(R.id.request_start_date);
        requestEndDate = findViewById(R.id.request_end_date);
        selectedFileNameTextView = findViewById(R.id.selected_file_name);
        reasonSpinner = findViewById(R.id.reason);
        submitButton = findViewById(R.id.submit_request);

        populateReasonSpinner();

        requestStartDate.setOnClickListener(v -> showDatePickerDialog(requestStartDate));
        requestEndDate.setOnClickListener(v -> showDatePickerDialog(requestEndDate));
        submitButton.setOnClickListener(v -> submitOutOfStationRequest());
        findViewById(R.id.select_file_button).setOnClickListener(v -> openFilePicker());
    }

    private void populateReasonSpinner() {
        List<String> reasons = sessionService.getReasonList();
        if (reasons != null && !reasons.isEmpty()) {
            ArrayAdapter<String> adapter = new ArrayAdapter<>(this, android.R.layout.simple_spinner_item, reasons);
            adapter.setDropDownViewResource(android.R.layout.simple_spinner_dropdown_item);
            reasonSpinner.setAdapter(adapter);
        } else {
            Toast.makeText(this, "No reasons available", Toast.LENGTH_SHORT).show();
        }
    }

    private void submitOutOfStationRequest() {
        String startDate = requestStartDate.getText() != null ? requestStartDate.getText().toString().trim() : "";
        String endDate   = requestEndDate.getText() != null ? requestEndDate.getText().toString().trim() : "";
        EditText commentsField = findViewById(R.id.comments);
        String comments = commentsField != null && commentsField.getText() != null ? commentsField.getText().toString().trim() : "";
        String reason   = (reasonSpinner != null && reasonSpinner.getSelectedItem() != null) ? reasonSpinner.getSelectedItem().toString() : "";

        if (startDate.isEmpty() || endDate.isEmpty() || reason.isEmpty()) {
            Toast.makeText(this, "Please fill in all required fields", Toast.LENGTH_SHORT).show();
            return;
        }

        // Build multipart parts
        RequestBody startDateBody = RequestBody.create(MediaType.parse("text/plain"), startDate);
        RequestBody endDateBody   = RequestBody.create(MediaType.parse("text/plain"), endDate);
        RequestBody reasonBody    = RequestBody.create(MediaType.parse("text/plain"), reason);
        RequestBody commentsBody  = RequestBody.create(MediaType.parse("text/plain"), comments);

        MultipartBody.Part filePart = null;
        if (selectedFileUri != null) {
            File file = new File(getCacheDir(), getFileName(selectedFileUri));
            try {
                InputStream in  = getContentResolver().openInputStream(selectedFileUri);
                OutputStream out = new FileOutputStream(file);
                byte[] buf = new byte[4096];
                int read;
                while ((read = in.read(buf)) != -1) out.write(buf, 0, read);
                out.flush();
                in.close();
                out.close();
            } catch (Exception e) {
                e.printStackTrace();
            }
            String mime = getContentResolver().getType(selectedFileUri);
            RequestBody requestFile = RequestBody.create(MediaType.parse(mime != null ? mime : "application/octet-stream"), file);
            filePart = MultipartBody.Part.createFormData("document", file.getName(), requestFile);
        }

        // Show loading
        setFormEnabled(false);
        loadingDialog.show("Submitting request...");

        ApiService.getApiInterface(this).submitOutOfStationRequest(
                startDateBody, endDateBody, reasonBody, commentsBody, filePart
        ).enqueue(new Callback<OutOfStationResponse>() {
            @Override
            public void onResponse(Call<OutOfStationResponse> call, Response<OutOfStationResponse> response) {
                loadingDialog.close();
                if (response.isSuccessful() && response.body() != null && response.body().isSuccess()) {
                    showResultDialog(true, "Request submitted successfully.");
                } else {
                    String msg = (response.body() != null && response.body().getMessage() != null)
                            ? response.body().getMessage()
                            : "Failed to submit request. Please try again.";
                    showResultDialog(false, msg);
                }
            }

            @Override
            public void onFailure(Call<OutOfStationResponse> call, Throwable t) {
                loadingDialog.close();
                showResultDialog(false, "Network error: " + t.getMessage());
            }
        });
    }

    /** Shows a success or error dialog. On dismiss, always goes back to HomeActivity. */
    private void showResultDialog(boolean success, String message) {
        new AlertDialog.Builder(this)
                .setTitle(success ? "Success" : "Error")
                .setMessage(message)
                .setIcon(success ? android.R.drawable.ic_dialog_info : android.R.drawable.ic_dialog_alert)
                .setCancelable(false)
                .setPositiveButton("OK", (dialog, which) -> goHome())
                .show();
    }

    private void goHome() {
        Intent intent = new Intent(this, HomeActivity.class);
        intent.setFlags(Intent.FLAG_ACTIVITY_CLEAR_TOP | Intent.FLAG_ACTIVITY_SINGLE_TOP);
        startActivity(intent);
        finish();
    }

    /** Disable/enable the form while submitting to prevent double-taps. */
    private void setFormEnabled(boolean enabled) {
        submitButton.setEnabled(enabled);
        requestStartDate.setEnabled(enabled);
        requestEndDate.setEnabled(enabled);
        reasonSpinner.setEnabled(enabled);
        EditText commentsField = findViewById(R.id.comments);
        if (commentsField != null) commentsField.setEnabled(enabled);
        Button selectFileButton = findViewById(R.id.select_file_button);
        if (selectFileButton != null) selectFileButton.setEnabled(enabled);
    }

    private void showDatePickerDialog(final EditText editText) {
        DatePickerFragment newFragment = new DatePickerFragment();
        newFragment.setDateSetListener((view, year, month, dayOfMonth) -> {
            String selectedDate = year + "-" + String.format("%02d", month + 1) + "-" + String.format("%02d", dayOfMonth);
            editText.setText(selectedDate);
        });
        newFragment.show(getSupportFragmentManager(), "datePicker");
    }

    private void openFilePicker() {
        Intent intent = new Intent(Intent.ACTION_GET_CONTENT);
        intent.setType("*/*");
        intent.addCategory(Intent.CATEGORY_OPENABLE);
        startActivityForResult(Intent.createChooser(intent, "Select a file"), PICK_FILE_REQUEST_CODE);
    }

    @Override
    protected void onActivityResult(int requestCode, int resultCode, Intent data) {
        super.onActivityResult(requestCode, resultCode, data);
        if (requestCode == PICK_FILE_REQUEST_CODE && resultCode == RESULT_OK && data != null) {
            selectedFileUri = data.getData();
            selectedFileNameTextView.setText("Selected: " + getFileName(selectedFileUri));
        }
    }

    @SuppressLint("Range")
    private String getFileName(Uri uri) {
        String result = null;
        if ("content".equals(uri.getScheme())) {
            try (android.database.Cursor cursor = getContentResolver().query(uri, null, null, null, null)) {
                if (cursor != null && cursor.moveToFirst()) {
                    result = cursor.getString(cursor.getColumnIndex(OpenableColumns.DISPLAY_NAME));
                }
            }
        }
        if (result == null) {
            result = uri.getPath();
            int cut = result != null ? result.lastIndexOf('/') : -1;
            if (cut != -1) result = result.substring(cut + 1);
        }
        return result != null ? result : "unknown";
    }
}
