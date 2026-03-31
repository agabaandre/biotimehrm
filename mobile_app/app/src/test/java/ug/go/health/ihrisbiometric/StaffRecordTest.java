package ug.go.health.ihrisbiometric;

import org.junit.Test;
import static org.junit.Assert.*;
import ug.go.health.ihrisbiometric.models.StaffRecord;

public class StaffRecordTest {
    @Test
    public void testStaffRecordFields() {
        StaffRecord staff = new StaffRecord();
        staff.setFirstname("John");
        staff.setSurname("Doe");
        staff.setGender("Male");
        staff.setDistrict("Kampala");
        staff.setFacilityType("Hospital");
        staff.setDob("01/01/1990");
        staff.setJob("Doctor");

        assertEquals("John", staff.getFirstname());
        assertEquals("Doe", staff.getSurname());
        assertEquals("Male", staff.getGender());
        assertEquals("Kampala", staff.getDistrict());
        assertEquals("Hospital", staff.getFacilityType());
        assertEquals("01/01/1990", staff.getDob());
        assertEquals("Doctor", staff.getJob());
    }

    @Test
    public void testGetName() {
        StaffRecord staff = new StaffRecord();
        staff.setFirstname("john");
        staff.setOthername("middle");
        staff.setSurname("doe");

        assertEquals("John Middle Doe", staff.getName());
    }
}
