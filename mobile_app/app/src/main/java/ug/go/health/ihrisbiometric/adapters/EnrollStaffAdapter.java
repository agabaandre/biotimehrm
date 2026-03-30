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

import java.io.File;
import java.util.List;

import ug.go.health.ihrisbiometric.R;
import ug.go.health.ihrisbiometric.models.StaffRecord;

public class EnrollStaffAdapter extends RecyclerView.Adapter<EnrollStaffAdapter.EnrollStaffListViewHolder> {

    private List<StaffRecord> mStaffRecordList;

    public interface OnItemClickListener {
        void onItemClick(int position);
    }

    private OnItemClickListener mListener;

    public void setOnItemClickListener(OnItemClickListener listener) {
        mListener = listener;
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

        boolean fpOk = record.isFingerprintEnrolled()
                && record.getFingerprintPath() != null
                && new File(record.getFingerprintPath()).exists();
        boolean faceOk = record.isFaceEnrolled()
                && record.getFacePath() != null
                && new File(record.getFacePath()).exists();

        if (fpOk && faceOk) {
            holder.tvStatus.setText("Fully enrolled");
            holder.tvStatus.setTextColor(0xFF388E3C);
        } else if (fpOk) {
            holder.tvStatus.setText("Fingerprint only — face missing");
            holder.tvStatus.setTextColor(0xFFF57C00);
        } else if (faceOk) {
            holder.tvStatus.setText("Face only — fingerprint missing");
            holder.tvStatus.setTextColor(0xFFF57C00);
        } else if (record.isFingerprintEnrolled() || record.isFaceEnrolled()) {
            holder.tvStatus.setText("Enrolled but file missing — re-enroll needed");
            holder.tvStatus.setTextColor(0xFFD32F2F);
        } else {
            holder.tvStatus.setText("Not enrolled");
            holder.tvStatus.setTextColor(0xFF9E9E9E);
        }

        // Show face image from .face file or base64 if available
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

        public EnrollStaffListViewHolder(@NonNull View itemView) {
            super(itemView);
            ivStaffImage = itemView.findViewById(R.id.iv_staff_image);
            tvName = itemView.findViewById(R.id.tv_staff_name);
            tvStatus = itemView.findViewById(R.id.tv_enroll_status);
        }
    }
}
