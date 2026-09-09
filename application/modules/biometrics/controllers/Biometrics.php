
<?php
defined('BASEPATH') OR exit('No direct script access allowed');
use \utils\HttpUtil;
/**
 * Biometrics Controller (formerly Biotime)
 * Handles biometrics/biotime integration tasks
 */
class Biometrics extends MX_Controller{
    private $user;
    private $watermark;
    private $filters;

    public function __construct(){
        parent::__construct();
        
        try {
        $this->user = $this->session->get_userdata();
        $this->load->library('pagination');
        $this->watermark = FCPATH . "assets/img/448px-Coat_of_arms_of_Uganda.svg.png";
            
            // Safely get filters
            try {
        $this->filters = Modules::run('filters/sessionfilters');
            } catch (Exception $e) {
                $this->filters = array();
                log_message('error', 'Failed to get session filters: ' . $e->getMessage());
            }

        $this->load->model('biometrics_model', 'biometrics_mdl');
        } catch (Exception $e) {
            log_message('error', 'Biometrics controller constructor error: ' . $e->getMessage());
        }
    }
    public function updateTerminals(){
     
         $data['view']='biotime_devices';
         $data['uptitle']="Bio Time Devices";
         $data['title']="Bio Time Devices";
		 $data['module']="biometrics";
       
		 echo Modules::run("templates/main",$data);
      
    
    }
    public function tasks(){
    
            $data['view']='biotime_tasks';
            $data['uptitle']="iHRIS & BioTime Tasks";
            $data['title']="iHRIS BioTime Tasks ";
            $data['module']="biometrics";
            echo Modules::run("templates/main",$data);
  
       
    }

    /**
     * Admin-only (sadmin): purge orphan users left on devices after BioTime deletes.
     * POST mode=rebuild|upload, scope=facility|all
     */
    public function purgeOrphanMachines()
    {
        $this->output->set_content_type('application/json');

        if (!$this->session->userdata('isLoggedIn')) {
            return $this->output->set_status_header(401)->set_output(json_encode([
                'ok' => false,
                'error' => 'unauthorized',
                'message' => 'Please sign in again.',
            ]));
        }

        if ((string) $this->session->userdata('role') !== 'sadmin') {
            return $this->output->set_status_header(403)->set_output(json_encode([
                'ok' => false,
                'error' => 'forbidden',
                'message' => 'Only system administrators can dispatch machine cleanup.',
            ]));
        }

        if (strtoupper((string) $this->input->method(true)) !== 'POST') {
            return $this->output->set_status_header(405)->set_output(json_encode([
                'ok' => false,
                'error' => 'method_not_allowed',
                'message' => 'POST required.',
            ]));
        }

        $mode = strtolower(trim((string) $this->input->post('mode')));
        if (!in_array($mode, ['rebuild', 'upload'], true)) {
            $mode = 'rebuild';
        }

        $scope = strtolower(trim((string) $this->input->post('scope')));
        $area_code = '';
        if ($scope !== 'all') {
            $facility = trim((string) (
                $this->session->userdata('dashboard_facility')
                ?: $this->session->userdata('facility')
                ?: $this->session->userdata('facility_id')
                ?: ''
            ));
            if ($facility === '') {
                return $this->output->set_status_header(400)->set_output(json_encode([
                    'ok' => false,
                    'error' => 'no_facility',
                    'message' => 'No facility in session. Switch facility first, or choose All terminals.',
                ]));
            }
            $area_code = (strpos($facility, 'facility|') === 0) ? $facility : ('facility|' . $facility);
        }

        ignore_user_abort(true);
        @ini_set('max_execution_time', '300');

        try {
            $result = Modules::run('biotimejobs/purge_orphan_machine_users', $mode, $area_code);
            if (!is_array($result)) {
                $result = [
                    'ok' => false,
                    'error' => 'dispatch_failed',
                    'message' => 'Cleanup job did not return a result.',
                    'mode' => $mode,
                    'area_code' => $area_code,
                ];
            } else {
                $result['dispatched_by'] = (string) $this->session->userdata('username');
                $result['dispatched_at'] = date('c');
            }

            $status = !empty($result['ok']) ? 200 : 502;
            return $this->output->set_status_header($status)->set_output(json_encode($result));
        } catch (Throwable $e) {
            log_message('error', 'biometrics/purgeOrphanMachines: ' . $e->getMessage());
            return $this->output->set_status_header(500)->set_output(json_encode([
                'ok' => false,
                'error' => 'server_error',
                'message' => 'Machine cleanup failed to start.',
            ]));
        }
    }
    public function enrolled(){
    
        $data['view']='enrolled';
        $data['uptitle']="Enrolled Users";
        $data['title']="Enrolled Users ";
        $data['module']="biometrics";
        echo Modules::run("templates/main",$data);
  
    }
    public function unenrolled(){
    
        $data['view']='unenrolled_users';
        $data['uptitle']="New Biometric Users";
        $data['title']="New Biometric Users ";
        $data['module']="biometrics";
        echo Modules::run("templates/main",$data);
  
    }

