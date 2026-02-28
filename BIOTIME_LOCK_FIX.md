# Lock wait timeout in `php index.php biotimejobs fetch_daily_attendance`

## Root cause

The job **fetch_daily_attendance** runs per machine, then **per day**:

1. **Controller `fetch_time_history`** loops over each date from start to end.
2. For each date it:
   - Calls **model `fetch_time_history`**: DELETE from `biotime_data` (terminal+range), fetch from PostgreSQL, INSERT batch, DELETE emp_code=0.
   - Calls **`biotimeClockin($dates)`** which:
     - Runs **`biotimeSyncAttendanceUnified($date)`** inside **one long transaction** (clock-in, clock-out, night correction, actuals).
     - Then **`TRUNCATE TABLE biotime_data`** (full-table exclusive lock).
     - Then **`$this->log($message)`** but **`$message` is never set** in this path (bug).

Lock/timeout causes:

1. **Single long transaction** in `biotimeSyncAttendanceUnified`: one `trans_start()` wraps all four steps (clock-in INSERT, clock-out UPDATE, night UPDATE, actuals INSERT). Locks on `clk_log` and `actuals` are held until `trans_complete()`. If any step is slow or another process touches those tables, you get "Lock wait timeout exceeded".
2. **TRUNCATE TABLE biotime_data** after every day: exclusive lock on the whole table. Any other reader/writer of `biotime_data` will wait and can hit lock wait timeout.
3. **Clock-in never executed**: the clock-in INSERT SQL is built but **never run**, so `$clockinInserted` is undefined (PHP notice) and clock-ins are not inserted.

---

## Fixes to apply in `application/modules/biotimejobs/controllers/Biotimejobs.php`

### 1. Fix `biotimeClockin` (around lines 1321–1336)

**Replace:**

```php
		$this->biotimeSyncAttendanceUnified($date);
       //  $this->db->query("CALL `clockin_users`();");

       //  $message = " Checkin " . $this->db->affected_rows();

       //  $this->biotimeClockoutUnified();
       // // $this->biotimeClockoutnight();
       //  $this->markAttendance();

       // $this->db->query("CALL `biotime_cache`();");

        $this->db->query("TRUNCATE TABLE biotime_data");


        $this->log($message);
```

**With:**

```php
		$message = $this->biotimeSyncAttendanceUnified($date);
       //  $this->db->query("CALL `clockin_users`();");
       //  $this->biotimeClockoutUnified();
       //  $this->markAttendance();
       // $this->db->query("CALL `biotime_cache`();");

        // Delete only this date's data to avoid full-table lock (reduces lock wait timeouts)
        $day_start = $date . ' 00:00:00';
        $day_end   = date('Y-m-d H:i:s', strtotime($date . ' +1 day') - 1);
        $this->db->where('punch_time >=', $day_start);
        $this->db->where('punch_time <=', $day_end);
        $this->db->delete('biotime_data');

        $this->log(is_string($message) ? $message : 'Unified Sync: ' . (int)$message . ' logs');
```

This:

- Uses the return value of `biotimeSyncAttendanceUnified` so `$message` is set and the log is correct.
- Replaces **TRUNCATE** with a **date-scoped DELETE** so only that day’s rows are removed and the rest of the table is not locked.

---

### 2. Fix `biotimeSyncAttendanceUnified`: run clock-in and use 4 short transactions (lines 1352–1477)

**Replace the single transaction and the four steps with:**

- Execute the clock-in INSERT and set `$clockinInserted`.
- Run each of the four steps in its **own** short transaction (trans_start → query → trans_complete), so locks are not held across all four steps.

**Replace from:**

