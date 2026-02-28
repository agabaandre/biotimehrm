-- Performance Indexes for Duty Roster Optimization
-- Run this script to improve query performance for large datasets

-- Index for facility_id in ihrisdata table (most important for filtering)
CREATE INDEX IF NOT EXISTS idx_ihrisdata_facility_id ON ihrisdata(facility_id);

-- Index for surname in ihrisdata table (for ORDER BY operations)
CREATE INDEX IF NOT EXISTS idx_ihrisdata_surname ON ihrisdata(surname);

-- Composite index for duty_rosta table (facility + date filtering)
CREATE INDEX IF NOT EXISTS idx_duty_rosta_facility_date ON duty_rosta(facility_id, duty_date);

-- Index for ihris_pid in duty_rosta table (for JOIN operations)
CREATE INDEX IF NOT EXISTS idx_duty_rosta_ihris_pid ON duty_rosta(ihris_pid);

-- Index for schedule_id in schedules table (for JOIN operations)
CREATE INDEX IF NOT EXISTS idx_schedules_schedule_id ON schedules(schedule_id);

-- Additional indexes for better performance

-- Index for department_id in ihrisdata (if using department filters)
CREATE INDEX IF NOT EXISTS idx_ihrisdata_department_id ON ihrisdata(department_id);

-- Index for division in ihrisdata (if using division filters)
CREATE INDEX IF NOT EXISTS idx_ihrisdata_division ON ihrisdata(division);

-- Index for unit in ihrisdata (if using unit filters)
CREATE INDEX IF NOT EXISTS idx_ihrisdata_unit ON ihrisdata(unit);

-- Composite index for duty_rosta (facility + ihris_pid + date)
CREATE INDEX IF NOT EXISTS idx_duty_rosta_facility_pid_date ON duty_rosta(facility_id, ihris_pid, duty_date);

-- Index for actuals table if using attendance data
CREATE INDEX IF NOT EXISTS idx_actuals_facility_date ON actuals(facility_id, date);

-- Index for actuals ihris_pid
CREATE INDEX IF NOT EXISTS idx_actuals_ihris_pid ON actuals(ihris_pid);

-- Analyze tables after creating indexes
ANALYZE TABLE ihrisdata;
ANALYZE TABLE duty_rosta;
ANALYZE TABLE schedules;
ANALYZE TABLE actuals;

-- Show created indexes
SHOW INDEX FROM ihrisdata;
SHOW INDEX FROM duty_rosta;
SHOW INDEX FROM schedules;
SHOW INDEX FROM actuals;
