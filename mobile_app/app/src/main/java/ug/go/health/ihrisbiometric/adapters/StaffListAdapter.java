package ug.go.health.ihrisbiometric.adapters;

import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.TextView;

import androidx.recyclerview.widget.RecyclerView;

import java.util.List;

import ug.go.health.ihrisbiometric.R;
import ug.go.health.ihrisbiometric.models.StaffRecord;

public class StaffListAdapter extends RecyclerView.Adapter<StaffListAdapter.StaffListViewHolder> {


    private List<StaffRecord> mStaffList;

    public StaffListAdapter(List<StaffRecord> staffList) {
        mStaffList = staffList;
    }

    @Override
    public StaffListAdapter.StaffListViewHolder onCreateViewHolder(ViewGroup parent, int viewType) {
        View itemView = LayoutInflater.from(parent.getContext())
                .inflate(R.layout.list_item_staff, parent, false);
        return new StaffListAdapter.StaffListViewHolder(itemView);
    }

    @Override
    public void onBindViewHolder(StaffListAdapter.StaffListViewHolder holder, int position) {
        StaffRecord staff = mStaffList.get(position);
        String firstname = capitalizeFirstLetter(staff.getFirstname());
        String othername = capitalizeFirstLetter(staff.getOthername());
        String surname = capitalizeFirstLetter(staff.getSurname());

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

        String positionName = staff.getJob() == null ? "No Position Data" : staff.getJob();
        String name = String.join(" ", firstname, othername, surname).trim();

        holder.tvName.setText(name);
        holder.tvPosition.setText(positionName);
    }

    public String capitalizeFirstLetter(String name) {
        return name != null && !name.isEmpty() ? Character.toUpperCase(name.charAt(0)) + name.substring(1).toLowerCase() : "";
    }

    @Override
    public int getItemCount() {
        return mStaffList.size();
    }

    public class StaffListViewHolder extends RecyclerView.ViewHolder {

        public TextView tvName;
        public TextView tvPosition;;

        public StaffListViewHolder(View itemView) {
            super(itemView);
            tvName = itemView.findViewById(R.id.tv_staff_name);
            tvPosition = itemView.findViewById(R.id.tv_staff_position);
        }
    }

    public interface OnItemClickListener {
        void onItemClick(StaffRecord item);
    }
}
