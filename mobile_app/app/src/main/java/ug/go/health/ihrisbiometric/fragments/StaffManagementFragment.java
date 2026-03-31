package ug.go.health.ihrisbiometric.fragments;

import android.os.Bundle;
import android.util.Log;
import android.view.LayoutInflater;
import android.view.MenuItem;
import android.view.View;
import android.view.ViewGroup;
import android.widget.Toast;

import androidx.annotation.NonNull;
import androidx.annotation.Nullable;
import androidx.appcompat.widget.SearchView;
import androidx.appcompat.widget.Toolbar;
import androidx.fragment.app.Fragment;
import androidx.navigation.NavController;
import androidx.navigation.Navigation;
import androidx.recyclerview.widget.LinearLayoutManager;
import androidx.recyclerview.widget.RecyclerView;

import com.google.android.material.dialog.MaterialAlertDialogBuilder;
import com.google.android.material.floatingactionbutton.FloatingActionButton;

import java.util.ArrayList;
import java.util.List;

import ug.go.health.ihrisbiometric.R;
import ug.go.health.ihrisbiometric.adapters.StaffManagementAdapter;
import ug.go.health.ihrisbiometric.models.StaffRecord;
import ug.go.health.ihrisbiometric.services.DbService;

public class StaffManagementFragment extends Fragment implements StaffManagementAdapter.OnStaffActionListener {

    private static final String TAG = "StaffManagementFragment";
    private RecyclerView recyclerView;
    private StaffManagementAdapter adapter;
    private List<StaffRecord> staffList = new ArrayList<>();
    private List<StaffRecord> filteredList = new ArrayList<>();
    private View emptyView;
    private NavController navController;
    private DbService dbService;

    @Override
    public void onCreate(@Nullable Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        dbService = new DbService(requireContext());
    }

    @Nullable
    @Override
    public View onCreateView(@NonNull LayoutInflater inflater, @Nullable ViewGroup container, @Nullable Bundle savedInstanceState) {
        View view = inflater.inflate(R.layout.fragment_staff_management, container, false);

        navController = Navigation.findNavController(requireActivity(), R.id.nav_host_fragment);

        Toolbar toolbar = view.findViewById(R.id.staff_mgmt_toolbar);
        toolbar.setNavigationIcon(R.drawable.baseline_arrow_back_24);
        toolbar.setNavigationOnClickListener(v -> navController.popBackStack());
        setupMenu(toolbar);

        recyclerView = view.findViewById(R.id.rv_staff_list);
        emptyView = view.findViewById(R.id.empty_view);
        FloatingActionButton fabAdd = view.findViewById(R.id.fab_add_staff);

        recyclerView.setLayoutManager(new LinearLayoutManager(requireContext()));
        adapter = new StaffManagementAdapter(filteredList, this);
        recyclerView.setAdapter(adapter);

        fabAdd.setOnClickListener(v -> {
            Bundle bundle = new Bundle();
            bundle.putInt("staffId", -1);
            navController.navigate(R.id.action_staffManagementFragment_to_addEditStaffFragment, bundle);
        });

        loadStaff();

        return view;
    }

    private void setupMenu(Toolbar toolbar) {
        toolbar.inflateMenu(R.menu.menu_enroll_user);
        MenuItem searchItem = toolbar.getMenu().findItem(R.id.action_search);
        SearchView searchView = (SearchView) searchItem.getActionView();

        searchView.setOnQueryTextListener(new SearchView.OnQueryTextListener() {
            @Override
            public boolean onQueryTextSubmit(String query) {
                filter(query);
                return true;
            }

            @Override
            public boolean onQueryTextChange(String newText) {
                filter(newText);
                return true;
            }
        });
    }

    private void loadStaff() {
        dbService.getStaffRecordsAsync(records -> {
            staffList.clear();
            staffList.addAll(records);
            filter("");
        });
    }

    private void filter(String query) {
        filteredList.clear();
        for (StaffRecord staff : staffList) {
            if (staff.getName().toLowerCase().contains(query.toLowerCase())) {
                filteredList.add(staff);
            }
        }
        adapter.notifyDataSetChanged();
        if (filteredList.isEmpty()) {
            recyclerView.setVisibility(View.GONE);
            emptyView.setVisibility(View.VISIBLE);
        } else {
            recyclerView.setVisibility(View.VISIBLE);
            emptyView.setVisibility(View.GONE);
        }
    }

    @Override
    public void onEdit(StaffRecord staff) {
        Bundle bundle = new Bundle();
        bundle.putInt("staffId", staff.getId());
        navController.navigate(R.id.action_staffManagementFragment_to_addEditStaffFragment, bundle);
    }

    @Override
    public void onDelete(StaffRecord staff) {
        new MaterialAlertDialogBuilder(requireContext())
                .setTitle("Delete Staff")
                .setMessage("Are you sure you want to delete " + staff.getName() + "?")
                .setPositiveButton("Delete", (dialog, which) -> {
                    dbService.deleteStaffRecordAsync(staff, success -> {
                        if (success) {
                            Toast.makeText(requireContext(), "Staff marked for deletion", Toast.LENGTH_SHORT).show();
                            loadStaff();
                        } else {
                            Toast.makeText(requireContext(), "Failed to delete staff", Toast.LENGTH_SHORT).show();
                        }
                    });
                })
                .setNegativeButton("Cancel", null)
                .show();
    }
}
