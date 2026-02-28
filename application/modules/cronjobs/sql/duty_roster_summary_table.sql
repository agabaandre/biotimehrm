-- Duty Roster Summary Table Creation Script
-- This table stores monthly aggregated duty roster data for reporting and analytics
-- Table name: person_dut_final (as specified in rosta_cron.php)

-- Drop table if exists (use with caution in production)
-- DROP TABLE IF EXISTS person_dut_final;

-- Create the person_dut_final table with exact structure from rosta_cron.php
CREATE TABLE IF NOT EXISTS person_dut_final (
    id INT(11) NOT NULL AUTO_INCREMENT,
    entry_id VARCHAR(100) NOT NULL,
    ihris_pid VARCHAR(100) NOT NULL,
    fullname VARCHAR(100) NOT NULL,
    othername VARCHAR(100) NOT NULL,
    facility_id VARCHAR(100) NOT NULL,
    facility_name VARCHAR(150) NOT NULL,
    schedule_id VARCHAR(20) NOT NULL,
    duty_date VARCHAR(100) NOT NULL,
    job VARCHAR(100) NOT NULL,
    D INT(11) NOT NULL,
    E INT(11) NOT NULL,
    N INT(11) NOT NULL,
    O INT(11) NOT NULL,
    A INT(11) NOT NULL,
    S INT(11) NOT NULL,
    M INT(11) NOT NULL,
    Z INT(11) NOT NULL,
    month_days INT DEFAULT 0,
    last_gen TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY entry_id (entry_id),
    INDEX idx_ihris_pid (ihris_pid),
    INDEX idx_facility_id (facility_id),
    INDEX idx_duty_date (duty_date),
    INDEX idx_schedule_id (schedule_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert sample data for testing (optional)
-- INSERT INTO person_dut_final (entry_id, ihris_pid, fullname, othername, facility_id, facility_name, schedule_id, duty_date, job, D, E, N, O, A, S, M, Z, month_days, last_gen) VALUES
-- ('12345-2025-01', '12345', 'John Doe', 'Middle Name', 'FAC001', 'Sample Hospital', '14', '2025-01', 'Nurse', 20, 5, 2, 3, 0, 0, 0, 0, 31, NOW());

-- Create view for easy reporting (optional)
CREATE OR REPLACE VIEW v_person_dut_final AS
SELECT 
    pdf.*,
    -- Calculate total duties for the month
    (pdf.D + pdf.E + pdf.N + pdf.O + pdf.A + pdf.S + pdf.M + pdf.Z) as total_duties,
    -- Calculate duty percentage
    ROUND(((pdf.D + pdf.E + pdf.N + pdf.O + pdf.A + pdf.S + pdf.M + pdf.Z) / pdf.month_days) * 100, 2) as duty_percentage,
    -- Get schedule name
    CASE pdf.schedule_id
        WHEN '14' THEN 'Day'
        WHEN '15' THEN 'Evening'
        WHEN '16' THEN 'Night'
        WHEN '17' THEN 'Off-duty'
        WHEN '18' THEN 'Annual Leave'
        WHEN '19' THEN 'Study Leave'
        WHEN '20' THEN 'Maternity Leave'
        WHEN '21' THEN 'Other Leave'
        ELSE 'Unknown'
    END as schedule_name
FROM person_dut_final pdf;

-- Show table structure
DESCRIBE person_dut_final;

-- Show indexes
SHOW INDEX FROM person_dut_final;

-- Show view
SHOW CREATE VIEW v_person_dut_final;
