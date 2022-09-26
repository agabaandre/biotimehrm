<?php

/*
 * Custom Helpers
 *
 */

//render with navigation
if (!function_exists('render')) {

    function render($view, $data = [], $plain = false)
    {

        $data['view'] = $view;

        //plain renders without navigation
        $template_method = ($plain) ? 'templates/plain' : 'templates/main';

        $data['settings'] = settings();

        echo Modules::run($template_method, $data);
    }
}

//render-front-web with navigation
if (!function_exists('render_client')) {

    function render_client($view, $data = [], $plain = false)
    {

        $data['view'] = $view;

        //renders without navigation
        $template_method = 'templates/front_end_client';

        $data['settings'] = settings();

        echo Modules::run($template_method, $data);
    }
}

//render-front-main website
if (!function_exists('render_site')) {

    function render_site($view, $data = [], $plain = false)
    {

        $data['view'] = $view;

        //renders without navigation
        $template_method = 'templates/front_end_site';

        $data['settings'] = settings();

        echo Modules::run($template_method, $data);
    }
}


//retrieve system settings like them and display
if (!function_exists('settings')) {
    function settings()
    {
        $ci = &get_instance();
        $settings = $ci->settingsmodel->get_row();
        return $settings;
    }
}

//set flash data
if (!function_exists('set_flash')) {
    function set_flash($message, $isError = false)
    {
        // Get a reference to the controller object
        $ci = &get_instance();
        $msgClass =  ($isError) ? 'danger' : 'success';
        return $ci->session->set_flashdata($msgClass, $message);
    }
}

if (!function_exists('get_flash')) {
    function get_flash($key)
    {
        // Get a reference to the controller object
        $ci = &get_instance();
        return $ci->session->flashdata($key);
    }
}

//read from language file

if (!function_exists('lang')) {
    function lang($string, $plural = false, $capital = false)
    {
        $ci = &get_instance();

        $phrase = $ci->lang->line($string);

        if ($plural)
            $phrase = $phrase . "s";
        if ($capital)
            $phrase = strtoupper($phrase);
        return $phrase;
    }
}

//print old form data
if (!function_exists('old')) {
    function old($field)
    {
        $ci = &get_instance();
        return ($ci->session->flashdata('form_data')) ? html_escape($ci->session->flashdata('form_data')[$field]) : null;
    }
}

//print old form data
if (!function_exists('truncate')) {
    function truncate($content, $limit)
    {
        return (strlen($content) > $limit) ? substr($content, 0, $limit) . "..." : $content;
    }
}

if (!function_exists('paginate')) {
    function paginate($route, $totals, $perPage = 20, $segment = 3)
    {
        $ci = &get_instance();
        $config = array();

        $config["base_url"] = base_url() . $route;
        $config["total_rows"]     = $totals;
        $config["per_page"]       = $perPage;
        $config["uri_segment"]    = $segment;
        $config['full_tag_open'] = '<ul class="pagination">';
        $config['full_tag_close'] = '</ul>';
        $config['attributes'] = ['class' => 'page-link'];
        $config['first_link'] = false;
        $config['last_link'] = false;
        $config['first_tag_open'] = '<li class="page-item">';
        $config['first_tag_close'] = '</li>';
        $config['prev_link'] = '&laquo';
        $config['prev_tag_open'] = '<li class="page-item">';
        $config['prev_tag_close'] = '</li>';
        $config['next_link'] = '&raquo';
        $config['next_tag_open'] = '<li class="page-item">';
        $config['next_tag_close'] = '</li>';
        $config['last_tag_open'] = '<li class="page-item">';
        $config['last_tag_close'] = '</li>';
        $config['cur_tag_open'] = '<li class="page-item active"><a href="#" class="page-link">';
        $config['cur_tag_close'] = '<span class="sr-only">(current)</span></a></li>';
        $config['num_tag_open'] = '<li class="page-item">';
        $config['num_tag_close'] = '</li>';
        $config['use_page_numbers'] = false;

        $ci->pagination->initialize($config);

        return $ci->pagination->create_links();
    }
}

