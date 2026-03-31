package ug.go.health.ihrisbiometric.adapters;

import android.content.Context;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.BaseAdapter;
import android.widget.ImageView;
import android.widget.TextView;

import java.util.List;

import ug.go.health.ihrisbiometric.R;
import ug.go.health.ihrisbiometric.models.SettingsItem;

public class SettingsAdapter extends BaseAdapter {

    private Context mContext;
    private List<SettingsItem> mSettingsItemList;

    private OnItemClickListener onItemClickListener;

    public SettingsAdapter(Context context, List<SettingsItem> settingsItemList) {
        mContext = context;
        mSettingsItemList = settingsItemList;
    }

    @Override
    public int getCount() {
        return mSettingsItemList.size();
    }

    @Override
    public Object getItem(int position) {
        return mSettingsItemList.get(position);
    }

    @Override
    public long getItemId(int position) {
        return position;
    }

    public void setOnItemClickListener(OnItemClickListener listener) {
        this.onItemClickListener = listener;
    }

    @Override
    public View getView(int position, View convertView, ViewGroup parent) {

        if (convertView == null) {
            LayoutInflater inflater = LayoutInflater.from(mContext);
            convertView = inflater.inflate(R.layout.settings_list_item, parent, false);
        }

        ImageView iconImageView = convertView.findViewById(R.id.settings_list_item_icon);
        TextView titleTextView = convertView.findViewById(R.id.settings_list_item_name);
        TextView descriptionTextView = convertView.findViewById(R.id.settings_list_item_description);

        SettingsItem settingsItem = mSettingsItemList.get(position);

        iconImageView.setImageResource(settingsItem.getIconResId());
        titleTextView.setText(settingsItem.getTitle());
        descriptionTextView.setText(settingsItem.getDescription());


        convertView.setOnClickListener(v -> onItemClickListener.onItemClick(settingsItem));



        return convertView;
    }

    public interface OnItemClickListener {
        void onItemClick(SettingsItem item);
    }
}
