package ug.go.health.ihrisbiometric.adapters;

import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.ImageButton;
import android.widget.TextView;

import androidx.annotation.NonNull;
import androidx.recyclerview.widget.RecyclerView;

import java.util.List;

import ug.go.health.ihrisbiometric.R;
import ug.go.health.ihrisbiometric.models.StaffRecord;

public class StaffManagementAdapter extends RecyclerView.Adapter<StaffManagementAdapter.StaffViewHolder> {

    private List<StaffRecord> staffList;
    private OnStaffActionListener listener;

    public interface OnStaffActionListener {
        void onEdit(StaffRecord staff);
        void onDelete(StaffRecord staff);
    }

    public StaffManagementAdapter(List<StaffRecord> staffList, OnStaffActionListener listener) {
        this.staffList = staffList;
        this.listener = listener;
    }

    @NonNull
    @Override
    public StaffViewHolder onCreateViewHolder(@NonNull ViewGroup parent, int viewType) {
        View view = LayoutInflater.from(parent.getContext()).inflate(R.layout.item_staff_management, parent, false);
        return new StaffViewHolder(view);
    }

    @Override
    public void onBindViewHolder(@NonNull StaffViewHolder holder, int position) {
        StaffRecord staff = staffList.get(position);
        holder.tvName.setText(staff.getName());
        holder.tvJob.setText(staff.getJob());
        holder.tvFacility.setText(staff.getFacility());

        holder.btnEdit.setOnClickListener(v -> listener.onEdit(staff));
        holder.btnDelete.setOnClickListener(v -> listener.onDelete(staff));
    }

    @Override
    public int getItemCount() {
        return staffList.size();
    }

    static class StaffViewHolder extends RecyclerView.ViewHolder {
        TextView tvName, tvJob, tvFacility;
        ImageButton btnEdit, btnDelete;

        StaffViewHolder(@NonNull View itemView) {
            super(itemView);
            tvName = itemView.findViewById(R.id.tv_staff_name);
            tvJob = itemView.findViewById(R.id.tv_staff_job);
            tvFacility = itemView.findViewById(R.id.tv_staff_facility);
            btnEdit = itemView.findViewById(R.id.btn_edit_staff);
            btnDelete = itemView.findViewById(R.id.btn_delete_staff);
        }
    }
}