if (!function_exists('time_ago')) {

    function time_ago($timestamp)
    {
        $time_ago = strtotime($timestamp);
        $current_time = time();
        $time_difference = $current_time - $time_ago;
        $seconds = $time_difference;

        $minutes = round($seconds / 60);           // value 60 is seconds
        $hours = round($seconds / 3600);           //value 3600 is 60 minutes * 60 sec
        $days = round($seconds / 86400);          //86400 = 24 * 60 * 60;
        $weeks = round($seconds / 604800);          // 7*24*60*60;
        $months = round($seconds / 2629440);     //((365+365+365+365+366)/5/12)*24*60*60
        $years = round($seconds / 31553280);     //(365+365+365+365+366)/5 * 24 * 60 * 60

        if ($seconds <= 60) {
            return "Just now";
        } else if ($minutes <= 60) {
            if ($minutes == 1) {
                return "1 " . "Minute" . " " . "ago";
            } else {
                return $minutes . " " . "Minutes" . " ago";
            }
        } else if ($hours <= 24) {
            if ($hours == 1) {
                return "1 " . "hour" . " " . "ago";
            } else {
                return $hours . " " . "hours" . " " . "ago";
            }
        } else if ($days <= 30) {
            if ($days == 1) {
                return "1 " . "day" . " " . "ago";
            } else {
                return $days . " " . "days" . " " . "ago";
            }
        } else if ($months <= 12) {
            if ($months == 1) {
                return "1 " . "month" . " " . "ago";
            } else {
                return $months . " " . "months" . " " . "ago";
            }
        } else {
            if ($years == 1) {
                return "1 " . "year" . " " . "ago";
            } else {
                return $years . " " . "years" . " " . "ago";
            }
        }
    }
}


if (!function_exists('is_past')) {

    function is_past($date)
    {
        $date_now = new DateTime();
        $date2    = new DateTime($date);
        return ($date_now > $date2);
    }
}

if (!function_exists('text_date')) {

    function text_date($date)
    {
        return date("M jS, Y", strtotime($date));;
    }
}

if (!function_exists('setting')) {

    function setting()
    {
        $ci = &get_instance();
        return $ci->db->get('setting')->row();
    }
}


if (!function_exists('user_session')) {
    function user_session()
    {
        $ci = &get_instance();
        return ($ci->session->userdata('id')) ? (object) $ci->session->userdata() : (object) ['is_logged_in' => false, 'is_admin' => false];
    }
}


if (!function_exists('dd')) {
    function dd($data)
    {
        print_r($data);
        exit();
    }
}


if (!function_exists('poeple_titles')) {
    function poeple_titles()
    {
        $titles = ['Mr.', 'Mrs.', 'Ms.', 'Hon.', 'Dr.', 'Pr.', 'He.', 'Hh.'];
        return $titles;
    }
}



//date diff
if (!function_exists('date_difference')) {
    function date_difference($date1, $date2, $format = '%a')
    {
        $datetime_1 = date_create($date1);
        $datetime_2 = date_create($date2);
        $diff = date_diff($datetime_1, $datetime_2);
        return $diff->format($format);
    }
}



//generate unique id
if (!function_exists('generate_unique_id')) {
    function generate_unique_id()
    {
        $id = uniqid("", TRUE);
        $id = str_replace(".", "-", $id);
        return $id . "-" . rand(10000000, 99999999);
    }
}


//generate unique id
if (!function_exists('flash_form')) {
    function flash_form($data = null, $key = 'form_data')
    {
        $ci = &get_instance();

        if ($data == null)
            $data = $ci->input->post();

        $ci->session->set_flashdata($key, $data);
    }
}


function check_logged_in()
{

    if (!user_session()->is_logged_in)
        redirect(base_url('client/login'));
}

function check_admin_access()
{

    if (!user_session()->is_admin)
        redirect(base_url("admin"));
}
if (!function_exists('render_csv_data')) {
    function render_csv_data($datas, $filename)
    {
        //datas should be assoc array
        $csv_file = $filename;
        header("Content-Type: text/csv");
        header("Content-Disposition: attachment; filename=\"$csv_file\"");
        $fh = fopen('php://output', 'w');

        $is_coloumn = true;
        if (!empty($datas)) {
            foreach ($datas as $data) {

                if ($is_coloumn) {
                    fputcsv($fh, array_keys(($data)));
                    $is_coloumn = false;
                }
                fputcsv($fh, array_values($data));
            }
            fclose($fh);
        }
        exit;
    }
}
