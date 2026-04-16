package ug.go.health.ihrisbiometric.utils;

import android.app.Activity;
import android.view.LayoutInflater;
import android.view.View;
import android.widget.Button;
import android.widget.ImageView;
import android.widget.TextView;

import androidx.appcompat.app.AlertDialog;

import ug.go.health.ihrisbiometric.R;

public class CustomAlertDialog {

    private Activity activity;
    private AlertDialog alertDialog;

    private ImageView ivIcon;
    private TextView tvTitle;
    private TextView tvMessage;
    private Button btnAction;

    public CustomAlertDialog(Activity activity) {
        this.activity = activity;
    }

    public void show( int icon, String title, String message, String actionText, Runnable action) {
        AlertDialog.Builder builder = new AlertDialog.Builder(activity);

        LayoutInflater inflater = activity.getLayoutInflater();
        View view = inflater.inflate(R.layout.custom_alert_dialog, null);
        builder.setView(view);

        alertDialog = builder.create();

        // Set background to white
        alertDialog.getWindow().setBackgroundDrawableResource(android.R.color.white);

        // Customise the icon
        ivIcon = view.findViewById(R.id.iv_icon);
        ivIcon.setImageResource(icon);

        // Customise the title
        tvTitle = view.findViewById(R.id.tv_title);
        tvTitle.setText(title);

        // Customise the message
        tvMessage = view.findViewById(R.id.tv_message);
        tvMessage.setText(message);

        alertDialog.setCanceledOnTouchOutside(false);

        // Customise the action button
        btnAction = view.findViewById(R.id.btn_action);
        btnAction.setText(actionText);

        btnAction.setOnClickListener(v -> {
            action.run();
            hide();
        });

        alertDialog.show();

    }

    public void hide() {
        alertDialog.dismiss();
    }

}
