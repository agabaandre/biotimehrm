<?php defined('BASEPATH') or exit('No direct script access allowed');
class Employee_model extends CI_Model
{
    public  function __construct()
    {
        parent::__construct();
        $this->facility = $this->session->userdata['facility'];
    }
    public function get_employees($filters)
    {
        $query = $this->db->query("select distinct ihris_pid,surname,employment_terms,firstname,othername,job,telephone,mobile,department,facility,district,nin,card_number,birth_date,cadre,gender, ihris_pid,facility_id,ipps from  ihrisdata where $filters");
        $result = $query->result();
        return $result;
    }
    public function district_employees($district, $job, $facility, $count = FALSE, $start=FALSE, $limit=FALSE, $csv=FALSE)
    {


        //$kyc =  implode(',', array_map('add_quotes', $kycs));
        if (!empty($start) && ($csv != 1)) {
            $limits = " LIMIT $limit,$start";
        } else {
            $limits = " ";
        }
        if (!empty($job)) {
            $j  = implode("','", $job);

            $quot = "'";
            $jfilter = $_SESSION['jfilter'] = " and job_id in  ($quot$j$quot)";
        } else {
            $jfilter = $_SESSION['jfilter'] = "";
        }
        //print_r($jfilter);

        //$kyc =  implode(',', array_map('add_quotes', $kycs));
        if (!empty($facility)) {
            $f = implode("','", $facility);

            $quot = "'";
            $ffilter = $_SESSION['ffilter'] = " and facility_id in  ($quot$f$quot)";
        } else {
            $ffilter = $_SESSION['ffilter'] = "";
        }

        $affilter = $_SESSION['ffilter'];
        $ajfilter = $_SESSION['jfilter'];

        $query = $this->db->query("select distinct ihris_pid,surname,employment_terms,firstname,othername,job,telephone,mobile,department,department_id,is_incharge,facility,facility_id,district,district_id,nin,card_number,birth_date,cadre,gender, ihris_pid,facility_id from  ihrisdata where district ='$district' $affilter $ajfilter $limits");
        if ($count == 'count') {
            return $query->num_rows();
        } else if ($csv == 1) {
            $result = $query->result_array();
            return $result;
        } else {
            $result = $query->result();
            return $result;
        }
    }
    public function get_employee($id = FALSE)
    {
        $this->db->where('ihris_pid', $id);
        $query = $this->db->get('ihrisdata');
        return $query->row();
    }
    public function get_employee_clock($facility, $staffId)
    {
        $this->db->select('status');
        $this->db->from('clk_log');
        $this->db->where('facility_id', urldecode($facility));
        $this->db->where('ihris_pid', urldecode($staffId));
        $this->db->where('date', date("Y-m-d"));
        $query = $this->db->get();
        if ($query->num_rows() == 1) {
            return $query->row();
        } else {
            return false;
        }
    }
    public function search_employees($facility, $search_term)
    {
        $this->db->select('ihris_pid, firstname, surname, job, facility_id');
        $this->db->where('ihrisdata.facility_id', urldecode($facility))
            ->like('ihris_pid', urldecode($search_term));
        $this->db->or_where('ihrisdata.facility_id', urldecode($facility))
            ->like('firstname', urldecode($search_term));
        $this->db->or_where('ihrisdata.facility_id', urldecode($facility))
            ->like('surname', urldecode($search_term));
        $this->db->order_by('surname', 'asc');
        $this->db->from('ihrisdata');
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            return $query->result();
        } else {
            return false;
        }
    }
    public function count_employees($facility)
    {
        $department = $this->department;
        $unit = $this->unit;
        // return $this->db->count_all_results('ihrisdata');
        if ($this->department) {
            $this->db->where('department_id', $this->department);
            $this->db->or_where('division', $this->division);
            $this->db->or_where('unit', $this->unit);
        }
        $this->db->where('facility_id', urldecode($facility));
        $this->db->from('ihrisdata');
        $qry = $this->db->get();
        return $qry->num_rows();
    }
    public function get_schedules($employeeId)
    {
        $this->db->select('duty_date, end, allDay, starts, ends, letter');
        $this->db->from('ihrisdata');
        $this->db->join('duty_rosta', 'duty_rosta.ihris_pid = ihrisdata.ihris_pid');
        $this->db->join('schedules', 'schedules.schedule_id = duty_rosta.schedule_id');
        $this->db->where('duty_rosta.ihris_pid', urldecode($employeeId));
        $query = $this->db->get()->result();
        return $query;
    }
    public function count_employees_present($facility)
    {
        $this->db->select('*');
        $this->db->from('actuals');
        if ($this->department) {
            $this->db->where('department_id', $this->department);
        }
        $this->db->where('actuals.facility_id', urldecode($facility));
        $this->db->where('actuals.date', date("Y-m-d"));
        $this->db->where('actuals.schedule_id', '22');
        $qry = $this->db->get();
        return $qry->num_rows();
    }
    public function count_employees_off($facility)
    {
        $this->db->select('*');
        $this->db->from('duty_rosta');
        if ($this->department) {
            $this->db->where('department_id', $this->department);
        }
        $this->db->where('duty_rosta.facility_id', urldecode($facility));
        $this->db->where('duty_rosta.schedule_id', '17');
        $this->db->where('duty_rosta.duty_date', date("Y-m-d"));
        $qry = $this->db->get();
        return $qry->num_rows();
    }
    //Staff On Duty Today
    public function count_employees_working($facility)
    {
        $work_days = array('14', '15', '16');
        $this->db->select('*');
        $this->db->from('duty_rosta');
        if ($this->department) {
            $this->db->where('department_id', $this->department);
        }
        $this->db->where('duty_date', date("Y-m-d"));
        $this->db->where('duty_rosta.facility_id', urldecode($facility));
        $this->db->join('schedules', 'schedules.schedule_id = duty_rosta.schedule_id');
        $this->db->where_in('schedules.schedule_id', $work_days);
        $qry = $this->db->get();
        return $qry->num_rows();
    }
    public function staff_working_today($facility)
    {
        $work_days = array('14', '15', '16');
        $this->db->select('*');
        $this->db->from('duty_rosta');
        if ($this->department) {
            $this->db->where('department_id', $this->department);
        }
        $this->db->where('duty_date', date("Y-m-d"));
        $this->db->where('duty_rosta.facility_id', urldecode($facility));
        $this->db->join('ihrisdata', 'ihrisdata.ihris_pid = duty_rosta.ihris_pid');
        $this->db->join('schedules', 'schedules.schedule_id = duty_rosta.schedule_id');
        $this->db->where_in('schedules.schedule_id', $work_days);
        return $this->db->get()->result_array();
    }
    public function get_schedule_types($type)
    {
        if ($type == 'attendance') {
            $purpose = 'a';
        } else if ($type == 'roster') {
            $purpose = 'r';
        } else {
            die();
        }
        $this->db->select('schedule_id, schedule, letter');
        $this->db->from('schedules');
        $this->db->where('purpose', $purpose);
        return $this->db->get()->result_array();
    }
    public function insert_schedule($data)
    {
        $response = array();
        foreach ($data->workdates as $key => $value) {
            // echo $value;
            $duty = array(
                'entry_id' => $value . '' . $data->ihris_pid,
                'facility_id' => $data->facility_id,
                'ihris_pid' => $data->ihris_pid,
                'schedule_id' => $data->schedule_id,
                'duty_date' => $value,
                'end' => strftime("%Y-%m-%d", strtotime("$value +1 day")),
                'allDay' => 'true',
                'color' => $this->setColor($data->schedule_id)
            );
            $entry_id = $value . '' . $data->ihris_pid;
            $duty_date = $value;
            $end_date = strftime("%Y-%m-%d", strtotime("$value +1 day"));
            $this->db->select('entry_id');
            $this->db->where('entry_id', $entry_id);
            $query = $this->db->get('duty_rosta');
            if ($query->num_rows() == 1) {
                $this->db->query("UPDATE duty_rosta SET duty_date='" . $duty_date . "', end='" . $end_date . "' WHERE entry_id = '" . $entry_id . "' ");
                // $response[] = "$entry_id Updated";
                $response[] = true;
            } else {
                if ($this->db->insert('duty_rosta', $duty)) {
                    $response[] = true;
                } else {
                    $response[] = false;
                }
            }
        }
        return $response;
    }
    public function insert_attendance($data)
    {
        $response = array();
        foreach ($data->workdates as $key => $value) {
            // echo $value;
            $duty = array(
                'entry_id' => $value . '' . $data->ihris_pid,
                'facility_id' => $data->facility_id,
                'ihris_pid' => $data->ihris_pid,
                'schedule_id' => $data->schedule_id,
                'date' => $value
            );
            $entry_id = $value . '' . $data->ihris_pid;
            $duty_date = $value;
            $this->db->select('entry_id');
            $this->db->where('entry_id', $entry_id);
            $query = $this->db->get('actuals');
            if ($query->num_rows() == 1) {
                $this->db->query("UPDATE actuals SET `date`='" . $duty_date . "' WHERE entry_id = '" . $entry_id . "' ");
                // $response[] = "$entry_id Updated";
                $response[] = array('status' => 'updated', 'entry' => $entry_id);
            } else {
                $this->db->insert('actuals', $duty);
                // $response[] = "$entry_id Created";
                $response[] = array('status' => 'created', 'entry' => $entry_id);
            }
        }
        return $response;
    }
    public function setColor($schedule)
    {
        switch ($schedule) {
            case '14':
                # Day
                $color = '#d1a110';
                break;
            case '15':
                # Evening
                $color = '#49b229';
                break;
            case '16':
                # Night
                $color = '#29b229';
                break;
            case '17':
                # Off Duty
                $color = '#297bb2';
                break;
            case '18':
                # Annual Leave
                $color = '#603E1F';
                break;
            case '19':
                # Study Leave
                $color = '#0592942';
                break;
            case '20':
                # Maternity Leave
                $color = '#280542';
                break;
            case '21':
                # Other Leave
                $color = '#420524';
                break;
            default:
                # code...
                break;
        }
        return $color;
    }
    public function get_employee_schedule($facility, $person)
    {
        $this->db->select('duty_date');
        $this->db->from('duty_rosta');
        if ($this->department) {
            $this->db->where('department_id', $this->department);
        }
        $this->db->where('facility_id', urldecode($facility));
        $this->db->where('ihris_pid', urldecode($person));
        $this->db->order_by('duty_date', 'DESC');
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            return $query->result();
        } else {
            return false;
        }
    }
    public function get_employee_attendance($facility, $person)
    {
        $this->db->select('date');
        $this->db->from('actuals');
        if ($this->department) {
            $this->db->where('department_id', $this->department);
        }
        $this->db->where('facility_id', urldecode($facility));
        $this->db->where('ihris_pid', urldecode($person));
        $this->db->order_by('date', 'DESC');
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            return $query->result();
        } else {
            return false;
        }
    }
    public function clock_in_employee($userdata)
    {
        $data = array(
            'entry_id' => $userdata->date . $userdata->ihris_pid,
            'ihris_pid' => $userdata->ihris_pid,
            'facility_id' => $userdata->facility_id,
            'time_in' => $userdata->time_in,
            'time_out' => '',
            'status' => $userdata->status,
            'date' => $userdata->date
        );
        $query = $this->db->insert('clk_log', $data);
        if ($query) {
            $query2 = $this->db->insert('clk_log_history', array(
                'ihris_pid' => $userdata->ihris_pid,
                'facility_id' => $userdata->facility_id,
                'entry_id' => $userdata->date . $userdata->ihris_pid,
                'time_in' => $userdata->time_in,
                'status' => $userdata->status,
                'date' => $userdata->date
            ));
            if ($query2) {
                return true;
            } else {
                return false;
            };
        } else {
            return false;
        }
    }
    public function clock_out_employee($userdata)
    {
        $data = array(
            'time_out' => $userdata->time_out,
            'status' => $userdata->status
        );
        $entry_id = $userdata->date . $userdata->ihris_pid;
        $this->db->set($data);
        if ($this->department) {
            $this->db->where('department_id', $this->department);
        }
        $this->db->where('entry_id', $entry_id);
        $query = $this->db->update('clk_log');
        if ($query) {
            $query2 = $this->db->insert('clk_log_history', array(
                'ihris_pid' => $userdata->ihris_pid,
                'facility_id' => $userdata->facility_id,
                'entry_id' => $entry_id,
                'time_out' => $userdata->time_out,
                'status' => $userdata->status,
                'date' => $userdata->date
            ));
            if ($query2) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
    //datatable ops
    function countStaff()
    {
        $facility = $this->facility;
        if ($this->department) {
            $this->db->where('department_id', $this->department);
        }
        if ($facility) {
            $this->db->where('facility_id', $facility);
        }
        $query = $this
            ->db
            ->where('facility_id', $facility)
            ->get('ihrisdata');
        return $query->num_rows();
    }
    function fetchAllStaff($limit, $start, $col, $dir)
    {
        $facility = $this->facility;
        if ($this->department) {
            $this->db->where('department_id', $this->department);
        }
        if ($facility) {
            $this->db->where('facility_id', $facility);
        }
        $query = $this
            ->db
            ->limit($limit, $start)
            ->order_by($col, $dir)
            ->get('ihrisdata');
        if ($query->num_rows() > 0) {
            return $query->result();
        } else {
            return null;
        }
    }
    function searchStaff($limit, $start, $search, $col, $dir)
    {
        $facility = $this->facility;
        if ($this->department) {
            $this->db->where('department_id', $this->department);
        }
        if ($facility) {
            $this->db->where('facility_id', $facility);
        }
        $query = $this
            ->db
            ->like('surname', $search)
            ->or_like('othername', $search)
            ->or_like('firstname', $search)
            ->limit($limit, $start)
            ->order_by($col, $dir)
            ->get('ihrisdata');
        if ($query->num_rows() > 0) {
            return $query->result();
        } else {
            return null;
        }
    }
    public function countforSearch($search)
    {
        $facility = $this->facility;
        if ($this->department) {
            $this->db->where('department_id', $this->department);
        }
        if ($facility) {
            $this->db->where('facility_id', $facility);
        }
        $query = $this->db
            ->like('surname', $search)
            ->or_like('othername', $search)
            ->or_like('firstname', $search)
            ->get('ihrisdata');
        return $query->num_rows();
    }
    public function count_timelogs($search_data, $filter)
    {
        $rows = $this->getTimeLogs($limit = false, $start = FALSE, $search_data, $filter);
        return count($rows);
    }
    public function count_monthlytimelogs($search_data, $filter)
    {
        $rows = $this->groupmonthlyTimeLogs($limit = false, $start = FALSE, $search_data, $filter);
        return count($rows);
    }
    public function groupmonthlyTimeLogs($limit, $start, $search_data, $filter)
    {
        if (empty($search_data['date_from'])) {
            $date_from = date("Y-m-d", strtotime("-1 month"));
            $date_to = date('Y-m-d');
        } else {
            $date_from = $search_data['date_from'];
            $date_to = $search_data['date_to'];
        }
        if (!empty($search_data['name'])) {
            $ids = $this->getIds($search_data['name']);
            $emps = "'" . implode("','", $ids) . "'";
            $namesearch = "and ihris_pid in ($emps)";
        } else {
            $namesearch = "";
        }
        if (!empty($search_data['job'])) {
            $job = $search_data['job'];
            $sjob = "AND job like'$job' ";
        } else {
            $sjob = "";
        }
        if (!empty($limit)) {
            $limit = "LIMIT $start,$limit";
        } else {
            $limit = "";
        }
        $query = $this->db->query("SELECT k.*, SUM(k.time_diff) as m_timediff  from clk_diff k WHERE date BETWEEN '$date_from' AND '$date_to' AND $filter $namesearch $sjob GROUP BY pid, DATE_FORMAT(date, '%Y-%m') order by surname ASC, date ASC $limit");
        $data = $query->result();
        return $data;
    }
    public function getTimeLogs($limit, $start, $search_data, $filter)
    {
        $date_from = $search_data['date_from'];
        $date_to = $search_data['date_to'];
        if (!empty($search_data['name'])) {
            $ids = $this->getIds($search_data['name']);
            $emps = "'" . implode("','", $ids) . "'";
            $namesearch = "and ihrisdata.ihris_pid in ($emps)";
        } else {
            $namesearch = "";
        }
        if (!empty($search_data['job'])) {
            $job = $search_data['job'];
            $sjob = "AND job like'$job' ";
        } else {
            $sjob = "";
        }
        if (!empty($limit)) {
            $limit = "LIMIT $start,$limit";
        } else {
            $limit = "";
        }
        $query = $this->db->query("SELECT surname,firstname,othername,department,job,ihrisdata.ihris_pid as pid,ihrisdata.facility_id as facid, ihrisdata.facility as fac, time_in ,  time_out,clk_log.date as date  from clk_log, ihrisdata WHERE ihrisdata.ihris_pid=clk_log.ihris_pid and clk_log.date BETWEEN '$date_from' AND '$date_to' AND $filter $namesearch $sjob order by surname ASC, clk_log.date ASC $limit");
        $data = $query->result();
        return $data;
    }
    public function getIds($name)
    {
        $query = $this->db->query("SELECT ihris_pid from ihrisdata WHERE firstname like'$name%' OR surname like '$name%' ");
        $result = $query->result();
        $ids = array();
        foreach ($result as $row) {
            array_push($ids, $row->ihris_pid);
        }
        return $ids;
    }
    public function count_Staff($filters)
    {
        $query = $this->db->query("SELECT * from ihrisdata where $filters");
        return $query->num_rows();
    }
    public function timelogscsv($date_from, $date_to, $name, $job, $filter)
    {
        if (!empty($name)) {
            $sname = "AND (firstname like'$name%' OR surname like '$name%') ";
        } else {
            $sname = "";
        }
        if (!empty($job)) {
            $sjob = "AND ihrisdata.job like '$job' ";
        } else {
            $sjob = "";
        }
        $query = $this->db->query("SELECT surname,firstname,othername,department,job,gender,birth_date,cadre,ihrisdata.ihris_pid as pid,ihrisdata.facility_id as facid, ihrisdata.facility as fac, time_in ,  time_out,clk_log.date as date  from clk_log, ihrisdata WHERE ihrisdata.ihris_pid=clk_log.ihris_pid and clk_log.date BETWEEN '$date_from' AND '$date_to'  AND $filter $sname $sjob ORDER BY surname ASC, clk_log.date ASC");
        $data = $query->result();
        return $data;
    }
    public function countTimesheet($filters, $employee)
    {
        $facility = $this->facility;
        if (!empty($employee)) {
            $search = "and ihrisdata.ihris_pid='$employee'";
        }
        else{
            $search = "";
        }
        $query = $this->db->query("SELECT ihris_pid from ihrisdata where $filters $search");
        return $query->num_rows();
    }
    public function fetch_TimeSheet($date_range = FALSE, $start = FALSE, $limit = FALSE, $employee = FALSE, $filters=FALSE, $job = NULL)
    {
        $month = $this->input->post('month');
        $year = $this->input->post('year');
        $date = $year . "-" . $month;
        if ($month != "") {
            $valid_range = $date;
        } else {
            $valid_range = $date_range;
        }
        $search = "";
        if (!empty($employee)) {
            $search = "and ihrisdata.ihris_pid='$employee'";
        }
        if (!empty($job)) {
            $jsearch = "and ihrisdata.job like '$job' ";
        } else {
            $jsearch = "";
        }
        if (!empty($start)) {
            $limit = "LIMIT $limit,$start";
        } else {
            $limit = "";
        }
        $facility = $_SESSION['facility'];
        $all = $this->db->query("select distinct ihrisdata.ihris_pid,CONCAT(
				COALESCE(surname,'','')
				,' ',
				COALESCE(firstname,'','')
				,' ',
				COALESCE(othername,'','')
			) AS fullname,ihrisdata.job from ihrisdata where $filters $search $jsearch  order by surname ASC $limit");
        $data = $all->result_array();
        return $data;
    }
    //get employees
    public function count_employeeTimelogs($ihris_pid, $date = NULL)
    {
        $query = $this->db->query("SELECT ihris_pid from clk_log where ihris_pid='$ihris_pid' and date like'$date-%'");
        return $query->num_rows();
    }
    public function getEmployeeTimeLogs($ihris_pid, $limit = false, $start = FALSE, $search_data = FALSE, $search_data2 = FALSE)
    {
        if ($search_data) {
            $date_from = $search_data['date_from'];
            $date_to = $search_data['date_to'];
            $date_from = date('Y-m-d', strtotime($date_from));
            $date_to = date('Y-m-d', strtotime($date_to));
            $this->db->where("date >= '$date_from' AND date <= '$date_to'");
        }
        if ($search_data2) {
            $date_from = $search_data2['date_from'];
            $date_to = $search_data2['date_to'];
            $date_from = date('Y-m-d', strtotime($date_from));
            $date_to = date('Y-m-d', strtotime($date_to));
            $this->db->where("date >= '$date_from' AND date <= '$date_to'");
        }
        $this->db->where("clk_log.ihris_pid", $ihris_pid);
        $this->db->limit($limit, $start);
        $this->db->order_by('date','ASC');
        $query = $this->db->get("clk_log");
        $data['timelogs'] = $query->result();
        //======userdata====
        $this->db->where('ihris_pid', $ihris_pid);
        $qry = $this->db->get('ihrisdata');
        $data['employee'] = $qry->row();
        //leave info
        $ihris_pid = @$data['employee']->ihris_pid;
        $this->db->where('ihris_pid', $ihris_pid);
        $this->db->where('schedule_id', '25');
        if ($search_data) {
            $this->db->where("date >= '$date_from' AND date <= '$date_to'");
        }
        if ($search_data2) {
            $date_from = $search_data2['date_from'];
            $date_to = $search_data2['date_to'];
            $date_from = date('Y-m-d', strtotime($date_from));
            $date_to = date('Y-m-d', strtotime($date_to));
            $this->db->where("date >= '$date_from' AND date <= '$date_to'");
        }
        $qry = $this->db->get('actuals');
        $data['leaves'] = $qry->result();
        //offs info
        $ihris_pid = @$data['employee']->ihris_pid;
        $this->db->where('ihris_pid', $ihris_pid);
        $schedules = array('24', '27');
        $this->db->where_in('schedule_id', $schedules);
        if ($search_data) {
            $this->db->where("date >= '$date_from' AND date <= '$date_to'");
        }
        if ($search_data2) {
            $date_from = $search_data2['date_from'];
            $date_to = $search_data2['date_to'];
            $date_from = date('Y-m-d', strtotime($date_from));
            $date_to = date('Y-m-d', strtotime($date_to));
            $this->db->where("date >= '$date_from' AND date <= '$date_to'");
        }
        $qry = $this->db->get('actuals');
        $data['offs'] = $qry->result();
        //requests info
        $ihris_pid = @$data['employee']->ihris_pid;
        $this->db->where('ihris_pid', $ihris_pid);
        $this->db->where('schedule_id', '23');
        if ($search_data) {
            $this->db->where("date >= '$date_from' AND date <= '$date_to'");
        }
        if ($search_data2) {
            $date_from = $search_data2['date_from'];
            $date_to = $search_data2['date_to'];
            $date_from = date('Y-m-d', strtotime($date_from));
            $date_to = date('Y-m-d', strtotime($date_to));
            $this->db->where("date >= '$date_from' AND date <= '$date_to'");
        }
        $qry = $this->db->get('actuals');
        $data['requests'] = $qry->result();
        //holidays
        $darray = array();
        $this->db->select('holidaydate');
        $this->db->from('public_holiday');
        $query = $this->db->get();
        $dates = $query->result();
        foreach ($dates as $date) {
            $fdates =   $date->holidaydate;
            array_push($darray, $fdates);
        }
        //days supposed to work
        $ihris_pid = @$data['employee']->ihris_pid;
        $this->db->where('ihris_pid', $ihris_pid);
        $this->db->where('schedule_id', '14');
        if ($search_data) {
            $this->db->where("duty_date >= '$date_from' AND duty_date <= '$date_to'");
        }
        if ($search_data2) {
            $date_from = $search_data2['date_from'];
            $date_to = $search_data2['date_to'];
            $date_from = date('Y-m-d', strtotime($date_from));
            $date_to = date('Y-m-d', strtotime($date_to));
            $this->db->where("duty_date >= '$date_from' AND duty_date <= '$date_to'");
        }
        $this->db->where_not_in('duty_date', $darray);
        $qry = $this->db->get('duty_rosta');
        $data['dutydays'] = $qry->result();
        return $data;
    }

    public function gettimedata($person_id, $date)
    {
        $sql    = "SELECT time_in,time_out FROM clk_log WHERE ihris_pid = '$person_id' AND date='$date'";
        $query  = $this->db->query($sql);
        return  $query->row();
    }


    public function save_employee($postdata)
    {

        $data = array(

            'firstname' => $postdata['firstname'],
            'othername' => $postdata['othername'],
            'surname' => $postdata['surname'],
            'gender' => $postdata['gender'],
            'birth_date' => $postdata['birth_date'],
            'home_district' => $postdata['home_district'],
            'mobile' => $postdata['mobile'],
            'telephone' => $postdata['telephone'],
            'email' => $postdata['email'],
            'place_of_residence' => $postdata['place_of_residence'],
            'nin' => $postdata['nin'],
            'job' => $postdata['job'],
            'job_id' => $postdata['job_id'],
            'salary_grade' => $postdata['salary_grade'],
            'employment_terms' => $postdata['employment_terms'],
            'cadre' => $postdata['cadre'],
            'facility_id' => $postdata['facility_id'],
            'facility' => $postdata['facility'],
            'institution_cateegory' => $postdata['institution_cateegory'],
            'institutiontype_name' => $postdata['institutiontype_name'],
            'institution_level' => $postdata['institution_level'],
            'district_id' => $postdata['district_id']
        );

        $qry = $this->db->insert("ihrisdata", $data);
        $rows = $this->db->affected_rows();

        if ($rows > 0) {

            return "Employee has been Added Successfully";
        } else {

            return "Operation failed";
        }
    }
}
