person_att_final structure
    `entry_id`,
    `ihris_pid`,
    `fullname`,
    `othername`,
    `gender`,
    `facility_id`,
    `facility_name`,
    `district`,
    `institution_type`,
    `facility_type_name`,
    `cadre`,
    `department_id`,
    `region`,
    `schedule_id`,
    `duty_date`,
    `job`,
    `P`,
    `O`,
    `L`,
    `R`,
    `X`,
    `H`,
    `base_line`



  
SET @date_from = '2025-01-01';
SET @date_to   = '2025-12-31';

SELECT
  CONCAT(t.ihris_pid, '-', t.yyyy_mm)                       AS entry_id,
  t.ihris_pid,
  CONCAT(d.surname, ' ', d.firstname, ' ')                  AS fullname,
  COALESCE(d.othername, '')                                 AS othername,
  d.gender,
  t.facility_id,
  COALESCE(t.department_id, d.department_id, '')            AS department_id,

  /* If your ihrisdata column is named facility_name, keep this line;
     if it is named facility, change to d.facility AS facility_name */
  d.facility                                          AS facility_name,

  COALESCE(d.district, '')                                  AS district,

  /* Same note: if your ihrisdata has facility_type_name use this;
     otherwise map/alias accordingly */
  COALESCE(d.facility_type_id, '')                        AS facility_type_name,

  COALESCE(d.cadre, '')                                     AS cadre,

  /* If column is institution_type use it; if it's institutiontype_name, swap the name */
  COALESCE(d.institutiontype_name, '')                          AS institution_type,

  COALESCE(d.region, '')                                    AS region,

  /* Dominant schedule_id for the month by counts (tie-break P > O > L > R > X > H) */
  CASE
    WHEN t.P_ct = GREATEST(t.P_ct, t.O_ct, t.L_ct, t.R_ct, t.X_ct, t.H_ct) THEN 22
    WHEN t.O_ct = GREATEST(t.P_ct, t.O_ct, t.L_ct, t.R_ct, t.X_ct, t.H_ct) THEN 24
    WHEN t.L_ct = GREATEST(t.P_ct, t.O_ct, t.L_ct, t.R_ct, t.X_ct, t.H_ct) THEN 25
    WHEN t.R_ct = GREATEST(t.P_ct, t.O_ct, t.L_ct, t.R_ct, t.X_ct, t.H_ct) THEN 23
    WHEN t.X_ct = GREATEST(t.P_ct, t.O_ct, t.L_ct, t.R_ct, t.X_ct, t.H_ct) THEN 26
    ELSE 27
  END                                                       AS schedule_id,

  t.yyyy_mm                                                 AS duty_date,
  COALESCE(d.job, '')                                       AS job,

  t.P_ct AS P, t.O_ct AS O, t.L_ct AS L, t.R_ct AS R, t.X_ct AS X, t.H_ct AS H,
  t.month_days                                              AS base_line,
  NOW()                                                     AS last_gen
FROM (
  /* Aggregate once per person x facility x department x month */
  SELECT
    a.ihris_pid,
    a.facility_id,
    a.department_id,
    DATE_FORMAT(a.`date`, '%Y-%m')                          AS yyyy_mm,
    DAY(LAST_DAY(a.`date`))                                 AS month_days,
    SUM(a.schedule_id = 22)                                 AS P_ct,  -- Present
    SUM(a.schedule_id = 24)                                 AS O_ct,  -- Off-duty
    SUM(a.schedule_id = 25)                                 AS L_ct,  -- Leave
    SUM(a.schedule_id = 23)                                 AS R_ct,  -- Official Request
    SUM(a.schedule_id = 26)                                 AS X_ct,  -- Absent
    SUM(a.schedule_id = 27)                                 AS H_ct   -- Holiday
  FROM actuals a
  WHERE a.schedule_id IN (22,23,24,25,26,27)
    AND a.`date` BETWEEN @date_from AND @date_to
  GROUP BY
    a.ihris_pid, a.facility_id, a.department_id, DATE_FORMAT(a.`date`, '%Y-%m')
) t
LEFT JOIN ihrisdata d
  ON d.ihris_pid = t.ihris_pid;
