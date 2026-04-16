package ug.go.health.ihrisbiometric.adapters;

import android.content.Context;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.BaseExpandableListAdapter;
import android.widget.ImageView;
import android.widget.TextView;

import androidx.core.content.ContextCompat;

import java.util.List;
import java.util.Map;

import ug.go.health.ihrisbiometric.R;
import ug.go.health.ihrisbiometric.models.ClockHistory;
import ug.go.health.ihrisbiometric.models.StaffRecord;

public class SyncCategoryAdapter extends BaseExpandableListAdapter {

    private Context context;
    private List<String> categories;
    private Map<String, List<?>> categoryItems;

    public SyncCategoryAdapter(Context context, List<String> categories, Map<String, List<?>> categoryItems) {
        this.context = context;
        this.categories = categories;
        this.categoryItems = categoryItems;
    }

    @Override
    public int getGroupCount() {
        return categories.size();
    }

    @Override
    public int getChildrenCount(int groupPosition) {
        return categoryItems.get(categories.get(groupPosition)).size();
    }

    @Override
    public Object getGroup(int groupPosition) {
        return categories.get(groupPosition);
    }

    @Override
    public Object getChild(int groupPosition, int childPosition) {
        return categoryItems.get(categories.get(groupPosition)).get(childPosition);
    }

    @Override
    public long getGroupId(int groupPosition) {
        return groupPosition;
    }

    @Override
    public long getChildId(int groupPosition, int childPosition) {
        return childPosition;
    }

    @Override
    public boolean hasStableIds() {
        return false;
    }

    @Override
    public View getGroupView(int groupPosition, boolean isExpanded, View convertView, ViewGroup parent) {
        String categoryTitle = (String) getGroup(groupPosition);
        if (convertView == null) {
            LayoutInflater inflater = (LayoutInflater) context.getSystemService(Context.LAYOUT_INFLATER_SERVICE);
            convertView = inflater.inflate(R.layout.list_group, null);
        }
        TextView tvGroup = convertView.findViewById(R.id.tvGroup);
        tvGroup.setText(categoryTitle);
        return convertView;
    }

    @Override
    public View getChildView(int groupPosition, int childPosition, boolean isLastChild, View convertView, ViewGroup parent) {
        Object item = getChild(groupPosition, childPosition);
        if (convertView == null) {
            LayoutInflater inflater = (LayoutInflater) context.getSystemService(Context.LAYOUT_INFLATER_SERVICE);
            convertView = inflater.inflate(R.layout.list_item, null);
        }
        TextView tvItem = convertView.findViewById(R.id.tvItem);
        ImageView ivFace = convertView.findViewById(R.id.ivFace);
        ImageView ivFingerprint = convertView.findViewById(R.id.ivFingerprint);


        if (item instanceof StaffRecord) {
            StaffRecord staffRecord = (StaffRecord) item;
            tvItem.setText(staffRecord.getName() + " - " + staffRecord.getIhrisPid());

            // Set face icon state
            if (staffRecord.isFaceEnrolled()) {
                ivFace.setColorFilter(ContextCompat.getColor(context, R.color.primary));
            } else {
                ivFace.setColorFilter(ContextCompat.getColor(context, R.color.gray));
            }

            // Set fingerprint icon state
            if (staffRecord.isFingerprintEnrolled()) {
                ivFingerprint.setColorFilter(ContextCompat.getColor(context, R.color.primary));
            } else {
                ivFingerprint.setColorFilter(ContextCompat.getColor(context, R.color.gray));
            }

            ivFace.setVisibility(View.VISIBLE);
            ivFingerprint.setVisibility(View.VISIBLE);
        } else if (item instanceof ClockHistory) {
            ClockHistory clockHistory = (ClockHistory) item;
            tvItem.setText(clockHistory.getName() + " - " + clockHistory.getClockTime());

            // Hide icons for ClockHistory items
            ivFace.setVisibility(View.GONE);
            ivFingerprint.setVisibility(View.GONE);
        }

        return convertView;
    }

    @Override
    public boolean isChildSelectable(int groupPosition, int childPosition) {
        return true;
    }
}