<?php
use function GuzzleHttp\json_encode;
date_default_timezone_set('Africa/Kampala');
defined('BASEPATH') or exit('No direct script access allowed');

use utils\HttpUtils;

class Biotimejobs extends MX_Controller
{

    private $username;
    private $password;

    private $facility;
    //private $biotimejobs_mdl;

    public function __construct()
    {
        parent::__construct();

        $this->username = Modules::run('svariables/getSettings')->biotime_username;
        $this->password = Modules::run('svariables/getSettings')->biotime_password;
        $this->load->model('biotimejobs_mdl');
        // $this->facility = $_SESSION['facility'];
    }

    public function index()
    {
        echo "BIO-TIME HERE";
    }


    public function get_token($uri = FALSE)
    {
        if (empty($this->username) || $this->password === null || $this->password === '') {
            log_message('error', 'get_token: biotime_username/password missing from settings');
            return null;
        }

        $http = new HttpUtils();
        $headers = ['Content-Type' => 'application/json'];
        $body = [
            'username' => $this->username,
            'password' => $this->password,
        ];
        $response = $http->sendRequest('jwt-api-token-auth', 'POST', $headers, $body, $search = FALSE);
        if (!is_object($response) || empty($response->token)) {
            $detail = is_object($response) && isset($response->detail) ? $response->detail : json_encode($response);
            log_message('error', 'get_token: failed to obtain JWT — ' . $detail);
            return null;
        }
        return $response->token;
    }

    /**
     * @return array<int, string>
     */
    protected function _biotime_json_headers($token, $bodyJson = null)
    {
        $headers = [
            'Content-type: application/json',
            'Accept: application/json',
            'Authorization: JWT ' . $token,
        ];
        if ($bodyJson !== null) {
            array_unshift($headers, 'Content-length:' . strlen($bodyJson));
        }
        return $headers;
    }

    /**
     * True when BioTime create/update returned an employee object (has id or emp_code).
     *
     * @param mixed $response
     * @return bool
     */
    protected function _biotime_response_ok($response)
    {
        if (is_string($response)) {
            if (strpos($response, 'CURL Error') === 0) {
                return false;
            }
            // BioTime sometimes returns HTML error pages with HTTP 200
            if (stripos($response, '<html') !== false || stripos($response, '500 Error') !== false) {
                return false;
            }
            return false;
        }
        if (!is_object($response)) {
            return false;
        }
        if (isset($response->detail) && is_string($response->detail)
            && stripos($response->detail, 'success') !== false) {
            return true;
        }
        if (isset($response->detail) && !isset($response->id) && !isset($response->emp_code)) {
            // Auth/permission/validation error object
            return false;
        }
        if (isset($response->id) || isset($response->emp_code)) {
            return true;
        }
        // Some endpoints return {code:0, msg:"", data:...}
        if (isset($response->code) && (int) $response->code === 0) {
            return true;
        }
        return false;
    }

    //get terminals
    public function terminals()
    {
        try {
        $http = new HttpUtils();
        $headr = array();
        $headr[] = 'Content-length: 0';
        $headr[] = 'Content-type: application/json';
        $headr[] = 'Authorization: JWT ' . $this->get_token();

        $query = array(
            'page_size' => 5000000
        );

        $params = '?' . http_build_query($query);
        $endpoint = 'iclock/api/terminals/' . $params;

        $response = $http->curlgetHttp($endpoint, $headr, []);
            
            // Validate response
            if (!$response || !is_object($response)) {
                throw new Exception("Invalid API response for terminals");
            }
            
            // Check if data exists and is an array
            if (!isset($response->data) || !is_array($response->data)) {
                throw new Exception("No terminal data in API response");
            }
            
        $insert1 = array();
        foreach ($response->data as $terminal) {
                // Validate terminal object
                if (!is_object($terminal) || !isset($terminal->sn)) {
                    continue; // Skip invalid terminals
                }

            $insert = array(
                    'sn' => isset($terminal->sn) ? $terminal->sn : '',
                    'ip_address' => isset($terminal->ip_address) ? $terminal->ip_address : '',
                    'area_code' => isset($terminal->area) && isset($terminal->area->area_code) ? $terminal->area->area_code : '',
                    'user_count' => isset($terminal->user_count) ? $terminal->user_count : 0,
                    'face_count' => isset($terminal->face_count) ? $terminal->face_count : 0,
                    'palm_count' => isset($terminal->palm_count) ? $terminal->palm_count : 0,
                    'area_name' => isset($terminal->area_name) ? $terminal->area_name : '',
                    'last_activity' => isset($terminal->last_activity) ? $terminal->last_activity : NULL
            );
            array_push($insert1, $insert);
        }
            
        $message = $this->biotimejobs_mdl->addMachines($insert1);
        $this->log($message);
            
        $process = 1;
        $method = "bioitimejobs/terminals";
            
            // Check if we have data to insert (count the array, not the response object)
            if (count($insert1) > 0) {
            $status = "successful";
        } else {
            $status = "failed";
        }
            
        $this->cronjob_register($process, $method, $status);

            return $response;
            
        } catch (Exception $e) {
            $this->log("terminals() exception: " . $e->getMessage());
            $process = 1;
            $method = "bioitimejobs/terminals";
            $this->cronjob_register($process, $method, "failed");
            return FALSE;
        } catch (Error $e) {
            $this->log("terminals() fatal error: " . $e->getMessage());
            $process = 1;
            $method = "bioitimejobs/terminals";
            $this->cronjob_register($process, $method, "failed");
            return FALSE;
        }
    }
    //cron job
    //Fetches ihris stafflsit via the api
    // public function get_ihrisdata()
    // {
    //     $http = new HttpUtils();
    //     $headers = [
    //         'Content-Type' => 'application/json',
    //         'Accept' => 'application/json',
    //     ];

    //     $response = $http->sendiHRISRequest('apiv1/index.php/api/ihrisdata', "GET", $headers, []);

    //     if ($response) {
    //         //dd(count($response));
    //         //$message = $this->biotimejobs_mdl->add_ihrisdata($response);
    //         $this->db->query("TRUNCATE table ihrisdata");
    //         foreach($response as $data){

    //             $message = $this->db->replace('ihrisdata', $data);
    //             ///dd($this->last->query);
    //         }
           
    //         $this->log($message);
    //     }
    //     $process = 2;
    //     $method = "bioitimejobs/get_ihrisdata";
    //     if (count($response) > 0) {
    //         $status = "successful";
    //     } else {
    //         $status = "failed";
    //     }
    //     $this->cronjob_register($process, $method, $status);
    //     $this->get_ucmbdata();
    //     $this->update_ipps();
    // }
    //employees all enrolled users before creating new ones.
	public function get_ihrisdata_old()
{
    $http = new HttpUtils();
    $headers = [
        'Content-Type' => 'application/json',
        'Accept' => 'application/json',
    ];

    $response = $http->sendiHRISRequest('apiv1/index.php/api/ihrisdata', "GET", $headers, []);

    if ($response) {
        // Optional: You can truncate if you want to replace all data every time.
        // $this->db->query("TRUNCATE table ihrisdata");

        $inserted = 0;
        $errors = [];

        foreach ($response as $data) {
            try {
                // Use REPLACE INTO or INSERT ... ON DUPLICATE KEY UPDATE
                $this->db->replace('ihrisdata', $data); // Assumes primary key/unique key exists in table
                $inserted++;
            } catch (Exception $e) {
                // Continue on error, but log it
                $errors[] = $e->getMessage();
                continue;
            }
        }

        $this->log("Inserted: $inserted. Errors: " . count($errors));
        if (!empty($errors)) {
            foreach ($errors as $error) {
                $this->log("Insert error: $error");
            }
        }
    }

    $process = 2;
    $method = "bioitimejobs/get_ihrisdata";
    $status = (count($response) > 0) ? "successful" : "failed";
    $this->cronjob_register($process, $method, $status);

    $this->get_ucmbdata();
    $this->update_ipps();
}


/**
 * Draw a simple text progress bar.
 * @param int $current Processed count
 * @param int $total Total count (0 = unknown)
 * @return string e.g. [████████--------] 50%
 */
private function _draw_progress_bar($current, $total)
{
    $total = (int) $total;
    if ($total <= 0) {
        return '[--------] ?%';
    }
    $pct = min(100, (int) (($current / $total) * 100));
    $barLen = 40;
    $filled = (int) (($pct / 100) * $barLen);
    $bar = str_repeat('█', $filled) . str_repeat('-', $barLen - $filled);
    return "[$bar] {$pct}%";
}

/**
 * Fetch iHRIS data (paginated API), upsert into ihrisdata (no truncate). Then merge UCMB data (non-paginated).
 * - Update existing by ihris_pid, insert new. status=1 and is_active_employee=1 for all synced rows.
 * - After sync: set status=0 where surname LIKE 'delete%' OR firstname LIKE 'delete%'.
 * Requires ihrisdata.status and ihrisdata.is_active_employee (add if missing).
 */
public function get_ihrisdata($page = 1, $batch_size = 100)
{
    $base_url = "https://hris.health.go.ug/apiv1/index.php/api/ihrisdatapaginated/92cfdef7-8f2c-433e-ba62-49fa7a243974";
    $per_page = 200;
    $total_pages = 0;
    $total_records = 0;
    $total_upserted = 0;
    $current_page = $page;
    $batch_data = array();
    $start_time = microtime(true);
    $is_cli = (php_sapi_name() === 'cli');
    $has_status = $this->db->field_exists('status', 'ihrisdata');
    $has_is_active = $this->db->field_exists('is_active_employee', 'ihrisdata');
    $upsert_cols = $this->_get_ihrisdata_upsert_columns($has_status, $has_is_active);

    echo $is_cli ? "\n" : "";
    echo "Fetching iHRIS data (upsert: update existing, insert new)...\n";
    if (!$is_cli) {
        echo "<pre>";
    }
    flush();

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    curl_setopt($ch, CURLOPT_TIMEOUT, 60);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);

    do {
        $url = $base_url . "?page=" . $current_page . "&per_page=" . $per_page;
        curl_setopt($ch, CURLOPT_URL, $url);
        $result = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($http_code !== 200 || $result === false) {
            $error = curl_error($ch);
            log_message('error', "get_ihrisdata: page $current_page HTTP $http_code - $error");
            sleep(2);
            continue;
        }

        $response = json_decode($result, true);
        if (!isset($response['status']) || $response['status'] !== 'SUCCESS') {
            log_message('error', "get_ihrisdata: API error page $current_page - " . json_encode($response));
            break;
        }

        if ($total_pages == 0 && isset($response['pagination'])) {
            $total_pages = $response['pagination']['total_pages'];
            $total_records = $response['pagination']['total_records'];
            echo "Total records: " . number_format($total_records) . " | Pages: $total_pages | Page size: $per_page\n";
            if (!$is_cli) {
                echo "<br>";
            }
            flush();
        }

        $records_fetched = 0;
        if (isset($response['data']) && is_array($response['data'])) {
            $records_fetched = count($response['data']);
            foreach ($response['data'] as $record) {
                $row = $this->_map_ihris_api_record_to_row($record);
                if ($has_status) {
                    $row['status'] = 1;
                }
                if ($has_is_active) {
                    $row['is_active_employee'] = 1;
                }
                $batch_data[] = $row;

                if (count($batch_data) >= $batch_size) {
                    $total_upserted += $this->_upsert_ihrisdata_batch($batch_data, $upsert_cols);
                    $batch_data = array();
                }
            }
        }

        $has_next = isset($response['pagination']['has_next_page']) ? $response['pagination']['has_next_page'] : false;
        $current_page++;

        $processed = $total_upserted;
        $progress_bar = $this->_draw_progress_bar($processed, $total_records);
        $elapsed = microtime(true) - $start_time;
        $rate = $processed > 0 ? $processed / $elapsed : 0;
        $remaining = max(0, $total_records - $processed);
        $eta_seconds = $rate > 0 ? round($remaining / $rate) : 0;
        $eta_formatted = $eta_seconds > 0 ? sprintf("%dm %ds", floor($eta_seconds / 60), $eta_seconds % 60) : "calculating...";

        if ($is_cli) {
            printf("\rProgress: %s | Upserted: %s | ETA: %s   ", $progress_bar, number_format($total_upserted), $eta_formatted);
        } else {
            echo "<div style='font-family: monospace;'>Progress: $progress_bar | Upserted: " . number_format($total_upserted) . " | ETA: $eta_formatted</div>";
            flush();
        }

        if ($records_fetched == 0) {
            break;
        }
        usleep(100000);
    } while ($has_next && ($total_pages == 0 || $current_page <= $total_pages));

    if (!empty($batch_data)) {
        $total_upserted += $this->_upsert_ihrisdata_batch($batch_data, $upsert_cols);
    }

    curl_close($ch);

    // Mark status=0 for records where surname or firstname starts with 'delete'
    if ($has_status) {
        $this->db->query("UPDATE ihrisdata SET status = 0 WHERE (TRIM(COALESCE(surname,'')) LIKE 'delete%' OR TRIM(COALESCE(firstname,'')) LIKE 'delete%')");
        $marked = $this->db->affected_rows();
        if ($is_cli) {
            echo "\n  Marked status=0 (delete*): " . $marked . " record(s)\n";
        } else {
            echo "<div>Marked status=0 (delete*): $marked record(s)</div>";
        }
    }

    $elapsed_total = round(microtime(true) - $start_time, 2);
    $final_progress = $this->_draw_progress_bar($total_upserted, $total_records);

    if ($is_cli) {
        echo "\n\n═══════════════════════════════════════════════════════════\n";
        echo "  iHRIS paginated: COMPLETED | Upserted: " . number_format($total_upserted) . " | $final_progress | " . $elapsed_total . "s\n";
        echo "═══════════════════════════════════════════════════════════\n";
    } else {
        echo "<br><div style='font-family: monospace; padding: 10px; background: #f0f0f0; border: 1px solid #ccc;'>";
        echo "<strong>iHRIS paginated:</strong> COMPLETED | Upserted: " . number_format($total_upserted) . " | $final_progress | " . $elapsed_total . "s";
        echo "</div>";
    }
    flush();

    // Merge UCMB data (not paginated)
    $ucmb_merged = $this->_merge_ucmbdata($is_cli, $has_status, $has_is_active);
    if ($is_cli) {
        echo "  UCMB merged: " . $ucmb_merged . " record(s)\n";
    } else {
        echo "<div>UCMB merged: $ucmb_merged record(s)</div></pre>";
    }

    $this->log("get_ihrisdata: upserted " . $total_upserted . ", UCMB merged " . $ucmb_merged);
    $this->cronjob_register(2, "biotimejobs/get_ihrisdata", ($total_upserted > 0 || $ucmb_merged > 0) ? "successful" : "failed");
    return $total_upserted + $ucmb_merged;
}

/**
 * Map API payload (ihrisdatapaginated / UCMB) to ihrisdata row. All API fields represented.
 * API: ihris_pid, district_id, district, dhis_facility_id, dhis_district_id, nin, card_number, ipps,
 * facility_type_id, facility_id, facility, department_id, department, job_id, job, employment_terms,
 * surname, firstname, othername, mobile, telephone, institutiontype_name, institution_type_id,
 * last_update, gender, birth_date, cadre, email, region.
 */
private function _map_ihris_api_record_to_row($record)
{
    $rec = is_array($record) ? $record : (array) $record;
    return array(
        'ihris_pid'          => isset($rec['ihris_pid']) ? $rec['ihris_pid'] : null,
        'district_id'        => isset($rec['district_id']) ? $rec['district_id'] : null,
        'district'           => isset($rec['district']) ? $rec['district'] : null,
        'dhis_facility_id'   => isset($rec['dhis_facility_id']) ? $rec['dhis_facility_id'] : null,
        'dhis_district_id'   => isset($rec['dhis_district_id']) ? $rec['dhis_district_id'] : null,
        'nin'                => isset($rec['nin']) ? $rec['nin'] : null,
        'card_number'        => isset($rec['card_number']) ? $rec['card_number'] : null,
        'ipps'               => isset($rec['ipps']) ? $rec['ipps'] : null,
        'facility_type_id'   => isset($rec['facility_type_id']) ? $rec['facility_type_id'] : null,
        'facility_id'        => isset($rec['facility_id']) ? $rec['facility_id'] : null,
        'facility'           => isset($rec['facility']) ? $rec['facility'] : null,
        'department_id'      => isset($rec['department_id']) ? $rec['department_id'] : null,
        'department'         => isset($rec['department']) ? $rec['department'] : null,
        'job_id'             => isset($rec['job_id']) ? $rec['job_id'] : null,
        'job'                => isset($rec['job']) ? $rec['job'] : null,
        'employment_terms'   => isset($rec['employment_terms']) ? $rec['employment_terms'] : null,
        'surname'            => isset($rec['surname']) ? $rec['surname'] : null,
        'firstname'          => isset($rec['firstname']) ? $rec['firstname'] : null,
        'othername'          => isset($rec['othername']) ? $rec['othername'] : null,
        'mobile'             => isset($rec['mobile']) ? $rec['mobile'] : null,
        'telephone'          => isset($rec['telephone']) ? $rec['telephone'] : null,
        'institution_type'   => isset($rec['institutiontype_name']) ? $rec['institutiontype_name'] : (isset($rec['institution_type']) ? $rec['institution_type'] : null),
        'institution_type_id'=> isset($rec['institution_type_id']) ? $rec['institution_type_id'] : null,
        'last_gen'           => isset($rec['last_update']) ? $rec['last_update'] : (isset($rec['last_gen']) ? $rec['last_gen'] : null),
        'gender'             => isset($rec['gender']) ? $rec['gender'] : null,
        'birth_date'         => isset($rec['birth_date']) ? $rec['birth_date'] : null,
        'cadre'              => isset($rec['cadre']) ? $rec['cadre'] : null,
        'email'              => isset($rec['email']) ? $rec['email'] : null,
        'region'             => isset($rec['region']) ? $rec['region'] : null,
    );
}

/**
 * Return list of ihrisdata columns that exist in DB (for upsert). Only includes columns present in table.
 */
