package ug.go.health.ihrisbiometric.fragments;

import android.app.DatePickerDialog;
import android.os.Bundle;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.EditText;

import androidx.appcompat.widget.SearchView;
import androidx.appcompat.widget.Toolbar;
import androidx.fragment.app.Fragment;
import androidx.recyclerview.widget.LinearLayoutManager;
import androidx.recyclerview.widget.RecyclerView;

import java.text.SimpleDateFormat;
import java.util.ArrayList;
import java.util.Calendar;
import java.util.Date;
import java.util.List;
import java.util.Locale;

import ug.go.health.ihrisbiometric.R;
import ug.go.health.ihrisbiometric.adapters.EnrollStaffAdapter;
import ug.go.health.ihrisbiometric.models.StaffRecord;
import ug.go.health.ihrisbiometric.services.DbService;

public class EnrollHistoryFragment extends Fragment {

    private RecyclerView recyclerView;
    private View emptyStateView;
    private EnrollStaffAdapter adapter;
    private List<StaffRecord> enrollStaffList;
    private DbService dbService;
    private SearchView searchView;
    private EditText startDateInput;
    private EditText endDateInput;
    private Date startDate, endDate;
    private SimpleDateFormat dateFormat = new SimpleDateFormat("dd/MM/yyyy", Locale.getDefault());

    @Override
    public View onCreateView(LayoutInflater inflater, ViewGroup container, Bundle savedInstanceState) {
        View view = inflater.inflate(R.layout.fragment_clock_history, container, false);

        Toolbar toolbar = view.findViewById(R.id.toolbar_clock_history);
        toolbar.setTitle("Enrollment History");
        toolbar.setNavigationIcon(R.drawable.baseline_arrow_back_24);
        toolbar.setNavigationOnClickListener(v -> requireActivity().onBackPressed());

        recyclerView = view.findViewById(R.id.recyclerViewClockHistory);
        emptyStateView = view.findViewById(R.id.empty_state);
        searchView = view.findViewById(R.id.search_view);
        startDateInput = view.findViewById(R.id.start_date_input);
        endDateInput = view.findViewById(R.id.end_date_input);

        dbService = new DbService(getContext());
        enrollStaffList = new ArrayList<>();
        adapter = new EnrollStaffAdapter(enrollStaffList);

        recyclerView.setLayoutManager(new LinearLayoutManager(getContext()));
        recyclerView.setAdapter(adapter);

        setupSearchView();
        setupDateInputs();

        loadEnrollStaffHistory();

        return view;
    }

    private void setupSearchView() {
        searchView.setOnQueryTextListener(new SearchView.OnQueryTextListener() {
            @Override
            public boolean onQueryTextSubmit(String query) {
                filterEnrollStaffHistory(query);
                return true;
            }

            @Override
            public boolean onQueryTextChange(String newText) {
                filterEnrollStaffHistory(newText);
                return true;
            }
        });
    }

    private void setupDateInputs() {
        startDateInput.setOnClickListener(v -> showDatePicker(true));
        endDateInput.setOnClickListener(v -> showDatePicker(false));
    }

    private void showDatePicker(final boolean isStartDate) {
        Calendar calendar = Calendar.getInstance();
        DatePickerDialog datePickerDialog = new DatePickerDialog(
                getContext(),
                (view, year, month, dayOfMonth) -> {
                    Calendar selectedCalendar = Calendar.getInstance();
                    selectedCalendar.set(year, month, dayOfMonth);
                    Date selectedDate = selectedCalendar.getTime();

                    if (isStartDate) {
                        startDate = selectedDate;
                        startDateInput.setText(dateFormat.format(startDate));
                    } else {
                        endDate = selectedDate;
                        endDateInput.setText(dateFormat.format(endDate));
                    }

                    filterEnrollStaffHistory(searchView.getQuery().toString());
                },
                calendar.get(Calendar.YEAR),
                calendar.get(Calendar.MONTH),
                calendar.get(Calendar.DAY_OF_MONTH)
        );
        datePickerDialog.show();
    }

    private void loadEnrollStaffHistory() {
        dbService.getStaffRecordsAsync(new DbService.Callback<List<StaffRecord>>() {
            @Override
            public void onResult(List<StaffRecord> result) {
                enrollStaffList.clear();
                enrollStaffList.addAll(result);
                updateUI();
            }
        });
    }

    private void filterEnrollStaffHistory(String query) {
        dbService.getFilteredStaffRecordsAsync(query, startDate, endDate, new DbService.Callback<List<StaffRecord>>() {
            @Override
            public void onResult(List<StaffRecord> result) {
                enrollStaffList.clear();
                enrollStaffList.addAll(result);
                updateUI();
            }
        });
    }

    private void updateUI() {
        adapter.notifyDataSetChanged();
        if (enrollStaffList.isEmpty()) {
            recyclerView.setVisibility(View.GONE);
            emptyStateView.setVisibility(View.VISIBLE);
        } else {
            recyclerView.setVisibility(View.VISIBLE);
            emptyStateView.setVisibility(View.GONE);
        }
    }
}
