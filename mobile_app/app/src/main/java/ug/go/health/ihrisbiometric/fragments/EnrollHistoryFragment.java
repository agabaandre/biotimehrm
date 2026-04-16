package ug.go.health.ihrisbiometric.fragments;

import android.os.Bundle;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.Toast;

import androidx.appcompat.app.AlertDialog;
import androidx.appcompat.widget.SearchView;
import androidx.appcompat.widget.Toolbar;
import androidx.fragment.app.Fragment;
import androidx.lifecycle.ViewModelProvider;
import androidx.navigation.Navigation;
import androidx.recyclerview.widget.LinearLayoutManager;
import androidx.recyclerview.widget.RecyclerView;

import java.util.ArrayList;
import java.util.List;

import ug.go.health.ihrisbiometric.R;
import ug.go.health.ihrisbiometric.adapters.EnrollStaffAdapter;
import ug.go.health.ihrisbiometric.models.StaffRecord;
import ug.go.health.ihrisbiometric.services.DbService;
import ug.go.health.ihrisbiometric.services.SessionService;
import ug.go.health.ihrisbiometric.viewmodels.HomeViewModel;

public class EnrollHistoryFragment extends Fragment {

    private RecyclerView recyclerView;
    private View emptyStateView;
    private EnrollStaffAdapter adapter;
    private final List<StaffRecord> enrollStaffList = new ArrayList<>();
    private final List<StaffRecord> allRecords = new ArrayList<>();
    private DbService dbService;
    private HomeViewModel viewModel;

    @Override
    public View onCreateView(LayoutInflater inflater, ViewGroup container, Bundle savedInstanceState) {
        View view = inflater.inflate(R.layout.fragment_enroll_history, container, false);

        dbService = new DbService(requireContext());
        viewModel = new ViewModelProvider(requireActivity()).get(HomeViewModel.class);

        Toolbar toolbar = view.findViewById(R.id.toolbar_enroll_history);
        toolbar.setTitle("Enrollment History");
        toolbar.setNavigationIcon(R.drawable.baseline_arrow_back_24);
        toolbar.setNavigationOnClickListener(v -> requireActivity().onBackPressed());

        SearchView searchView = view.findViewById(R.id.search_view_enroll);
        searchView.setOnQueryTextListener(new SearchView.OnQueryTextListener() {
            @Override public boolean onQueryTextSubmit(String q) { filter(q); return true; }
            @Override public boolean onQueryTextChange(String q) { filter(q); return true; }
        });

        recyclerView = view.findViewById(R.id.rv_enroll_history);
        emptyStateView = view.findViewById(R.id.empty_state_enroll);

        adapter = new EnrollStaffAdapter(enrollStaffList);
        adapter.setOnItemClickListener(position -> showReEnrollDialog(enrollStaffList.get(position)));

        recyclerView.setLayoutManager(new LinearLayoutManager(requireContext()));
        recyclerView.setAdapter(adapter);

        loadHistory();
        return view;
    }

    private void showReEnrollDialog(StaffRecord record) {
        SessionService session = new SessionService(requireContext());
        String deviceType = session.getDeviceSettings() != null
                ? session.getDeviceSettings().getDeviceType() : "Mobile";
        boolean hasScanner = "Scanner".equals(deviceType);

        // Build options based on device type
        String[] options = hasScanner
                ? new String[]{"Re-enroll Fingerprint", "Re-enroll Face"}
                : new String[]{"Re-enroll Face"};

        new AlertDialog.Builder(requireContext())
                .setTitle(record.getName())
                .setItems(options, (dialog, which) -> {
                    if (hasScanner) {
                        if (which == 0) reEnrollFingerprint(record);
                        else reEnrollFace(record);
                    } else {
                        reEnrollFace(record);
                    }
                })
                .setNegativeButton("Cancel", null)
                .show();
    }

    private void reEnrollFingerprint(StaffRecord record) {
        record.setFingerprintEnrolled(false);
        record.setFingerprintPath(null);
        record.setFingerprintSynced(false);
        record.setTemplateId(0);
        record.setSynced(false);

        dbService.updateStaffRecordAsync(record, success -> {
            if (success) {
                viewModel.setSelectedStaff(record);
                requireActivity().runOnUiThread(() -> {
                    Toast.makeText(requireContext(),
                            "Place " + record.getName() + "'s finger on scanner",
                            Toast.LENGTH_LONG).show();
                    Navigation.findNavController(requireView())
                            .navigate(R.id.action_enrollHistoryFragment_to_enrollUserFragment);
                });
            }
        });
    }

    private void reEnrollFace(StaffRecord record) {
        record.setFaceEnrolled(false);
        record.setFacePath(null);
        record.setFaceImage(null);
        record.setEmbeddingSynced(false);
        record.setSynced(false);

        dbService.updateStaffRecordAsync(record, success -> {
            if (success) {
                viewModel.setSelectedStaff(record);
                requireActivity().runOnUiThread(() ->
                        Navigation.findNavController(requireView())
                                .navigate(R.id.action_enrollHistoryFragment_to_cameraFragment));
            }
        });
    }

    private void loadHistory() {
        dbService.getStaffRecordsAsync(records -> {
            allRecords.clear();
            allRecords.addAll(records);
            filter("");
        });
    }

    private void filter(String query) {
        enrollStaffList.clear();
        for (StaffRecord r : allRecords) {
            if (query.isEmpty() || r.getName().toLowerCase().contains(query.toLowerCase())) {
                enrollStaffList.add(r);
            }
        }
        adapter.notifyDataSetChanged();
        recyclerView.setVisibility(enrollStaffList.isEmpty() ? View.GONE : View.VISIBLE);
        emptyStateView.setVisibility(enrollStaffList.isEmpty() ? View.VISIBLE : View.GONE);
    }

    @Override
    public void onResume() {
        super.onResume();
        loadHistory();
    }
}