```php
    $this->db->trans_start();

    /*
    |--------------------------------------------------------------------------
    | 1️⃣ INSERT CLOCK-IN
    |--------------------------------------------------------------------------
    */

   $sqlClockIn = "
    INSERT INTO clk_log (entry_id, ihris_pid, date, time_in)
    SELECT 
        CONCAT(DATE(b.punch_time), i.ihris_pid) AS entry_id,
        i.ihris_pid,
        DATE(b.punch_time),
        MIN(b.punch_time)
    FROM biotime_data b
    JOIN ihrisdata i
        ON (b.emp_code = i.card_number OR b.emp_code = i.ipps)
    LEFT JOIN clk_log cl
        ON cl.entry_id = CONCAT(DATE(b.punch_time), i.ihris_pid)
    WHERE b.punch_time >= ?
    AND b.punch_time < DATE_ADD(?, INTERVAL 1 DAY)
    AND cl.entry_id IS NULL
    GROUP BY DATE(b.punch_time), i.ihris_pid
";


    /*
    |--------------------------------------------------------------------------
    | 2️⃣ UPDATE CLOCK-OUT
    |--------------------------------------------------------------------------
    */

    $sqlClockOut = "
        UPDATE clk_log cl
        ...
    ";

    $this->db->query($sqlClockOut, [$startDate, $syncDate]);
    $clockoutUpdated = $this->db->affected_rows();


    /*
    |--------------------------------------------------------------------------
    | 3️⃣ NIGHT SHIFT CORRECTION
    |--------------------------------------------------------------------------
    */

    $sqlNight = "
        UPDATE clk_log cl
        JOIN duty_rosta dr
            ON dr.ihris_pid = cl.ihris_pid
        JOIN biotime_data b
            ...
        WHERE dr.schedule_id = '16'
        AND cl.date = ?
        ...
    ";

    $this->db->query($sqlNight, [$startDate, $syncDate, $startDate]);
    $nightUpdated = $this->db->affected_rows();


    /*
    |--------------------------------------------------------------------------
    | 4️⃣ POPULATE ACTUALS TABLE
    |--------------------------------------------------------------------------
    */
$sqlActuals = "
    ...
";

    $this->db->query($sqlActuals, [$startDate, $syncDate]);
    $actualsUpdated = $this->db->affected_rows();

    $this->db->trans_complete();

    $total = $clockinInserted + $clockoutUpdated + $nightUpdated;
```

**With the following (each step in its own transaction, and clock-in executed):**

```php
    /*
    |--------------------------------------------------------------------------
    | 1️⃣ INSERT CLOCK-IN (short transaction to reduce lock time)
    |--------------------------------------------------------------------------
    */
    $sqlClockIn = "
    INSERT INTO clk_log (entry_id, ihris_pid, date, time_in)
    SELECT
        CONCAT(DATE(b.punch_time), i.ihris_pid) AS entry_id,
        i.ihris_pid,
        DATE(b.punch_time),
        MIN(b.punch_time)
    FROM biotime_data b
    JOIN ihrisdata i
        ON (b.emp_code = i.card_number OR b.emp_code = i.ipps)
    LEFT JOIN clk_log cl
        ON cl.entry_id = CONCAT(DATE(b.punch_time), i.ihris_pid)
    WHERE b.punch_time >= ?
    AND b.punch_time < DATE_ADD(?, INTERVAL 1 DAY)
    AND cl.entry_id IS NULL
    GROUP BY DATE(b.punch_time), i.ihris_pid
    ";
    $this->db->trans_start();
    $this->db->query($sqlClockIn, [$startDate, $syncDate]);
    $clockinInserted = $this->db->affected_rows();
    $this->db->trans_complete();

    /*
    |--------------------------------------------------------------------------
    | 2️⃣ UPDATE CLOCK-OUT (separate short transaction)
    |--------------------------------------------------------------------------
    */
    $sqlClockOut = "
        UPDATE clk_log cl
        JOIN (
            SELECT
                i.ihris_pid,
                DATE(b.punch_time) AS log_date,
                MAX(b.punch_time) AS last_punch
            FROM biotime_data b
            JOIN ihrisdata i
                ON (b.emp_code = i.card_number OR b.emp_code = i.ipps)
            WHERE b.punch_time >= ?
            AND b.punch_time < DATE_ADD(?, INTERVAL 1 DAY)
            GROUP BY i.ihris_pid, DATE(b.punch_time)
        ) punches
        ON punches.ihris_pid = cl.ihris_pid
        AND punches.log_date = cl.date
        SET cl.time_out = punches.last_punch
        WHERE punches.last_punch > cl.time_in
    ";
    $this->db->trans_start();
    $this->db->query($sqlClockOut, [$startDate, $syncDate]);
    $clockoutUpdated = $this->db->affected_rows();
    $this->db->trans_complete();

    /*
    |--------------------------------------------------------------------------
    | 3️⃣ NIGHT SHIFT CORRECTION (separate short transaction)
    |--------------------------------------------------------------------------
    */
    $sqlNight = "
        UPDATE clk_log cl
        JOIN duty_rosta dr
            ON dr.ihris_pid = cl.ihris_pid
            AND dr.duty_date = cl.date
        JOIN biotime_data b
            ON b.punch_time >= ?
            AND b.punch_time < DATE_ADD(?, INTERVAL 1 DAY)
        JOIN ihrisdata i
            ON (b.emp_code = i.card_number OR b.emp_code = i.ipps)
            AND i.ihris_pid = cl.ihris_pid
        SET cl.time_out = b.punch_time
        WHERE dr.schedule_id = '16'
        AND cl.date = ?
        AND b.punch_time > cl.time_in
        AND TIMESTAMPDIFF(HOUR, cl.time_in, b.punch_time) <= 15
    ";
    $this->db->trans_start();
    $this->db->query($sqlNight, [$startDate, $syncDate, $startDate]);
    $nightUpdated = $this->db->affected_rows();
    $this->db->trans_complete();

    /*
    |--------------------------------------------------------------------------
    | 4️⃣ POPULATE ACTUALS TABLE (separate short transaction)
    |--------------------------------------------------------------------------
    */
    $sqlActuals = "
    INSERT INTO actuals (
        entry_id,
        facility_id,
        department_id,
        ihris_pid,
        schedule_id,
        color,
        date,
        end,
        stream
    )
    SELECT DISTINCT
        CONCAT(cl.date, id.ihris_pid) AS entry_id,
        id.facility_id,
        COALESCE(id.department_id, id.department) AS department_id,
        id.ihris_pid,
        s.schedule_id,
        s.color,
        cl.date,
        DATE_ADD(cl.date, INTERVAL 1 DAY) AS end,
        " . ($this->db->field_exists('source', 'clk_log') ? "cl.source" : "NULL") . " AS stream
    FROM ihrisdata id
    JOIN clk_log cl
        ON id.ihris_pid = cl.ihris_pid
    JOIN schedules s
        ON s.schedule_id = 22
    LEFT JOIN actuals a
        ON a.entry_id = CONCAT(cl.date, id.ihris_pid)
    WHERE cl.date BETWEEN ? AND ?
      AND a.entry_id IS NULL
    ";
    $this->db->trans_start();
    $this->db->query($sqlActuals, [$startDate, $syncDate]);
    $actualsUpdated = $this->db->affected_rows();
    $this->db->trans_complete();

    $total = $clockinInserted + $clockoutUpdated + $nightUpdated;
```