    /**
     * Users enrolled in BioTime whose facility/job needs syncing from iHRIS.
     */
    public function needsUpdate(){
        $data['view'] = 'needs_update';
        $data['uptitle'] = "Users Needing Update";
        $data['title'] = "Users Needing Update";
        $data['module'] = "biometrics";
        echo Modules::run("templates/main", $data);
    }

    /**
     * Server-side DataTables: enrolled users.
     */
    public function enrolledAjax()
    {
        if (ob_get_level()) {
            ob_end_clean();
        }
        header('Content-Type: application/json');
        try {
            echo json_encode($this->biometrics_mdl->get_enrolled_datatable());
        } catch (Throwable $e) {
            log_message('error', 'enrolledAjax: ' . $e->getMessage());
            echo json_encode(['draw' => 0, 'recordsTotal' => 0, 'recordsFiltered' => 0, 'data' => [], 'error' => $e->getMessage()]);
        }
        exit;
    }

    /**
     * Server-side DataTables: new / unenrolled users.
     */
    public function unenrolledAjax()
    {
        if (ob_get_level()) {
            ob_end_clean();
        }
        header('Content-Type: application/json');
        try {
            echo json_encode($this->biometrics_mdl->get_new_users_datatable());
        } catch (Throwable $e) {
            log_message('error', 'unenrolledAjax: ' . $e->getMessage());
            echo json_encode(['draw' => 0, 'recordsTotal' => 0, 'recordsFiltered' => 0, 'data' => [], 'error' => $e->getMessage()]);
        }
        exit;
    }

    /**
     * Server-side DataTables: users needing BioTime update.
     */
    public function needsUpdateAjax()
    {
        if (ob_get_level()) {
            ob_end_clean();
        }
        header('Content-Type: application/json');
        try {
            echo json_encode($this->biometrics_mdl->get_needs_update_datatable());
        } catch (Throwable $e) {
            log_message('error', 'needsUpdateAjax: ' . $e->getMessage());
            echo json_encode(['draw' => 0, 'recordsTotal' => 0, 'recordsFiltered' => 0, 'data' => [], 'error' => $e->getMessage()]);
        }
        exit;
    }

    /**
     * Stream Excel (.xls TSV) for a biometrics list.
     * GET: type=enrolled|unenrolled|needsUpdate, search=
     */
    public function exportExcel()
    {
        if (!$this->session->userdata('isLoggedIn')) {
            show_error('Unauthorized', 401);
            return;
        }
        $type = strtolower(trim((string) $this->input->get('type')));
        $search = trim((string) $this->input->get('search'));
        if ($type === 'enrolled') {
            $payload = $this->biometrics_mdl->export_enrolled_rows($search);
            $filename = 'enrolled_users_' . date('Y-m-d_His') . '.xls';
        } elseif ($type === 'unenrolled' || $type === 'new') {
            $payload = $this->biometrics_mdl->export_unenrolled_rows($search);
            $filename = 'new_users_' . date('Y-m-d_His') . '.xls';
        } elseif ($type === 'needsupdate' || $type === 'needs_update') {
            $payload = $this->biometrics_mdl->export_needs_update_rows($search);
            $filename = 'needs_update_' . date('Y-m-d_His') . '.xls';
        } else {
            show_error('Unknown export type', 400);
            return;
        }

        if (ob_get_level()) {
            ob_end_clean();
        }
        header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: no-cache');
        $fh = fopen('php://output', 'w');
        fprintf($fh, chr(0xEF) . chr(0xBB) . chr(0xBF));
        if (!empty($payload['headers'])) {
            fputcsv($fh, $payload['headers'], "\t");
        }
        foreach ($payload['rows'] as $row) {
            fputcsv($fh, $row, "\t");
        }
        fclose($fh);
        exit;
    }

