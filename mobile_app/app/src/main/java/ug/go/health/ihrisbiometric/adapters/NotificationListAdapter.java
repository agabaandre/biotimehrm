package ug.go.health.ihrisbiometric.adapters;

import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.TextView;

import androidx.recyclerview.widget.RecyclerView;

import java.util.List;

import ug.go.health.ihrisbiometric.R;
import ug.go.health.ihrisbiometric.models.NotificationListResponse;

public class NotificationListAdapter extends RecyclerView.Adapter<NotificationListAdapter.NotificationViewHolder> {

    private List<NotificationListResponse.Notification> mNotificationList;

    public NotificationListAdapter(List<NotificationListResponse.Notification> notificationList) {
        mNotificationList = notificationList;
    }

    @Override
    public NotificationViewHolder onCreateViewHolder(ViewGroup parent, int viewType) {
        View itemView = LayoutInflater.from(parent.getContext())
                .inflate(R.layout.notification_list_item, parent, false);
        return new NotificationViewHolder(itemView);
    }

    @Override
    public void onBindViewHolder(NotificationViewHolder holder, int position) {
        NotificationListResponse.Notification notification = mNotificationList.get(position);
        holder.tvNotificationTitle.setText(notification.getTitle());
        holder.tvNotificationMessage.setText(notification.getMessage());
    }

    @Override
    public int getItemCount() {
        return mNotificationList.size();
    }

    public class NotificationViewHolder extends RecyclerView.ViewHolder {

        public TextView tvNotificationTitle;
        public TextView tvNotificationMessage;;

        public NotificationViewHolder(View itemView) {
            super(itemView);
            tvNotificationTitle = itemView.findViewById(R.id.tv_notification_title);
            tvNotificationMessage = itemView.findViewById(R.id.tv_notification_message);
        }
    }
}
