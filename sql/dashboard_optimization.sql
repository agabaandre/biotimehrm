-- Dashboard Performance Optimization SQL
-- Run these queries to improve dashboard loading performance

-- 1. Add indexes for frequently queried columns
CREATE INDEX IF NOT EXISTS idx_ihrisdata_facility_id ON ihrisdata(facility_id);
CREATE INDEX IF NOT EXISTS idx_ihrisdata_ihris_pid ON ihrisdata(ihris_pid);
CREATE INDEX IF NOT EXISTS idx_ihrisdata_department ON ihrisdata(department);
CREATE INDEX IF NOT EXISTS idx_ihrisdata_last_update ON ihrisdata(last_update);

-- 2. Add indexes for actuals table (attendance data)
CREATE INDEX IF NOT EXISTS idx_actuals_facility_date ON actuals(facility_id, date);
CREATE INDEX IF NOT EXISTS idx_actuals_schedule_date ON actuals(schedule_id, date);
CREATE INDEX IF NOT EXISTS idx_actuals_facility_schedule_date ON actuals(facility_id, schedule_id, date);

-- 3. Add indexes for requests table
CREATE INDEX IF NOT EXISTS idx_requests_date ON requests(date);
CREATE INDEX IF NOT EXISTS idx_requests_facility_date ON requests(facility_id, date);

-- 4. Add indexes for person_att_final and person_dut_final
CREATE INDEX IF NOT EXISTS idx_person_att_final_last_gen ON person_att_final(last_gen);
CREATE INDEX IF NOT EXISTS idx_person_dut_final_last_gen ON person_dut_final(last_gen);

-- 5. Add indexes for biotime tables
CREATE INDEX IF NOT EXISTS idx_biotime_data_last_sync ON biotime_data(last_sync);
CREATE INDEX IF NOT EXISTS idx_biotime_data_history_last_sync ON biotime_data_history(last_sync);

-- 6. Add indexes for facilities and jobs tables
CREATE INDEX IF NOT EXISTS idx_facilities_id ON facilities(id);
CREATE INDEX IF NOT EXISTS idx_jobs_id ON jobs(id);

-- 7. Composite index for better performance on combined queries
CREATE INDEX IF NOT EXISTS idx_ihrisdata_facility_department ON ihrisdata(facility_id, department);

-- 8. Analyze tables to update statistics (MySQL)
-- ANALYZE TABLE ihrisdata, actuals, requests, person_att_final, person_dut_final, biotime_data;

-- 9. For PostgreSQL, run:
-- ANALYZE ihrisdata, actuals, requests, person_att_final, person_dut_final, biotime_data;

-- 10. Optional: Partition large tables by date if they contain millions of records
-- Example for actuals table (MySQL):
-- ALTER TABLE actuals PARTITION BY RANGE (YEAR(date)) (
--     PARTITION p2023 VALUES LESS THAN (2024),
--     PARTITION p2024 VALUES LESS THAN (2025),
--     PARTITION p2025 VALUES LESS THAN (2026),
--     PARTITION p_future VALUES LESS THAN MAXVALUE
-- );

-- 11. Check current table sizes and indexes
-- SELECT 
--     table_name,
--     table_rows,
--     data_length,
--     index_length
-- FROM information_schema.tables 
-- WHERE table_schema = DATABASE() 
-- AND table_name IN ('ihrisdata', 'actuals', 'requests', 'person_att_final', 'person_dut_final', 'biotime_data');

-- 12. Check index usage
-- SHOW INDEX FROM ihrisdata;
-- SHOW INDEX FROM actuals;
-- SHOW INDEX FROM requests;
