package ug.go.health.ihrisbiometric.activities;


import androidx.appcompat.app.AppCompatActivity;
import androidx.appcompat.widget.Toolbar;

import android.annotation.SuppressLint;
import android.os.Bundle;
import android.view.MenuItem;
import android.view.View;
import android.webkit.WebView;
import android.webkit.WebViewClient;
import android.widget.ProgressBar;

import ug.go.health.ihrisbiometric.R;

public class AboutProjectActivity extends AppCompatActivity {

    private Toolbar mToolbar;
    private WebView webView;
    private ProgressBar loadingIndicator;

    @SuppressLint("SetJavaScriptEnabled")
    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_about_project);

        webView = findViewById(R.id.webView);
        loadingIndicator = findViewById(R.id.loadingIndicator);

        mToolbar = findViewById(R.id.about_project_toolbar);
        setSupportActionBar(mToolbar);
        getSupportActionBar().setDisplayHomeAsUpEnabled(true);
        getSupportActionBar().setTitle("About Project");

        // Configure WebView settings
        webView.getSettings().setJavaScriptEnabled(true);

        // Set a WebViewClient to monitor page loading progress
        webView.setWebViewClient(new WebViewClient() {
            @Override
            public void onPageStarted(WebView view, String url, android.graphics.Bitmap favicon) {
                // Show the loading indicator
                loadingIndicator.setVisibility(View.VISIBLE);
            }

            @Override
            public void onPageFinished(WebView view, String url) {
                // Hide the loading indicator
                loadingIndicator.setVisibility(View.GONE);
            }
        });

        String htmlContent = generateHtmlContent();
        webView.loadDataWithBaseURL(null, htmlContent, "text/html", "utf-8", null);
    }

    @Override
    public boolean onOptionsItemSelected(MenuItem item) {
        if (item.getItemId() == android.R.id.home) {
            onBackPressed();
            return true;
        }
        return super.onOptionsItemSelected(item);
    }

    private String generateHtmlContent() {
        // Replace with the code that generates the HTML content using the details provided above
        // You can use HTML tags and CSS styles to structure and format the content
        String htmlContent = "<html>" +
                "<head>" +
                "<meta name=\"viewport\" content=\"width=device-width, initial-scale=1\">" +
                "<style>" +
                "body { font-family: Arial, sans-serif; padding: 10px; }" +
                "h1 { color: #333; font-size: 18px; font-weight:bold; }" +
                "h2 { color: gray; font-size: 16px; }" +
                "h3 { color: #000; font-size: 14px; }" +
                "img { max-width: 100px !important; height: auto; margin-bottom: 10px; margin-right: 20px; }" +
                "p { margin-bottom: 10px; }" +
                "</style>" +
                "</head>" +
                "<body>" +
                "<h1>HRM Attend Scanner App - Empowering Healthcare in Uganda</h1>" +
                "<h2><i>The HRM Attend App offers a comprehensive attendance verification system that goes beyond traditional methods. In addition to utilizing fingerprint readers, the app leverages the power of AI-powered face recognition technology. This advanced feature provides healthcare providers with an alternative option for attendance verification based on facial recognition.</i></h2>" +
                "<img src='file:///android_res/drawable/moh_logo.png' alt='Ministry of Health Uganda Logo' />" +
                "<h2>Our Partners</h2>" +
                "<hr />" +
                "<p style='font-style: italic;'>HRM Attend mobile app was developed with thanks to</p>" +
                "<img src='file:///android_res/drawable/intrahealth.png' alt='IntraHealth Logo' />" +
                "<img src='file:///android_res/drawable/usaid.png' alt='USAID Logo' />" +
                "<img src='file:///android_res/drawable/dfid_logo.png' alt='DFID Logo' />" +
                "<h2>Project Activities:</h2>" +
                "<p>1. App Development: ...</p>" +
                "<p>2. Biometric Integration: ...</p>" +
                "<p>3. Data Encryption and Security: ...</p>" +
                "<p>4. Real-time Verification: ...</p>" +
                "<p>5. Database Integration: ...</p>" +
                "<p>6. User Training and Support: ...</p>" +
                "</body></html>";

        return htmlContent;
    }
}
