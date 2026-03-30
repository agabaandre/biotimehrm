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
import androidx.navigation.NavController;
import androidx.navigation.Navigation;

import com.google.android.material.textfield.TextInputEditText;

import java.util.ArrayList;
import java.util.Calendar;
import java.util.List;
import java.util.UUID;

import ug.go.health.ihrisbiometric.R;
import ug.go.health.ihrisbiometric.models.FacilityRecord;
import ug.go.health.ihrisbiometric.models.StaffRecord;
import ug.go.health.ihrisbiometric.services.DbService;
import ug.go.health.ihrisbiometric.services.SessionService;

public class AddEditStaffFragment extends Fragment {

    private TextInputEditText etSurname, etFirstname, etOthername, etDob, etJob;
    private AutoCompleteTextView spinnerGender, spinnerDistrict, spinnerFacility, spinnerFacilityType;
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
        etJob = view.findViewById(R.id.et_job);
        spinnerGender = view.findViewById(R.id.spinner_gender);
        spinnerDistrict = view.findViewById(R.id.spinner_district);
        spinnerFacility = view.findViewById(R.id.spinner_facility);
        spinnerFacilityType = view.findViewById(R.id.spinner_facility_type);
        btnSave = view.findViewById(R.id.btn_save_staff);
    }

    private void setupSpinners() {
        String[] genders = {"Male", "Female", "Other"};
        spinnerGender.setAdapter(new ArrayAdapter<>(requireContext(), android.R.layout.simple_dropdown_item_1line, genders));

        String[] districts = {"Kampala", "Wakiso", "Mukono", "Jinija", "Entebbe"}; // Placeholders
        spinnerDistrict.setAdapter(new ArrayAdapter<>(requireContext(), android.R.layout.simple_dropdown_item_1line, districts));

        List<FacilityRecord> facilities = sessionService.getFacilities();
        List<String> facilityNames = new ArrayList<>();
        for (FacilityRecord f : facilities) {
            facilityNames.add(f.getFacility());
        }
        spinnerFacility.setAdapter(new ArrayAdapter<>(requireContext(), android.R.layout.simple_dropdown_item_1line, facilityNames));

        String[] facilityTypes = {"Hospital", "Health Center IV", "Health Center III", "Health Center II", "School"}; // Placeholders
        spinnerFacilityType.setAdapter(new ArrayAdapter<>(requireContext(), android.R.layout.simple_dropdown_item_1line, facilityTypes));
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
                etSurname.setText(staff.getSurname());
                etFirstname.setText(staff.getFirstname());
                etOthername.setText(staff.getOthername());
                spinnerGender.setText(staff.getGender(), false);
                spinnerDistrict.setText(staff.getDistrict(), false);
                spinnerFacility.setText(staff.getFacility(), false);
                spinnerFacilityType.setText(staff.getFacilityType(), false);
                etDob.setText(staff.getDob());
                etJob.setText(staff.getJob());
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
        currentStaff.setJob(etJob.getText().toString());
        currentStaff.setSynced(false);

        if (staffId == -1) {
            currentStaff.setIhrisPid("LOCAL_" + UUID.randomUUID().toString());
            dbService.saveStaffRecordAsync(currentStaff, success -> {
                if (success) {
                    Toast.makeText(requireContext(), "Staff added locally", Toast.LENGTH_SHORT).show();
                    Navigation.findNavController(requireView()).popBackStack();
                } else {
                    Toast.makeText(requireContext(), "Failed to add staff", Toast.LENGTH_SHORT).show();
                }
            });
        } else {
            dbService.updateStaffRecordAsync(currentStaff, success -> {
                if (success) {
                    Toast.makeText(requireContext(), "Staff updated locally", Toast.LENGTH_SHORT).show();
                    Navigation.findNavController(requireView()).popBackStack();
                } else {
                    Toast.makeText(requireContext(), "Failed to update staff", Toast.LENGTH_SHORT).show();
                }
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
