package ug.go.health.ihrisbiometric.fragments;

import android.os.Bundle;
import android.util.Log;
import android.view.LayoutInflater;
import android.view.MenuItem;
import android.view.View;
import android.view.ViewGroup;
import android.widget.RelativeLayout;

import androidx.annotation.NonNull;
import androidx.annotation.Nullable;
import androidx.appcompat.widget.SearchView;
import androidx.appcompat.widget.Toolbar;
import androidx.fragment.app.Fragment;
import androidx.lifecycle.ViewModelProvider;
import androidx.navigation.NavController;
import androidx.navigation.Navigation;
import androidx.recyclerview.widget.LinearLayoutManager;
import androidx.recyclerview.widget.RecyclerView;

import java.util.ArrayList;
import java.util.List;

import ug.go.health.ihrisbiometric.R;
import ug.go.health.ihrisbiometric.adapters.EnrollStaffAdapter;
import ug.go.health.ihrisbiometric.models.StaffRecord;
import ug.go.health.ihrisbiometric.viewmodels.HomeViewModel;

public class EnrollUserFragment extends Fragment implements EnrollStaffAdapter.OnItemClickListener {

    private static final String TAG = "EnrollUserFragment";

    private HomeViewModel viewModel;
    private RecyclerView recyclerView;
    private EnrollStaffAdapter adapter;
    private List<StaffRecord> staffRecordList;
    private List<StaffRecord> filteredStaffRecordList;
    private View emptyView;
    private Toolbar toolbar;
    private NavController navController;
    private String currentScanMethod;

    @Override
    public void onCreate(@Nullable Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        viewModel = new ViewModelProvider(requireActivity()).get(HomeViewModel.class);
    }

    @Nullable
    @Override
    public View onCreateView(@NonNull LayoutInflater inflater, @Nullable ViewGroup container, @Nullable Bundle savedInstanceState) {
        View view = inflater.inflate(R.layout.fragment_enroll_user, container, false);

        setupToolbar(view);
        setupRecyclerView(view);
        setupEmptyView(view, inflater);
        observeViewModel();

        navController = Navigation.findNavController(requireActivity(), R.id.nav_host_fragment);

        // Observe the scan method
        viewModel.getScanMethod().observe(getViewLifecycleOwner(), this::onScanMethodChanged);

        return view;
    }

    private void onScanMethodChanged(String scanMethod) {
        currentScanMethod = scanMethod;
        updateToolbarTitle();
        filterStaffList("");
    }

    private void updateToolbarTitle() {
        String title = "Enroll Staff - " + (currentScanMethod.equals("face") ? "Face" : "Fingerprint");
        toolbar.setTitle(title);
    }

    private void setupToolbar(View view) {
        toolbar = view.findViewById(R.id.enroll_staff_toolbar);
        toolbar.setNavigationIcon(R.drawable.baseline_arrow_back_24);
        toolbar.setNavigationOnClickListener(v -> requireActivity().onBackPressed());

        toolbar.inflateMenu(R.menu.menu_enroll_user);
        MenuItem searchItem = toolbar.getMenu().findItem(R.id.action_search);
        SearchView searchView = (SearchView) searchItem.getActionView();

        searchView.setOnQueryTextListener(new SearchView.OnQueryTextListener() {
            @Override
            public boolean onQueryTextSubmit(String query) {
                filterStaffList(query);
                return true;
            }

            @Override
            public boolean onQueryTextChange(String newText) {
                filterStaffList(newText);
                return true;
            }
        });
    }

    private void setupRecyclerView(View view) {
        recyclerView = view.findViewById(R.id.rv_enroll_staff_list);
        staffRecordList = new ArrayList<>();
        filteredStaffRecordList = new ArrayList<>();
        adapter = new EnrollStaffAdapter(filteredStaffRecordList);
        adapter.setOnItemClickListener(this);
        recyclerView.setAdapter(adapter);
        recyclerView.setLayoutManager(new LinearLayoutManager(requireContext()));
    }

    private void setupEmptyView(View view, LayoutInflater inflater) {
        RelativeLayout.LayoutParams layoutParams = new RelativeLayout.LayoutParams(
                RelativeLayout.LayoutParams.MATCH_PARENT,
                RelativeLayout.LayoutParams.MATCH_PARENT
        );
        emptyView = inflater.inflate(R.layout.layout_empty_view, null);
        layoutParams.addRule(RelativeLayout.BELOW, R.id.enroll_staff_toolbar);
        ((RelativeLayout) view.findViewById(R.id.fragment_enroll_user)).addView(emptyView, layoutParams);
    }

    private void observeViewModel() {
        viewModel.getStaffRecords().observe(getViewLifecycleOwner(), this::updateStaffList);
    }

    private void updateStaffList(List<StaffRecord> staffRecords) {
        staffRecordList.clear();
        staffRecordList.addAll(staffRecords);
        filterStaffList("");
    }

    private void filterStaffList(String query) {
        filteredStaffRecordList.clear();
        for (StaffRecord staff : staffRecordList) {
            if (staff.getName().toLowerCase().contains(query.toLowerCase()) &&
                    shouldIncludeStaff(staff)) {
                filteredStaffRecordList.add(staff);
            }
        }
        adapter.notifyDataSetChanged();
        updateViewVisibility();
    }

    private boolean shouldIncludeStaff(StaffRecord staff) {
        if ("face".equals(currentScanMethod)) {
            return !staff.isFaceEnrolled();
        } else if ("fingerprint".equals(currentScanMethod)) {
            return !staff.isFingerprintEnrolled();
        }
        return true; // Include all staff if scan method is unknown
    }

    private void updateViewVisibility() {
        if (filteredStaffRecordList.isEmpty()) {
            recyclerView.setVisibility(View.GONE);
            emptyView.setVisibility(View.VISIBLE);
        } else {
            recyclerView.setVisibility(View.VISIBLE);
            emptyView.setVisibility(View.GONE);
        }
    }

    @Override
    public void onItemClick(int position) {
        if (position >= 0 && position < filteredStaffRecordList.size()) {
            StaffRecord selectedStaff = filteredStaffRecordList.get(position);
            viewModel.setSelectedStaff(selectedStaff);

            if ("face".equals(currentScanMethod)) {
                navController.navigate(R.id.action_enrollUserFragment_to_cameraFragment);
            } else if ("fingerprint".equals(currentScanMethod)) {
                navController.popBackStack();
            }
        } else {
            Log.e(TAG, "Invalid position received: " + position);
        }
    }

    @Override
    public void onResume() {
        super.onResume();
        viewModel.refreshStaffRecords();
    }
}