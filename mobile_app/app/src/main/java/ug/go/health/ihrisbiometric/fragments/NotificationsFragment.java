package ug.go.health.ihrisbiometric.fragments;

import android.os.Bundle;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.RelativeLayout;

import androidx.annotation.NonNull;
import androidx.annotation.Nullable;
import androidx.appcompat.widget.Toolbar;
import androidx.fragment.app.Fragment;
import androidx.recyclerview.widget.LinearLayoutManager;
import androidx.recyclerview.widget.RecyclerView;

import java.util.ArrayList;
import java.util.List;

import retrofit2.Call;
import retrofit2.Callback;
import retrofit2.Response;
import ug.go.health.ihrisbiometric.R;
import ug.go.health.ihrisbiometric.adapters.NotificationListAdapter;
import ug.go.health.ihrisbiometric.models.NotificationListResponse;
import ug.go.health.ihrisbiometric.services.ApiInterface;
import ug.go.health.ihrisbiometric.services.ApiService;
import ug.go.health.ihrisbiometric.services.SessionService;

public class NotificationsFragment extends Fragment {

    ApiInterface apiService;
    SessionService session;
    private RecyclerView mRecyclerView;
    private NotificationListAdapter mAdapter;
    private List<NotificationListResponse.Notification> mNotificationsList;

    private View emptyView;

    @Nullable
    @Override
    public View onCreateView(@NonNull LayoutInflater inflater, @Nullable ViewGroup container, @Nullable Bundle savedInstanceState) {
        View rootView = inflater.inflate(R.layout.fragment_notifications, container, false);

        Toolbar toolbar = rootView.findViewById(R.id.notifications_toolbar);
        toolbar.setTitle("Notifications");
        toolbar.setNavigationIcon(R.drawable.baseline_arrow_back_24);
        toolbar.setNavigationOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                getActivity().onBackPressed();
            }
        });

        mRecyclerView = rootView.findViewById(R.id.rv_notification_list);
        mNotificationsList = new ArrayList<>();
        mAdapter = new NotificationListAdapter(mNotificationsList);
        mRecyclerView.setAdapter(mAdapter);
        mRecyclerView.setLayoutManager(new LinearLayoutManager(getContext()));

        session = new SessionService(getContext());

        String token = session.getToken();
        apiService = ApiService.getApiInterface(getContext());

        mRecyclerView.setVisibility(View.GONE);
        if (emptyView != null) {
            emptyView.setVisibility(View.VISIBLE);
        }

        apiService.getNotificationList().enqueue(new Callback<NotificationListResponse>() {
            @Override
            public void onResponse(Call<NotificationListResponse> call, Response<NotificationListResponse> response) {
                if (response.isSuccessful() && response.body() != null) {
                    mNotificationsList.addAll(response.body().getNotifications());
                    mAdapter.notifyDataSetChanged();

                    if (mNotificationsList.isEmpty()) {
                        mRecyclerView.setVisibility(View.GONE);
                        if (emptyView != null) {
                            emptyView.setVisibility(View.VISIBLE);
                        }
                    } else {
                        mRecyclerView.setVisibility(View.VISIBLE);
                        if (emptyView != null) {
                            emptyView.setVisibility(View.GONE);
                        }
                    }
                }
            }

            @Override
            public void onFailure(Call<NotificationListResponse> call, Throwable t) {
                // Handle failure
            }
        });

        mRecyclerView = rootView.findViewById(R.id.rv_notification_list);
        mNotificationsList = new ArrayList<>();
        mAdapter = new NotificationListAdapter(mNotificationsList);
        mRecyclerView.setAdapter(mAdapter);
        mRecyclerView.setLayoutManager(new LinearLayoutManager(getContext()));

        emptyView = getLayoutInflater().inflate(R.layout.layout_empty_view, null);
        RelativeLayout.LayoutParams layoutParams = new RelativeLayout.LayoutParams(
                RelativeLayout.LayoutParams.MATCH_PARENT,
                RelativeLayout.LayoutParams.MATCH_PARENT
        );
        layoutParams.addRule(RelativeLayout.BELOW, R.id.notifications_toolbar);
        ((RelativeLayout) rootView.findViewById(R.id.fragment_notifications_container)).addView(emptyView, layoutParams);

        session = new SessionService(getContext());

        token = session.getToken();
        apiService = ApiService.getApiInterface(getContext());

        mRecyclerView.setVisibility(View.GONE);
        emptyView.setVisibility(View.VISIBLE);

        apiService.getNotificationList().enqueue(new Callback<NotificationListResponse>() {
            @Override
            public void onResponse(Call<NotificationListResponse> call, Response<NotificationListResponse> response) {
                if (response.isSuccessful() && response.body() != null) {
                    mNotificationsList.addAll(response.body().getNotifications());
                    mAdapter.notifyDataSetChanged();

                    if (mNotificationsList.isEmpty()) {
                        mRecyclerView.setVisibility(View.GONE);
                        emptyView.setVisibility(View.VISIBLE);
                    } else {
                        mRecyclerView.setVisibility(View.VISIBLE);
                        emptyView.setVisibility(View.GONE);
                    }
                }
            }

            @Override
            public void onFailure(Call<NotificationListResponse> call, Throwable t) {

            }
        });

        return rootView;
    }
}
