package ug.go.health.ihrisbiometric.adapters;

import android.graphics.Bitmap;
import android.graphics.BitmapFactory;
import android.os.Environment;
import android.util.Log;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.ImageView;
import android.widget.TextView;

import androidx.annotation.NonNull;
import androidx.recyclerview.widget.RecyclerView;

import java.util.List;

import ug.go.health.ihrisbiometric.R;
import ug.go.health.ihrisbiometric.models.StaffRecord;

public class EnrollStaffAdapter extends RecyclerView.Adapter<EnrollStaffAdapter.EnrollStaffListViewHolder> {

    private List<StaffRecord> mStaffRecordList;

    // Setup a listener
    public interface OnItemClickListener {
        void onItemClick(int position);
    }

    // Create a listener variable
    private OnItemClickListener mListener;

    // Create a setter
    public void setOnItemClickListener(OnItemClickListener listener) {
        mListener = listener;
    }

    public EnrollStaffAdapter(List<StaffRecord> staffRecordList)
    {
        mStaffRecordList = staffRecordList;
    }


    @NonNull
    @Override
    public EnrollStaffAdapter.EnrollStaffListViewHolder onCreateViewHolder(@NonNull ViewGroup parent, int viewType) {
        View itemView = LayoutInflater.from(parent.getContext())
                .inflate(R.layout.enroll_staff_list_item, parent, false);
        return new EnrollStaffAdapter.EnrollStaffListViewHolder(itemView);
    }

    @Override
    public void onBindViewHolder(@NonNull EnrollStaffAdapter.EnrollStaffListViewHolder holder, int position) {
        StaffRecord staffRecord = mStaffRecordList.get(position);
        String firstname = staffRecord.getFirstname();
        String othername = staffRecord.getOthername();
        String surname = staffRecord.getSurname();

        // Skip any of the names that is null
        if (firstname == null) {
            firstname = "";
        }

        if (othername == null) {
            othername = "";
        }

        if (surname == null) {
            surname = "";
        }

        String name = firstname + " " + othername + " " + surname;
        holder.tvName.setText(name);
        if (staffRecord.isFaceEnrolled() && staffRecord.isFingerprintEnrolled()) {
            holder.tvStatus.setText("Staff Enrolled");
        } else if(staffRecord.isFingerprintEnrolled() && !staffRecord.isFaceEnrolled()) {
            holder.tvStatus.setText("Only Fingerprint Enrolled");
        } else if(staffRecord.isFaceEnrolled() && !staffRecord.isFingerprintEnrolled()) {
            holder.tvStatus.setText("Only Face Enrolled");
        } else {
            holder.tvStatus.setText("Not Enrolled");
        }

        // If face is enrolled then we can show the image by getting it from directory
        if (staffRecord.isFaceEnrolled()) {
            // Get the image from the directory
            String savedPath = Environment.getExternalStorageDirectory().getAbsolutePath();
            String image = savedPath + "/iHRIS Biometric/Staff Images/" + staffRecord.getIhrisPid() + ".jpg";

            try {
                // Check if the image is null and set the image
                Bitmap staffImage = BitmapFactory.decodeFile(image);
                if (staffImage != null) {
                    holder.ivStaffImage.setImageBitmap(staffImage);
                } else {
                    // Log an error message or handle the case when the image is null
                    Log.e("ImageLoad", "Failed to load image: " + image);
                }
            } catch (Exception e) {
                // Log any exceptions that occur during image loading
                Log.e("ImageLoad", "Error loading image: " + image, e);
            }
        }

        // Set the click listener
        holder.itemView.setOnClickListener(v -> {
            // Check if the listener is set
            if (mListener != null) {
                // Get the position of the item that was clicked
                int position1 = holder.getAdapterPosition();
                // Check if the position is valid
                if (position1 != RecyclerView.NO_POSITION) {
                    // Call the listener
                    mListener.onItemClick(position1);
                }
            }
        });
    }

    @Override
    public int getItemCount() {
        return mStaffRecordList.size();
    }

    public class EnrollStaffListViewHolder extends RecyclerView.ViewHolder {

        private ImageView ivStaffImage;
        public TextView tvName;
        public TextView tvStatus;

        public EnrollStaffListViewHolder(@NonNull View itemView) {

            super(itemView);
            tvName = itemView.findViewById(R.id.tv_staff_name);
            tvStatus = itemView.findViewById(R.id.tv_enroll_status);
            ivStaffImage = itemView.findViewById(R.id.iv_staff_image);
        }
    }
}
