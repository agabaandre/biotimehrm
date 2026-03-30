package ug.go.health.ihrisbiometric.utils;

import android.content.Context;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.ArrayAdapter;
import android.widget.TextView;

import androidx.annotation.NonNull;

import java.util.List;

import ug.go.health.ihrisbiometric.R;

public class DropdownAdapter extends ArrayAdapter<String> {

    public DropdownAdapter(@NonNull Context context, @NonNull List<String> items) {
        super(context, 0, items);
    }

    @Override
    public View getView(int position, View convertView, ViewGroup parent) {
        return getCustomView(position, convertView, parent);
    }

    @Override
    public View getDropDownView(int position, View convertView, ViewGroup parent) {
        return getCustomView(position, convertView, parent);
    }

    private View getCustomView(int position, View convertView, ViewGroup parent) {
        if (convertView == null) {
            convertView = LayoutInflater.from(getContext()).inflate(R.layout.dropdown_item_layout, parent, false);
        }

        TextView textViewItem = convertView.findViewById(R.id.textViewItem);
        textViewItem.setText(getItem(position));

        return convertView;
    }


}


