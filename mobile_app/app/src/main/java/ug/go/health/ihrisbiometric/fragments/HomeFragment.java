package ug.go.health.ihrisbiometric.fragments;

import android.content.Intent;
import android.os.Bundle;
import android.os.Handler;
import android.util.Log;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.AdapterView;
import android.widget.ImageButton;
import android.widget.LinearLayout;
import android.widget.Spinner;
import android.widget.TextView;

import androidx.annotation.NonNull;
import androidx.annotation.Nullable;
import androidx.appcompat.widget.SwitchCompat;
import androidx.fragment.app.Fragment;
import androidx.lifecycle.ViewModel;
import androidx.lifecycle.ViewModelProvider;
import androidx.navigation.NavController;
import androidx.navigation.Navigation;

import com.google.android.material.bottomsheet.BottomSheetBehavior;

import java.text.SimpleDateFormat;
import java.util.ArrayList;
import java.util.Date;
import java.util.List;
import java.util.Locale;

import retrofit2.Call;
import retrofit2.Callback;
import retrofit2.Response;
import ug.go.health.ihrisbiometric.R;
import ug.go.health.ihrisbiometric.activities.LoginActivity;
import ug.go.health.ihrisbiometric.models.FacilityListResponse;
import ug.go.health.ihrisbiometric.models.FacilityRecord;
import ug.go.health.ihrisbiometric.services.ApiInterface;
import ug.go.health.ihrisbiometric.services.ApiService;
import ug.go.health.ihrisbiometric.services.DbService;
import ug.go.health.ihrisbiometric.services.SessionService;
import ug.go.health.ihrisbiometric.utils.DropdownAdapter;
import ug.go.health.ihrisbiometric.viewmodels.HomeViewModel;

public class HomeFragment extends Fragment {

    private static final String TAG = "HomeFragment";
    private TextView tvStatus, tvFacilityName, tvCurrentTime;
    private LinearLayout clockUserBtn;
    private LinearLayout enrollUserBtn;
    private LinearLayout clockHistoryBtn;
    private HomeViewModel viewModel;

    private DbService dbService;
    private SessionService sessionService;
    private ApiInterface apiService;

    private Spinner facilityDropdown;
    private List<String> facilities = new ArrayList<>();
    private DropdownAdapter facilityAdapter;

    private SwitchCompat scanMethodSwitch;
    private String deviceType, scanMethod;

    private ImageButton btnAttendance;

    private LinearLayout switchHolder;


    private BottomSheetBehavior<View> bottomSheetBehavior;
    private View overlay;

    public HomeFragment() {
        // Required empty public constructor
    }

