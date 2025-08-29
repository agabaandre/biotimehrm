<?php
use Adldap\Adldap;
use Adldap\Exceptions\Auth\BindException;

class Ldap_auth {

    protected $adldap;
    protected $config;

    public function __construct() {
        $CI =& get_instance();
        $this->config = $CI->config->item('adldap');

        try {
            $this->adldap = new Adldap();
            $this->adldap->addProvider($this->config);
        } catch (Exception $e) {
            log_message('error', 'LDAP connection error: ' . $e->getMessage());
        }
    }

    public function authenticate($username, $password) {
        try {
            $provider = $this->adldap->connect();
            return $provider->auth()->attempt($username, $password);
        } catch (BindException $e) {
            log_message('error', 'LDAP authentication failed: ' . $e->getMessage());
            return false;
        }
    }
}
