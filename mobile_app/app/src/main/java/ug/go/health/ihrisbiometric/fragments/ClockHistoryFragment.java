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
import androidx.recyclerview.widget.RecyclerView;
import androidx.recyclerview.widget.LinearLayoutManager;

import java.text.SimpleDateFormat;
import java.util.ArrayList;
import java.util.Calendar;
import java.util.Date;
import java.util.List;
import java.util.Locale;

import ug.go.health.ihrisbiometric.R;
import ug.go.health.ihrisbiometric.adapters.ClockHistoryAdapter;
import ug.go.health.ihrisbiometric.models.ClockHistory;
import ug.go.health.ihrisbiometric.services.DbService;

public class ClockHistoryFragment extends Fragment {

    private RecyclerView recyclerView;
    private View emptyStateView;
    private ClockHistoryAdapter adapter;
    private List<ClockHistory> clockHistoryList;
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
        toolbar.setTitle("Clock History");
        toolbar.setNavigationIcon(R.drawable.baseline_arrow_back_24);
        toolbar.setNavigationOnClickListener(v -> requireActivity().onBackPressed());

        recyclerView = view.findViewById(R.id.recyclerViewClockHistory);
        emptyStateView = view.findViewById(R.id.empty_state);
        searchView = view.findViewById(R.id.search_view);
        startDateInput = view.findViewById(R.id.start_date_input);
        endDateInput = view.findViewById(R.id.end_date_input);

        dbService = new DbService(getContext());
        clockHistoryList = new ArrayList<>();
        adapter = new ClockHistoryAdapter(clockHistoryList);

        recyclerView.setLayoutManager(new LinearLayoutManager(getContext()));
        recyclerView.setAdapter(adapter);

        setupSearchView();
        setupDateInputs();

        loadClockHistory();

        return view;
    }

    private void setupSearchView() {
        searchView.setOnQueryTextListener(new SearchView.OnQueryTextListener() {
            @Override
            public boolean onQueryTextSubmit(String query) {
                filterClockHistory(query);
                return true;
            }

            @Override
            public boolean onQueryTextChange(String newText) {
                filterClockHistory(newText);
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

                    filterClockHistory(searchView.getQuery().toString());
                },
                calendar.get(Calendar.YEAR),
                calendar.get(Calendar.MONTH),
                calendar.get(Calendar.DAY_OF_MONTH)
        );
        datePickerDialog.show();
    }

    private void loadClockHistory() {
        dbService.getClockHistoryAsync(new DbService.Callback<List<ClockHistory>>() {
            @Override
            public void onResult(List<ClockHistory> result) {
                clockHistoryList.clear();
                clockHistoryList.addAll(result);
                updateUI();
            }
        });
    }

    private void filterClockHistory(String query) {
        dbService.getFilteredClockHistoryAsync(query, startDate, endDate, new DbService.Callback<List<ClockHistory>>() {
            @Override
            public void onResult(List<ClockHistory> result) {
                clockHistoryList.clear();
                clockHistoryList.addAll(result);
                updateUI();
            }
        });
    }

    private void updateUI() {
        adapter.notifyDataSetChanged();
        if (clockHistoryList.isEmpty()) {
            recyclerView.setVisibility(View.GONE);
            emptyStateView.setVisibility(View.VISIBLE);
        } else {
            recyclerView.setVisibility(View.VISIBLE);
            emptyStateView.setVisibility(View.GONE);
        }
    }
}