    @Override
    public void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        viewModel = new ViewModelProvider(requireActivity(), new ViewModelProvider.Factory() {
            @NonNull
            @Override
            public <T extends ViewModel> T create(@NonNull Class<T> modelClass) {
                if (modelClass.isAssignableFrom(HomeViewModel.class)) {
                    SessionService sessionService = new SessionService(requireContext());
                    return (T) new HomeViewModel(requireActivity().getApplication(), sessionService.getToken());
                }
                throw new IllegalArgumentException("Unknown ViewModel class");
            }
        }).get(HomeViewModel.class);
    }

    @Override
    public void onViewCreated(@NonNull View view, @Nullable Bundle savedInstanceState) {
        super.onViewCreated(view, savedInstanceState);
        tvStatus = view.findViewById(R.id.status);
        clockUserBtn = view.findViewById(R.id.goto_clock_user);
        enrollUserBtn = view.findViewById(R.id.goto_enroll_user);
        tvFacilityName = view.findViewById(R.id.facility_name);
        facilityDropdown = view.findViewById(R.id.facility_dropdown);
        tvCurrentTime = view.findViewById(R.id.current_time);
        scanMethodSwitch = view.findViewById(R.id.scan_method_switch);
        btnAttendance = view.findViewById(R.id.btn_attendance);
        clockHistoryBtn = view.findViewById(R.id.goto_clock_history);
        switchHolder = view.findViewById(R.id.switch_holder);

        setupClock();
        setupServices();
        setupClickListeners();
        observeViewModel();
        setupFacilityDropdown();
        setupBottomSheet(view);

        scanMethod = sessionService.getDeviceSettings().getScanMethod().toString();
        deviceType = sessionService.getDeviceSettings().getDeviceType();

        setupScanMethodSwitch();
        changeButtonIcons();


    }

    private void setupBottomSheet(View view) {
        View bottomSheet = view.findViewById(R.id.bottom_sheet);
        bottomSheetBehavior = BottomSheetBehavior.from(bottomSheet);
        bottomSheetBehavior.setState(BottomSheetBehavior.STATE_HIDDEN);

        overlay = view.findViewById(R.id.overlay);

        LinearLayout moreOptionsBtn = view.findViewById(R.id.goto_more_options);
        moreOptionsBtn.setOnClickListener(v -> toggleBottomSheet());

        overlay.setOnClickListener(v -> toggleBottomSheet());

        bottomSheetBehavior.addBottomSheetCallback(new BottomSheetBehavior.BottomSheetCallback() {
            @Override
            public void onStateChanged(@NonNull View bottomSheet, int newState) {
                if (newState == BottomSheetBehavior.STATE_HIDDEN) {
                    overlay.setVisibility(View.GONE);
                } else if (newState == BottomSheetBehavior.STATE_EXPANDED) {
                    overlay.setVisibility(View.VISIBLE);
                }
            }

            @Override
            public void onSlide(@NonNull View bottomSheet, float slideOffset) {
                // You can implement a fade effect here if you want
                overlay.setAlpha(slideOffset);
            }
        });

        setupBottomSheetOptions(view);
    }

    private void toggleBottomSheet() {
        if (bottomSheetBehavior.getState() == BottomSheetBehavior.STATE_HIDDEN) {
            bottomSheetBehavior.setState(BottomSheetBehavior.STATE_EXPANDED);
            overlay.setVisibility(View.VISIBLE);
        } else {
            bottomSheetBehavior.setState(BottomSheetBehavior.STATE_HIDDEN);
            overlay.setVisibility(View.GONE);
        }
    }

    private void setupBottomSheetOptions(View view) {
        LinearLayout staffManagementOption = view.findViewById(R.id.staff_management_option);
        staffManagementOption.setOnClickListener(v -> {
            Navigation.findNavController(requireView()).navigate(R.id.action_homeFragment_to_staffManagementFragment);
            bottomSheetBehavior.setState(BottomSheetBehavior.STATE_HIDDEN);
        });
        LinearLayout notificationsOption = view.findViewById(R.id.notifications_option);
        notificationsOption.setOnClickListener(v -> {
            // Handle notifications option click
            Navigation.findNavController(requireView()).navigate(R.id.action_homeFragment_to_notificationsFragment);
            bottomSheetBehavior.setState(BottomSheetBehavior.STATE_HIDDEN);
        });

        LinearLayout syncOption = view.findViewById(R.id.sync_option);
        syncOption.setOnClickListener(v -> {
            navigateToSyncFragment();
            bottomSheetBehavior.setState(BottomSheetBehavior.STATE_HIDDEN);
        });

        LinearLayout deviceSetupOption = view.findViewById(R.id.device_setup_option);
        deviceSetupOption.setOnClickListener(v -> {
            NavController navController = Navigation.findNavController(requireView());
            navController.navigate(R.id.deviceSetupActivity);
            bottomSheetBehavior.setState(BottomSheetBehavior.STATE_HIDDEN);
        });

        LinearLayout outOfStationOption = view.findViewById(R.id.out_of_station_option);
        outOfStationOption.setOnClickListener(v -> {
            NavController navController = Navigation.findNavController(requireView());
            navController.navigate(R.id.outOfStationActivity);
            bottomSheetBehavior.setState(BottomSheetBehavior.STATE_HIDDEN);
        });

        LinearLayout aboutProjectOption = view.findViewById(R.id.about_project_option);
        aboutProjectOption.setOnClickListener(v -> {
            NavController navController = Navigation.findNavController(requireView());
            navController.navigate(R.id.aboutProjectActivity);
            bottomSheetBehavior.setState(BottomSheetBehavior.STATE_HIDDEN);
        });

        LinearLayout enrollHistoryOption = view.findViewById(R.id.enroll_history_option);
        enrollHistoryOption.setOnClickListener(v -> {
            NavController navController = Navigation.findNavController(requireView());
            navController.navigate(R.id.action_homeFragment_to_enrollHistoryFragment);
            bottomSheetBehavior.setState(BottomSheetBehavior.STATE_HIDDEN);
        });

        LinearLayout logoutOption = view.findViewById(R.id.logout_option);
        logoutOption.setOnClickListener(v -> {
            logout();
            bottomSheetBehavior.setState(BottomSheetBehavior.STATE_HIDDEN);
        });
    }

    private void logout() {
        sessionService.logout();
        Intent intent = new Intent(requireActivity(), LoginActivity.class);
        intent.addFlags(Intent.FLAG_ACTIVITY_CLEAR_TASK | Intent.FLAG_ACTIVITY_NEW_TASK);
        startActivity(intent);
        requireActivity().finish();
    }

    private void navigateToSyncFragment() {
        Navigation.findNavController(requireView()).navigate(R.id.action_homeFragment_to_syncFragment);
    }

    private void setupScanMethodSwitch() {
        scanMethodSwitch.setChecked(viewModel.getScanMethod().getValue().equals("face"));
        scanMethodSwitch.setOnCheckedChangeListener((buttonView, isChecked) -> {
            String newMethod = isChecked ? "face" : "fingerprint";
            viewModel.updateScanMethod(newMethod);
            changeButtonIcons();
        });

        if(deviceType.equals("Mobile")) {
            scanMethodSwitch.setChecked(true);
            switchHolder.setVisibility(View.GONE);
        }
    }

    private void changeButtonIcons() {
        String scanMethod = viewModel.getScanMethod().getValue();
        int iconResource = scanMethod.equals("fingerprint") ? R.drawable.fingerprint_icon : R.drawable.face_recognition;
        btnAttendance.setImageResource(iconResource);
    }

    private void setupFacilityDropdown() {
        facilityAdapter = new DropdownAdapter(getContext(), facilities);
        facilityDropdown.setAdapter(facilityAdapter);

        if (sessionService.getFacilities().isEmpty()) {
            fetchFacilitiesFromApi();
        } else {
            populateFacilityDropdown();
        }

        facilityDropdown.setOnItemSelectedListener(new AdapterView.OnItemSelectedListener() {
            @Override
            public void onItemSelected(AdapterView<?> parent, View view, int position, long id) {
                handleFacilitySelection(position);
            }

            @Override
            public void onNothingSelected(AdapterView<?> parent) {}
        });
    }

    private void handleFacilitySelection(int position) {
        FacilityRecord facility = sessionService.getFacilities().get(position);
        tvFacilityName.setText(facility.getFacility());
    }


    private void setupClock() {
        new Handler().postDelayed(new Runnable() {
            @Override
            public void run() {
                updateTime();
                new Handler().postDelayed(this, 1000);
            }
        }, 0);
    }

    private void updateTime() {
        if (getActivity() != null) {
            getActivity().runOnUiThread(() -> {
                SimpleDateFormat dateFormat = new SimpleDateFormat("hh:mm:ss a", Locale.getDefault());
                tvCurrentTime.setText(dateFormat.format(new Date()));
            });
        }
    }

    private void fetchFacilitiesFromApi() {
        apiService.getFacilities().enqueue(new Callback<FacilityListResponse>() {
            @Override
            public void onResponse(@NonNull Call<FacilityListResponse> call, @NonNull Response<FacilityListResponse> response) {
                if (response.isSuccessful() && response.body() != null) {
                    List<FacilityRecord> facilities = new ArrayList<>();
                    FacilityRecord currentFacility = new FacilityRecord();
                    currentFacility.setFacility(sessionService.getCurrentUser().getFacilityName());
                    currentFacility.setFacilityId(sessionService.getCurrentUser().getFacilityId());
                    facilities.add(currentFacility);
                    facilities.addAll(response.body().getFacilities());
                    sessionService.setFacilities(facilities);
                    populateFacilityDropdown();
                }
            }

            @Override
            public void onFailure(@NonNull Call<FacilityListResponse> call, @NonNull Throwable t) {
                Log.e("Facilities", "Error fetching facilities", t);
            }
        });
    }

    private void populateFacilityDropdown() {
        List<FacilityRecord> sessionFacilities = sessionService.getFacilities();
        facilities.clear();
        for (FacilityRecord facility : sessionFacilities) {
            facilities.add(facility.getFacility());
        }
        if (getActivity() != null) {
            getActivity().runOnUiThread(() -> facilityAdapter.notifyDataSetChanged());
        }
    }

    private void setupServices() {
        dbService = new DbService(getContext());
        sessionService = new SessionService(getContext());
        apiService = ApiService.getApiInterface(getContext());
    }

    private void setupClickListeners() {
        clockUserBtn.setOnClickListener(v -> {
            viewModel.setActionType("clock");
        });

        enrollUserBtn.setOnClickListener(v -> {
            viewModel.setActionType("enroll");
        });

        clockHistoryBtn.setOnClickListener(v -> {
            NavController navController = Navigation.findNavController(requireActivity(), R.id.nav_host_fragment);
            navController.navigate(R.id.action_homeFragment_to_clockHistoryFragment);
        });
    }
    private void observeViewModel() {
        viewModel.getStatus().observe(getViewLifecycleOwner(), this::updateStatus);
    }

    @Override
    public View onCreateView(LayoutInflater inflater, ViewGroup container,
                             Bundle savedInstanceState) {
        return inflater.inflate(R.layout.fragment_home, container, false);
    }

    public void updateStatus(String message) {
        tvStatus.setText(message);
    }
}