Summary of changes in this block:

- **Clock-in**: INSERT is executed; `$clockinInserted` is set.
- **Four short transactions**: each of the four operations runs in its own `trans_start()` / `trans_complete()`, so locks are released after each step and lock wait timeouts are less likely.
- **Night query**: `AND dr.duty_date = cl.date` and `AND i.ihris_pid = cl.ihris_pid` so the update is correctly scoped.
- **Actuals**: `COALESCE(id.department_id, id.department)` for `department_id` and conditional `cl.source` only if `clk_log` has a `source` column.

---

## Optional: increase lock wait timeout for this job

If timeouts persist only for this CLI job, you can raise the session lock wait timeout before the per-day work (e.g. at the start of the day loop in `fetch_time_history`), and restore it after:

```php
$this->db->query("SET SESSION innodb_lock_wait_timeout = 120");
// ... do work ...
$this->db->query("SET SESSION innodb_lock_wait_timeout = 50");
```

Prefer the transaction and TRUNCATE fixes above first; only add this if needed.

---

## Summary

| Issue | Fix |
|-------|-----|
| Long single transaction holding locks on `clk_log` / `actuals` | Split into 4 short transactions (one per step). |
| TRUNCATE locks entire `biotime_data` | Replace with DELETE for the processed date only. |
| Clock-in INSERT never run, `$clockinInserted` undefined | Execute clock-in query and set `$clockinInserted`. |
| `$message` undefined in `biotimeClockin` | Set `$message` from return value of `biotimeSyncAttendanceUnified` and log it. |
| Night shift JOIN ambiguous | Add `dr.duty_date = cl.date` and `i.ihris_pid = cl.ihris_pid`. |

Apply the two code replacements in `Biotimejobs.php` as shown above, then re-run:

`php index.php biotimejobs fetch_daily_attendance`

Lock wait timeouts should reduce or disappear; clock-ins will be written and logging will be correct.
