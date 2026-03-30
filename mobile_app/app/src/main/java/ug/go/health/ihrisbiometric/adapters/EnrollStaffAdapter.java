package ug.go.health.ihrisbiometric.adapters;

import android.graphics.Bitmap;
import android.graphics.BitmapFactory;
import android.util.Base64;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.ImageView;
import android.widget.TextView;

import androidx.annotation.NonNull;
import androidx.recyclerview.widget.RecyclerView;

import com.google.android.material.button.MaterialButton;

import java.io.File;
import java.util.List;

import ug.go.health.ihrisbiometric.R;
import ug.go.health.ihrisbiometric.models.StaffRecord;

public class EnrollStaffAdapter extends RecyclerView.Adapter<EnrollStaffAdapter.EnrollStaffListViewHolder> {

    private List<StaffRecord> mStaffRecordList;

    public interface OnItemClickListener {
        void onItemClick(int position);
    }

    public interface OnReEnrollListener {
        void onReEnrollFingerprint(StaffRecord record);
        void onReEnrollFace(StaffRecord record);
    }

    private OnItemClickListener mListener;
    private OnReEnrollListener mReEnrollListener;

    public void setOnItemClickListener(OnItemClickListener listener) {
        mListener = listener;
    }

    public void setOnReEnrollListener(OnReEnrollListener listener) {
        mReEnrollListener = listener;
    }

    public EnrollStaffAdapter(List<StaffRecord> staffRecordList) {
        mStaffRecordList = staffRecordList;
    }

    @NonNull
    @Override
    public EnrollStaffListViewHolder onCreateViewHolder(@NonNull ViewGroup parent, int viewType) {
        View itemView = LayoutInflater.from(parent.getContext())
                .inflate(R.layout.enroll_staff_list_item, parent, false);
        return new EnrollStaffListViewHolder(itemView);
    }

    @Override
    public void onBindViewHolder(@NonNull EnrollStaffListViewHolder holder, int position) {
        StaffRecord record = mStaffRecordList.get(position);

        holder.tvName.setText(record.getName());

        // Determine actual enrollment health (enrolled flag + file exists)
        boolean fpOk = record.isFingerprintEnrolled()
                && record.getFingerprintPath() != null
                && new File(record.getFingerprintPath()).exists();
        boolean faceOk = record.isFaceEnrolled()
                && record.getFacePath() != null
                && new File(record.getFacePath()).exists();

        // Status text
        if (fpOk && faceOk) {
            holder.tvStatus.setText("Fully enrolled");
            holder.tvStatus.setTextColor(0xFF388E3C); // green
        } else if (fpOk) {
            holder.tvStatus.setText("Fingerprint only — face missing");
            holder.tvStatus.setTextColor(0xFFF57C00); // orange
        } else if (faceOk) {
            holder.tvStatus.setText("Face only — fingerprint missing");
            holder.tvStatus.setTextColor(0xFFF57C00);
        } else if (record.isFingerprintEnrolled() || record.isFaceEnrolled()) {
            holder.tvStatus.setText("Enrolled but file missing — re-enroll needed");
            holder.tvStatus.setTextColor(0xFFD32F2F); // red
        } else {
            holder.tvStatus.setText("Not enrolled");
            holder.tvStatus.setTextColor(0xFF9E9E9E); // gray
        }

        // Show re-enroll fingerprint button if fingerprint is missing or file gone
        boolean needsFpReEnroll = !fpOk; // not enrolled OR file missing
        holder.btnReEnrollFingerprint.setVisibility(needsFpReEnroll ? View.VISIBLE : View.GONE);
        holder.btnReEnrollFingerprint.setOnClickListener(v -> {
            if (mReEnrollListener != null) mReEnrollListener.onReEnrollFingerprint(record);
        });

        // Show re-enroll face button if face is missing or file gone
        boolean needsFaceReEnroll = !faceOk;
        holder.btnReEnrollFace.setVisibility(needsFaceReEnroll ? View.VISIBLE : View.GONE);
        holder.btnReEnrollFace.setOnClickListener(v -> {
            if (mReEnrollListener != null) mReEnrollListener.onReEnrollFace(record);
        });

        // Show face image from .face file if available
        if (record.getFacePath() != null && new File(record.getFacePath()).exists()) {
            try {
                byte[] bytes = java.nio.file.Files.readAllBytes(new File(record.getFacePath()).toPath());
                Bitmap bmp = BitmapFactory.decodeByteArray(bytes, 0, bytes.length);
                if (bmp != null) holder.ivStaffImage.setImageBitmap(bmp);
            } catch (Exception ignored) {}
        } else if (record.getFaceImage() != null && !record.getFaceImage().isEmpty()) {
            try {
                byte[] bytes = Base64.decode(record.getFaceImage(), Base64.DEFAULT);
                Bitmap bmp = BitmapFactory.decodeByteArray(bytes, 0, bytes.length);
                if (bmp != null) holder.ivStaffImage.setImageBitmap(bmp);
            } catch (Exception ignored) {}
        }

        holder.itemView.setOnClickListener(v -> {
            if (mListener != null) {
                int pos = holder.getAdapterPosition();
                if (pos != RecyclerView.NO_POSITION) mListener.onItemClick(pos);
            }
        });
    }

    @Override
    public int getItemCount() {
        return mStaffRecordList.size();
    }

    public static class EnrollStaffListViewHolder extends RecyclerView.ViewHolder {
        ImageView ivStaffImage;
        TextView tvName;
        TextView tvStatus;
        MaterialButton btnReEnrollFingerprint;
        MaterialButton btnReEnrollFace;

        public EnrollStaffListViewHolder(@NonNull View itemView) {
            super(itemView);
            ivStaffImage = itemView.findViewById(R.id.iv_staff_image);
            tvName = itemView.findViewById(R.id.tv_staff_name);
            tvStatus = itemView.findViewById(R.id.tv_enroll_status);
            btnReEnrollFingerprint = itemView.findViewById(R.id.btn_re_enroll_fingerprint);
            btnReEnrollFace = itemView.findViewById(R.id.btn_re_enroll_face);
        }
    }
}