private function _get_ihrisdata_upsert_columns($has_status, $has_is_active)
{
    $candidates = array('ihris_pid', 'district_id', 'district', 'dhis_facility_id', 'dhis_district_id', 'nin', 'card_number', 'ipps', 'facility_type_id', 'facility_id', 'facility', 'department_id', 'department', 'job_id', 'job', 'employment_terms', 'surname', 'firstname', 'othername', 'mobile', 'telephone', 'institution_type', 'institution_type_id', 'last_gen', 'gender', 'birth_date', 'cadre', 'email', 'region');
    $cols = array();
    foreach ($candidates as $c) {
        if ($this->db->field_exists($c, 'ihrisdata')) {
            $cols[] = $c;
        }
    }
    if ($has_status) {
        $cols[] = 'status';
    }
    if ($has_is_active) {
        $cols[] = 'is_active_employee';
    }
    return $cols;
}

/**
 * Upsert one batch into ihrisdata (insert or update by ihris_pid). $cols = list of columns that exist in table.
 */
private function _upsert_ihrisdata_batch($rows, $cols)
{
    if (empty($rows) || empty($cols)) {
        return 0;
    }
    $values = array();
    $params = array();
    foreach ($rows as $r) {
        $placeholders = array();
        foreach ($cols as $c) {
            $placeholders[] = '?';
            $params[] = isset($r[$c]) ? $r[$c] : null;
        }
        $values[] = '(' . implode(',', $placeholders) . ')';
    }
    $col_list = '`' . implode('`,`', $cols) . '`';
    $updates = array();
    foreach (array_diff($cols, array('ihris_pid')) as $c) {
        // Preserve existing email in ihrisdata when API sends empty (e.g. after assign incharge)
        if ($c === 'email') {
            $updates[] = "`email`=IF(COALESCE(TRIM(VALUES(`email`)), '') = '', `email`, VALUES(`email`))";
        } else {
            $updates[] = "`$c`=VALUES(`$c`)";
        }
    }
    $sql = "INSERT INTO ihrisdata ($col_list) VALUES " . implode(',', $values) . " ON DUPLICATE KEY UPDATE " . implode(', ', $updates);
    $this->db->query($sql, $params);
    return count($rows);
}

/**
 * Fetch UCMB iHRIS data (non-paginated) and merge into ihrisdata (upsert). Sets status=1, is_active_employee=1.
 */
