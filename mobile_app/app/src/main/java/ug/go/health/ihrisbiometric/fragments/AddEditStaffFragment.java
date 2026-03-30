package ug.go.health.ihrisbiometric.fragments;

import android.app.DatePickerDialog;
import android.os.Bundle;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.ArrayAdapter;
import android.widget.AutoCompleteTextView;
import android.widget.Button;
import android.widget.Toast;

import androidx.annotation.NonNull;
import androidx.annotation.Nullable;
import androidx.appcompat.widget.Toolbar;
import androidx.fragment.app.Fragment;
import androidx.navigation.Navigation;

import com.google.android.material.textfield.TextInputEditText;

import java.util.ArrayList;
import java.util.Calendar;
import java.util.List;
import java.util.UUID;

import ug.go.health.ihrisbiometric.R;
import ug.go.health.ihrisbiometric.models.StaffRecord;
import ug.go.health.ihrisbiometric.services.DbService;
import ug.go.health.ihrisbiometric.services.SessionService;

public class AddEditStaffFragment extends Fragment {

    private TextInputEditText etSurname, etFirstname, etOthername, etDob;
    private AutoCompleteTextView spinnerGender, spinnerDistrict, spinnerFacility, spinnerFacilityType;
    private AutoCompleteTextView spinnerCadre, spinnerJob;
    private Button btnSave;
    private DbService dbService;
    private SessionService sessionService;
    private StaffRecord currentStaff;
    private int staffId;

    @Override
    public void onCreate(@Nullable Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        dbService = new DbService(requireContext());
        sessionService = new SessionService(requireContext());
        if (getArguments() != null) {
            staffId = getArguments().getInt("staffId", -1);
        }
    }

    @Nullable
    @Override
    public View onCreateView(@NonNull LayoutInflater inflater, @Nullable ViewGroup container, @Nullable Bundle savedInstanceState) {
        View view = inflater.inflate(R.layout.fragment_add_edit_staff, container, false);

        initViews(view);
        setupSpinners();
        setupDatePicker();

        if (staffId != -1) {
            loadStaff();
        } else {
            currentStaff = new StaffRecord();
        }

        btnSave.setOnClickListener(v -> saveStaff());

        return view;
    }

    private void initViews(View view) {
        Toolbar toolbar = view.findViewById(R.id.add_edit_staff_toolbar);
        toolbar.setNavigationIcon(R.drawable.baseline_arrow_back_24);
        toolbar.setNavigationOnClickListener(v -> Navigation.findNavController(v).popBackStack());
        toolbar.setTitle(staffId == -1 ? "Add Staff" : "Edit Staff");

        etSurname = view.findViewById(R.id.et_surname);
        etFirstname = view.findViewById(R.id.et_firstname);
        etOthername = view.findViewById(R.id.et_othername);
        etDob = view.findViewById(R.id.et_dob);
        spinnerGender = view.findViewById(R.id.spinner_gender);
        spinnerDistrict = view.findViewById(R.id.spinner_district);
        spinnerFacility = view.findViewById(R.id.spinner_facility);
        spinnerFacilityType = view.findViewById(R.id.spinner_facility_type);
        spinnerCadre = view.findViewById(R.id.spinner_cadre);
        spinnerJob = view.findViewById(R.id.spinner_job);
        btnSave = view.findViewById(R.id.btn_save_staff);
    }

    private void setupSpinners() {
        // Gender - static
        String[] genders = {"Male", "Female", "Other"};
        spinnerGender.setAdapter(new ArrayAdapter<>(requireContext(), android.R.layout.simple_dropdown_item_1line, genders));

        // Districts - from employee_districts via sync
        List<String> districts = sessionService.getDistrictList();
        if (districts.isEmpty()) districts.add("No districts available - please sync first");
        spinnerDistrict.setAdapter(new ArrayAdapter<>(requireContext(), android.R.layout.simple_dropdown_item_1line, districts));

        // Facilities - from employee_facility via sync
        List<String> allFacilities = sessionService.getAllFacilityList();
        if (allFacilities.isEmpty()) allFacilities.add("No facilities available - please sync first");
        spinnerFacility.setAdapter(new ArrayAdapter<>(requireContext(), android.R.layout.simple_dropdown_item_1line, allFacilities));

        // Facility Type - static
        String[] facilityTypes = {"Hospital", "Health Center IV", "Health Center III", "Health Center II", "Clinic", "School"};
        spinnerFacilityType.setAdapter(new ArrayAdapter<>(requireContext(), android.R.layout.simple_dropdown_item_1line, facilityTypes));

        // Cadres - from employee_cadre via sync
        List<String> cadres = sessionService.getCadreList();
        if (cadres.isEmpty()) cadres.add("No cadres available - please sync first");
        spinnerCadre.setAdapter(new ArrayAdapter<>(requireContext(), android.R.layout.simple_dropdown_item_1line, cadres));

        // Jobs - from employee_jobs via sync
        List<String> jobs = sessionService.getJobList();
        if (jobs.isEmpty()) jobs.add("No jobs available - please sync first");
        spinnerJob.setAdapter(new ArrayAdapter<>(requireContext(), android.R.layout.simple_dropdown_item_1line, jobs));
    }


