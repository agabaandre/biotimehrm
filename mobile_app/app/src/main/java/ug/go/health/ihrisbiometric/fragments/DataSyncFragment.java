package ug.go.health.ihrisbiometric.fragments;

import android.os.Bundle;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.ArrayAdapter;
import android.widget.ListView;
import android.widget.ProgressBar;
import android.widget.TextView;
import android.widget.Toast;

import android.content.Context;
import androidx.annotation.NonNull;
import androidx.annotation.Nullable;
import androidx.fragment.app.Fragment;
import androidx.lifecycle.Observer;
import androidx.lifecycle.ViewModel;
import androidx.lifecycle.ViewModelProvider;
import ug.go.health.ihrisbiometric.services.SessionService;

import com.google.android.material.appbar.MaterialToolbar;
import com.google.android.material.button.MaterialButton;

import java.util.ArrayList;
import java.util.HashMap;
import java.util.List;
import java.util.Map;

import ug.go.health.ihrisbiometric.R;
import ug.go.health.ihrisbiometric.adapters.SyncCategoryAdapter;
import ug.go.health.ihrisbiometric.models.ClockHistory;
import ug.go.health.ihrisbiometric.models.StaffRecord;
import ug.go.health.ihrisbiometric.utils.NonScrollableExpandableListView;
import ug.go.health.ihrisbiometric.viewmodels.DataSyncViewModel;
import ug.go.health.ihrisbiometric.viewmodels.HomeViewModel;

public class DataSyncFragment extends Fragment {

    private DataSyncViewModel viewModel;
    private TextView tvStaffSyncedCount, tvStaffUnsyncedCount, tvClockSyncedCount, tvClockUnsyncedCount, tvSyncMessage;
    private MaterialButton btnSync;
    private ProgressBar staffProgressBar;
    private ProgressBar clockProgressBar;
    private ProgressBar fingerprintProgressBar;
    private ProgressBar embeddingProgressBar;
    private NonScrollableExpandableListView expandableListView;
    private SyncCategoryAdapter syncCategoryAdapter;
    private List<String> categories;
    private Map<String, List<?>> categoryItems;

    @Nullable
    @Override
    public View onCreateView(@NonNull LayoutInflater inflater, @Nullable ViewGroup container, @Nullable Bundle savedInstanceState) {
        return inflater.inflate(R.layout.fragment_data_sync, container, false);
    }

    @Override
    public void onViewCreated(@NonNull View view, @Nullable Bundle savedInstanceState) {
        super.onViewCreated(view, savedInstanceState);

        initializeViews(view);
        setupToolbar();
        setupViewModel();
        initializeExpandableListView();
        observeViewModel();
        setupSyncButton();
    }

    private void initializeViews(View view) {
        tvStaffSyncedCount = view.findViewById(R.id.tvStaffSyncedCount);
        tvStaffUnsyncedCount = view.findViewById(R.id.tvStaffUnsyncedCount);
        tvClockSyncedCount = view.findViewById(R.id.tvClockSyncedCount);
        tvClockUnsyncedCount = view.findViewById(R.id.tvClockUnsyncedCount);
        tvSyncMessage = view.findViewById(R.id.tvSyncMessage);
        btnSync = view.findViewById(R.id.btnSync);
        staffProgressBar = view.findViewById(R.id.staffProgressBar);
        clockProgressBar = view.findViewById(R.id.clockProgressBar);
        fingerprintProgressBar = view.findViewById(R.id.fingerprintProgressBar);
        embeddingProgressBar = view.findViewById(R.id.embeddingProgressBar);
    }

    private void setupToolbar() {
        MaterialToolbar topAppBar = requireView().findViewById(R.id.topAppBar);
        // Set the navigation icon (back button) for the toolbar
        topAppBar.setNavigationIcon(R.drawable.baseline_arrow_back_24);
        topAppBar.setNavigationOnClickListener(v -> requireActivity().onBackPressed());
    }

