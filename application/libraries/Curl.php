<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 *  Class used to Curl URL in CodeIgniter 3
 *
 *  @author
 *    Pande Made Saka Mahendra Arioka
 *    085338845666 / saka@sakarioka.com
 *
 *  @copyright
 *    Sakarioka.com
 *    https://www.sakarioka.com
 *
 */

class Curl {

  protected $ch           = null;
  protected $url          = '';
  protected $params       = [];
  protected $token_name   = '';
  protected $token_value  = '';
  protected $method       = 'get';
  protected $content_type = 'json';

  public function __construct($data = [])
  {
    $this->init();
  }

  // create curl resource 
  public function init($reset_token = false)
  {
    $this->ch = curl_init();

    if ($reset_token) {
      $this->token_name  = '';
      $this->token_value = '';
    }

    $this->default_option();
  }

  // set up curl url 
  public function set_url($url)
  {
    $this->url = $url;
  }

  public function set_params($data)
  {
    if (is_array($data)) {
      $this->params = array_merge($this->params, $data);
    } else {
      $params = $this->params;
      $params[] = $data;
      $this->params = $params;
    }
  }

  public function set_param($key, $value)
  {
    $this->params[$key] = $value;
  }

  public function get_params()
  {
    return $this->params;
  }

  public function get_param($key)
  {
    return $this->params[$key];
  }

  public function set_token_name($value)
  {
    $this->token_name = $value;
  }

  public function set_token_value($value)
  {
    $this->token_value = $value;
  }

  public function get_token_name()
  {
    return $this->token_name;
  }

  public function get_token_value()
  {
    return $this->token_value;
  }

  public function set_token($key, $value)
  {
    $this->token_name  = $key;
    $this->token_value = $value;
  }

  public function get_token()
  {
    if ($this->has_token()) {
      return [$this->token_name => $this->token_value];
    }
  }

  public function has_token()
  {
    return !empty($this->token_name) && !empty($this->token_value);
  }

  public function set_method($method)
  {
    $this->method = $method;
  }

  public function get_method()
  {
    return $this->method;
  }

  /**
   *  Send CURL and get result
   *  
   *  @author
   *    Saka Mahendra Arioka
   *    saka@sakarioka.com / 085338845666
   * 
   *  @copyright
   *    Sakarioka.com
   *    https://www.sakarioka.com
   */ 
  public function result($return_string = false, $close = true)
  {
    // get params and URL
    $params = $this->params;
    $url    = $this->url;

    // check token if available
    if ($this->has_token()) {
      $params += $this->get_token();
    }

    // check content type used
    if ($this->content_type == 'json') {
      $send_params = json_encode($params);
    } else {
      $send_params = http_build_query($params);
    }

    // set up the request
    switch ($this->method) {
      case 'post':
        curl_setopt($this->ch, CURLOPT_POST, 1);
      break;
      case 'put':
        curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, 'PUT');
      break;
      case 'delete':
        curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
      break;
      default: return false;
    }

    // insert params
    switch ($this->method) {
      case 'get':
        if (!empty($params)) {
          $url .= '?'.$send_params;
        }
      break;
      case 'post':
      case 'put':
      case 'delete':
        $this->set_option(CURLOPT_POSTFIELDS, $send_params);

        if ($this->content_type == 'json') {
          $this->set_option(CURLOPT_HEADER, ['Content-Type:application/json']);
        }
      break;
      default: return false;
    }

    // set option for URL
    $this->set_option(CURLOPT_URL, $url);

    // get output
    $output = curl_exec($this->ch); 

    // close connection
    if ($close) {
      $this->close();
    }

    // if return string is true the output will not converted into array
    if ($return_string) {
      return $output;
    } else {
      return json_decode($output, true);
    }  
  }

  // close curl resource to free up system resources 
  public function close()
  {
    curl_close($this->ch);
  }

  public function default_option()
  {
    // return the transfer as a string 
    $this->set_option(CURLOPT_RETURNTRANSFER, 1);

    // connection time
    $this->set_option(CURLOPT_CONNECTTIMEOUT, 0);

    // waiting time
    $this->set_option(CURLOPT_TIMEOUT, 400);
  }

  public function set_option($key, $value)
  {
    curl_setopt($this->ch, $key, $value); 
  }

  public function post($key = null)
  {
    if (isset($key) && !is_empty($key)) {
      if (isset($_POST[$key])) {
        return $_POST[$key];
      } else {
        return [];
      }
    } else {
      $post = $this->replace_empty_string_with_null($_POST);
      return $this->delete_unused_key($post);
    }
  }

  public function get($key = null, $convert_json = false)
  {
    if (isset($key) && !is_empty($key)) {
      if (isset($_GET[$key]) && $_GET[$key] !== null) {
        if ($_GET[$key][0] == '{' || $convert_json == true) {
          return json_decode($_GET[$key], true);
        }

        return $_GET[$key];
      } else {
        return [];
      }
    } else {
      $get = $this->replace_empty_string_with_null($_GET);
      return $this->delete_unused_key($get);
    }
  }

  public function put($key = null, $convert_json = false)
  {
    $CI =& get_instance();
    parse_str($CI->input->raw_input_stream, $params);

    if (isset($key) && !is_empty($key)) {
      if (isset($params[$key]) && $params[$key] !== null) {
        if ($convert_json) {
          return json_decode($params[$key], true);
        }

        return $params[$key];
      } else {
        return [];
      }
    } else {
      $put = $this->replace_empty_string_with_null($params);
      return $this->delete_unused_key($params);
    }
  }

  public function delete_unused_key($data)
  {
    // unset if found token
    if (isset($data[$this->token_name])) {
      unset($data[$this->token_name]);
    }

    // unset if found SUBMIT
    if (isset($data['SUBMIT'])) {
      unset($data['SUBMIT']);
    }
  
    return $data; 
  }

  public function replace_empty_string_with_null($data)
  {
    if (!empty($data)) {
      foreach ($data as $key => $value) {
        if ($value === '') {
          $data[$key] = null;
        }
      }
    }

    return $data;
  }

  public function check_status($result)
  {
    if (isset($result['status']) && $result['status'] == 200 && empty($result['error'])) {
      return true;
    }

    return false;
  }

  public function check_api_data($data, $validator = [])
  {
    foreach ($data as $key => $value) {
      if (!in_array($key, $validator)) {
        return false;
      }
    }

    return true;
  }

  public function add_table_name($data, $table_name)
  {
    if (is_array($data)) {
      $return_data = [];

      foreach ($data as $key => $value) {
        $return_data[$table_name.'.'.$key] = $value;
      }

      return $return_data;
    } else {
      return $table_name.'.'.$data;
    }
  }
}