    private void setupDatePicker() {
        etDob.setOnClickListener(v -> {
            Calendar calendar = Calendar.getInstance();
            int year = calendar.get(Calendar.YEAR);
            int month = calendar.get(Calendar.MONTH);
            int day = calendar.get(Calendar.DAY_OF_MONTH);

            DatePickerDialog datePickerDialog = new DatePickerDialog(requireContext(), (view, year1, month1, dayOfMonth) -> {
                String date = dayOfMonth + "/" + (month1 + 1) + "/" + year1;
                etDob.setText(date);
            }, year, month, day);
            datePickerDialog.show();
        });
    }

    private void loadStaff() {
        dbService.getStaffRecordByIdAsync(staffId, staff -> {
            if (staff != null) {
                currentStaff = staff;
                requireActivity().runOnUiThread(() -> {
                    etSurname.setText(staff.getSurname());
                    etFirstname.setText(staff.getFirstname());
                    etOthername.setText(staff.getOthername());
                    spinnerGender.setText(staff.getGender(), false);
                    spinnerDistrict.setText(staff.getDistrict(), false);
                    spinnerFacility.setText(staff.getFacility(), false);
                    spinnerFacilityType.setText(staff.getFacilityType(), false);
                    etDob.setText(staff.getDob());
                    spinnerJob.setText(staff.getJob(), false);
                    spinnerCadre.setText(staff.getCadre(), false);
                });
            }
        });
    }

    private void saveStaff() {
        if (!validateFields()) return;

        currentStaff.setSurname(etSurname.getText().toString());
        currentStaff.setFirstname(etFirstname.getText().toString());
        currentStaff.setOthername(etOthername.getText().toString());
        currentStaff.setGender(spinnerGender.getText().toString());
        currentStaff.setDistrict(spinnerDistrict.getText().toString());
        currentStaff.setFacility(spinnerFacility.getText().toString());
        currentStaff.setFacilityType(spinnerFacilityType.getText().toString());
        currentStaff.setDob(etDob.getText().toString());
        currentStaff.setJob(spinnerJob.getText().toString());
        currentStaff.setCadre(spinnerCadre.getText().toString());
        currentStaff.setSynced(false);

        // Resolve facility_id from the full AllFacilityRecord list (from employee_facility)
        for (ug.go.health.ihrisbiometric.models.AllFacilityRecord f : sessionService.getAllFacilityRecords()) {
            if (f.getFacility().equals(currentStaff.getFacility())) {
                currentStaff.setFacilityId(f.getFacilityId());
                break;
            }
        }

        if (staffId == -1) {
            currentStaff.setIhrisPid("LOCAL_" + UUID.randomUUID().toString());
            dbService.saveStaffRecordAsync(currentStaff, success -> {
                requireActivity().runOnUiThread(() -> {
                    if (success) {
                        Toast.makeText(requireContext(), "Staff added locally", Toast.LENGTH_SHORT).show();
                        Navigation.findNavController(requireView()).popBackStack();
                    } else {
                        Toast.makeText(requireContext(), "Failed to add staff", Toast.LENGTH_SHORT).show();
                    }
                });
            });
        } else {
            dbService.updateStaffRecordAsync(currentStaff, success -> {
                requireActivity().runOnUiThread(() -> {
                    if (success) {
                        Toast.makeText(requireContext(), "Staff updated locally", Toast.LENGTH_SHORT).show();
                        Navigation.findNavController(requireView()).popBackStack();
                    } else {
                        Toast.makeText(requireContext(), "Failed to update staff", Toast.LENGTH_SHORT).show();
                    }
                });
            });
        }
    }

    private boolean validateFields() {
        if (etSurname.getText().toString().isEmpty()) {
            etSurname.setError("Required");
            return false;
        }
        if (etFirstname.getText().toString().isEmpty()) {
            etFirstname.setError("Required");
            return false;
        }
        return true;
    }
}
