<?php 
$date_from = '2025-01-01';
$date_to = '2025-12-31';

SELECT
  CONCAT(t.ihris_pid, '-', t.yyyy_mm)       AS entry_id,
  t.ihris_pid,
  CONCAT(d.surname, ' ', d.firstname, ' ')  AS fullname,
  COALESCE(d.othername, '')                 AS othername,
  t.facility_id,
  d.facility                                AS facility_name,
  /* Dominant roster code for the month (tie-break D > E > N > O > A > S > M > Z) */
  CASE
    WHEN t.D_ct = GREATEST(t.D_ct,t.E_ct,t.N_ct,t.O_ct,t.A_ct,t.S_ct,t.M_ct,t.Z_ct) THEN 14
    WHEN t.E_ct = GREATEST(t.D_ct,t.E_ct,t.N_ct,t.O_ct,t.A_ct,t.S_ct,t.M_ct,t.Z_ct) THEN 15
    WHEN t.N_ct = GREATEST(t.D_ct,t.E_ct,t.N_ct,t.O_ct,t.A_ct,t.S_ct,t.M_ct,t.Z_ct) THEN 16
    WHEN t.O_ct = GREATEST(t.D_ct,t.E_ct,t.N_ct,t.O_ct,t.A_ct,t.S_ct,t.M_ct,t.Z_ct) THEN 17
    WHEN t.A_ct = GREATEST(t.D_ct,t.E_ct,t.N_ct,t.O_ct,t.A_ct,t.S_ct,t.M_ct,t.Z_ct) THEN 18
    WHEN t.S_ct = GREATEST(t.D_ct,t.E_ct,t.N_ct,t.O_ct,t.A_ct,t.S_ct,t.M_ct,t.Z_ct) THEN 19
    WHEN t.M_ct = GREATEST(t.D_ct,t.E_ct,t.N_ct,t.O_ct,t.A_ct,t.S_ct,t.M_ct,t.Z_ct) THEN 20
    ELSE 21
  END                                        AS schedule_id,
  t.yyyy_mm                                  AS duty_date,   -- YYYY-MM
  COALESCE(d.job, '')                        AS job,
  /* Monthly counts by roster letter */
  t.D_ct AS D, t.E_ct AS E, t.N_ct AS N, t.O_ct AS O,
  t.A_ct AS A, t.S_ct AS S, t.M_ct AS M, t.Z_ct AS Z,
  NOW()                                      AS last_gen
FROM (
  /* Aggregate once per person x facility x month for speed */
  SELECT
    r.ihris_pid,
    r.facility_id,
    DATE_FORMAT(r.duty_date, '%Y-%m') AS yyyy_mm,
    SUM(r.schedule_id = 14) AS D_ct,  -- Day
    SUM(r.schedule_id = 15) AS E_ct,  -- Evening
    SUM(r.schedule_id = 16) AS N_ct,  -- Night
    SUM(r.schedule_id = 17) AS O_ct,  -- Off-duty
    SUM(r.schedule_id = 18) AS A_ct,  -- Annual leave
    SUM(r.schedule_id = 19) AS S_ct,  -- Study leave
    SUM(r.schedule_id = 20) AS M_ct,  -- Maternity leave
    SUM(r.schedule_id = 21) AS Z_ct   -- Other authorised leave
  FROM duty_rosta r
  WHERE r.schedule_id IN (14,15,16,17,18,19,20,21)
    AND r.duty_date BETWEEN @date_from AND @date_to
  GROUP BY r.ihris_pid, r.facility_id, DATE_FORMAT(r.duty_date, '%Y-%m')
) t
LEFT JOIN ihrisdata d
  ON d.ihris_pid = t.ihris_pid;