private function _merge_ucmbdata($is_cli, $has_status, $has_is_active)
{
    try {
        $upsert_cols = $this->_get_ihrisdata_upsert_columns($has_status, $has_is_active);
        $http = new HttpUtils();
        $headers = array('Content-Type: application/json', 'Accept: application/json');
        $response = $http->sendUCMBiHRISRequest('apiv1/index.php/api/ihrisdata', 'GET', $headers, array());
        if (!is_array($response) && !is_object($response)) {
            return 0;
        }
        $arr = is_array($response) ? $response : (isset($response->data) ? $response->data : array($response));
        if (empty($arr)) {
            return 0;
        }
        $total = count($arr);
        $merged = 0;
        $batch = array();
        $batch_size = 100;
        foreach ($arr as $i => $record) {
            $row = $this->_map_ihris_api_record_to_row($record);
            if ($has_status) {
                $row['status'] = 1;
            }
            if ($has_is_active) {
                $row['is_active_employee'] = 1;
            }
            $batch[] = $row;
            if (count($batch) >= $batch_size) {
                $merged += $this->_upsert_ihrisdata_batch($batch, $upsert_cols);
                $batch = array();
                if ($is_cli) {
                    $pct = (int) ((($i + 1) / $total) * 100);
                    printf("\r  UCMB: %s   ", $this->_draw_progress_bar($i + 1, $total));
                    flush();
                }
            }
        }
        if (!empty($batch)) {
            $merged += $this->_upsert_ihrisdata_batch($batch, $upsert_cols);
        }
        return $merged;
    } catch (Exception $e) {
        log_message('error', '_merge_ucmbdata: ' . $e->getMessage());
        return 0;
    }
}


    public function update_ipps()
    {
        // Select records where card_number is NULL and ipps is NOT NULL
        $ipps_nos = $this->db->query("SELECT ihris_pid, ipps FROM ihrisdata WHERE card_number IS NULL AND ipps IS NOT NULL")->result();

        foreach ($ipps_nos as $ipps_no) {
            $ipps = $ipps_no->ipps*1;
            $id = $ipps_no->ihris_pid;

            // Update the card_number for each record
            $this->db->query("UPDATE ihrisdata SET card_number = '$ipps' WHERE ihris_pid = '$id'");
        }
    }



    /**
     * Fetch UCMB iHRIS data (non-paginated) and merge into ihrisdata (upsert). Can be called standalone or via get_ihrisdata.
     */
    public function get_ucmbdata()
    {
        $is_cli = (php_sapi_name() === 'cli');
        $has_status = $this->db->field_exists('status', 'ihrisdata');
        $has_is_active = $this->db->field_exists('is_active_employee', 'ihrisdata');
        $merged = $this->_merge_ucmbdata($is_cli, $has_status, $has_is_active);
        $this->log("get_ucmbdata: merged $merged records");
        $this->cronjob_register(2, "biotimejobs/get_ucmbdata", $merged > 0 ? "successful" : "failed");
        return $merged;
    }

    /**
     * BioTime 9.x employee list page size (API accepts page_size / limit).
     * @see https://attendance.health.go.ug/docs/api-docs/
     */
    private $biotime_employee_page_size = 100;

    /**
     * GET /personnel/api/employees/ — enrolled personnel (BioTime 8.5 → 9.5 compatible).
     *
     * @param int|false $page
     * @param int|null  $page_size
     * @return object|null
     */
    public function get_Enrolled($page = FALSE, $page_size = null)
    {
        $http = new HttpUtils();
        $headers = [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'Authorization' => 'JWT ' . $this->get_token(),
        ];

        $endpoint = 'personnel/api/employees/';
        $page_size = ($page_size === null) ? $this->biotime_employee_page_size : (int) $page_size;
        $options = (object) [
            'page' => ($page === FALSE || $page === null || $page === '') ? 1 : (int) $page,
            'page_size' => max(1, $page_size),
        ];

        return $http->get_List($endpoint, 'GET', $headers, $options);
    }

    /**
     * Normalize BioTime employee area (array in 8.5/9.x; sometimes empty).
     *
     * @param object $employee
     * @return object|null {id, area_code, area_name}
     */
    protected function _biotime_employee_area($employee)
    {
        if (!is_object($employee) || !isset($employee->area)) {
            return null;
        }
        $area = $employee->area;
        if (is_array($area) && isset($area[0])) {
            $first = $area[0];
            return is_object($first) ? $first : null;
        }
        if (is_object($area) && (isset($area->area_code) || isset($area->id))) {
            return $area;
        }
        return null;
    }

    /**
     * Attendance enabled flag: BioTime 9.5 may use top-level enable_att or attemployee.enable_attendance.
     *
     * @param object $employee
     * @return int 0|1
     */
    protected function _biotime_employee_att_status($employee)
    {
        if (!is_object($employee)) {
            return 0;
        }
        if (isset($employee->enable_att)) {
            return !empty($employee->enable_att) ? 1 : 0;
        }
        if (isset($employee->attemployee) && is_object($employee->attemployee)
            && isset($employee->attemployee->enable_attendance)) {
            return !empty($employee->attemployee->enable_attendance) ? 1 : 0;
        }
        return 0;
    }

    /**
     * Page count from BioTime list response (uses page_size, not hard-coded 10).
     *
     * @param object $resp
     * @param int    $page_size
     * @return int
     */
    protected function _biotime_list_pages($resp, $page_size)
    {
        $count = (isset($resp->count) && is_numeric($resp->count)) ? (int) $resp->count : 0;
        $page_size = max(1, (int) $page_size);
        if ($count <= 0) {
            return 0;
        }
        return (int) ceil($count / $page_size);
    }

    /**
     * Extract employee rows from a BioTime list payload (data or results).
     *
     * @param object|null $response
     * @return array
     */
    protected function _biotime_list_rows($response)
    {
        if (!is_object($response)) {
            return [];
        }
        if (isset($response->data) && is_array($response->data)) {
            return $response->data;
        }
        if (isset($response->results) && is_array($response->results)) {
            return $response->results;
        }
        return [];
    }

    //cronjob
    //get enrolled data from biotime
    //after run call fingerprint cache procedure
    public function saveEnrolled()
    {
        try {
            $page_size = $this->biotime_employee_page_size;
            $resp = $this->get_Enrolled(1, $page_size);

            if (empty($resp) || !isset($resp->count)) {
                log_message('error', 'saveEnrolled: Invalid response from get_Enrolled()');
                return false;
            }

            $pages = $this->_biotime_list_pages($resp, $page_size);
            $rows = [];
            $seen = [];

            for ($currentPage = 1; $currentPage <= $pages; $currentPage++) {
                $response = ($currentPage === 1) ? $resp : $this->get_Enrolled($currentPage, $page_size);
                $employees = $this->_biotime_list_rows($response);

                if (empty($employees)) {
                    log_message('error', "saveEnrolled: Empty/invalid response for page $currentPage");
                    continue;
                }

                foreach ($employees as $mydata) {
                    if (!is_object($mydata) || !isset($mydata->emp_code) || $mydata->emp_code === '' || $mydata->emp_code === null) {
                        log_message('error', 'saveEnrolled: Missing emp_code in employee record');
                        continue;
                    }

                    $area = $this->_biotime_employee_area($mydata);
                    if ($area === null || !isset($area->area_code) || $area->area_code === '' || $area->area_code === null) {
                        log_message('debug', 'saveEnrolled: Skipping emp_code ' . $mydata->emp_code . ' (no area)');
                        continue;
                    }

                    $emp_code = (string) $mydata->emp_code;
                    $area_code = (string) $area->area_code;
                    $entry_id = $area_code . '-' . $emp_code;
                    if (isset($seen[$entry_id])) {
                        continue;
                    }
                    $seen[$entry_id] = true;

                    $rows[] = [
                        'entry_id' => $entry_id,
                        'card_number' => $emp_code,
                        'facilityId' => $area_code,
                        'source' => 'Biotime',
                        'device' => isset($mydata->enroll_sn) ? (string) $mydata->enroll_sn : '',
                        'att_status' => $this->_biotime_employee_att_status($mydata),
                    ];
                }
            }

            if (empty($rows)) {
                log_message('error', 'saveEnrolled: No rows to insert');
                return false;
            }

            $message = $this->biotimejobs_mdl->add_enrolled($rows);
            $this->log($message);
            $process = 3;
            $method = 'bioitimejobs/save_Enrolled';
            $status = (count($rows) > 0) ? 'successful' : 'failed';
            $this->cronjob_register($process, $method, $status);

            return true;
        } catch (Exception $e) {
            log_message('error', 'saveEnrolled Exception: ' . $e->getMessage());
            log_message('error', 'Stack trace: ' . $e->getTraceAsString());
            return false;
        } catch (Error $e) {
            log_message('error', 'saveEnrolled Fatal Error: ' . $e->getMessage());
            return false;
        }
    }


    //get cron jobs from the server
    

    /**
     * Get time logs from BioTime API with support for date range and terminal filtering
     * 
     * @param int $page Page number (default: 1)
     * @param string|bool $end_date End date in Y-m-d or Y-m-d H:i:s format (default: FALSE = current date/time)
     * @param string|bool $terminal Terminal serial number (default: FALSE = all terminals)
     * @param string|bool $start_date Start date in Y-m-d or Y-m-d H:i:s format (default: FALSE = 24 hours before end_date)
     * @param int $max_retries Maximum number of retry attempts on failure (default: 3)
     * @return object|bool API response object or FALSE on failure
     */
    public function getTime($page = 1, $end_date = FALSE, $terminal = FALSE, $start_date = FALSE, $max_retries = 3)
    {
        date_default_timezone_set('Africa/Kampala');
        $http = new HttpUtils();
        
        $attempt = 0;
        while ($attempt < $max_retries) {
            try {
        $headers = [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'Authorization' => "JWT " . $this->get_token(),
        ];
                
                // Handle date parameters
        if (empty($end_date)) {
            $edate = date('Y-m-d H:i:s');
                } else {
                    // If only date is provided (Y-m-d), add time component
                    if (strlen($end_date) == 10) {
                        $edate = $end_date . ' 23:59:59';
        } else {
            $edate = $end_date;
                    }
                }
                
                // Handle start date
                if (empty($start_date)) {
            $sdate = date("Y-m-d H:i:s", strtotime("-24 hours", strtotime($edate)));
                } else {
                    // If only date is provided (Y-m-d), add time component
                    if (strlen($start_date) == 10) {
                        $sdate = $start_date . ' 00:00:00';
                    } else {
                        $sdate = $start_date;
                    }
                }
                
                // Ensure start_date is before end_date
                if (strtotime($sdate) > strtotime($edate)) {
                    $temp = $sdate;
                    $sdate = $edate;
                    $edate = $temp;
                }

                // Build query parameters
            $query = array(
                'page' => $page,
                'start_time' => $sdate,
                'end_time' => $edate,
            );

                // Add terminal filter if provided
                if (!empty($terminal)) {
                    $query['terminal_sn'] = $terminal;
        }

        $params = '?' . http_build_query($query);
        $endpoint = 'iclock/api/transactions/' . $params;

        $response = $http->getTimeLogs($endpoint, "GET", $headers);
                
                // Validate response
                if (!isset($response) || !is_object($response)) {
                    throw new Exception("Invalid API response format");
                }
                
                // Check for API errors in response
                if (isset($response->error) || isset($response->detail)) {
                    $error_msg = isset($response->error) ? $response->error : $response->detail;
                    throw new Exception("API Error: " . $error_msg);
                }
                
        return $response;
                
            } catch (Exception $e) {
                $attempt++;
                $this->log("getTime() attempt $attempt failed: " . $e->getMessage());
                
                if ($attempt >= $max_retries) {
                    $this->log("getTime() failed after $max_retries attempts");
                    return FALSE;
                }
                
                // Wait before retry (exponential backoff)
                sleep(pow(2, $attempt - 1));
            } catch (Error $e) {
                $attempt++;
                $this->log("getTime() fatal error on attempt $attempt: " . $e->getMessage());
                
                if ($attempt >= $max_retries) {
                    return FALSE;
                }
                
                sleep(pow(2, $attempt - 1));
            }
        }
        
        return FALSE;
    }


    /**
     * Fetch BioTime logs from API and save to database
     * 
     * @param string|bool $end_date End date in Y-m-d or Y-m-d H:i:s format (default: FALSE = current date)
     * @param string|bool $terminal Terminal serial number (default: FALSE = all terminals)
     * @param string|bool $start_date Start date in Y-m-d or Y-m-d H:i:s format (default: FALSE = 24 hours before end_date)
     * @param int $batch_size Number of records per page (default: 10, API default)
     * @param callable|null $progress_callback Optional callback function for progress updates
     * @return array Result array with status, message, and statistics
     */
    public function fetchBiotTimeLogs($end_date = FALSE, $terminal = FALSE, $start_date = FALSE, $batch_size = 10, $progress_callback = NULL)
    {
        ignore_user_abort(true);
        set_time_limit(0);
        ini_set('max_execution_time', 0);
        ini_set('memory_limit', '512M');
        
        $result = array(
            'status' => 'error',
            'message' => '',
            'records_fetched' => 0,
            'records_saved' => 0,
            'pages_processed' => 0,
            'errors' => array()
        );
        
        try {
            // Get first page to determine total count
            $resp = $this->getTime(1, $end_date, $terminal, $start_date);
            
            if ($resp === FALSE) {
                throw new Exception("Failed to fetch initial data from API");
            }
            
            if (!isset($resp->count) || !isset($resp->data)) {
                throw new Exception("Invalid API response structure");
            }
            
            $count = (int) $resp->count;
            $pages = (int) ceil($count / $batch_size);
            
            if ($pages == 0) {
                $result['status'] = 'success';
                $result['message'] = 'No records found for the specified date range';
                return $result;
            }
            
        $rows = array();
            $total_processed = 0;

            // Process all pages
        for ($currentPage = 1; $currentPage <= $pages; $currentPage++) {
                $response = $this->getTime($currentPage, $end_date, $terminal, $start_date);
                
                if ($response === FALSE) {
                    $error_msg = "Failed to fetch page $currentPage";
                    $result['errors'][] = $error_msg;
                    $this->log("fetchBiotTimeLogs() error: $error_msg");
                    continue;
                }
                
                if (!isset($response->data) || !is_array($response->data)) {
                    $error_msg = "Invalid data structure on page $currentPage";
                    $result['errors'][] = $error_msg;
                    $this->log("fetchBiotTimeLogs() error: $error_msg");
                    continue;
                }
                
                // Process records from this page
            foreach ($response->data as $mydata) {
                    if (!isset($mydata->punch_time) || !isset($mydata->emp_code)) {
                        continue; // Skip invalid records
                    }
                    
                $datetime = date("Y-m-d H:i:s", strtotime($mydata->punch_time));
             
                $data = array(
                        "emp_code" => isset($mydata->emp_code) ? $mydata->emp_code : '',
                        "terminal_sn" => isset($mydata->terminal_sn) ? $mydata->terminal_sn : '',
                        "area_alias" => isset($mydata->area_alias) ? $mydata->area_alias : '',
                        "longitude" => isset($mydata->longitude) ? $mydata->longitude : NULL,
                        "latitude" => isset($mydata->latitude) ? $mydata->latitude : NULL,
                        "punch_state" => isset($mydata->punch_state) ? $mydata->punch_state : '',
                    "punch_time" => $datetime
                );
                array_push($rows, $data);
                    $total_processed++;
        }

                $result['pages_processed'] = $currentPage;
                
                // Call progress callback if provided
                if (is_callable($progress_callback)) {
                    call_user_func($progress_callback, array(
                        'page' => $currentPage,
                        'total_pages' => $pages,
                        'records_processed' => $total_processed,
                        'total_records' => $count
                    ));
                }
                
                // Insert in batches to avoid memory issues
                if (count($rows) >= 1000) {
        $message = $this->biotimejobs_mdl->add_time_logs($rows);
                    $result['records_saved'] += count($rows);
                    $rows = array(); // Clear array
                }
            }
            
            // Insert remaining records
            if (count($rows) > 0) {
                $message = $this->biotimejobs_mdl->add_time_logs($rows);
                $result['records_saved'] += count($rows);
            }
            
            $result['records_fetched'] = $total_processed;
            $result['status'] = 'success';
            $result['message'] = "Successfully fetched and saved $total_processed records";

            $this->logattendance($result['message']);
            
            // Register cronjob
        $process = 4;
        $method = "bioitimejobs/fetchBiotTimeLogs";
            $status = ($result['records_saved'] > 0) ? "successful" : "failed";
        $this->cronjob_register($process, $method, $status);
            
            // Clock-in/out: use fetch_daily_attendance (streaming) for full sync. This method only fetches to biotime_data to avoid double-processing.
            
        } catch (Exception $e) {
            $result['status'] = 'error';
            $result['message'] = "Error: " . $e->getMessage();
            $result['errors'][] = $e->getMessage();
            $this->log("fetchBiotTimeLogs() exception: " . $e->getMessage());
        } catch (Error $e) {
            $result['status'] = 'error';
            $result['message'] = "Fatal Error: " . $e->getMessage();
            $result['errors'][] = $e->getMessage();
            $this->log("fetchBiotTimeLogs() fatal error: " . $e->getMessage());
        }
        
        return $result;
    }

    /**
     * Sync attendance by area (area_name = area_alias in PG). Used by biometrics/tasks view.
     * GET: area_name, start_date, end_date. Runs fetch_time_history_streaming for that area.
     */
    public function sync_area()
    {
        header('Content-Type: application/json');
        try {
            $area_name = trim((string) $this->input->get('area_name'));
            $start_date_input = $this->input->get('start_date');
            $end_date_input = $this->input->get('end_date');
            if (empty($area_name)) {
                echo json_encode(array('status' => 'error', 'message' => 'area_name is required'));
                return;
            }
            if (empty($end_date_input)) {
                $end_date = date('Y-m-d');
        } else {
                $end_date = date('Y-m-d', strtotime($end_date_input));
                if ($end_date === '1970-01-01' || $end_date === false) {
                    echo json_encode(array('status' => 'error', 'message' => 'Invalid end_date'));
                    return;
                }
            }
            if (empty($start_date_input)) {
                $start_date = date('Y-m-d', strtotime('-30 days', strtotime($end_date)));
            } else {
                $start_date = date('Y-m-d', strtotime($start_date_input));
                if ($start_date === '1970-01-01' || $start_date === false) {
                    echo json_encode(array('status' => 'error', 'message' => 'Invalid start_date'));
                    return;
                }
            }
            if (strtotime($start_date) > strtotime($end_date)) {
                echo json_encode(array('status' => 'error', 'message' => 'Start date must be before or equal to end date'));
                return;
            }
            $result = $this->fetch_time_history_streaming($start_date, $end_date, $area_name, $area_name, false);
            $response = array(
                'status' => $result['status'],
                'message' => $result['message'],
                'total_records' => isset($result['total_records']) ? (int) $result['total_records'] : 0,
                'timestamp' => date('Y-m-d H:i:s'),
                'parameters' => array('area_name' => $area_name, 'start_date' => $start_date, 'end_date' => $end_date)
            );
            if (!empty($result['timing'])) {
                $response['timing'] = $result['timing'];
            }
            echo json_encode($response);
        } catch (Exception $e) {
            echo json_encode(array('status' => 'error', 'message' => $e->getMessage()));
        } catch (Error $e) {
            echo json_encode(array('status' => 'error', 'message' => $e->getMessage()));
        }
    }

    /**
     * Custom logs endpoint for frontend - syncs individual machines
     * Supports async background processing with proper JSON responses
     * 
     * @return void Outputs JSON response
     */
    public function custom_logs()
    {
        header('Content-Type: application/json');
        
        try {
            // Get parameters
            $end_date_input = $this->input->get('end_date');
        $terminal_sn = $this->input->get('terminal_sn');
            $start_date_input = $this->input->get('start_date');
            $sync_type = $this->input->get('sync_type') ?: 'attendance';
            $batch_size = (int) ($this->input->get('batch_size') ?: 10);
            $async = $this->input->get('async') !== 'false'; // Default to async
            
            // Validate terminal_sn
            if (empty($terminal_sn)) {
                throw new Exception("Terminal serial number (terminal_sn) is required");
            }
            
            // Validate and set dates
            if (empty($end_date_input)) {
                $end_date = date('Y-m-d');
            } else {
                $end_date = date('Y-m-d', strtotime($end_date_input));
                if ($end_date === '1970-01-01' || $end_date === FALSE) {
                    throw new Exception("Invalid end_date format. Expected Y-m-d format.");
                }
            }
            
            if (empty($start_date_input)) {
                // Default to 30 days before end_date
                $start_date = date('Y-m-d', strtotime('-30 days', strtotime($end_date)));
            } else {
                $start_date = date('Y-m-d', strtotime($start_date_input));
                if ($start_date === '1970-01-01' || $start_date === FALSE) {
                    throw new Exception("Invalid start_date format. Expected Y-m-d format.");
                }
            }
            
            // Validate date range (maximum 30 days)
            $start_timestamp = strtotime($start_date);
            $end_timestamp = strtotime($end_date);
            $days_diff = (int) ceil(($end_timestamp - $start_timestamp) / 86400) + 1;
            
            if ($days_diff > 30) {
                throw new Exception("Date range cannot exceed 30 days. Selected range: $days_diff days.");
            }
            
            if ($start_timestamp > $end_timestamp) {
                throw new Exception("Start date must be before or equal to end date.");
            }
            
            // Get facility name for logging
            $facility = 'Unknown';
            if (!empty($terminal_sn)) {
                $machine = $this->db->query("SELECT area_name FROM biotime_devices WHERE sn = ?", array($terminal_sn))->row();
                if ($machine && isset($machine->area_name)) {
                    $facility = $machine->area_name;
                }
            }
            
            // Prepare response data
            $response = array(
                'status' => 'initiated',
                'message' => 'Sync process started',
                'timestamp' => date('Y-m-d H:i:s'),
                'parameters' => array(
                    'terminal_sn' => $terminal_sn,
                    'facility' => $facility,
                    'start_date' => $start_date,
                    'end_date' => $end_date,
                    'days_range' => $days_diff,
                    'sync_type' => $sync_type,
                    'batch_size' => $batch_size,
                    'async' => $async
                )
            );
            
            // Log the sync request
            $log_message = "custom_logs() - Terminal: $terminal_sn, Facility: $facility, Start: $start_date, End: $end_date, Days: $days_diff, Type: $sync_type, Batch: $batch_size, Async: " . ($async ? 'yes' : 'no');
            $this->log($log_message);
            
            if ($async) {
                // Async processing - return immediately and run in background
                if (function_exists('fastcgi_finish_request')) {
                    // Send response immediately
                    echo json_encode($response, JSON_PRETTY_PRINT);
                    fastcgi_finish_request();
                } else {
                    // For non-FastCGI, flush output
                    echo json_encode($response, JSON_PRETTY_PRINT);
                    if (ob_get_level() > 0) {
                        ob_end_flush();
                    }
                    flush();
                }
                
                // Run sync in background using fetch_time_history
                $this->run_sync_background($terminal_sn, $start_date, $end_date, $facility, $sync_type, $batch_size);
                
            } else {
                // Synchronous processing - wait for completion using fetch_time_history
                $result = $this->fetch_time_history($start_date, $end_date, $terminal_sn, $facility);
                
                $response['status'] = $result['status'];
                $response['message'] = $result['message'];
                $response['dates_processed'] = $result['dates_processed'];
                $response['total_records'] = $result['total_records'];
                
                if (!empty($result['errors'])) {
                    $response['errors'] = $result['errors'];
                }
                
                if (!empty($result['daily_stats'])) {
                    $response['daily_stats'] = $result['daily_stats'];
                }
                
                echo json_encode($response, JSON_PRETTY_PRINT);
            }
            
        } catch (Exception $e) {
            $error_response = array(
                'status' => 'error',
                'message' => $e->getMessage(),
                'timestamp' => date('Y-m-d H:i:s')
            );
            echo json_encode($error_response, JSON_PRETTY_PRINT);
            $this->log("custom_logs() exception: " . $e->getMessage());
        } catch (Error $e) {
            $error_response = array(
                'status' => 'error',
                'message' => "Fatal Error: " . $e->getMessage(),
                'timestamp' => date('Y-m-d H:i:s')
            );
            echo json_encode($error_response, JSON_PRETTY_PRINT);
            $this->log("custom_logs() fatal error: " . $e->getMessage());
        }
    }
    
    /**
     * Run sync in background (for async processing)
     * 
     * @param string $terminal_sn Terminal serial number
     * @param string|bool $start_date Start date
     * @param string $end_date End date
     * @param string $facility Facility name
     * @param string $sync_type Sync type
     * @param int $batch_size Batch size
     * @return void
     */
    private function run_sync_background($terminal_sn, $start_date, $end_date, $facility, $sync_type, $batch_size)
    {
        ignore_user_abort(true);
        set_time_limit(0);
        ini_set('max_execution_time', 0);
        ini_set('memory_limit', '512M');
        
        try {
            $this->log("run_sync_background() started for terminal $terminal_sn, date range: $start_date to $end_date");
            
            // Use fetch_time_history instead of fetchBiotTimeLogs
            $result = $this->fetch_time_history($start_date, $end_date, $terminal_sn, $facility, FALSE, NULL, TRUE);
            
            $this->log("run_sync_background() completed for terminal $terminal_sn: " . $result['message']);
            
            // Update machine last_activity if successful
            if ($result['status'] === 'success' && $result['total_records'] > 0) {
                $this->db->where('sn', $terminal_sn);
                // Use current timestamp so same-day incremental re-syncs remain possible
                $this->db->update('biotime_devices', array('last_activity' => date('Y-m-d H:i:s')));
            }
            
        } catch (Exception $e) {
            $this->log("run_sync_background() exception for terminal $terminal_sn: " . $e->getMessage());
        } catch (Error $e) {
            $this->log("run_sync_background() fatal error for terminal $terminal_sn: " . $e->getMessage());
        }
    }
    
    /**
     * Sync individual machine endpoint with progress tracking
     * Returns JSON response suitable for frontend terminal display
     * 
     * @param string $terminal_sn Terminal serial number (URL parameter)
     * @param string $end_date End date in Y-m-d format (URL parameter, optional)
     * @return void Outputs JSON response
     */
    public function syncMachine($terminal_sn = FALSE, $end_date = FALSE)
    {
        header('Content-Type: application/json');
        
        try {
            // Get parameters from URL or GET
            if (empty($terminal_sn)) {
                $terminal_sn = $this->input->get('terminal_sn') ?: $this->uri->segment(3);
            }
            
            if (empty($end_date)) {
                $end_date = $this->input->get('end_date') ?: $this->uri->segment(4);
            }
            
            // Validate terminal_sn
            if (empty($terminal_sn)) {
                throw new Exception("Terminal serial number is required");
            }
            
            // Set default end_date if not provided
            if (empty($end_date)) {
                $end_date = date('Y-m-d');
            } else {
                $end_date = date('Y-m-d', strtotime($end_date));
                if ($end_date === '1970-01-01' || $end_date === FALSE) {
                    throw new Exception("Invalid end_date format. Expected Y-m-d format.");
                }
            }
            
            // Get machine info
            $machine = $this->db->query("SELECT * FROM biotime_devices WHERE sn = ?", array($terminal_sn))->row();
            
            if (empty($machine)) {
                throw new Exception("Machine with serial number '$terminal_sn' not found");
            }
            
            $facility = isset($machine->area_name) ? $machine->area_name : 'Unknown';
            $start_date = isset($machine->last_activity) && !empty($machine->last_activity) 
                ? date('Y-m-d', strtotime($machine->last_activity . ' -1 day'))
                : date('Y-m-d', strtotime('-7 days'));
            
            // Prepare response
            $response = array(
                'status' => 'initiated',
                'message' => 'Machine sync process started',
                'timestamp' => date('Y-m-d H:i:s'),
                'machine' => array(
                    'terminal_sn' => $terminal_sn,
                    'facility' => $facility,
                    'last_activity' => isset($machine->last_activity) ? $machine->last_activity : NULL
                ),
                'parameters' => array(
                    'start_date' => $start_date,
                    'end_date' => $end_date
                )
            );
            
            $this->log("syncMachine() initiated for terminal $terminal_sn ($facility) from $start_date to $end_date");
            
            // Run sync asynchronously
            if (function_exists('fastcgi_finish_request')) {
                echo json_encode($response, JSON_PRETTY_PRINT);
                fastcgi_finish_request();
            } else {
                echo json_encode($response, JSON_PRETTY_PRINT);
                if (ob_get_level() > 0) {
                    ob_end_flush();
                }
                flush();
            }
            
            // Run sync in background
            $this->run_sync_background($terminal_sn, $start_date, $end_date, $facility, 'attendance', 10);
            
        } catch (Exception $e) {
            $error_response = array(
                'status' => 'error',
                'message' => $e->getMessage(),
                'timestamp' => date('Y-m-d H:i:s')
            );
            echo json_encode($error_response, JSON_PRETTY_PRINT);
            $this->log("syncMachine() exception: " . $e->getMessage());
        } catch (Error $e) {
            $error_response = array(
                'status' => 'error',
                'message' => "Fatal Error: " . $e->getMessage(),
                'timestamp' => date('Y-m-d H:i:s')
            );
            echo json_encode($error_response, JSON_PRETTY_PRINT);
            $this->log("syncMachine() fatal error: " . $e->getMessage());
        }
    }


    //create multiple new users cronjob
    public function multiple_new_users()
    {
        $query = $this->db->query(
            "SELECT * FROM ihrisdata
             WHERE ihrisdata.facility_id IN (SELECT area_code FROM biotime_devices)
               AND ihrisdata.card_number IS NOT NULL
               AND ihrisdata.card_number <> ''
               AND ihrisdata.card_number NOT IN (
                    SELECT fingerprints_staging.card_number
                    FROM fingerprints_staging
                    WHERE fingerprints_staging.card_number IS NOT NULL
               )"
        );
        $newusers = $query ? $query->result() : [];
        $ok = 0;
        $fail = 0;

        foreach ($newusers as $newuser) {
            $response = $this->create_new_biotimeuser_from_ihris($newuser);
            if ($this->_biotime_response_ok($response)) {
                $ok++;
            } else {
                $fail++;
            }
        }

        $process = 5;
        $method = 'bioitimejobs/multiple_new_users';
        $status = ($ok > 0 && $fail === 0) ? 'successful' : (($ok > 0) ? 'partial' : 'failed');
        $this->cronjob_register($process, $method, $status);
        $this->log(['multiple_new_users' => $status, 'created' => $ok, 'failed' => $fail, 'candidates' => count($newusers)]);

        return $status;
    }

    /**
     * Map iHRIS gender values to BioTime API (S, F, M).
     *
     * @param mixed $gender
     * @return string|null
     */
    protected function _biotime_map_gender($gender)
    {
        $g = strtoupper(trim((string) $gender));
        if ($g === '') {
            return null;
        }
        if ($g === 'M' || $g === 'MALE' || $g === '1') {
            return 'M';
        }
        if ($g === 'F' || $g === 'FEMALE' || $g === '2') {
            return 'F';
        }
        if ($g === 'S' || $g === 'OTHER') {
            return 'S';
        }
        return null;
    }

    /**
     * Build BioTime 9.5 employee create/update body from iHRIS / transfer data.
     * Required: emp_code, area. Department defaults to 1 when unmapped.
     * Optional: position (job), names, mobile, email, gender, birthday, etc.
     *
     * @param object|array $staff
     * @param array $overrides facility/area/department/job keys when transferring
     * @return array{ok:bool,error?:string,body?:array,facility_code?:string,area_id?:int}
     */
    protected function _build_biotime_employee_payload($staff, array $overrides = [])
    {
        $s = is_array($staff) ? (object) $staff : $staff;
        if (!is_object($s)) {
            return ['ok' => false, 'error' => 'invalid staff payload'];
        }

        $emp_code = '';
        foreach (['emp_code', 'card_number'] as $k) {
            if (!empty($overrides[$k])) {
                $emp_code = trim((string) $overrides[$k]);
                break;
            }
            if (isset($s->$k) && trim((string) $s->$k) !== '') {
                $emp_code = trim((string) $s->$k);
                break;
            }
        }
        if ($emp_code === '') {
            return ['ok' => false, 'error' => 'emp_code/card_number is required'];
        }
        if (strlen($emp_code) > 20) {
            return ['ok' => false, 'error' => 'emp_code exceeds 20 characters'];
        }

        $facility_code = '';
        foreach (['new_facility', 'facility_id', 'area'] as $k) {
            if (!empty($overrides[$k])) {
                $facility_code = trim((string) $overrides[$k]);
                break;
            }
            if (isset($s->$k) && trim((string) $s->$k) !== '') {
                $facility_code = trim((string) $s->$k);
                break;
            }
        }
        $facility_code = urldecode($facility_code);
        if ($facility_code === '') {
            return ['ok' => false, 'error' => 'facility/area is required'];
        }

        $barea = $this->getbioloc($facility_code);
        if (empty($barea)) {
            return ['ok' => false, 'error' => 'BioTime area not found for ' . $facility_code];
        }

        // Department: map when possible, else default 1 (per BioTime docs / product default)
        $dep_key = '';
        foreach (['department_id', 'department'] as $k) {
            if (!empty($overrides[$k])) {
                $dep_key = trim((string) $overrides[$k]);
                break;
            }
            if (isset($s->$k) && trim((string) $s->$k) !== '') {
                $dep_key = trim((string) $s->$k);
                break;
            }
        }
        $bdep = ($dep_key !== '') ? $this->getbiodeps(urldecode($dep_key)) : null;
        if (empty($bdep)) {
            $bdep = 1;
        }

        // Job / position — include only when mapped
        $job_key = '';
        foreach (['job_id', 'job', 'position'] as $k) {
            if (!empty($overrides[$k])) {
                $job_key = trim((string) $overrides[$k]);
                break;
            }
            if (isset($s->$k) && trim((string) $s->$k) !== '') {
                $job_key = trim((string) $s->$k);
                break;
            }
        }
        $bpos = ($job_key !== '') ? $this->getbiojobs(urldecode($job_key)) : null;

        $firstname = '';
        if (!empty($overrides['firstname'])) {
            $firstname = (string) $overrides['firstname'];
        } elseif (!empty($overrides['first_name'])) {
            $firstname = (string) $overrides['first_name'];
        } elseif (!empty($s->firstname)) {
            $firstname = (string) $s->firstname;
        } elseif (!empty($s->first_name)) {
            $firstname = (string) $s->first_name;
        }

        $surname = '';
        if (!empty($overrides['surname'])) {
            $surname = (string) $overrides['surname'];
        } elseif (!empty($overrides['last_name'])) {
            $surname = (string) $overrides['last_name'];
        } elseif (!empty($s->surname)) {
            $surname = (string) $s->surname;
        } elseif (!empty($s->last_name)) {
            $surname = (string) $s->last_name;
        }

        // Required by BioTime create/update docs
        $body = [
            'emp_code' => $emp_code,
            'department' => (int) $bdep,
            'area' => [(int) $barea],
        ];

        if ($firstname !== '') {
            $body['first_name'] = $firstname;
        }
        if ($surname !== '') {
            $body['last_name'] = $surname;
        }
        if (!empty($bpos)) {
            $body['position'] = (int) $bpos;
        }

        // Optional fields present in ihrisdata and accepted by BioTime API
        $mobile = '';
        if (!empty($s->mobile)) {
            $mobile = trim((string) $s->mobile);
        } elseif (!empty($s->telephone)) {
            $mobile = trim((string) $s->telephone);
        }
        if ($mobile !== '') {
            $body['mobile'] = $mobile;
            $body['contact_tel'] = $mobile;
        }

        if (!empty($s->email)) {
            $body['email'] = trim((string) $s->email);
        }

        $gender = $this->_biotime_map_gender(isset($s->gender) ? $s->gender : '');
        if ($gender !== null) {
            $body['gender'] = $gender;
        }

        if (!empty($s->birth_date) && $s->birth_date !== '0000-00-00') {
            $bts = strtotime((string) $s->birth_date);
            if ($bts !== false) {
                $body['birthday'] = date('Y-m-d', $bts);
            }
        }

        if (!empty($s->nin)) {
            $body['ssn'] = trim((string) $s->nin);
        }

        // Default hire_date to today when creating (API allows omit; useful for audit)
        if (empty($body['hire_date'])) {
            $body['hire_date'] = date('Y-m-d');
        }

        return [
            'ok' => true,
            'body' => $body,
            'facility_code' => $facility_code,
            'area_id' => (int) $barea,
            'emp_code' => $emp_code,
        ];
    }

    /**
     * Update enrolled BioTime employee (facility transfer / job change).
     * BioTime 9.5 PUT requires emp_code + department + area; department defaults to 1.
     * @see https://attendance.health.go.ug/docs/api-docs/employee_api.html#create
     */
    public function update_biotimeuser($userdata)
    {
        if (empty($userdata) || empty($userdata->biotime_emp_id)) {
            log_message('error', 'update_biotimeuser: missing biotime_emp_id');
            return false;
        }

        $overrides = [];
        if (!empty($userdata->new_facility)) {
            $overrides['new_facility'] = $userdata->new_facility;
        }
        if (!empty($userdata->facility_id) && empty($overrides['new_facility'])) {
            $overrides['facility_id'] = $userdata->facility_id;
        }

        // Resolve emp_code from local enrollment map when transfer row lacks it
        if (empty($userdata->emp_code) && empty($userdata->card_number)) {
            $enr = $this->db->query(
                'SELECT emp_code FROM biotime_enrollment WHERE biotime_emp_id = ? LIMIT 1',
                [(string) $userdata->biotime_emp_id]
            )->row();
            if ($enr && !empty($enr->emp_code)) {
                $overrides['emp_code'] = $enr->emp_code;
            }
        }

        $built = $this->_build_biotime_employee_payload($userdata, $overrides);
        if (empty($built['ok'])) {
            log_message('error', 'update_biotimeuser: ' . (isset($built['error']) ? $built['error'] : 'payload failed'));
            return false;
        }

        $token = $this->get_token();
        if (empty($token)) {
            return false;
        }

        $http = new HttpUtils();
        $empId = (int) $userdata->biotime_emp_id;
        $ok = false;
        $response = null;
        $body = $built['body'];
        $barea = $built['area_id'];
        $emp_code = $built['emp_code'];

        // 1) Adjust area (facility move) — proven working on BioTime 9.5
        $adjustBody = [
            'employees' => [$empId],
            'areas' => [(int) $barea],
        ];
        $adjustJson = json_encode($adjustBody);
        $response = $http->curlsendHttpPost(
            'personnel/api/employees/adjust_area/',
            $this->_biotime_json_headers($token, $adjustJson),
            $adjustBody
        );
        $ok = $this->_biotime_response_ok($response);
        if ($response) {
            $this->log(['adjust_area' => $response]);
        }

        // 2) Full PUT with required emp_code, department (default 1), area + optional job/ihris fields
        $json = json_encode($body);
        $endpoint = 'personnel/api/employees/' . $empId . '/';
        $putResponse = $http->curlupdateHttpPost($endpoint, $this->_biotime_json_headers($token, $json), $body);
        if ($putResponse) {
            $this->log(['update_put' => $putResponse]);
        }
        if ($this->_biotime_response_ok($putResponse)) {
            $ok = true;
            $response = $putResponse;
        }

        if ($ok) {
            $this->db->replace('biotime_enrollment', [
                'emp_code' => $emp_code,
                'biotime_emp_id' => (string) $empId,
                'biotime_facility_id' => (string) (int) $barea,
                'biotime_fac_id' => (string) $built['facility_code'],
            ]);
        }

        $process = 6;
        $method = 'bioitimejobs/update_biotimeuser';
        $status = $ok ? 'successful' : 'failed';
        $this->cronjob_register($process, $method, $status);
        return $ok ? $response : false;
    }


    //enroll new users (Front End Action that requires login);
    public function get_new_users($facility)
    {
        $facility = $this->db->escape_str($facility);
        $query = $this->db->query("SELECT * FROM  ihrisdata WHERE ihrisdata.facility_id='$facility' AND ihrisdata.card_number NOT IN (SELECT fingerprints_staging.card_number from fingerprints_staging)");
        return $query->result();
    }

    /**
     * Create from full ihrisdata row (preferred).
     *
     * @param object $staff
     * @return object|false
     */
    public function create_new_biotimeuser_from_ihris($staff)
    {
        $built = $this->_build_biotime_employee_payload($staff);
        if (empty($built['ok'])) {
            log_message('error', 'create_new_biotimeuser_from_ihris: ' . (isset($built['error']) ? $built['error'] : 'payload failed'));
            return false;
        }

        $token = $this->get_token();
        if (empty($token)) {
            return false;
        }

        $body = $built['body'];
        $json = json_encode($body);
        $http = new HttpUtils();
        $response = $http->curlsendHttpPost(
            'personnel/api/employees/',
            $this->_biotime_json_headers($token, $json),
            $body
        );

        if ($response) {
            $this->log(['create_employee' => $response, 'request' => $body]);
        } else {
            log_message('error', 'create_new_biotimeuser_from_ihris: empty response emp_code=' . $built['emp_code'] . ' body=' . $json);
        }

        $ok = $this->_biotime_response_ok($response);
        if ($ok && isset($response->id)) {
            $this->db->replace('biotime_enrollment', [
                'emp_code' => $built['emp_code'],
                'biotime_emp_id' => (string) (int) $response->id,
                'biotime_facility_id' => (string) (int) $built['area_id'],
                'biotime_fac_id' => (string) $built['facility_code'],
            ]);
        }

        $process = 6;
        $method = 'bioitimejobs/create_new_biotimeuser';
        $this->cronjob_register($process, $method, $ok ? 'successful' : 'failed');
        return $ok ? $response : false;
    }


    /**
     * Create BioTime employee — POST /personnel/api/employees/
     * Required: emp_code, area. Department defaults to 1. Position/job when available.
     * @see https://attendance.health.go.ug/docs/api-docs/employee_api.html#create
     */
    public function create_new_biotimeuser($firstname, $surname, $emp_code, $area, $department, $position, $extra = null)
    {
        // Allow passing a full ihrisdata object as the only argument
        if (is_object($firstname) && func_num_args() === 1) {
            return $this->create_new_biotimeuser_from_ihris($firstname);
        }

        $staff = is_object($extra) ? clone $extra : (object) [];
        $staff->firstname = $firstname;
        $staff->surname = $surname;
        $staff->card_number = $emp_code;
        $staff->facility_id = $area;
        $staff->department_id = $department;
        $staff->job_id = $position;

        return $this->create_new_biotimeuser_from_ihris($staff);
    }
    public function log($message)
    {
        //add double [] at the beggining and at the end of file contents
        return file_put_contents('logs/log.txt', "\n{" . '"REQUEST DETAILS: ' . date('Y-m-d H:i:s') . ' Time": ' . json_encode($message) . '}\n', FILE_APPEND);
    }
    public function logattendance($message)
    {
        //add double [] at the beggining and at the end of file contents
        return file_put_contents('logs/fetchatt_log.txt', "\n{" . '"REQUEST DETAILS: ' . date('Y-m-d H:i:s') . ' Time": ' . json_encode($message) . '},\n', FILE_APPEND);
    }
    public function getbiojobs($job)
    {
        $job = $this->db->escape_str((string) $job);
        $query = $this->db->query("SELECT id from biotime_jobs where position_code='$job' LIMIT 1");
        if (!$query || $query->num_rows() < 1) {
            return null;
        }
        return $query->row()->id;
    }
    public function getbiodeps($dep_id)
    {
        $dep_id = $this->db->escape_str((string) $dep_id);
        // Prefer BioTime department id when column exists; API create/update expects numeric id
        if ($this->db->field_exists('biotime_dept_id', 'biotime_departments')) {
            $query = $this->db->query("SELECT biotime_dept_id AS id from biotime_departments where dept_code='$dep_id' LIMIT 1");
        } else {
            $query = $this->db->query("SELECT id from biotime_departments where dept_code='$dep_id' LIMIT 1");
        }
        if (!$query || $query->num_rows() < 1) {
            return null;
        }
        return $query->row()->id;
    }
    public function getbioloc($facility)
    {
        $facility = $this->db->escape_str((string) $facility);
        $query = $this->db->query("SELECT id from biotime_facilities where area_code='$facility' LIMIT 1");
        if (!$query || $query->num_rows() < 1) {
            return null;
        }
        return $query->row()->id;
    }
    //not working
    public function biotimeFacilities()
    {

        $http = new HttpUtils();
        $headr = array();
        $headr[] = 'Content-length: 0';
        $headr[] = 'Content-type: application/json';
        $headr[] = 'Authorization: JWT ' . $this->get_token();



        $query = array(
            'page_size' => 50000
        );

        $params = '?' . http_build_query($query);
        $endpoint = 'personnel/api/areas/' . $params;

        //leave options and undefined. guzzle will use the http:query;

        $response = $http->curlgetHttp($endpoint, $headr, []);
        //return $response;
        //return $response;
        $j = array();
        foreach ($response->data as $facs) {
            $data = array(
                'id' => $facs->id,
                'area_code' => $facs->area_code,
                'area_name' => $facs->area_name
            );
            array_push($j, $data);
        }

        $message = $this->biotimejobs_mdl->save_facilities($j);
        //  print_r($response->data[0]->id);
        $process = 7;
        $method = "bioitimejobs/biotimeFacilities";
        if ($response) {
            $status = "successful";
        } else {
            $status = "failed";
        }
        $this->cronjob_register($process, $method, $status);
        return $this->log($message);
    }

    public function biotime_jobs()
    {

        $http = new HttpUtils();
        $headr = array();
        $headr[] = 'Content-length: 0';
        $headr[] = 'Content-type: application/json';
        $headr[] = 'Authorization: JWT ' . $this->get_token();



        $query = array(
            'page_size' => 50000
        );

        $params = '?' . http_build_query($query);
        // BioTime 9.x: /personnel/api/positions/ (plural; 8.5 used /position/)
        $endpoint = 'personnel/api/positions/' . $params;

        //leave options and undefined. guzzle will use the http:query;

        $response = $http->curlgetHttp($endpoint, $headr, []);
        if (!is_object($response) || empty($response->data) || !is_array($response->data)) {
            // Fallback for older BioTime 8.5 endpoints
            $endpoint = 'personnel/api/position/' . $params;
            $response = $http->curlgetHttp($endpoint, $headr, []);
        }
        //return $response;
        $j = array();
        $rows = $this->_biotime_list_rows($response);
        foreach ($rows as $jobs) {
            if (!is_object($jobs) || !isset($jobs->id)) {
                continue;
            }
            $data = array(
                'id' => $jobs->id,
                'position_code' => isset($jobs->position_code) ? $jobs->position_code : '',
                'position_name' => isset($jobs->position_name) ? $jobs->position_name : ''
            );

            array_push($j, $data);

        }
        // dd($j);

        $message = $this->biotimejobs_mdl->save_jobs($j);
        $process = 8;
        $method = "bioitimejobs/biotime_jobs";
        if ($response) {
            $status = "successful";
        } else {
            $status = "failed";
        }
        $this->cronjob_register($process, $method, $status);
        return $this->log($message);
    }
    public function biotimedepartments()
    {

        $http = new HttpUtils();
        $headr = array();
        $headr[] = 'Content-length: 0';
        $headr[] = 'Content-type: application/json';
        $headr[] = 'Authorization: JWT ' . $this->get_token();



        $query = array(
            'page_size' => 5000000
        );

        $params = '?' . http_build_query($query);
        // BioTime 9.x: /personnel/api/departments/ (plural; 8.5 used /department/)
        $endpoint = 'personnel/api/departments/' . $params;

        //leave options and undefined. guzzle will use the http:query;

        $response = $http->curlgetHttp($endpoint, $headr, []);
        if (!is_object($response) || empty($response->data) || !is_array($response->data)) {
            $endpoint = 'personnel/api/department/' . $params;
            $response = $http->curlgetHttp($endpoint, $headr, []);
        }
        //return $response;
        $j = array();
        $has_biotime_id = $this->db->field_exists('biotime_dept_id', 'biotime_departments');
        $rows = $this->_biotime_list_rows($response);
        foreach ($rows as $deps) {
            if (!is_object($deps) || !isset($deps->dept_code)) {
                continue;
            }
            $data = array(
                'dept_code' => $deps->dept_code,
                'dept_name' => isset($deps->dept_name) ? $deps->dept_name : ''
            );
            if ($has_biotime_id && isset($deps->id)) {
                $data['biotime_dept_id'] = (int) $deps->id;
            }
            // Note: id column is auto-increment, so we don't include it in the insert
            array_push($j, $data);
        }

        $message = $this->biotimejobs_mdl->save_department($j);
        $process = 9;
        $method = "bioitimejobs/biotimedepartments";
        if ($response) {
            $status = "successful";
        } else {
            $status = "failed";
        }
        $this->cronjob_register($process, $method, $status);

        return $this->log($message);
    }
    //clean
    public function create_jobs()
    {
    }
    public function facilities()
    {
        //get biotime_facilities
        //get ihris_facilities
        //if not exits in biotime_facilities create
        //method
        //personnel/api/areas/{area_code	area_name}

    }
    public function facility_departments()
    {
    }
    public function deleteEnrolled()
    {
    }
    // get all biotime deployments (BioTime 9.x employees list)
    public function fetch_biotime_employees($page = 1, $page_size = null)
    {
        date_default_timezone_set('Africa/Kampala');
        $http = new HttpUtils();
        $headers = [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'Authorization' => 'JWT ' . $this->get_token(),
        ];

        $page_size = ($page_size === null) ? $this->biotime_employee_page_size : (int) $page_size;
        $query = [
            'page' => max(1, (int) $page),
            'page_size' => max(1, $page_size),
        ];

        $params = '?' . http_build_query($query);
        $endpoint = 'personnel/api/employees/' . $params;

        return $http->getempData($endpoint, 'GET', $headers);
    }

    public function biotime_employees()
    {
        ignore_user_abort(true);
        ini_set('max_execution_time', 0);

        try {
            $page_size = $this->biotime_employee_page_size;
            $resp = $this->fetch_biotime_employees(1, $page_size);

            if (empty($resp) || !isset($resp->count)) {
                log_message('error', 'biotime_employees: Invalid response from fetch_biotime_employees()');
                $this->cronjob_register(7, 'bioitimejobs/biotime_employees', 'failed');
                return false;
            }

            $count = (int) $resp->count;
            $pages = $this->_biotime_list_pages($resp, $page_size);
            $saved = 0;
            $message = null;

            if ($count > 0) {
                $this->db->truncate('biotime_enrollment');
            }

            for ($currentPage = 1; $currentPage <= $pages; $currentPage++) {
                $response = ($currentPage === 1) ? $resp : $this->fetch_biotime_employees($currentPage, $page_size);
                $employees = $this->_biotime_list_rows($response);

                foreach ($employees as $mydata) {
                    if (!is_object($mydata) || !isset($mydata->emp_code) || !isset($mydata->id)) {
                        continue;
                    }

                    $area = $this->_biotime_employee_area($mydata);
                    // biotime_enrollment columns are NOT NULL — use empty string when area missing
                    $data = [
                        'emp_code' => (string) $mydata->emp_code,
                        'biotime_emp_id' => (string) (int) $mydata->id,
                        'biotime_facility_id' => ($area && isset($area->id)) ? (string) (int) $area->id : '',
                        'biotime_fac_id' => ($area && isset($area->area_code)) ? (string) $area->area_code : '',
                    ];
                    $message = $this->db->replace('biotime_enrollment', $data);
                    if ($message) {
                        $saved++;
                    }
                }
            }

            $process = 7;
            $method = 'bioitimejobs/biotime_employees';
            $status = ($saved > 0) ? 'successful' : 'failed';
            $this->cronjob_register($process, $method, $status);
            return $this->log($message ? $message : ('biotime_employees saved=' . $saved));
        } catch (Exception $e) {
            log_message('error', 'biotime_employees Exception: ' . $e->getMessage());
            $this->cronjob_register(7, 'bioitimejobs/biotime_employees', 'failed');
            return false;
        } catch (Error $e) {
            log_message('error', 'biotime_employees Fatal Error: ' . $e->getMessage());
            $this->cronjob_register(7, 'bioitimejobs/biotime_employees', 'failed');
            return false;
        }
    }

    /**
     * Sync facility/job changes for enrolled users (iHRIS vs biotime_enrollment).
     * Uses inline SQL equivalent of biotime_transfers (avoids broken view definer).
     */
    public function transfer_employees()
    {
        $query = $this->db->query(
            "SELECT i.*,
                    i.facility_id AS new_facility,
                    i.facility AS new_fname,
                    be.id AS enrollment_row_id,
                    be.emp_code,
                    be.biotime_emp_id,
                    be.biotime_facility_id AS biotime_area_id,
                    be.biotime_fac_id,
                    be.last_update AS enrollment_last_update
             FROM ihrisdata i
             INNER JOIN biotime_enrollment be ON be.emp_code = i.card_number
             WHERE i.facility_id <> be.biotime_fac_id
               AND i.card_number IS NOT NULL
               AND i.card_number <> ''"
        );
        $transfers = $query ? $query->result() : [];
        $ok = 0;
        $fail = 0;

        foreach ($transfers as $newuser) {
            $message = $this->update_biotimeuser($newuser);
            if ($message) {
                $ok++;
            } else {
                $fail++;
            }
        }

        $process = 5;
        $method = 'bioitimejobs/tranfer_employees';
        $status = ($ok > 0 && $fail === 0) ? 'successful' : (($ok > 0) ? 'partial' : (count($transfers) === 0 ? 'successful' : 'failed'));
        $this->cronjob_register($process, $method, $status);
        $this->log([
            'transfer_employees' => $status,
            'updated' => $ok,
            'failed' => $fail,
            'candidates' => count($transfers),
        ]);

        echo $status;
        return $status;
    }

    /**
     * CLI/browser smoke test for BioTime 9.5 employee sync (fixtures + optional live API).
     * Usage: php index.php biotimejobs testBioTime95Sync
     */
    public function testBioTime95Sync()
    {
        header('Content-Type: text/plain; charset=utf-8');
        $pass = 0;
        $fail = 0;
        $lines = [];
        $assert = function ($ok, $label) use (&$pass, &$fail, &$lines) {
            if ($ok) {
                $pass++;
                $lines[] = "[PASS] $label";
            } else {
                $fail++;
                $lines[] = "[FAIL] $label";
            }
        };

        $lines[] = '=== BioTime 9.5 employee sync smoke test ===';
        $lines[] = 'BIO_URL=' . (defined('BIO_URL') ? BIO_URL : '(undefined)');
        $lines[] = 'Time=' . date('Y-m-d H:i:s');

        // --- Fixture shaped like BioTime 9.5 employee list ---
        $fixture = json_decode(json_encode([
            'count' => 2,
            'next' => null,
            'previous' => null,
            'msg' => '',
            'code' => 0,
            'data' => [
                [
                    'id' => 91001,
                    'emp_code' => 'TESTBT95001',
                    'first_name' => 'Demo',
                    'last_name' => 'One',
                    'enroll_sn' => 'SN-TEST-001',
                    'enable_att' => true,
                    'area' => [
                        ['id' => 77, 'area_code' => 'facility|TEST77', 'area_name' => 'Test Facility 77'],
                    ],
                ],
                [
                    'id' => 91002,
                    'emp_code' => 'TESTBT95002',
                    'first_name' => 'Demo',
                    'last_name' => 'Two',
                    'enroll_sn' => '',
                    'attemployee' => [
                        'id' => 91002,
                        'enable_attendance' => false,
                    ],
                    'area' => [
                        ['id' => 88, 'area_code' => 'facility|TEST88', 'area_name' => 'Test Facility 88'],
                    ],
                ],
            ],
        ]));

        $rows = $this->_biotime_list_rows($fixture);
        $assert(count($rows) === 2, 'parse list rows from data[]');
        $assert($this->_biotime_list_pages($fixture, 100) === 1, 'page count with page_size=100');
        $assert($this->_biotime_list_pages($fixture, 1) === 2, 'page count with page_size=1');

        $area1 = $this->_biotime_employee_area($rows[0]);
        $assert($area1 && $area1->area_code === 'facility|TEST77', 'area array[0].area_code');
        $assert($this->_biotime_employee_att_status($rows[0]) === 1, 'enable_att true → att_status 1');
        $assert($this->_biotime_employee_att_status($rows[1]) === 0, 'attemployee.enable_attendance false → 0');

        // area as object (some 9.x payloads)
        $objEmp = (object) [
            'emp_code' => 'X',
            'area' => (object) ['id' => 1, 'area_code' => 'A1', 'area_name' => 'Area1'],
        ];
        $areaObj = $this->_biotime_employee_area($objEmp);
        $assert($areaObj && $areaObj->area_code === 'A1', 'area as object');

        // empty area
        $assert($this->_biotime_employee_area((object) ['emp_code' => 'Y', 'area' => []]) === null, 'empty area → null');

        // results[] alternate key
        $alt = (object) ['results' => $rows, 'count' => 2];
        $assert(count($this->_biotime_list_rows($alt)) === 2, 'parse list rows from results[]');

        // --- Persist enrolled staging (insert) ---
        $staging_rows = [];
        foreach ($rows as $mydata) {
            $area = $this->_biotime_employee_area($mydata);
            $staging_rows[] = [
                'entry_id' => $area->area_code . '-' . $mydata->emp_code,
                'card_number' => (string) $mydata->emp_code,
                'facilityId' => (string) $area->area_code,
                'source' => 'Biotime',
                'device' => isset($mydata->enroll_sn) ? (string) $mydata->enroll_sn : '',
                'att_status' => $this->_biotime_employee_att_status($mydata),
            ];
        }

        // Isolate test rows: delete previous test markers then insert via model path
        $this->db->where_in('card_number', ['TESTBT95001', 'TESTBT95002']);
        $this->db->delete('fingerprints_staging');

        $inserted = $this->db->insert_batch('fingerprints_staging', $staging_rows);
        $assert($inserted !== false && (int) $inserted === 2, 'insert fingerprints_staging (2 rows)');

        $q = $this->db->query("SELECT card_number, facilityId, att_status, device FROM fingerprints_staging WHERE card_number IN ('TESTBT95001','TESTBT95002') ORDER BY card_number");
        $got = $q ? $q->result() : [];
        $assert(count($got) === 2, 'staging rows readable');
        $assert(isset($got[0]) && (string) $got[0]->att_status === '1' && $got[0]->device === 'SN-TEST-001', 'staging row1 att/device');
        $assert(isset($got[1]) && (string) $got[1]->att_status === '0', 'staging row2 att_status 0');

        // --- Update staging (att_status flip) ---
        $this->db->where('card_number', 'TESTBT95001');
        $upd = $this->db->update('fingerprints_staging', ['att_status' => '0', 'device' => 'SN-UPDATED']);
        $assert($upd === true, 'update fingerprints_staging');
        $check = $this->db->query("SELECT att_status, device FROM fingerprints_staging WHERE card_number='TESTBT95001'")->row();
        $assert($check && (string) $check->att_status === '0' && $check->device === 'SN-UPDATED', 'staging update persisted');

        // --- biotime_enrollment replace insert + update ---
        $this->db->where_in('emp_code', ['TESTBT95001', 'TESTBT95002']);
        $this->db->delete('biotime_enrollment');

        foreach ($rows as $mydata) {
            $area = $this->_biotime_employee_area($mydata);
            $data = [
                'emp_code' => (string) $mydata->emp_code,
                'biotime_emp_id' => (string) (int) $mydata->id,
                'biotime_facility_id' => ($area && isset($area->id)) ? (string) (int) $area->id : '',
                'biotime_fac_id' => ($area && isset($area->area_code)) ? (string) $area->area_code : '',
            ];
            $assert($this->db->replace('biotime_enrollment', $data) === true, 'enrollment replace insert ' . $mydata->emp_code);
        }

        $enr = $this->db->query("SELECT emp_code, biotime_emp_id, biotime_facility_id, biotime_fac_id FROM biotime_enrollment WHERE emp_code='TESTBT95001'")->row();
        $assert($enr && $enr->biotime_emp_id === '91001' && $enr->biotime_fac_id === 'facility|TEST77', 'enrollment insert values');

        // update same emp_code via replace (facility change)
        $assert($this->db->replace('biotime_enrollment', [
            'emp_code' => 'TESTBT95001',
            'biotime_emp_id' => '91001',
            'biotime_facility_id' => '99',
            'biotime_fac_id' => 'facility|UPDATED99',
        ]) === true, 'enrollment replace update');
        $enr2 = $this->db->query("SELECT biotime_facility_id, biotime_fac_id FROM biotime_enrollment WHERE emp_code='TESTBT95001'")->row();
        $assert($enr2 && $enr2->biotime_facility_id === '99' && $enr2->biotime_fac_id === 'facility|UPDATED99', 'enrollment update persisted');

        // employee with missing area still saves with empty facility fields
        $assert($this->db->replace('biotime_enrollment', [
            'emp_code' => 'TESTBT95003',
            'biotime_emp_id' => '91003',
            'biotime_facility_id' => '',
            'biotime_fac_id' => '',
        ]) === true, 'enrollment without area (empty strings)');

        // --- Lookup helpers against existing synced tables ---
        $fac = $this->db->query('SELECT area_code FROM biotime_facilities LIMIT 1')->row();
        if ($fac) {
            $id = $this->getbioloc($fac->area_code);
            $assert(!empty($id), 'getbioloc resolves existing facility ' . $fac->area_code);
        } else {
            $lines[] = '[SKIP] getbioloc — biotime_facilities empty';
        }
        $job = $this->db->query('SELECT position_code FROM biotime_jobs WHERE position_code IS NOT NULL AND position_code <> "" LIMIT 1')->row();
        if ($job) {
            $jid = $this->getbiojobs($job->position_code);
            $assert(!empty($jid), 'getbiojobs resolves existing position ' . $job->position_code);
        } else {
            $lines[] = '[SKIP] getbiojobs — biotime_jobs empty';
        }
        $dep = $this->db->query('SELECT dept_code FROM biotime_departments WHERE dept_code IS NOT NULL AND dept_code <> "" LIMIT 1')->row();
        if ($dep) {
            $did = $this->getbiodeps($dep->dept_code);
            $assert(!empty($did), 'getbiodeps resolves existing department ' . $dep->dept_code);
        } else {
            $lines[] = '[SKIP] getbiodeps — biotime_departments empty';
        }

        // --- Live API probe (skip if BioTime host unreachable from this network) ---
        $lines[] = '--- Live API probe ---';
        $bioHostReachable = false;
        if (defined('BIO_URL')) {
            $parts = parse_url(BIO_URL);
            $host = isset($parts['host']) ? $parts['host'] : '';
            $port = isset($parts['port']) ? (int) $parts['port'] : ((isset($parts['scheme']) && $parts['scheme'] === 'https') ? 443 : 80);
            if ($host !== '') {
                $errno = 0;
                $errstr = '';
                $fp = @fsockopen($host, $port, $errno, $errstr, 3);
                if ($fp) {
                    $bioHostReachable = true;
                    fclose($fp);
                } else {
                    $lines[] = "[SKIP] live API — cannot connect to {$host}:{$port} ({$errstr})";
                }
            }
        }

        if ($bioHostReachable) {
            try {
                $token = $this->get_token();
                $assert(!empty($token), 'JWT token from /jwt-api-token-auth/');
                if (!empty($token)) {
                    $live = $this->get_Enrolled(1, 5);
                    $assert(is_object($live) && isset($live->count), 'GET /personnel/api/employees/?page=1&page_size=5');
                    if (is_object($live) && isset($live->count)) {
                        $lines[] = '[INFO] live employee count=' . (int) $live->count;
                        $liveRows = $this->_biotime_list_rows($live);
                        $lines[] = '[INFO] page1 rows=' . count($liveRows);
                        if (!empty($liveRows[0])) {
                            $a = $this->_biotime_employee_area($liveRows[0]);
                            $lines[] = '[INFO] sample emp_code=' . (isset($liveRows[0]->emp_code) ? $liveRows[0]->emp_code : '?')
                                . ' area=' . ($a && isset($a->area_code) ? $a->area_code : '(none)');
                        }
                    }
                }
            } catch (Exception $e) {
                $fail++;
                $lines[] = '[FAIL] live API: ' . $e->getMessage();
            } catch (Error $e) {
                $fail++;
                $lines[] = '[FAIL] live API fatal: ' . $e->getMessage();
            }
        }

        // cleanup test rows (keep DB clean)
        $this->db->where_in('card_number', ['TESTBT95001', 'TESTBT95002']);
        $this->db->delete('fingerprints_staging');
        $this->db->where_in('emp_code', ['TESTBT95001', 'TESTBT95002', 'TESTBT95003']);
        $this->db->delete('biotime_enrollment');
        $lines[] = '[INFO] cleaned test rows from staging/enrollment';

        $lines[] = "=== Result: $pass passed, $fail failed ===";
        echo implode("\n", $lines) . "\n";
        return ($fail === 0);
    }


    public function biotimeClockin($date = FALSE, $facility_name = null, $terminal_sn = null)
    {
        ignore_user_abort(true);
        ini_set('max_execution_time', 0);

		$message = $this->biotimeSyncAttendanceUnified($date, $facility_name, $terminal_sn);
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
    }

    /**
     * Run unified clock-in for each day in a date range (legacy / manual use).
     * NOT used by fetch_daily_attendance — that uses streaming + biotimeNightAndActualsOnly. Avoid calling both to prevent double processing.
     * @param string $start_date Y-m-d
     * @param string $end_date Y-m-d (inclusive)
     */
    public function biotimeClockinRange($start_date, $end_date)
    {
        $cursor = strtotime($start_date);
        $end_ts = strtotime($end_date . ' 23:59:59');
        while ($cursor <= $end_ts) {
            $day = date('Y-m-d', $cursor);
            $this->biotimeSyncAttendanceUnified($day, null, null);
            $day_start = $day . ' 00:00:00';
            $day_end   = date('Y-m-d H:i:s', strtotime($day . ' +1 day') - 1);
            $this->db->where('punch_time >=', $day_start);
            $this->db->where('punch_time <=', $day_end);
            $this->db->delete('biotime_data');
            $cursor = strtotime('+1 day', $cursor);
        }
    }

    /**
     * Run only actuals for a date range (clk_log + night already applied during streaming in the model).
     * Call after fetch_time_history_with_clocking for all devices. Night correction is done per-batch in the model to avoid deadlocks.
     * @param string $start_date Y-m-d
     * @param string $end_date Y-m-d (inclusive)
     * @param bool $clear_biotime_data If true, delete from biotime_data for this range (legacy cleanup; streaming writes to biotime_data_history only)
     * @return array [night_updated (0; done in model), actuals_updated]
     */
    public function biotimeNightAndActualsOnly($start_date, $end_date, $clear_biotime_data = true)
    {
        $start_dt = $start_date . ' 00:00:00';
        $end_dt   = $end_date . ' 23:59:59';
        $actualsUpdated = 0;
        $clkCandidates = 0;
        $actualsExisting = 0;
        $actualsPending = 0;

        // Night correction is applied per-batch in fetch_time_history_with_clocking; no separate night UPDATE here (avoids deadlocks)

        $stream_col = $this->db->field_exists('source', 'clk_log') ? 'cl.source' : 'NULL';
        // Debug counters: candidate clock rows vs already-existing actuals vs pending inserts.
        $q1 = $this->db->query("SELECT COUNT(DISTINCT CONCAT(date, ihris_pid)) AS n FROM clk_log WHERE date BETWEEN ? AND ?", [$start_date, $end_date]);
        if ($q1 && $q1->num_rows() > 0) {
            $clkCandidates = (int) $q1->row()->n;
        }
        $q2 = $this->db->query("SELECT COUNT(*) AS n FROM actuals WHERE date BETWEEN ? AND ? AND schedule_id = 22", [$start_date, $end_date]);
        if ($q2 && $q2->num_rows() > 0) {
            $actualsExisting = (int) $q2->row()->n;
        }
        $q3 = $this->db->query("
            SELECT COUNT(*) AS n
            FROM clk_log cl
            JOIN ihrisdata id ON id.ihris_pid = cl.ihris_pid
            LEFT JOIN actuals a ON a.entry_id = CONCAT(cl.date, id.ihris_pid)
            WHERE cl.date BETWEEN ? AND ?
              AND a.entry_id IS NULL
        ", [$start_date, $end_date]);
        if ($q3 && $q3->num_rows() > 0) {
            $actualsPending = (int) $q3->row()->n;
        }

        $this->db->trans_start();
        $this->db->query("
            INSERT INTO actuals (entry_id, facility_id, department_id, ihris_pid, schedule_id, color, date, end, stream)
            SELECT DISTINCT
                CONCAT(cl.date, id.ihris_pid),
                cl.facility_id,
                COALESCE(id.department_id, id.department),
                id.ihris_pid,
                s.schedule_id,
                s.color,
                cl.date,
                DATE_ADD(cl.date, INTERVAL 1 DAY),
                {$stream_col}
            FROM ihrisdata id
            JOIN clk_log cl ON id.ihris_pid = cl.ihris_pid
            JOIN schedules s ON s.schedule_id = 22
            LEFT JOIN actuals a ON a.entry_id = CONCAT(cl.date, id.ihris_pid)
            WHERE cl.date BETWEEN ? AND ?
            AND a.entry_id IS NULL
        ", [$start_date, $end_date]);
        $actualsUpdated = $this->db->affected_rows();
        $this->db->trans_complete();

        if ($clear_biotime_data) {
            $this->db->where('punch_time >=', $start_dt);
            $this->db->where('punch_time <=', $end_dt);
            $this->db->delete('biotime_data');
        }
        return array(
            'night_updated' => 0,
            'actuals_updated' => $actualsUpdated,
            'clk_candidates' => $clkCandidates,
            'actuals_existing' => $actualsExisting,
            'actuals_pending_before' => $actualsPending
        );
    }

    /**
     * Legacy unified sync: aggregate biotime_data → clk_log + night + actuals for a day.
     * NOT used by fetch_daily_attendance (which uses streaming). Used by biotimeClockin / biotimeClockinRange only.
     */
    public function biotimeSyncAttendanceUnified($date = null, $facility_name = null, $terminal_sn = null)
    {
        ignore_user_abort(true);
        ini_set('max_execution_time', 0);

        $syncDate    = $date ?? date('Y-m-d');
        $startDate   = date('Y-m-d', strtotime('-1 day', strtotime($syncDate)));
        $facilityLabel = $facility_name ? " [{$facility_name}]" : '';

        echo "\n========================================\n";
        echo " Unified Attendance Sync{$facilityLabel}\n";
        echo " Range: $startDate → $syncDate\n";
        echo "========================================\n";

        $terminalFilter = $terminal_sn ? ' AND b.terminal_sn = ?' : '';
        $aggParams = [$startDate, $syncDate];
        if ($terminal_sn) {
            $aggParams[] = $terminal_sn;
        }

        /*
        |--------------------------------------------------------------------------
        | 1️⃣ Single read from biotime_data → temp table (one pass for clock-in + clock-out)
        | Index on biotime_data (terminal_sn, punch_time) speeds this query.
        |--------------------------------------------------------------------------
        */
        $this->db->query("DROP TEMPORARY TABLE IF EXISTS _biotime_agg");
        $this->db->query("
            CREATE TEMPORARY TABLE _biotime_agg (
                log_date DATE NOT NULL,
                ihris_pid VARCHAR(224) NOT NULL,
                facility_id VARCHAR(223) NOT NULL,
                time_in DATETIME NOT NULL,
                time_out DATETIME NULL,
                location VARCHAR(100) NULL,
                facility VARCHAR(100) NOT NULL,
                PRIMARY KEY (log_date, ihris_pid)
            ) ENGINE=MEMORY
        ");
        $sqlAgg = "
            INSERT INTO _biotime_agg (log_date, ihris_pid, facility_id, time_in, time_out, location, facility)
            SELECT
                DATE(b.punch_time),
                i.ihris_pid,
                SUBSTRING_INDEX(GROUP_CONCAT(d.area_code ORDER BY b.punch_time), ',', 1),
                MIN(b.punch_time),
                MAX(b.punch_time),
                SUBSTRING_INDEX(GROUP_CONCAT(COALESCE(b.area_alias, d.area_name) ORDER BY b.punch_time), ',', 1),
                SUBSTRING_INDEX(GROUP_CONCAT(COALESCE(d.area_name, b.area_alias) ORDER BY b.punch_time), ',', 1)
            FROM biotime_data b
            JOIN biotime_devices d ON b.terminal_sn = d.sn
            JOIN ihrisdata i ON (b.emp_code = i.card_number OR b.emp_code = i.ipps)
            WHERE b.punch_time >= ?
            AND b.punch_time < DATE_ADD(?, INTERVAL 1 DAY)
            {$terminalFilter}
            GROUP BY DATE(b.punch_time), i.ihris_pid
        ";
        $this->db->query($sqlAgg, $aggParams);

        /*
        |--------------------------------------------------------------------------
        | 2️⃣ One INSERT into clk_log with ON DUPLICATE KEY UPDATE (faster than REPLACE; less lock time)
        |--------------------------------------------------------------------------
        */
        $this->db->trans_start();
        $this->db->query("
            INSERT INTO clk_log (entry_id, ihris_pid, facility_id, time_in, time_out, date, location, source, facility)
            SELECT
                CONCAT(a.log_date, a.ihris_pid),
                a.ihris_pid,
                a.facility_id,
                a.time_in,
                NULLIF(a.time_out, a.time_in),
                a.log_date,
                a.location,
                'BIO-TIME',
                a.facility
            FROM _biotime_agg a
            ON DUPLICATE KEY UPDATE
                time_in = VALUES(time_in),
                time_out = VALUES(time_out),
                facility_id = VALUES(facility_id),
                location = VALUES(location),
                facility = VALUES(facility),
                source = 'BIO-TIME'
        ");
        $clockinInserted = $this->db->affected_rows();
        $this->db->trans_complete();
        $rc = $this->db->query("SELECT COUNT(*) AS n FROM _biotime_agg WHERE time_out > time_in");
        $clockoutUpdated = $rc && $rc->num_rows() ? (int) $rc->row()->n : 0;

        /*
        |--------------------------------------------------------------------------
        | 3️⃣ Night shift correction (one UPDATE for range)
        |--------------------------------------------------------------------------
        */
        $this->db->trans_start();
        $this->db->query("
            UPDATE clk_log cl
            JOIN duty_rosta dr ON dr.ihris_pid = cl.ihris_pid AND dr.duty_date = cl.date
            JOIN biotime_data b ON b.punch_time >= ? AND b.punch_time < DATE_ADD(?, INTERVAL 1 DAY)
            JOIN ihrisdata i ON (b.emp_code = i.card_number OR b.emp_code = i.ipps) AND i.ihris_pid = cl.ihris_pid
            SET cl.time_out = b.punch_time
            WHERE dr.schedule_id = '16'
            AND cl.date BETWEEN ? AND ?
            AND b.punch_time > cl.time_in
            AND TIMESTAMPDIFF(HOUR, cl.time_in, b.punch_time) <= 15
        ", [$startDate, $syncDate, $startDate, $syncDate]);
        $nightUpdated = $this->db->affected_rows();
        $this->db->trans_complete();

        /*
        |--------------------------------------------------------------------------
        | 4️⃣ Actuals: one INSERT for full range (after clk_log is populated)
        |--------------------------------------------------------------------------
        */
        $stream_col = $this->db->field_exists('source', 'clk_log') ? 'cl.source' : 'NULL';
        $this->db->trans_start();
        $this->db->query("
            INSERT INTO actuals (entry_id, facility_id, department_id, ihris_pid, schedule_id, color, date, end, stream)
            SELECT DISTINCT
                CONCAT(cl.date, id.ihris_pid),
                cl.facility_id,
                COALESCE(id.department_id, id.department),
                id.ihris_pid,
                s.schedule_id,
                s.color,
                cl.date,
                DATE_ADD(cl.date, INTERVAL 1 DAY),
                {$stream_col}
            FROM ihrisdata id
            JOIN clk_log cl ON id.ihris_pid = cl.ihris_pid
            JOIN schedules s ON s.schedule_id = 22
            LEFT JOIN actuals a ON a.entry_id = CONCAT(cl.date, id.ihris_pid)
            WHERE cl.date BETWEEN ? AND ?
            AND a.entry_id IS NULL
        ", [$startDate, $syncDate]);
        $actualsUpdated = $this->db->affected_rows();
        $this->db->trans_complete();

        $this->db->query("DROP TEMPORARY TABLE IF EXISTS _biotime_agg");

        $total = $clockinInserted + $clockoutUpdated + $nightUpdated;

        echo "\nClock-in Inserted : $clockinInserted{$facilityLabel}\n";
        echo "Clock-out Updated : $clockoutUpdated{$facilityLabel}\n";
        echo "Night Corrected   : $nightUpdated{$facilityLabel}\n";
        echo "Actuals Updated   : $actualsUpdated{$facilityLabel}\n";
        echo "Total Affected    : $total{$facilityLabel}\n";

        $this->log("Unified Sync + Actuals: $total logs, $actualsUpdated actuals" . ($facility_name ? " [$facility_name]" : ''));

        return $total;
    }
	
    //rethink the clockin, clockin people as the data is fetched.
    public function biotimeClockout()
    {
        ignore_user_abort(true);
        ini_set('max_execution_time', 0);
        //$query = $this->db->query("SELECT concat(DATE(biotime_data.punch_time),ihrisdata.ihris_pid) as `entry_id`, punch_time from biotime_data,ihrisdata where (biotime_data.emp_code=ihrisdata.card_number or biotime_data.ihris_pid=ihrisdata.ihris_pid) AND (punch_state='1' OR punch_state='Check Out' OR punch_state='0') AND concat(DATE(biotime_data.punch_time),ihrisdata.ihris_pid) in (SELECT `entry_id` from clk_log) ");

        $query = $this->db->query("SELECT concat(DATE(biotime_data.punch_time),ihrisdata.ihris_pid) as `entry_id`, punch_time from biotime_data,ihrisdata where (biotime_data.emp_code=ihrisdata.card_number or biotime_data.emp_code=ihrisdata.ipps)  AND concat(DATE(biotime_data.punch_time),ihrisdata.ihris_pid) in (SELECT `entry_id` from clk_log) ");
        $entry_id = $query->result();

        foreach ($entry_id as $entry) {
            $final_time = strtotime($entry->punch_time) / 3600;
            if ($final_time > 0):
                $this->db->set('time_out', "$entry->punch_time");
                $this->db->where("time_in <", "$entry->punch_time");
                $this->db->where('entry_id', "$entry->entry_id");
                $query = $this->db->update('clk_log');
            endif;
        }
        //night shift

        echo $message = $this->db->affected_rows() . " Clocked Out";
        $this->log($message);
        
    }
    //clockout night people
    public function biotimeClockoutnight($dates=FALSE)
    {
        ignore_user_abort(true);
        ini_set('max_execution_time', 0);

        //get night shift people.
        if(!empty($today)){
        $today  = $dates;
       }
    
       else{
        $today = date('Y-m-d');

       }
        $yesterday = date($today, strtotime("-1 day"));

        $nights = $this->db->query("SELECT duty_date,duty_rosta.ihris_pid as person_id,entry_id,card_number from duty_rosta,ihrisdata where schedule_id='16' and ihrisdata.ihris_pid=duty_rosta.ihris_pid  and concat(duty_date,duty_rosta.ihris_pid) in (SELECT entry_id from clk_log WHERE date='$yesterday'
         )")->result();
        foreach ($nights as $night):
            //yesterdays entry_id 
            $nights = $yesterday . $night->person_id;

            $querys = $this->db->query("SELECT punch_time,punch_state from biotime_data,ihrisdata where (biotime_data.emp_code='$night->card_number') AND DATE(biotime_data.punch_time)='$today' ");
            $entry = $querys->row();
            //get time in for the log
            $timein = $this->db->query("select time_in from clk_log WHERE entry_id='$nights'")->row()->time_in;


            $initial_time = strtotime($timein) / 3600;
            $final_time = strtotime($entry->punch_time) / 3600;
            $hours_worked = round(($final_time - $initial_time), 1);
            //echo $final_time;
            if (($final_time > 0) && ($hours_worked <= 15)):
                $this->db->set('time_out', "$entry->punch_time");
                //  $this->db->where("time_in <","$entry->punch_time");
                //todays entry
                $this->db->where('entry_id', "$nights");
                $query = $this->db->update('clk_log');
                // print_r($entry);
                // echo "<br>";
            endif;



        endforeach;
        //night shift

        echo $message = $this->db->affected_rows() . " Clocked Out";

        $this->biotimeClockoutnight_ipps();
        $this->log($message);
     
    }

    //clockout night people ipps
    public function biotimeClockoutnight_ipps($dates = FALSE)
    {
        ignore_user_abort(true);
        ini_set('max_execution_time', 0);

        //get night shift people.
        if (!empty($today)) {
            $today = $dates;
        } else {
            $today = date('Y-m-d');

        }
        $yesterday = date($today, strtotime("-1 day"));

        $nights = $this->db->query("SELECT duty_date,duty_rosta.ihris_pid as person_id,entry_id,ipps as card_number from duty_rosta,ihrisdata where schedule_id='16' and ihrisdata.ihris_pid=duty_rosta.ihris_pid  and concat(duty_date,duty_rosta.ihris_pid) in (SELECT entry_id from clk_log WHERE date='$yesterday'
         )")->result();
        foreach ($nights as $night):
            //yesterdays entry_id 
            $nights = $yesterday . $night->person_id;

            $querys = $this->db->query("SELECT punch_time,punch_state from biotime_data,ihrisdata where (biotime_data.emp_code='$night->card_number') AND DATE(biotime_data.punch_time)='$today' ");
            $entry = $querys->row();
            //get time in for the log
            $timein = $this->db->query("select time_in from clk_log WHERE entry_id='$nights'")->row()->time_in;


            $initial_time = strtotime($timein) / 3600;
            $final_time = strtotime($entry->punch_time) / 3600;
            $hours_worked = round(($final_time - $initial_time), 1);
            //echo $final_time;
            if (($final_time > 0) && ($hours_worked <= 15)):
                $this->db->set('time_out', "$entry->punch_time");
                //  $this->db->where("time_in <","$entry->punch_time");
                //todays entry
                $this->db->where('entry_id', "$nights");
                $query = $this->db->update('clk_log');
                // print_r($entry);
                // echo "<br>";
            endif;



        endforeach;
        //night shift

        echo $message = $this->db->affected_rows() . " Clocked Out";
        $this->log($message);

    }
    public function markAttendance()
    {
        ini_set('max_execution_time', 0);
        //poplulate actuals
        $query = $this->db->query("CALL insert_actuals()");

        $rowsnow = $this->db->affected_rows();
        if ($query) {
          echo "\e[32m$rowsnow Attendance Records Marked\e[0m";
        } else {

           echo  "\e[31mFailed to Mark\e[0m";
        }
       
    }

    /**
     * Rosta to attendance: populate actuals from duty_rosta (schedules 17–21), then set leave (25) and offduty (24).
     * Runs monthly (e.g. from jobs master). Uses progress bar and more efficient queries.
     */
    public function rostatoAttend()
    {
        ignore_user_abort(true);
        ini_set('max_execution_time', 0);
        $ymonth = date('Y-m');
        $totalSteps = 3;
        $isCli = $this->input->is_cli_request();

        $progress = function ($step, $label) use ($totalSteps, $isCli) {
            $pct = (int) (($step / $totalSteps) * 100);
            $barLen = 40;
            $filled = (int) (($pct / 100) * $barLen);
            $bar = str_repeat('█', $filled) . str_repeat('-', $barLen - $filled);
            $line = "\r[$bar] {$pct}% | Step {$step}/{$totalSteps} | {$label}";
            if ($isCli) {
                echo $line;
                if (function_exists('flush')) {
                    flush();
                }
            }
            log_message('info', 'rostatoAttend: ' . $label);
        };

        // Step 1: Insert from duty_rosta into actuals (only rows not already in actuals) – LEFT JOIN instead of NOT IN for efficiency
        $progress(1, 'Insert duty_rosta → actuals');
        $this->db->trans_start();
        $sql1 = "INSERT INTO actuals (entry_id, facility_id, department_id, ihris_pid, schedule_id, color, date, end)
                 SELECT dr.entry_id, dr.facility_id, dr.department_id, dr.ihris_pid, dr.schedule_id, dr.color, dr.duty_date, dr.end
                 FROM duty_rosta dr
                 LEFT JOIN actuals a ON a.entry_id = dr.entry_id
                 WHERE dr.schedule_id IN (17, 18, 19, 20, 21)
                   AND DATE_FORMAT(dr.duty_date, '%Y-%m') <= " . $this->db->escape($ymonth) . "
                   AND a.entry_id IS NULL";
        $query1 = $this->db->query($sql1);
        $inserted = $this->db->affected_rows();
        $this->db->trans_complete();
        $msg1 = $query1 ? ($inserted . " attendance records marked") : "Failed to insert duty_rosta → actuals";
        if ($isCli) {
            echo "\n  → " . $msg1 . "\n";
        }
        $this->log('rostatoAttend: ' . $msg1);

        // Step 2: Mark leave (schedule 25)
        $progress(2, 'Mark leave (25)');
        $this->db->query("UPDATE actuals SET schedule_id = '25', color = '#29910d' WHERE schedule_id IN ('18','19','20','21')");
        $leaveRows = $this->db->affected_rows();
        $msg2 = $leaveRows . " leave records recognised";
        if ($isCli) {
            echo "\n  → " . $msg2 . "\n";
        }
        $this->log('rostatoAttend: ' . $msg2);

        // Step 3: Mark offduty (schedule 24)
        $progress(3, 'Mark offduty (24)');
        $this->db->query("UPDATE actuals SET schedule_id = '24', color = '#d1a110' WHERE schedule_id = '17'");
        $offdutyRows = $this->db->affected_rows();
        $msg3 = $offdutyRows . " offduty records recognised";
        if ($isCli) {
            echo "\n  → " . $msg3 . "\n";
        }
        $this->log('rostatoAttend: ' . $msg3);

        if ($isCli) {
            echo "\r[" . str_repeat('█', 40) . "] 100% | Step 3/3 | Done\n";
        }
        log_message('info', 'rostatoAttend: completed. Inserted=' . $inserted . ', leave=' . $leaveRows . ', offduty=' . $offdutyRows);
    }

    public function cronjob_register($process, $method, $status)
    {
        $data = array('process_id' => $process, 'process' => $method, 'status' => $status);
        $this->db->replace("cronjob_register", $data);
    }
    /**
     * Fetch time history with streaming: clock-in/clock-out merged into clk_log per batch as we fetch.
     * One call per device for the full range; no separate aggregation step. Run biotimeNightAndActualsOnly after all devices.
     *
     * Strategy: Clock-in/out are applied per batch (e.g. 50 rows) as we read from PostgreSQL, so we avoid
     * one large aggregation over biotime_data at the end. Drawbacks: (1) Night correction and actuals still
     * run once after all devices (they need full day/range data). (2) Per-record would be slower (many more
     * DB round-trips); per-batch is a balance of throughput and latency.
     * Efficiency: Fewer long locks, no big temp table; same PG read, more but smaller MySQL writes.
     *
     * @param string $start_date Start date Y-m-d
     * @param string $end_date End date Y-m-d
     * @param string|bool $area_alias Area name (matches area_name in biotime_devices and area_alias in PG; false = all areas)
     * @param string|bool $facility Facility name (for logging)
     * @param bool $output_console Whether to echo progress
     * @return array status, message, total_records
     */
    public function fetch_time_history_streaming($start_date, $end_date, $area_alias = FALSE, $facility = FALSE, $output_console = TRUE)
    {
        ignore_user_abort(true);
        set_time_limit(0);
        $console = function ($msg, $type = 'info') use ($output_console) {
            if ($output_console) {
                $p = ($type === 'success') ? '✓' : (($type === 'error') ? '✗' : '→');
                echo '[' . date('Y-m-d H:i:s') . "] $p $msg\n";
                if (ob_get_level() > 0) { ob_flush(); }
                flush();
            }
        };
        $result = array('status' => 'error', 'message' => '', 'total_records' => 0);
        try {
            $this->db->query("SET SESSION innodb_lock_wait_timeout = 120");
            $start_ts = $start_date . ' 00:00:00';
            $end_ts   = $end_date . ' 23:59:59';
            $max_retries = 3;
            $delay = 5;
            for ($attempt = 1; $attempt <= $max_retries; $attempt++) {
                try {
                    $r = $this->biotimejobs_mdl->fetch_time_history_with_clocking($start_ts, $end_ts, $area_alias, FALSE, 50);
                    $result['status'] = $r['status'];
                    $result['message'] = $r['message'];
                    $result['total_records'] = isset($r['records_saved']) ? (int) $r['records_saved'] : 0;
                    $result['timing'] = isset($r['timing']) ? $r['timing'] : array();
                    if ($output_console && !empty($r['clock_log_merged'])) {
                        $console("Clock-log merged: " . $r['clock_log_merged'], 'info');
                    }
                    if ($output_console && !empty($r['night_corrected'])) {
                        $console("Night corrected: " . $r['night_corrected'], 'info');
                    }
                    if ($output_console && isset($r['actuals_merged']) && $r['actuals_merged'] > 0) {
                        $console("Actuals (from clock-in): " . $r['actuals_merged'], 'info');
                    }
                    if ($output_console && isset($r['debug']) && is_array($r['debug'])) {
                        $d = $r['debug'];
                        $console(
                            "Debug — fetched: " . ($r['records_fetched'] ?? 0) .
                            " | mapped: " . ($d['mapped_rows'] ?? 0) .
                            " | unmapped_emp: " . ($d['unmapped_emp_rows'] ?? 0) .
                            " | missing_area_map: " . ($d['missing_device_area_rows'] ?? 0) .
                            " | emp_map_keys: " . ($d['lookup_emp_map_keys'] ?? 0) .
                            " | device_areas: " . ($d['lookup_device_areas'] ?? 0),
                            'info'
                        );
                        if (!empty($d['unmapped_emp_samples']) && is_array($d['unmapped_emp_samples'])) {
                            $console("Debug unmatched samples: " . json_encode($d['unmapped_emp_samples']), 'warning');
                        }
                    }
                    if ($output_console && !empty($r['timing'])) {
                        $t = $r['timing'];
                        $console("Time — total: " . ($t['total_s'] ?? 0) . "s | PG: " . ($t['pg_query_s'] ?? 0) . "s | lookups: " . ($t['lookups_s'] ?? 0) . "s | history: " . ($t['history_s'] ?? 0) . "s | agg: " . ($t['aggregate_s'] ?? 0) . "s | clk_log: " . ($t['clk_log_s'] ?? 0) . "s | actuals: " . ($t['actuals_s'] ?? 0) . "s | night: " . ($t['night_s'] ?? 0) . "s", 'info');
                    }
                    return $result;
                } catch (Exception $e) {
                    $is_lock = (strpos($e->getMessage(), 'Lock wait timeout exceeded') !== false);
                    if ($is_lock && $attempt < $max_retries) {
                        $console("Lock wait, retry $attempt/$max_retries in {$delay}s...", 'warning');
                        sleep($delay);
                        continue;
                    }
                    $result['message'] = $e->getMessage();
                    return $result;
                }
            }
        } catch (Exception $e) {
            $result['message'] = $e->getMessage();
        }
        return $result;
    }

    /**
     * Fetch time history for a date range, processing day by day
     * Uses PostgreSQL database for faster data retrieval
     * 
     * @param string $start_date Start date in Y-m-d format
     * @param string $end_date End date in Y-m-d format
     * @param string|bool $terminal_sn Terminal serial number (default: FALSE = all terminals)
     * @param string|bool $facility Facility name (for logging, default: FALSE)
     * @param string|bool $empcode Employee code filter (default: FALSE = all employees)
     * @param callable|null $progress_callback Optional callback function for progress updates
     * @param bool $output_console Whether to output console messages (default: true)
     * @return array Result array with status, message, and statistics
     */
    public function fetch_time_history($start_date, $end_date, $terminal_sn = FALSE, $facility = FALSE, $empcode = FALSE, $progress_callback = NULL, $output_console = TRUE)
    {
        ignore_user_abort(true);
        set_time_limit(0);
        ini_set('max_execution_time', 0);
        ini_set('memory_limit', '512M');
        
        $result = array(
            'status' => 'error',
            'message' => '',
            'dates_processed' => 0,
            'total_records' => 0,
            'errors' => array(),
            'daily_stats' => array()
        );
        
        // Console output helper
        $console = function($message, $type = 'info') use ($output_console) {
            if ($output_console) {
                $timestamp = date('Y-m-d H:i:s');
                $prefix = '';
                switch($type) {
                    case 'success':
                        $prefix = '✓';
                        break;
                    case 'error':
                        $prefix = '✗';
                        break;
                    case 'warning':
                        $prefix = '⚠';
                        break;
                    case 'info':
                    default:
                        $prefix = '→';
                        break;
                }
                echo "[$timestamp] $prefix $message\n";
                if (ob_get_level() > 0) {
                    ob_flush();
                }
                flush();
            }
        };
        
        try {
            // Validate dates
            $currentDate = strtotime($start_date);
            $endDate = strtotime($end_date);
            
            if ($currentDate === FALSE || $endDate === FALSE) {
                throw new Exception("Invalid date format. Expected Y-m-d format.");
            }
            
            if ($currentDate > $endDate) {
                throw new Exception("Start date must be before or equal to end date.");
            }
            
            // Calculate total days
            $total_days = (int) ceil(($endDate - $currentDate) / 86400) + 1;
            $days_processed = 0;
            
            $console("=== Starting Time History Sync ===", 'info');
            $console("Date Range: $start_date to $end_date ($total_days days)", 'info');
            if ($terminal_sn) {
                $console("Terminal: $terminal_sn", 'info');
            } 
            if ($facility) {
                $console("Facility: $facility", 'info');
            }
            $console("", 'info');

            // Allow this job to wait longer for MySQL locks (default 50s often too short under load)
            $this->db->query("SET SESSION innodb_lock_wait_timeout = 120");
            
            // Loop through each date
        while ($currentDate <= $endDate) {
            $dates = date('Y-m-d', $currentDate);
                $day_start = $dates . ' 00:00:00';
                $day_end = $dates . ' 23:59:59';
                $day_number = $days_processed + 1;
                
                $console("[$day_number/$total_days] Processing date: $dates", 'info');

                // Progress callback
                if (is_callable($progress_callback)) {
                    call_user_func($progress_callback, array(
                        'date' => $dates,
                        'day' => $day_number,
                        'total_days' => $total_days,
                        'terminal_sn' => $terminal_sn,
                        'facility' => $facility
                    ));
                }

                $fetch_result = null;
                $max_retries = 3;
                $retry_delay_seconds = 5;
                for ($attempt = 1; $attempt <= $max_retries; $attempt++) {
                    try {
                        $fetch_result = $this->biotimejobs_mdl->fetch_time_history($day_start, $day_end, $terminal_sn, $empcode);
                        break;
                    } catch (Exception $e) {
                        $is_lock_wait = (strpos($e->getMessage(), 'Lock wait timeout exceeded') !== false);
                        if ($is_lock_wait && $attempt < $max_retries) {
                            $console("  ⚠ Lock wait, retry $attempt/$max_retries in {$retry_delay_seconds}s...", 'warning');
                            sleep($retry_delay_seconds);
                            continue;
                        }
                        $fetch_result = array('status' => 'error', 'records_fetched' => 0, 'records_saved' => 0, 'message' => $e->getMessage(), 'errors' => array());
                        break;
                    }
                }
                
                $daily_stat = array(
                    'date' => $dates,
                    'status' => $fetch_result['status'],
                    'records_fetched' => $fetch_result['records_fetched'],
                    'records_saved' => $fetch_result['records_saved'],
                    'message' => $fetch_result['message']
                );
                
                if ($fetch_result['status'] === 'success') {
                    $result['total_records'] += $fetch_result['records_saved'];
                    $days_processed++;
                    
                    $console("  ✓ Fetched: {$fetch_result['records_fetched']} | Saved: {$fetch_result['records_saved']}", 'success');
                    
                    // Clock-in is run once after all machines are synced (in fetch_daily_attendance), not per day per machine
                    $this->log("fetch_time_history() processed date $dates: " . $fetch_result['records_saved'] . " records saved");
                } else {
                    $error_msg = "Failed to fetch data for date $dates: " . $fetch_result['message'];
                    $result['errors'][] = $error_msg;
                    $daily_stat['errors'] = isset($fetch_result['errors']) ? $fetch_result['errors'] : array();
                    $console("  ✗ Error: " . $fetch_result['message'], 'error');
                    $this->log("fetch_time_history() error: $error_msg");
                }
                
                $result['daily_stats'][] = $daily_stat;

            // Increment current date by 1 day
            $currentDate = strtotime('+1 day', $currentDate);
            }
            
            $result['dates_processed'] = $days_processed;
            $result['status'] = 'success';
            $result['message'] = "Successfully processed $days_processed of $total_days days. Total records: " . $result['total_records'];
            
            $console("", 'info');
            $console("=== Sync Summary ===", 'info');
            $console("Days Processed: $days_processed / $total_days", 'info');
            $console("Total Records: " . $result['total_records'], 'info');
            if (!empty($result['errors'])) {
                $console("Errors: " . count($result['errors']), 'warning');
            }
            $console("=== Sync Completed ===", 'success');
            
            $this->log("fetch_time_history() completed: " . $result['message']);
            
        } catch (Exception $e) {
            $result['status'] = 'error';
            $result['message'] = "Error: " . $e->getMessage();
            $result['errors'][] = $e->getMessage();
            $console("✗ Fatal Error: " . $e->getMessage(), 'error');
            $this->log("fetch_time_history() exception: " . $e->getMessage());
        } catch (Error $e) {
            $result['status'] = 'error';
            $result['message'] = "Fatal Error: " . $e->getMessage();
            $result['errors'][] = $e->getMessage();
            $console("✗ Fatal Error: " . $e->getMessage(), 'error');
            $this->log("fetch_time_history() fatal error: " . $e->getMessage());
        }
        
        return $result;
    }

    /**
     * Fetch daily attendance for all machines (canonical daily sync).
     * Uses only: fetch_time_history_streaming (model does clock-in/out + night + actuals per batch), then biotimeNightAndActualsOnly (actuals backfill + clear).
     * Does NOT use: biotimeClockin, biotimeClockinRange, biotimeSyncAttendanceUnified, or the old day-by-day fetch_time_history.
     *
     * @param string|bool $end_date End date in Y-m-d format (default: FALSE = current date)
     * @param int $max_days Maximum number of days to sync per machine (default: 365)
     * @param string|bool $specific_device Specific device SN to sync (default: FALSE = all devices)
     * @param bool $output_console Whether to output console messages (default: true)
     * @return array Result array with status, message, and statistics per machine
     */
    public function fetch_daily_attendance($end_date = FALSE, $max_days = 365, $specific_device = FALSE, $output_console = TRUE)
    {
        ignore_user_abort(true);
        set_time_limit(0);
        ini_set('max_execution_time', 0);
        ini_set('memory_limit', '512M');
        
        $result = array(
            'status' => 'error',
            'message' => '',
            'machines_processed' => 0,
            'machines_total' => 0,
            'total_records' => 0,
            'machine_results' => array(),
            'errors' => array()
        );
        
        // Console output helper
        $console = function($message, $type = 'info') use ($output_console) {
            if ($output_console) {
                $timestamp = date('Y-m-d H:i:s');
                $prefix = '';
                switch($type) {
                    case 'success':
                        $prefix = '✓';
                        break;
                    case 'error':
                        $prefix = '✗';
                        break;
                    case 'warning':
                        $prefix = '⚠';
                        break;
                    case 'info':
                    default:
                        $prefix = '→';
                        break;
                }
                echo "[$timestamp] $prefix $message\n";
                if (ob_get_level() > 0) {
                    ob_flush();
                }
                flush();
            }
        };
        
        try {
            // Set end date
            if (empty($end_date)) {
        $end_date = date('Y-m-d');
            }
            
            $console("═══════════════════════════════════════════════════════", 'info');
            $console("  DAILY ATTENDANCE SYNC - BY AREA (area_name = area_alias)", 'info');
            $console("═══════════════════════════════════════════════════════", 'info');
            $console("End Date: $end_date", 'info');
            $console("Max Days Per Area: $max_days", 'info');
            if ($specific_device) {
                $console("Specific Device (filter to its area only): $specific_device", 'info');
            }
            $console("", 'info');
            
            // Distinct area_name from biotime_devices (area_name matches area_alias on PostgreSQL; one area can have multiple machines)
            $query = "SELECT area_name, MAX(last_activity) AS last_activity FROM biotime_devices";
            if (!empty($specific_device)) {
                $query .= " WHERE sn = " . $this->db->escape($specific_device);
            }
            $query .= " GROUP BY area_name ORDER BY area_name ASC";
            $areas = $this->db->query($query)->result();
            
            if (empty($areas)) {
                throw new Exception("No areas found in biotime_devices");
            }
            
            $result['machines_total'] = count($areas);
            $machines_processed = 0;
            $sync_date_range_start = null;
            
            $console("Found " . $result['machines_total'] . " area(s) to sync (area_name = area_alias in PG)", 'info');
            $console("", 'info');
            
            foreach ($areas as $area_index => $area_row) {
                $area_name = isset($area_row->area_name) ? $area_row->area_name : '';
                if ($area_name === '') {
                    continue;
                }
                $area_num = $area_index + 1;
                $facility = $area_name;
                
                $console("─────────────────────────────────────────────────────", 'info');
                $console("AREA [$area_num/{$result['machines_total']}]: $area_name", 'info');
                $console("Facility: $facility", 'info');
                
                $machine_result = array(
                    'device' => $area_name,
                    'facility' => $facility,
                    'status' => 'error',
                    'records' => 0,
                    'message' => ''
                );
                
                try {
                    $last_activity = isset($area_row->last_activity) && !empty($area_row->last_activity)
                        ? $area_row->last_activity
                        : NULL;
                    
                    if ($last_activity) {
                        $last_activity_date = date('Y-m-d', strtotime($last_activity));
                        if ($last_activity_date === '1970-01-01' || $last_activity_date === FALSE) {
                            $last_activity_date = NULL;
                        }
                    } else {
                        $last_activity_date = NULL;
                    }
                    
                    if ($last_activity_date) {
                        $start_timestamp = strtotime($last_activity_date . ' -1 day');
                        $start = date('Y-m-d', $start_timestamp);
                    } else {
                        $start = date('Y-m-d', strtotime('-7 days'));
                    }
                    
                    $start_timestamp = strtotime($start);
                    $end_timestamp = strtotime($end_date);
                    $difference_seconds = $end_timestamp - $start_timestamp;
            $difference_days = $difference_seconds / (60 * 60 * 24);
                    
                    $last_activity_timestamp = $last_activity_date ? strtotime($last_activity_date) : 0;
                    $end_date_timestamp = strtotime($end_date);
                    $is_already_synced = $last_activity_timestamp > $end_date_timestamp;
                    
                    $console("Date Range: $start to $end_date ($difference_days days)", 'info');
                    $console("Last Activity: " . ($last_activity ?: 'Never'), 'info');
                    
                    if ($is_already_synced) {
                        $machine_result['status'] = 'skipped';
                        $machine_result['message'] = "Already up to date (last activity: $last_activity_date > end date: $end_date)";
                        $console("✓ Skipped: Already up to date (last activity: $last_activity_date)", 'info');
                        $this->log("fetch_daily_attendance() skipped area $area_name: already up to date");
                    } elseif ($difference_days < 0) {
                        $machine_result['status'] = 'skipped';
                        $machine_result['message'] = "Invalid date range (start date after end date)";
                        $console("⚠ Skipped: Invalid date range", 'warning');
                        $this->log("fetch_daily_attendance() skipped area $area_name: invalid date range");
                    } elseif ($difference_days > $max_days) {
                        $machine_result['status'] = 'skipped';
                        $machine_result['message'] = "Date range too large ($difference_days days, max: $max_days)";
                        $console("⚠ Skipped: Date range too large ($difference_days days, max: $max_days)", 'warning');
                        $this->log("fetch_daily_attendance() skipped area $area_name: date range too large ($difference_days days)");
                    } else {
                        $console("Starting sync...", 'info');
                        $this->log("fetch_daily_attendance() starting sync for area $area_name from $start to $end_date");
                        
                        if ($sync_date_range_start === null || strtotime($start) < strtotime($sync_date_range_start)) {
                            $sync_date_range_start = $start;
                        }
                        
                        $fetch_result = $this->fetch_time_history_streaming($start, $end_date, $area_name, $facility, $output_console);
                        
                        if ($fetch_result['status'] === 'success') {
                            $machine_result['status'] = 'success';
                            $machine_result['records'] = $fetch_result['total_records'];
                            $machine_result['message'] = $fetch_result['message'];
                            $result['total_records'] += $fetch_result['total_records'];
                            $machines_processed++;
                            
                            $this->db->where('area_name', $area_name);
                            $this->db->update('biotime_devices', array('last_activity' => date('Y-m-d H:i:s')));
                            
                            $console("✓ Sync completed: {$fetch_result['total_records']} records", 'success');
                            $this->log("fetch_daily_attendance() completed for area $area_name: " . $fetch_result['total_records'] . " records");
                        } else {
                            $machine_result['message'] = $fetch_result['message'];
                            $result['errors'][] = "Area $area_name: " . $fetch_result['message'];
                            $console("✗ Sync failed: " . $fetch_result['message'], 'error');
                            $this->log("fetch_daily_attendance() failed for area $area_name: " . $fetch_result['message']);
                        }
                    }
                    
                } catch (Exception $e) {
                    $machine_result['message'] = "Error: " . $e->getMessage();
                    $result['errors'][] = "Area $area_name: " . $e->getMessage();
                    $console("✗ Exception: " . $e->getMessage(), 'error');
                    $this->log("fetch_daily_attendance() exception for area $area_name: " . $e->getMessage());
                } catch (Error $e) {
                    $machine_result['message'] = "Fatal Error: " . $e->getMessage();
                    $result['errors'][] = "Area $area_name: " . $e->getMessage();
                    $console("✗ Fatal Error: " . $e->getMessage(), 'error');
                    $this->log("fetch_daily_attendance() fatal error for area $area_name: " . $e->getMessage());
                }
                
                $result['machine_results'][] = $machine_result;
                $console("", 'info');
            }
            
            // Night-shift correction + actuals only (clk_log already filled by streaming fetch); run in chunks
            if ($sync_date_range_start !== null && $machines_processed > 0) {
                $total_days = (int) ceil((strtotime($end_date . ' 23:59:59') - strtotime($sync_date_range_start)) / 86400) + 1;
                $chunk_size = ($total_days > 10) ? 10 : 3;
                $console("", 'info');
                $console("═══════════════════════════════════════════════════════", 'info');
                $console("  ACTUALS (clock-in/out + night applied during stream)", 'info');
                $console("  Date range: $sync_date_range_start → $end_date (chunk size: $chunk_size)", 'info');
                $console("═══════════════════════════════════════════════════════", 'info');
                $cursor = strtotime($sync_date_range_start);
                $end_ts = strtotime($end_date . ' 23:59:59');
                $chunk_num = 0;
                $days_done = 0;
                while ($cursor <= $end_ts) {
                    $chunk_start = date('Y-m-d', $cursor);
                    $chunk_end_ts = strtotime("+{$chunk_size} days", $cursor) - 1;
                    if ($chunk_end_ts > $end_ts) {
                        $chunk_end_ts = $end_ts;
                    }
                    $chunk_end = date('Y-m-d', $chunk_end_ts);
                    $chunk_num++;
                    try {
                        $stats = $this->biotimeNightAndActualsOnly($chunk_start, $chunk_end, true);
                        $chunk_days = (int) ceil((strtotime($chunk_end . ' 23:59:59') - strtotime($chunk_start)) / 86400) + 1;
                        $days_done += $chunk_days;
                        $console(
                            "  ✓ Chunk $chunk_num: $chunk_start → $chunk_end (actuals inserted: {$stats['actuals_updated']} | clk candidates: {$stats['clk_candidates']} | existing actuals: {$stats['actuals_existing']} | pending before insert: {$stats['actuals_pending_before']})",
                            'success'
                        );
                    } catch (Exception $e) {
                        $console("  ✗ Chunk $chunk_num ($chunk_start → $chunk_end) failed: " . $e->getMessage(), 'error');
                        $this->log("fetch_daily_attendance() night/actuals chunk failed: " . $e->getMessage());
                    } catch (Error $e) {
                        $console("  ✗ Chunk $chunk_num ($chunk_start → $chunk_end) error: " . $e->getMessage(), 'error');
                        $this->log("fetch_daily_attendance() night/actuals chunk error: " . $e->getMessage());
                    }
                    $cursor = strtotime("+{$chunk_size} days", $cursor);
                }
                $console("  ✓ Night + actuals completed for $days_done day(s) in $chunk_num chunk(s)", 'success');
                $console("", 'info');
            }
            
            // Sync terminals after processing all machines
            $console("Syncing terminal information...", 'info');
        $this->terminals();
            $console("✓ Terminal sync completed", 'success');
            $console("", 'info');
            
            $result['machines_processed'] = $machines_processed;
            $result['status'] = ($machines_processed > 0) ? 'success' : 'error';
            $result['message'] = "Processed $machines_processed of " . $result['machines_total'] . " machines. Total records: " . $result['total_records'];
            
            $console("═══════════════════════════════════════════════════════", 'info');
            $console("  SYNC SUMMARY", 'info');
            $console("═══════════════════════════════════════════════════════", 'info');
            $console("Machines Processed: $machines_processed / {$result['machines_total']}", 'info');
            $console("Total Records: " . $result['total_records'], 'info');
            if (!empty($result['errors'])) {
                $console("Errors: " . count($result['errors']), 'warning');
            }
            $console("═══════════════════════════════════════════════════════", 'info');
            
            $this->log("fetch_daily_attendance() completed: " . $result['message']);
            
        } catch (Exception $e) {
            $result['status'] = 'error';
            $result['message'] = "Error: " . $e->getMessage();
            $result['errors'][] = $e->getMessage();
            $console("✗ Fatal Error: " . $e->getMessage(), 'error');
            $this->log("fetch_daily_attendance() exception: " . $e->getMessage());
        } catch (Error $e) {
            $result['status'] = 'error';
            $result['message'] = "Fatal Error: " . $e->getMessage();
            $result['errors'][] = $e->getMessage();
            $console("✗ Fatal Error: " . $e->getMessage(), 'error');
            $this->log("fetch_daily_attendance() fatal error: " . $e->getMessage());
        }
        
        return $result;
    }

    public function daily_logs($ihris_id,$date){
        $ihris_pid = urldecode($ihris_id);
        $this->db->where("date", "$date");
        $this->db->where("ihris_pid","$ihris_pid");
       $data =  $this->db->get('clk_log')->row();
    echo json_encode($data);
    }

    // public function attendance_data($valid_range, $district = FALSE, $facility_id = FALSE)
    // {
    //     // Set the default date range if not provided
    //     if (empty($valid_range)) {
    //         $valid_range = date('Y-m');
    //     }

    //     // Decode URL parameters
    //     $facility = urldecode($facility_id);
    //     $district = ucwords(urldecode($district));

    //     // Initialize necessary variables
    //     $empid = "";
    //     $dep = "";

    //     // Fetch attendance summary data
    //     $datas = $this->attendance_model->attendance_summary($valid_range, $this->filters, $config['per_page'] = NULL, $page = NULL, $district, $facility, $empid, $dep, 'api');

    //     // Pre-fetch fields to reduce redundant database queries
    //     $ihris_pids = array_column($datas, 'ihris_pid');
    //     $fields = $this->get_fields_for_ihris_pids($ihris_pids, ['card_number', 'nin', 'ipps']);

    //     $attendanceData = [];

    //     foreach ($datas as $data) {
    //         $ihris_pid = $data['ihris_pid'];

    //         // Fetch roster data
    //         $roster = Modules::run('attendance/attrosta', $valid_range, urlencode($ihris_pid));

    //         // Use pre-fetched fields
    //         $cardnumber = $fields[$ihris_pid]['card_number'];
    //         $nin = $fields[$ihris_pid]['nin'];
    //         $ipps = $fields[$ihris_pid]['ipps'];

    //         $present = !empty($data['P']) ? $data['P'] : 0;
    //         $off = !empty($data['O']) ? $data['O'] : 0;
    //         $leave = !empty($data['L']) ? $data['L'] : 0;
    //         $request = !empty($data['R']) ? $data['R'] : 0;
    //         $holiday = !empty($data['H']) ? $data['H'] : 0;
    //         $duty_date = $data['duty_date'];

    //         $eve = isset($roster['Evening'][0]) ? $roster['Evening'][0]->days : 0;
    //         $day = isset($roster['Day'][0]) ? $roster['Day'][0]->days : 0;
    //         $night = isset($roster['Night'][0]) ? $roster['Night'][0]->days : 0;
    //         $r_days = ($eve + $day + $night);
    //         if ($r_days == 0) {
    //             $r_days = 22;
    //         }

    //         $absent = days_absent_helper($present, $r_days);
    //         $per = per_present_helper($present, $r_days);

    //         // Construct the normal JSON data structure
    //         $attendance = [
    //             "ihris_pid" => $ihris_pid,
    //             "ipps" => $ipps,
    //             "nin" => $nin,
    //             "card_number" => $cardnumber,
    //             "facility_id" => $data["facility_id"],
    //             "district" => $data["district"],
    //             "Name" => $data['fullname'],
    //             "Job" => $data['job'],
    //             "Department" => $data['department_id'],
    //             "Duty Date" => $duty_date,
    //             "Off Duty" => $off,
    //             "Official Request" => $request,
    //             "Leave" => $leave,
    //             "Holiday" => $holiday,
    //             "Total Days Expected at Work" => $r_days,
    //             "Total Days Worked" => $present,
    //             "Total Days Absent" => $absent,
    //             "% Present" => $per
    //         ];

    //         $attendanceData[] = $attendance;
    //     }

    //     echo json_encode($attendanceData);
    // }

    private function get_fields_for_ihris_pids($ihris_pids, $fields)
    {
        $this->db->select('ihris_pid, ' . implode(', ', $fields));
        $this->db->from('ihrisdata');
        $this->db->where_in('ihris_pid', $ihris_pids);
        $query = $this->db->get();

        $result = [];
        foreach ($query->result_array() as $row) {
            $result[$row['ihris_pid']] = $row;
        }

        return $result;
    }


    public function attendance_data($fhir,$valid_range, $district = FALSE, $facility_id = FALSE)
    {
        // Set the default date range if not provided
        if (empty($valid_range)) {
            $valid_range = date('Y-m');
        }

        // Decode URL parameters
        $facility = urldecode($facility_id);
        $district = ucwords(urldecode($district));

        // Initialize necessary variables
        $empid = "";
        $dep = "";

        // Fetch attendance summary data
        $datas = $this->attendance_model->attendance5_summary($valid_range, $this->filters, $config['per_page'] = NULL, $page = NULL, $district, $facility, $empid, $dep, 'api');

        // Pre-fetch fields to reduce redundant database queries
        $ihris_pids = array_column($datas, 'ihris_pid');
        $fields = $this->get_fields_for_ihris_pids($ihris_pids, ['card_number', 'nin', 'ipps']);

        $attendanceData = [];

        foreach ($datas as $data) {
           // dd($data);
            $ihris_pid = $data['ihris_pid'];
            $ihris5_pid = $data['ihris5_pid'];

            // Fetch roster data
            $roster = Modules::run('attendance/attrosta', $valid_range, urlencode($ihris_pid));

            // Use pre-fetched fields
            $cardnumber = $fields[$ihris_pid]['card_number'];
            $nin = $fields[$ihris_pid]['nin'];
            $ipps = $fields[$ihris_pid]['ipps'];

            $present = !empty($data['P']) ? $data['P'] : 0;
            $off = !empty($data['O']) ? $data['O'] : 0;
            $leave = !empty($data['L']) ? $data['L'] : 0;
            $request = !empty($data['R']) ? $data['R'] : 0;
            $holiday = !empty($data['H']) ? $data['H'] : 0;
            $duty_date = $data['duty_date'];

            $eve = isset($roster['Evening'][0]) ? $roster['Evening'][0]->days : 0;
            $day = isset($roster['Day'][0]) ? $roster['Day'][0]->days : 0;
            $night = isset($roster['Night'][0]) ? $roster['Night'][0]->days : 0;
            $r_days = ($eve + $day + $night);
            if ($r_days == 0) {
                $r_days = 22;
            }

            $absent = days_absent_helper($present, $r_days);
            $per = per_present_helper($present, $r_days);

            $attendance = [
                "ihris_pid" => $ihris5_pid,
                "ipps" => $ipps,
                "nin" => $nin,
                "card_number" => $cardnumber,
                "facility_id" => $data["facility_id"],
                "district" => $data["district"],
                "Name" => $data['fullname'],
                "Job" => $data['job'],
                "Department" => $data['department_id'],
                "Duty Date" => $duty_date . '-01',
                "Off Duty" => $off,
                "Official Request" => $request,
                "Leave" => $leave,
                "Holiday" => $holiday,
                "Expected" => $r_days,
                "Total Days Worked" => $present,
                "Total Days Absent" => $absent,
                "percent" => intval(round(str_replace(" %","",$per),0))
            ];

            $attendanceData[] = $attendance;
        }

        if ($fhir=='view') {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode($this->convert_to_fhir($attendanceData));
             
        } else {
           return $this->convert_to_fhir($attendanceData);
           
        }
    }
    private function convert_to_fhir($attendanceData)
    {
        $fhirData = [
            "resourceType" => "Bundle",
            "type" => "transaction",
            "entry" => []
        ];

        foreach ($attendanceData as $data) {
            //dd($data);
            $entry = [
                "resource" => [
                    "resourceType" => "Basic",
                     // Generate a unique ID for each entry
                    "meta" => [
                        "profile" => ["http://ihris.org/fhir/StructureDefinition/ihris-basic-attendance"]
                    ],
                    "extension" => [
                        [
                            "url" => "http://ihris.org/fhir/StructureDefinition/ihris-practitioner-reference",
                            "valueReference" => [
                                "reference" => "Practitioner/" . $data["ihris_pid"]
                            ]
                        ],
                        [
                            "url" => "http://ihris.org/fhir/StructureDefinition/ihris-attendance",
                            "extension" => [
                                ["url" => "period", "valueDate" => $data["Duty Date"]],
                                ["url" => "present", "valueInteger" => $data["Total Days Worked"]],
                                ["url" => "absent", "valueInteger" => $data["Total Days Absent"]],
                                ["url" => "offDuty", "valueInteger" => $data["Off Duty"]],
                                ["url" => "leave", "valueInteger" => $data["Leave"]],
                                ["url" => "request", "valueInteger" => $data["Official Request"]],
                                ["url" => "holidays", "valueInteger" => $data["Holiday"]],
                                ["url" => "expected", "valueInteger" => $data["Expected"]],
                                ["url" => "percentPresent", "valueInteger" => $data["percent"]]
                            ]
                        ]
                    ]
                ],
                "request" => [
                    "method" => "POST",
                    "url" => "Basic" // Generate a unique URL for each entry
                ]
            ];

            $fhirData["entry"][] = $entry;
        }
        //header('Content-Type: application/json; charset=utf-8');

        return $fhirData;
    }

   

    public function get_ihris5data()
    {
        $http = new HttpUtils();
        $headers = [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];
      $districts  = $this->db->get('ihris5_districts')->result();
        $this->db->query("TRUNCATE table ihrisdata5");
      foreach($districts as $district){

       //s $dist = str_replace(" District","",$district->name);
        $dist = 'Mbale';
        $response = $http->sendiHRIS5Request('ihrisdata/'.$dist, "GET", $headers, []);

        if ($response) {
            //dd(count($response));
            //$message = $this->biotimejobs_mdl->add_ihrisdata($response);
        
            foreach ($response->entry as $insert) {
                //dd($insert);



                
                         $data = array(
                'ihris_pid' => $insert->ihris_pid,
                'district_id' => $insert->district_id,
                'district' => $insert->district,
                'nin' => isset($insert->nin) ? $insert->nin : null,
                'card_number' => $insert->card_number,
                'ipps' => $insert->ipps,
                'facility_type_id' => $insert->facility_type_id,
                'facility_id' => null, // Assuming facility_id is not present in JSON
                'facility' => $insert->facility,
                'department_id' => null, // Assuming department_id is not present in JSON
                'department' => null, // Assuming department is not present in JSON
                'division' => null, // Assuming division is not present in JSON
                'section' => null, // Assuming section is not present in JSON
                'unit' => '', // Assuming unit is not present in JSON
                'job_id' => $insert->job_id,
                'job' => $insert->job,
                'employment_terms' => $insert->employmentTerms,
                'salary_grade' => isset($insert->salary_grade) ? $insert->salary_grade : null,
                'surname' => $insert->surname,
                'firstname' => $insert->firstname,
                'othername' => $insert->othername,
                'mobile' => isset($insert->mobile) ? $insert->mobile : null,
                'telephone' => isset($insert->telephone) ? $insert->telephone : null,
                'institution_type_id' =>  $insert->facility_type_id,
                'institutiontype_name' =>  $insert->facility_type_id, 
                'gender' => $insert->gender,
                'birth_date' => date('Y-m-d', strtotime($insert->birth_date)),
                'cadre' => isset($insert->cadre) ? $insert->cadre : null,
                'email' => isset($insert->email) ? $insert->email : null,
                'region' => $insert->region
            );
                    


                    //dd($data);


                $message = $this->db->replace('ihrisdata5', $data);
                ///dd($this->last->query);
            }
            $this->remap_data();

            $this->log($message);
        }
        $process = 2;
        $method = "bioitimejobs/get_ihris5data";
        if (count($response) > 0) {
            $status = "successful";
        } else {
            $status = "failed";
        }
    }
        
    }

    public function get_districts()
    {
        $http = new HttpUtils();
        $headers = [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];
     
        $response = $http->sendiHRIS5Request('ihrisdata/districts', "GET", $headers, []);

        if ($response) {
            //dd(count($response));
            //$message = $this->biotimejobs_mdl->add_ihrisdata($response);
            $this->db->query("TRUNCATE table ihris5_districts");
            foreach ($response as $insert) {
                    
              //  dd($insert);

                $message = $this->db->insert('ihris5_districts', $insert);
                ///dd($this->last->query);
            }

            $this->log($message);
        }
        $process = 2;
        $method = "bioitimejobs/ihris5_districts";
        if (count($response) > 0) {
            $status = "successful";
        } else {
            $status = "failed";
        }
    }
    public function remap_data(){

        // Optimized and fixed query to get matching values
        $this->db->select('ihrisdata.ihris_pid as ihris4_pid, ihrisdata5.ihris_pid as ihris5_pid');
        $this->db->from('ihrisdata');
        $this->db->join(
            'ihrisdata5',
            'ihrisdata.card_number = ihrisdata5.card_number OR 
     ihrisdata.ipps = ihrisdata5.ipps OR 
     ihrisdata.nin = ihrisdata5.nin'
        );
        $this->db->where('ihrisdata.nin IS NOT NULL');
        $this->db->where('ihrisdata.ipps IS NOT NULL');
        $this->db->where('ihrisdata.card_number IS NOT NULL');

        $query = $this->db->get();
        $map_values = $query->result();

        // Check if there are values to insert
        if (!empty($map_values)) {
            foreach ($map_values as $insert) {
                $data = array(
                    'ihris4_pid' => $insert->ihris4_pid,
                    'ihris5_pid' => $insert->ihris5_pid
                );

                // Using REPLACE to avoid duplicates
                $this->db->replace('data_mapper', $data);
            }
        }

    }
    public function fhir_Server_post()
    {
        $valid_range = '2024-07';
        $district = 'MBALE';
        $body = $this->attendance_data('false', $valid_range, $district);
        // dd($body);
        $http = new HttpUtils();

        $endpoint = 'hapi/fhir';
        $headers = array(
            'Content-Type: application/fhir+json',
            'Content-Length: ' . strlen(json_encode($body)),
            //'Authorization: JWT ' . $this->get_token()
        );

        $response = $http->curlsendiHRIS5HttpPost($endpoint, $headers, $body);

        if ($response) {
            dd($response);
        }
    }
public function ihris5jobs(){
// Sample FHIR resource data (JSON)
        $http = new HttpUtils();
        $headers = [
            'Content-Type: application/fhir+json',
            'Accept' => '*',
        ];

        $response = $http->curlgetihris5Http('hapi/fhir/Basic?_profile=http://ihris.org/fhir/StructureDefinition/ihris-manage-job', "GET", $headers);
//var_dump($response);
// Decode the JSON string into an associative array
$fhirData = json_decode($response, true);

// Initialize an array to hold the formatted data
$formattedData = [];

if (isset($fhirData['entry'])) {
    foreach ($fhirData['entry'] as $entry) {
        $jobName = '';
        $dhis2Uuid = '';

        // Iterate through the extensions to find the job name and dhis2_uuid
        foreach ($entry['resource']['extension'] as $extension) {
            if ($extension['url'] === 'http://ihris.org/fhir/StructureDefinition/ihris-basic-name') {
                $jobName = $extension['valueString'];
            }
            if ($extension['url'] === 'http://ihris.org/fhir/StructureDefinition/ihris-dhis2-id') {
                $dhis2Uuid = $extension['valueString'];
            }
        }

        // Add the job name and empty dhis2_uuid to the formatted data
        $formattedData[] = [
            'dhis2_uuid' => $dhis2Uuid,
            'job_name' => $jobName
        ];
    }
}

// Encode the formatted data into JSON
$jsonOutput = json_encode($formattedData, JSON_PRETTY_PRINT);

// Output the JSON
dd($jsonOutput);

}







}