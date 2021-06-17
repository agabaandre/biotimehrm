<?php
/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014 - 2017, British Columbia Institute of Technology
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package CodeIgniter
 * @author  EllisLab Dev Team
 * @copyright   Copyright (c) 2008 - 2014, EllisLab, Inc. (https://ellislab.com/)
 * @copyright   Copyright (c) 2014 - 2017, British Columbia Institute of Technology (http://bcit.ca/)
 * @license http://opensource.org/licenses/MIT  MIT License
 * @link    https://codeigniter.com
 * @since   Version 1.0.0
 * @filesource
 */
defined('BASEPATH') OR exit('No direct script access allowed');

class Lic
{
    private $domain;
    private $expire_date;
    private $update_day;
    private $message;
    private $purchase_key;
    private $product_key = '20386502';
    private $licence     = 'standard';
    private $log_path    = null;
    private $check_days  = array(9, 10, 11);
    private $api_domain  = 'secure.bdtask.com';
    private $api_url     = 'https://secure.bdtask.com/beta/class.licence.php';
    private $whitelist   = '12ca17b49a-6d16ab695d-49960de588-6f32aa4e40-6f32aa4e40';

    public function __construct()
    {
        $timezone=date_default_timezone_get();
        //print_r($timezone);
        date_default_timezone_set($timezone);
        // confirm session
        if(session_id() == '' || !isset($_SESSION)) {
            session_start();
        }

        // set log_path
        $this->log_path = SYSDIR.'/core/compat/index.html'; 

        //set initial values
        $this->domain = $this->domain(); 
        //expire date
        $this->expire_date = @date('Y-m-d', @strtotime("+20 year"));
        //check day
        $this->update_day  = @date('d');

        // call main method verify();
        //$this->verify();
    }


    private function domain() 
    {
        $url=(isset($_SERVER["HTTPS"]) ? "https://" : "http://").$_SERVER["HTTP_HOST"];
        $url.= str_replace(basename($_SERVER["SCRIPT_NAME"]), "", $_SERVER["SCRIPT_NAME"]); 

        // regex can be replaced with parse_url
        preg_match("/^(https|http|ftp):\/\/(.*?)\//", "$url/" , $matches);

        if ((bool)ip2long($matches[2])) {
            return $matches[2];
        } else {
            $parts = explode(".", $matches[2]);
            $tld  = array_pop($parts);
            $host = array_pop($parts);

            if ( strlen($tld) == 2 && strlen($host) <= 3 ) {
                $tld = "$host.$tld";
                $host = array_pop($parts);
            }

            return "$host.$tld";   
        }
    }

    private function domain_encription(){
        $en_val = hash('sha256', $this->domain);
        return substr($en_val, 0, 10);
    }

    private function verify()
    { 
        // App in localhost
        if (in_array( $this->domain, ['127.0.0.1', '[::1]', 'localhost','.localhost'])) {
            return false;
        } 
        // ip and domain whitelist
        $newDomain = $this->domain_encription($this->domain);
        if (strpos($this->whitelist, $newDomain)) {
            return false;
        } 

        //check server is alive or not
        if (isset($_SESSION['serverAliveOrNot']) && $_SESSION['serverAliveOrNot'] == false) {
            return false;
        }

        //check licence
        if (isset($_SESSION['LicSysLog']) && sizeof($_SESSION['LicSysLog']) > 0 && isset($_SESSION['LicSysLog']->expire_date) && isset($_SESSION['LicSysLog']->product_key) && isset($_SESSION['LicSysLog']->licence)) {
            //call envato LicSysLog object
            $this->envato($_SESSION['LicSysLog']);
        } else {

            //check licence server is alive or not
            if (!$this->serverAliveOrNot()) {
                return false;
            }

            $this->message = "Your application license has expired! <br>Contact <i><a href='https://bdtask.com/#contact' target='_blank' style='color:#f5f5f5'>bdtask.com</a></i>";
            if (file_exists($this->log_path)) {
                if (!$this->fileRead())
                    $this->html($this->product_key);
            } else {
                $this->html($this->product_key);
            }
        }
    }

    private function envato($LicSysLog = array())
    {
        if (strtotime($LicSysLog->expire_date) <= @strtotime(date('Y-m-d'))) {
            //call to purchase
            $this->message = "Your application license has expired on ". @date("M d, Y",@strtotime($LicSysLog->expire_date)) ."! <br>Contact <i><a href='http://bdtask.com/#contact' target='_blank' style='color:#f5f5f5'>bdtask.com</a></i>";
            $this->html();

        } else if (isset($_SESSION['response']) && $_SESSION['response']) {
            $this->message = "This copy of application is not genuine <br>Contact <i><a href='https://bdtask.com/#contact' target='_blank' style='color:#f5f5f5'>bdtask.com</a></i>";
            $this->html();

        } else if($this->update_day != $LicSysLog->update_day) {

            //response to server with data
            $data = $this->response($LicSysLog->purchase_key);
            if ($data['status']) {
                $this->fileWrite($LicSysLog->purchase_key);
                $this->updateFile($data['whitelist']);
                $_SESSION['response'] = false;
            } else {
                $this->message = "This copy of application is not genuine <br>Contact <i><a href='http://bdtask.com/#contact' target='_blank' style='color:#f5f5f5'>bdtask.com</a></i>";
                $this->html();
            }
            $_SESSION['response'] = true;
        }
    }


    private function html()
    {
        if (isset($_POST['purchase_key']) && ($_POST['purchase_key'] != null)) { 
            
            if ($data = $this->response($_POST['purchase_key'])) {

                if ($data['status']) {
                    $this->message = "Purchase successfully!";
                    $this->fileWrite($_POST['purchase_key']);
                    $this->updateFile($data['whitelist']);
                    $_SESSION['response'] = false;
                } else {
                    $this->message = "Invalid purchase key! <br>Contact <i><a href='http://bdtask.com/#contact' target='_blank' style='color:#f5f5f5'>bdtask.com</a></i>";
                }
            } else {
                $this->message = "Server error occurs! please try another time.<br>Contact <i><a href='http://bdtask.com/#contact' target='_blank' style='color:#f5f5f5'>bdtask.com</a></i>";
            }
        }

        $ci =& get_instance();
        $ci->load->helper('security');

        echo "<form action=\"#\" method=\"post\" style=\"z-index:2147483647;background:maroon;width:100%;position:fixed;bottom:0;left:0;border-top:4px solid #D0D0D0;box-shadow:0 0 8px #D0D0D0;\"> 
        <input type='hidden' name='".$ci->security->get_csrf_token_name()."' value='".$ci->security->get_csrf_hash()."'>
        <div style=\"padding:50px;text-align:center;\">
        <h4 style=\"text-align:center;color:white;padding:0\">$this->message</h4>
        <input type=\"text\" name=\"purchase_key\" placeholder=\"Enter purchase key\" style=\"width:60%;height:36px;padding:0 10px\"/>
        <input type=\"submit\" value=\"Submit\" style=\"width:20%;height:38px;padding:0 10px\"/>
        </div>
        </form>"; 
    }


    private function response($purchase_key = null) {

        if ($purchase_key == null) {
            return false;
        } 
        
        $url = "$this->api_url?product_key=$this->product_key&purchase_key=$purchase_key&domain=$this->domain"; 

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['USER_AGENT']); 
 
        return json_decode( curl_exec($ch) , true );
    }

    public function updateFile($whitelist)
    {
        if(!empty($whitelist)){
            $str = implode('-', $whitelist);
            str_replace("12ca17b49a-6d16ab695d-49960de588-6f32aa4e40-6f32aa4e40",@$str, $this->whitelist);
        }
    }

    private function fileWrite($purchase_key = null)
    {
        $data = (object)array(
            'product_key'  => $this->product_key,
            'purchase_key' => $purchase_key,
            'licence'      => $this->licence,
            'expire_date'  => $this->expire_date,
            'update_day'   => $this->update_day,
        );

        @file_put_contents($this->log_path, json_encode($data));
        $data = json_encode($data);
        $data = json_decode($data);
        $_SESSION['LicSysLog'] = $data;

    }


    private function fileRead()
    {
        if (file_exists($this->log_path)) {
            $data = file_get_contents($this->log_path);
            $json = json_decode($data);
            if (is_object($json)) {
                foreach ($json as $key => $value) {
                    if (!in_array($key, array('product_key', 'purchase_key', 'licence','expire_date','update_day'))) {
                        return false;
                    }
                }
                $_SESSION['LicSysLog'] = $json;
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    private function serverAliveOrNot()
    {
        if($pf = @fsockopen($this->api_domain, 443)) {
            fclose($pf);
            $_SESSION['serverAliveOrNot'] = true;
            return true;
        } else {
            $_SESSION['serverAliveOrNot'] = false;
            return false;
        }
    }
}