    private void setupViewModel() {
        viewModel = new ViewModelProvider(this, new ViewModelProvider.Factory() {
            @NonNull
            @Override
            public <T extends ViewModel> T create(@NonNull Class<T> modelClass) {
                if (modelClass.isAssignableFrom(DataSyncViewModel.class)) {
                    return (T) new DataSyncViewModel(requireActivity().getApplication());
                }
                throw new IllegalArgumentException("Unknown ViewModel class");
            }
        }).get(DataSyncViewModel.class);

        // Pass the shared HomeViewModel so DataSyncViewModel can access the scanner
        HomeViewModel homeViewModel = new ViewModelProvider(requireActivity()).get(HomeViewModel.class);
        viewModel.setHomeViewModel(homeViewModel);
    }

    private void initializeExpandableListView() {
        categories = new ArrayList<>();
        categories.add("Staff Records Ready for Sync");
        categories.add("Staff Records Missing Info");
        categories.add("Clock History Ready for Sync");

        categoryItems = new HashMap<>();
        categoryItems.put(categories.get(0), new ArrayList<StaffRecord>());
        categoryItems.put(categories.get(1), new ArrayList<StaffRecord>());
        categoryItems.put(categories.get(2), new ArrayList<ClockHistory>());

        syncCategoryAdapter = new SyncCategoryAdapter(requireContext(), categories, categoryItems);
//        expandableListView.setAdapter(syncCategoryAdapter);
    }

    private void observeViewModel() {
        viewModel.getSyncStatus().observe(getViewLifecycleOwner(), this::updateSyncStatus);
        viewModel.getStaffSyncProgress().observe(getViewLifecycleOwner(), this::updateStaffProgressBar);
        viewModel.getClockSyncProgress().observe(getViewLifecycleOwner(), this::updateClockProgressBar);
        viewModel.getSyncMessages().observe(getViewLifecycleOwner(), this::updateSyncMessages);
        viewModel.getSyncedStaffCount().observe(getViewLifecycleOwner(), count -> updateCountView(tvStaffSyncedCount, "Synced", count));
        viewModel.getUnsyncedStaffCount().observe(getViewLifecycleOwner(), count -> updateCountView(tvStaffUnsyncedCount, "Unsynced", count));
        viewModel.getSyncedClockCount().observe(getViewLifecycleOwner(), count -> updateCountView(tvClockSyncedCount, "Synced", count));
        viewModel.getUnsyncedClockCount().observe(getViewLifecycleOwner(), count -> updateCountView(tvClockUnsyncedCount, "Unsynced", count));
        viewModel.getClockSyncProgress().observe(getViewLifecycleOwner(), new Observer<Integer>() {
            @Override
            public void onChanged(Integer integer) {
                // Update Clock Progress Bar
            }
        });
        viewModel.getStaffSyncProgress().observe(getViewLifecycleOwner(), new Observer<Integer>() {
            @Override
            public void onChanged(Integer integer) {
                // Update Staff Progress Bar
            }
        });

        // Observe lists for expandable list view
        viewModel.getStaffRecordsReadyForSync().observe(getViewLifecycleOwner(), this::updateStaffRecordsReadyForSync);
        viewModel.getStaffRecordsMissingInfo().observe(getViewLifecycleOwner(), this::updateStaffRecordsMissingInfo);
        viewModel.getClockHistoryReadyForSync().observe(getViewLifecycleOwner(), this::updateClockHistoryReadyForSync);

        // Observe fingerprint sync progress
        viewModel.getFingerprintSyncProgress().observe(getViewLifecycleOwner(), this::updateFingerprintProgressBar);
        viewModel.getFingerprintUploadCount().observe(getViewLifecycleOwner(), count -> {
            // Upload count is reported via sync messages from the ViewModel
        });
        viewModel.getFingerprintDownloadCount().observe(getViewLifecycleOwner(), count -> {
            // Download count is reported via sync messages from the ViewModel
        });

        // Observe embedding sync progress
        viewModel.getEmbeddingSyncProgress().observe(getViewLifecycleOwner(), this::updateEmbeddingProgressBar);
        viewModel.getEmbeddingUploadCount().observe(getViewLifecycleOwner(), count -> {
            // Upload count is reported via sync messages from the ViewModel
        });
        viewModel.getEmbeddingDownloadCount().observe(getViewLifecycleOwner(), count -> {
            // Download count is reported via sync messages from the ViewModel
        });
    }

