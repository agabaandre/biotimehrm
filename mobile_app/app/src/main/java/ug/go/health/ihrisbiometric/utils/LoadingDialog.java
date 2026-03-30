package ug.go.health.ihrisbiometric.utils;

import android.app.Activity;
import android.view.LayoutInflater;
import android.view.View;
import android.widget.TextView;

import androidx.appcompat.app.AlertDialog;

import ug.go.health.ihrisbiometric.R;

public class LoadingDialog {

    private Activity activity;
    private AlertDialog alertDialog;

    private TextView tvLoadingMessage;
    private String message;

    public LoadingDialog(Activity activity) {
        this.activity = activity;
        this.message = message;
    }

    public void show() {
        AlertDialog.Builder builder = new AlertDialog.Builder(activity);

        LayoutInflater inflater = activity.getLayoutInflater();
        View view = inflater.inflate(R.layout.loading_dialog, null);
        builder.setView(view);

        alertDialog = builder.create();

        // Set background to transparent
        alertDialog.getWindow().setBackgroundDrawableResource(android.R.color.transparent);

        // Customise the loading message
        tvLoadingMessage = view.findViewById(R.id.tv_loading_message);
        tvLoadingMessage.setText(message);

        // Set to not dismissable
        alertDialog.setCanceledOnTouchOutside(false);

        alertDialog.show();
    }

    public void close() {
        alertDialog.dismiss();
    }


    public void show(String message) {
        this.message = message;
        show();
    }

    public void hide() {
        alertDialog.dismiss();
    }
}