    public function get_enrolled(){
        return $this->biometrics_mdl->get_enrolled();
    }

    public function get_users_needing_update(){
        return $this->biometrics_mdl->get_users_needing_update();
    }

    /**
     * Force a single BioTime employee update (facility/job sync).
     * POST card_number and/or ihris_pid
     * If the iHRIS facility has no BioTime area, resigns the employee (reinstated later when area exists).
     */
    public function forceUpdate(){
        if (ob_get_level()) {
            ob_end_clean();
        }
        header('Content-Type: application/json');

        $card = trim((string) $this->input->post('card_number'));
        $ihris_pid = trim((string) $this->input->post('ihris_pid'));
        if ($card === '' && $ihris_pid === '') {
            echo json_encode(['status' => 'error', 'message' => 'card_number or ihris_pid is required']);
            exit;
        }

        $row = null;
        if ($card !== '') {
            $row = $this->biometrics_mdl->get_transfer_by_card($card);
        }
        if (!$row) {
            $staff = null;
            if ($ihris_pid !== '') {
                $staff = $this->biometrics_mdl->get_ihris_by_pid($ihris_pid);
            }
            if (!$staff && $card !== '') {
                $staff = $this->biometrics_mdl->get_ihris_by_card($card);
            }
            if (!$staff) {
                echo json_encode(['status' => 'error', 'message' => 'Staff not found']);
                exit;
            }
            $resolved = $this->biometrics_mdl->resolve_emp_code_for_staff($staff);
            $enr = null;
            if ($resolved !== '') {
                $enr = $this->db->get_where('biotime_enrollment', ['emp_code' => $resolved], 1)->row();
            }
            if (!$enr && $card !== '') {
                $enr = $this->db->get_where('biotime_enrollment', ['emp_code' => $card], 1)->row();
            }
            if (!$enr && !empty($staff->card_number)) {
                $enr = $this->db->get_where('biotime_enrollment', ['emp_code' => $staff->card_number], 1)->row();
            }
            if (!$enr && !empty($staff->ipps)) {
                $enr = $this->db->get_where('biotime_enrollment', ['emp_code' => $staff->ipps], 1)->row();
            }
            if (!$enr || empty($enr->biotime_emp_id)) {
                echo json_encode(['status' => 'error', 'message' => 'No BioTime enrollment for this staff']);
                exit;
            }
            $staff->new_facility = $staff->facility_id;
            $staff->new_fname = $staff->facility;
            $staff->emp_code = $enr->emp_code;
            $staff->biotime_emp_id = $enr->biotime_emp_id;
            $staff->biotime_fac_id = $enr->biotime_fac_id;
            $row = $staff;
        }

        $targetFac = '';
        if (!empty($row->new_facility)) {
            $targetFac = trim((string) $row->new_facility);
        } elseif (!empty($row->facility_id)) {
            $targetFac = trim((string) $row->facility_id);
        }

        $areaId = null;
        if ($targetFac !== '') {
            $esc = $this->db->escape_str($targetFac);
            $areaRow = $this->db->query("SELECT id FROM biotime_facilities WHERE area_code = '$esc' LIMIT 1")->row();
            if (!$areaRow && strpos($targetFac, 'facility|') === 0) {
                $bare = $this->db->escape_str(substr($targetFac, strlen('facility|')));
                $areaRow = $this->db->query("SELECT id FROM biotime_facilities WHERE area_code = '$bare' LIMIT 1")->row();
            }
            if (!$areaRow && strpos($targetFac, 'facility|') !== 0) {
                $pref = $this->db->escape_str('facility|' . $targetFac);
                $areaRow = $this->db->query("SELECT id FROM biotime_facilities WHERE area_code = '$pref' LIMIT 1")->row();
            }
            if ($areaRow) {
                $areaId = (int) $areaRow->id;
            }
        }
        $willResign = empty($areaId);
        if ($willResign) {
            log_message('error', 'forceUpdate: BioTime area not found for ' . $targetFac . '; will resign employee');
        }

        $label = $card !== '' ? $card : $ihris_pid;
        try {
            $response = Modules::run('biotimejobs/update_biotimeuser', $row);
            if ($response === false || $response === null) {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Update failed for ' . $label . '. Check BioTime logs.',
                    'timestamp' => date('Y-m-d H:i:s'),
                ]);
            } else {
                $msg = 'BioTime update applied for ' . $label;
                $resigned = $willResign || (is_object($response) && isset($response->resign_type));
                if ($resigned) {
                    $rid = is_object($response) && isset($response->id) ? $response->id : '';
                    $msg = 'Resigned in BioTime for ' . $label
                        . ' (facility has no BioTime area'
                        . ($rid !== '' ? '; resign_id ' . $rid : '')
                        . '). Will reinstate when area is available.';
                }
                echo json_encode([
                    'status' => 'success',
                    'message' => $msg,
                    'area_id' => $areaId,
                    'resigned' => $resigned,
                    'timestamp' => date('Y-m-d H:i:s'),
                ]);
            }
        } catch (Exception $e) {
            log_message('error', 'forceUpdate: ' . $e->getMessage());
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        exit;
    }

    /**
     * Force create / enroll a single iHRIS staff member in BioTime.
     * POST card_number and/or ihris_pid
     */
    public function forceEnroll(){
        if (ob_get_level()) {
            ob_end_clean();
        }
        header('Content-Type: application/json');

        $card = trim((string) $this->input->post('card_number'));
        $ihris_pid = trim((string) $this->input->post('ihris_pid'));
        if ($card === '' && $ihris_pid === '') {
            echo json_encode(['status' => 'error', 'message' => 'card_number or ihris_pid is required']);
            exit;
        }

        $staff = null;
        if ($ihris_pid !== '') {
            $staff = $this->biometrics_mdl->get_ihris_by_pid($ihris_pid);
        }
        if (!$staff && $card !== '') {
            $staff = $this->biometrics_mdl->get_ihris_by_card($card);
        }
        if (!$staff) {
            echo json_encode(['status' => 'error', 'message' => 'Staff not found']);
            exit;
        }

        $label = $card !== '' ? $card : $ihris_pid;
        try {
            // Free slots / remove no-bio junk before force create
            Modules::run('biotimejobs/cleanup_biotime_employees', 50);
            $response = Modules::run('biotimejobs/create_new_biotimeuser_from_ihris', $staff);
            if ($response === 'skipped') {
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Already enrolled (or invalid emp_code skipped) for ' . $label,
                    'timestamp' => date('Y-m-d H:i:s'),
                ]);
                exit;
            }
            $ok = is_object($response) && (
                isset($response->id) || isset($response->emp_code)
            );

            if (!$ok) {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Enrollment failed for ' . $label . '. Check BioTime API / server logs.',
                    'timestamp' => date('Y-m-d H:i:s'),
                ]);
            } else {
                $emp = isset($response->emp_code) ? $response->emp_code : '';
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Enrollment submitted for ' . $label . ($emp !== '' ? ' (emp_code ' . $emp . ')' : ''),
                    'timestamp' => date('Y-m-d H:i:s'),
                ]);
            }
        } catch (Exception $e) {
            log_message('error', 'forceEnroll: ' . $e->getMessage());
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        exit;
    }
    //Department Department code, Department Name

    public function getbioDeps(){
        return $this->biometrics_mdl->getbioDeps();

    }
    public function getbiojobs(){
        return $this->biometrics_mdl->getbiojobs();

    }
    public function getbiofacilities(){
        return $this->biometrics_mdl->getbiofacilities();

    }
    public function getihrisDeps(){
        return $this->biometrics_mdl->getihrisDeps();

    }
    public function getihrisjobs(){
        return $this->biometrics_mdl->getihrisjobs();
    }
    public function getihrisfacilities(){

        return $this->biometrics_mdl->getihrisfacilities();

    }
    public function getihris_users(){

        return $this->biometrics_mdl->getihris_users();

    }

    public function bioihriscontrol(){
        $data['biousers']=$this->biometrics_mdl->count_enrolled();
        $data['ihrisusers']=count($this->getihris_users());
        $data['biojobs']=count($this->getbiojobs());
        $data['biodeps']=count($this->getbioDeps());
        $data['biofacs']=count($this->getbiofacilities());
        $data['ihrisjobs']=$this->getihrisjobs();
        $data['ihrisfacs']=$this->getihrisfacilities();
        $data['ihrisdeps']=$this->getihrisDeps();
        $data['usersgap']=$this->biometrics_mdl->count_new_users();
        $data['jobsgap']=count($this->biometrics_mdl->get_new_jobs());
        $data['depsgap']=count($this->biometrics_mdl->get_new_deps());
        $data['facsgap']=count($this->biometrics_mdl->get_new_facs());
        $enrolled_sample = $this->db->query(
            "SELECT f.last_gen FROM fingerprints f
             WHERE f.facilityId = ".$this->db->escape($this->session->userdata('facility'))."
               AND f.device != '' AND f.device IS NOT NULL
             ORDER BY f.last_gen DESC LIMIT 1"
        )->row();
        $data['biouserssync'] = $enrolled_sample->last_gen ?? null;
        $ihris_sample = $this->db->query(
            "SELECT last_update FROM ihrisdata
             WHERE facility_id = ".$this->db->escape($this->session->userdata('facility'))."
             ORDER BY last_update DESC LIMIT 1"
        )->row();
        $data['ilastsync'] = $ihris_sample->last_update ?? null;
        $jobs = $this->biometrics_mdl->getbiojobs();
        $deps = $this->biometrics_mdl->getbioDeps();
        $facs = $this->biometrics_mdl->getbiofacilities();
        $data['blastjobssync'] = (!empty($jobs[0]->last_gen)) ? $jobs[0]->last_gen : null;
        $data['blastdepssync'] = (!empty($deps[0]->last_update)) ? $deps[0]->last_update : null;
        $data['blastfacsync'] = (!empty($facs[0]->last_gen)) ? $facs[0]->last_gen : null;
    return $data;
    }
    public function syncDepartments(){
        // Clear any previous output
        if (ob_get_level()) {
            ob_end_clean();
        }
        
        header('Content-Type: application/json');
        
        // Return immediately and run sync in background
        $result = array(
            'status' => 'initiated',
            'message' => 'Departments sync has been initiated and is running in the background',
            'timestamp' => date('Y-m-d H:i:s'),
            'type' => 'departments',
            'note' => 'Check server logs for completion status.'
        );
        
        echo json_encode($result, JSON_PRETTY_PRINT);
        
        // Close connection to client
        if (function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request();
        } else {
            ignore_user_abort(true);
            if (ob_get_level()) {
                ob_end_flush();
            }
            flush();
        }
        
        // Run sync in background
        try {
            set_time_limit(0);
            ini_set('max_execution_time', 0);
            ini_set('memory_limit', '256M');
            
            $response = Modules::run('biotimejobs/biotimedepartments');
            
            if ($response) {
                log_message('info', 'Departments sync completed successfully');
            } else {
                log_message('error', 'Departments sync completed with errors');
            }
        } catch (Exception $e) {
            log_message('error', 'Sync Departments Error: ' . $e->getMessage());
        } catch (Error $e) {
            log_message('error', 'Sync Departments Fatal Error: ' . $e->getMessage());
        }
        
        exit;
    }
    
    public function syncFacilities(){
        // Clear any previous output
        if (ob_get_level()) {
            ob_end_clean();
        }
        
        header('Content-Type: application/json');
        
        // Return immediately and run sync in background
        $result = array(
            'status' => 'initiated',
            'message' => 'Facilities sync has been initiated and is running in the background',
            'timestamp' => date('Y-m-d H:i:s'),
            'type' => 'facilities',
            'note' => 'Check server logs for completion status.'
        );
        
        echo json_encode($result, JSON_PRETTY_PRINT);
        
        // Close connection to client
        if (function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request();
        } else {
            ignore_user_abort(true);
            if (ob_get_level()) {
                ob_end_flush();
            }
            flush();
        }
        
        // Run sync in background
        try {
            set_time_limit(0);
            ini_set('max_execution_time', 0);
            ini_set('memory_limit', '256M');
            
            $response = Modules::run('biotimejobs/biotimeFacilities');
            
            if ($response) {
                log_message('info', 'Facilities sync completed successfully');
            } else {
                log_message('error', 'Facilities sync completed with errors');
            }
        } catch (Exception $e) {
            log_message('error', 'Sync Facilities Error: ' . $e->getMessage());
        } catch (Error $e) {
            log_message('error', 'Sync Facilities Fatal Error: ' . $e->getMessage());
        }
        
        exit;
    }
    
    //position code, Position Name
    public function syncJobs(){
        // Clear any previous output
        if (ob_get_level()) {
            ob_end_clean();
        }
        
        header('Content-Type: application/json');
        
        // Return immediately and run sync in background
        $result = array(
            'status' => 'initiated',
            'message' => 'Jobs sync has been initiated and is running in the background',
            'timestamp' => date('Y-m-d H:i:s'),
            'type' => 'jobs',
            'note' => 'Check server logs for completion status.'
        );
        
        echo json_encode($result, JSON_PRETTY_PRINT);
        
        // Close connection to client
        if (function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request();
        } else {
            ignore_user_abort(true);
            if (ob_get_level()) {
                ob_end_flush();
            }
            flush();
        }
        
        // Run sync in background
        try {
            set_time_limit(0);
            ini_set('max_execution_time', 0);
            ini_set('memory_limit', '256M');
            
            $response = Modules::run('biotimejobs/biotime_jobs');
            
            if ($response) {
                log_message('info', 'Jobs sync completed successfully');
            } else {
                log_message('error', 'Jobs sync completed with errors');
            }
        } catch (Exception $e) {
            log_message('error', 'Sync Jobs Error: ' . $e->getMessage());
        } catch (Error $e) {
            log_message('error', 'Sync Jobs Fatal Error: ' . $e->getMessage());
        }
        
        exit;
    }
    
    public function syncEmployees(){
        // Clear any previous output
        if (ob_get_level()) {
            ob_end_clean();
        }
        
        header('Content-Type: application/json');
        
        // Return immediately and run sync in background to avoid timeout
        $result = array(
            'status' => 'initiated',
            'message' => 'Employees sync has been initiated and is running in the background',
            'timestamp' => date('Y-m-d H:i:s'),
            'type' => 'employees',
            'note' => 'This is a long-running process. The sync will continue in the background. Check server logs for completion status.'
        );
        
        // Send response immediately
        echo json_encode($result, JSON_PRETTY_PRINT);
        
        // Close connection to client so sync can continue
        if (function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request();
        } else {
            // For non-FastCGI environments
            ignore_user_abort(true);
            if (ob_get_level()) {
                ob_end_flush();
            }
            flush();
        }
        
        // Now run the sync in background with no time limit
        try {
            set_time_limit(0); // No time limit for background process
            ini_set('max_execution_time', 0);
            ini_set('memory_limit', '512M');
            
            // Run the sync
            $response = Modules::run('biotimejobs/saveEnrolled');
            
            // Log completion
            if ($response === false) {
                log_message('error', 'Employees sync completed with errors - check logs');
            } else {
                log_message('info', 'Employees sync completed successfully');
            }
        } catch (Exception $e) {
            log_message('error', 'Sync Employees Error: ' . $e->getMessage());
            log_message('error', 'Stack trace: ' . $e->getTraceAsString());
        } catch (Error $e) {
            log_message('error', 'Sync Employees Fatal Error: ' . $e->getMessage());
        }
        
        exit;
    }
    public function syncPersons($facilty){
        

        
    }
    public function getMachines($search=FALSE){
        return $this->biometrics_mdl->getMachines($search);
    }

    public function getMachinesAjax(){
        // Clear any previous output
        if (ob_get_level()) {
            ob_end_clean();
        }
        ob_start();
        
        // Set JSON header
        header('Content-Type: application/json');
        
        // Initialize default values
        $draw = 1;
        $start = 0;
        $length = 25;
        $search = '';
        $order = null;
        
        try {
            // Get POST data (DataTables sends POST)
            $draw = $this->input->post('draw') ? intval($this->input->post('draw')) : 1;
            $start = $this->input->post('start') ? intval($this->input->post('start')) : 0;
            $length = $this->input->post('length') ? intval($this->input->post('length')) : 25;
            
            // Safely get search value
            $search_post = $this->input->post('search');
            $search = '';
            if (!empty($search_post) && isset($search_post['value'])) {
                $search = $search_post['value'];
            }
            
            // Safely get order
            $order_post = $this->input->post('order');
            $order = null;
            if (!empty($order_post) && isset($order_post[0])) {
                $order = $order_post[0];
            }
            
            // Ensure model is loaded
            if (!isset($this->biometrics_mdl)) {
                $this->load->model('biometrics_model', 'biometrics_mdl');
            }
            
            // Get total count (without search) for recordsTotal
            $recordsTotal = $this->biometrics_mdl->getMachinesCount('');
            
            // Get filtered count (with search) for recordsFiltered
            $recordsFiltered = $this->biometrics_mdl->getMachinesCount($search);
            
            // Get paginated data
            $machines = $this->biometrics_mdl->getMachinesPaginated($start, $length, $search, $order);
        
        $data = array();
            if (!empty($machines) && is_array($machines)) {
        foreach($machines as $machine) {
                    $lastActivity = isset($machine->last_activity) ? $machine->last_activity : null;
                    $status = $this->getMachineStatus($lastActivity);
                    $sn = isset($machine->sn) ? $machine->sn : '';
            $data[] = array(
                        $sn,
                        isset($machine->area_name) ? $machine->area_name : '',
                        isset($machine->last_activity) ? $machine->last_activity : '',
                        isset($machine->user_count) ? $machine->user_count : 0,
                        isset($machine->ip_address) ? $machine->ip_address : '',
                $status,
                        $this->getSyncButton($sn)
            );
                }
        }
        
        $response = array(
                'draw' => $draw,
                'recordsTotal' => $recordsTotal ? intval($recordsTotal) : 0,
                'recordsFiltered' => $recordsFiltered ? intval($recordsFiltered) : 0,
            'data' => $data
        );
        
            ob_end_clean();
            echo json_encode($response);
            exit;
        } catch (Exception $e) {
            ob_end_clean();
            log_message('error', 'getMachinesAjax Exception: ' . $e->getMessage());
            log_message('error', 'Stack trace: ' . $e->getTraceAsString());
            
            $response = array(
                'draw' => $draw,
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => array(),
                'error' => 'An error occurred: ' . $e->getMessage()
            );
            echo json_encode($response);
            exit;
        } catch (Error $e) {
            if (ob_get_level()) {
                ob_end_clean();
            }
            log_message('error', 'getMachinesAjax Fatal Error: ' . $e->getMessage());
            
            $response = array(
                'draw' => $draw,
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => array(),
                'error' => 'A fatal error occurred: ' . $e->getMessage()
            );
            header('Content-Type: application/json');
            echo json_encode($response);
            exit;
        } catch (Throwable $e) {
            // Catch any other throwable (PHP 7+)
            if (ob_get_level()) {
                ob_end_clean();
            }
            log_message('error', 'getMachinesAjax Throwable: ' . $e->getMessage());
            
            $response = array(
                'draw' => $draw,
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => array(),
                'error' => 'An error occurred: ' . $e->getMessage()
            );
            header('Content-Type: application/json');
        echo json_encode($response);
            exit;
        }
    }

    private function getMachineStatus($lastActivity) {
        if (empty($lastActivity)) {
            return '<span class="badge badge-secondary">Unknown</span>';
        }
        
        $today = date('Y-m-d');
        $lastDate = date('Y-m-d', strtotime($lastActivity));
        
        if ($lastDate == $today) {
            return '<span class="badge badge-success">Active</span>';
        } else {
            return '<span class="badge badge-danger">Inactive</span>';
        }
    }

    private function getSyncButton($sn) {
        return '<button type="button" class="btn btn-primary btn-sm sync-machine" data-sn="'.$sn.'">
                    <i class="fas fa-sync"></i> Sync
                </button>';
    }

    /**
     * Server-side DataTable: distinct areas (area_name) for Attendance Sync - uses area-based sync.
     */
    public function getAreasAjax() {
        $draw = (int) $this->input->post('draw');
        try {
            $start = (int) $this->input->post('start');
            $length = (int) $this->input->post('length');
            $search_val = $this->input->post('search');
            $search = (is_array($search_val) && isset($search_val['value'])) ? trim((string) $search_val['value']) : '';
            $order_post = $this->input->post('order');
            $order = (!empty($order_post) && isset($order_post[0])) ? $order_post[0] : null;

            if (!isset($this->biometrics_mdl)) {
                $this->load->model('biometrics_model', 'biometrics_mdl');
            }

            $recordsTotal = $this->biometrics_mdl->getAreasCount('');
            $recordsFiltered = $this->biometrics_mdl->getAreasCount($search);
            $areas = $this->biometrics_mdl->getAreasPaginated($start, $length, $search, $order);

            $data = array();
            if (!empty($areas) && is_array($areas)) {
                foreach ($areas as $area) {
                    $area_name = isset($area->area_name) ? $area->area_name : '';
                    $lastActivity = isset($area->last_activity) ? $area->last_activity : null;
                    $status = $this->getMachineStatus($lastActivity);
                    $data[] = array(
                        $area_name,
                        isset($area->last_activity) ? $area->last_activity : '',
                        isset($area->machine_count) ? (int) $area->machine_count : 0,
                        $status,
                        $this->getAreaSyncButton($area_name)
                    );
                }
            }

            $response = array(
                'draw' => $draw,
                'recordsTotal' => $recordsTotal ? (int) $recordsTotal : 0,
                'recordsFiltered' => $recordsFiltered ? (int) $recordsFiltered : 0,
                'data' => $data
            );
        } catch (Exception $e) {
            log_message('error', 'getAreasAjax: ' . $e->getMessage());
            $response = array('draw' => $draw, 'recordsTotal' => 0, 'recordsFiltered' => 0, 'data' => array(), 'error' => $e->getMessage());
        } catch (Error $e) {
            log_message('error', 'getAreasAjax Fatal: ' . $e->getMessage());
            $response = array('draw' => $draw, 'recordsTotal' => 0, 'recordsFiltered' => 0, 'data' => array(), 'error' => $e->getMessage());
        }
        if (ob_get_level()) {
            ob_end_clean();
        }
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }

    private function getAreaSyncButton($area_name) {
        $area_name = htmlspecialchars($area_name, ENT_QUOTES, 'UTF-8');
        return '<button type="button" class="btn btn-primary btn-sm sync-area-btn" data-area="' . $area_name . '">
                    <i class="fas fa-sync"></i> Sync
                </button>';
    }

    public function get_new_users(){
        return $this->biometrics_mdl->get_new_users();
    }
   


}