    private void updateStaffProgressBar(int progress) {
        staffProgressBar.setProgress(progress);
    }

    private void updateClockProgressBar(int progress) {
        clockProgressBar.setProgress(progress);
    }

    private void updateFingerprintProgressBar(int progress) {
        fingerprintProgressBar.setProgress(progress);
    }

    private void updateEmbeddingProgressBar(int progress) {
        embeddingProgressBar.setProgress(progress);
    }

    private void setupSyncButton() {
        btnSync.setOnClickListener(v -> viewModel.startSync());
    }

    private void updateSyncStatus(DataSyncViewModel.SyncStatus status) {
        switch (status) {
            case IDLE:
                btnSync.setEnabled(true);
                staffProgressBar.setVisibility(View.GONE);
                clockProgressBar.setVisibility(View.GONE);
                fingerprintProgressBar.setVisibility(View.GONE);
                embeddingProgressBar.setVisibility(View.GONE);
                break;
            case IN_PROGRESS:
                btnSync.setEnabled(false);
                staffProgressBar.setVisibility(View.VISIBLE);
                clockProgressBar.setVisibility(View.VISIBLE);
                fingerprintProgressBar.setVisibility(View.VISIBLE);
                embeddingProgressBar.setVisibility(View.VISIBLE);
                break;
            case COMPLETED:
                btnSync.setEnabled(true);
                staffProgressBar.setVisibility(View.GONE);
                clockProgressBar.setVisibility(View.GONE);
                fingerprintProgressBar.setVisibility(View.GONE);
                embeddingProgressBar.setVisibility(View.GONE);
//                Toast.makeText(requireContext(), "Sync completed", Toast.LENGTH_SHORT).show();
                break;
            case FAILED:
                btnSync.setEnabled(true);
                staffProgressBar.setVisibility(View.GONE);
                clockProgressBar.setVisibility(View.GONE);
                fingerprintProgressBar.setVisibility(View.GONE);
                embeddingProgressBar.setVisibility(View.GONE);
                String errorMessage = viewModel.getSyncMessages().getValue().get(viewModel.getSyncMessages().getValue().size() - 1);
//                Toast.makeText(requireContext(), "Sync failed: " + errorMessage, Toast.LENGTH_SHORT).show();
                break;
        }
    }

    private void updateSyncMessages(List<String> messages) {
        requireActivity().runOnUiThread(() -> {
            ArrayAdapter<String> adapter = new ArrayAdapter<>(requireContext(), android.R.layout.simple_list_item_1, messages);
            ListView syncMessagesListView = requireView().findViewById(R.id.syncMessagesListView);
            syncMessagesListView.setAdapter(adapter);
            adapter.notifyDataSetChanged();
        });
    }

    private void updateCountView(TextView textView, String label, int count) {
        textView.setText(String.format("%s: %d", label, count));
    }

    private void updateStaffRecordsReadyForSync(List<StaffRecord> staffRecords) {
        categoryItems.put(categories.get(0), staffRecords);
        syncCategoryAdapter.notifyDataSetChanged();
    }

    private void updateStaffRecordsMissingInfo(List<StaffRecord> staffRecords) {
        categoryItems.put(categories.get(1), staffRecords);
        syncCategoryAdapter.notifyDataSetChanged();
    }

    private void updateClockHistoryReadyForSync(List<ClockHistory> clockHistories) {
        categoryItems.put(categories.get(2), clockHistories);
        syncCategoryAdapter.notifyDataSetChanged();
    }
}
