<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Tiered cache for dashboard payloads: Redis → Memcached → miss (caller queries DB).
 */
class Dashboard_cache_store {

	/** @var CI_Controller */
	protected $ci;

	/** @var string */
	protected $key_prefix = 'attend_dash_';

	/** @var int */
	protected $version_ttl = 86400;

	/** @var array<string, bool|null> */
	protected $supported = ['redis' => null, 'memcached' => null];

	/** @var object|null */
	protected $redis_cache;

	/** @var object|null */
	protected $memcached_cache;

	public function __construct($config = [])
	{
		$this->ci =& get_instance();
		$this->ci->config->load('dashboard_cache', true, true);
		$cfg = $this->ci->config->item('dashboard_cache');
		if (is_array($cfg)) {
			if (!empty($cfg['key_prefix'])) {
				$this->key_prefix = (string) $cfg['key_prefix'];
			}
			if (isset($cfg['version_ttl'])) {
				$this->version_ttl = (int) $cfg['version_ttl'];
			}
		}
		if (isset($config['key_prefix']) && $config['key_prefix'] !== '') {
			$this->key_prefix = (string) $config['key_prefix'];
		}
	}

	/**
	 * @param string $key
	 * @return array|null
	 */
	public function read($key)
	{
		$key = $this->_key($key);
		$data = $this->_read_redis($key);
		if (is_array($data)) {
			return $data;
		}
		$data = $this->_read_memcached($key);
		return is_array($data) ? $data : null;
	}

	/**
	 * @param string $key
	 * @param array  $payload
	 * @param int    $ttl
	 */
	public function write($key, array $payload, $ttl)
	{
		$key = $this->_key($key);
		$ttl = max(5, (int) $ttl);
		$this->_write_redis($key, $payload, $ttl);
		$this->_write_memcached($key, $payload, $ttl);
	}

	/**
	 * Bump cache version for a facility so all scoped keys refresh.
	 *
	 * @param string $facility_id
	 */
	public function invalidateFacility($facility_id)
	{
		$facility_id = trim((string) $facility_id);
		if ($facility_id === '') {
			return;
		}
		$version_key = $this->_key('ver_' . $facility_id);
		$current = (int) $this->_read_redis($version_key);
		if ($current <= 0) {
			$current = (int) $this->_read_memcached($version_key);
		}
		$new_version = $current + 1;
		$this->_write_redis($version_key, $new_version, $this->version_ttl);
		$this->_write_memcached($version_key, $new_version, $this->version_ttl);
	}

	/**
	 * @param string $facility_id
	 * @return int
	 */
	public function facilityVersion($facility_id)
	{
		$facility_id = trim((string) $facility_id);
		if ($facility_id === '') {
			return 0;
		}
		$version_key = $this->_key('ver_' . $facility_id);
		$v = $this->_read_redis($version_key);
		if (!is_numeric($v)) {
			$v = $this->_read_memcached($version_key);
		}
		return is_numeric($v) ? (int) $v : 0;
	}

	/**
	 * @return array{redis:bool,memcached:bool}
	 */
	public function availability()
	{
		if ($this->supported['redis'] === false) {
			$redis = false;
		} elseif ($this->supported['redis'] === true) {
			$redis = true;
		} else {
			$redis = extension_loaded('redis');
		}

		if ($this->supported['memcached'] === false) {
			$memcached = false;
		} elseif ($this->supported['memcached'] === true) {
			$memcached = true;
		} else {
			$memcached = extension_loaded('memcached') || extension_loaded('memcache');
		}

		return [
			'redis'     => $redis,
			'memcached' => $memcached,
		];
	}

	protected function _key($key)
	{
		return $this->key_prefix . preg_replace('/[^a-zA-Z0-9_\-:.]/', '_', (string) $key);
	}

	protected function _read_redis($key)
	{
		$cache = $this->_redis_cache();
		if ($cache === null) {
			return null;
		}
		try {
			$data = $cache->get($key);
			return ($data === false || $data === null) ? null : $data;
		} catch (Throwable $e) {
			$this->supported['redis'] = false;
			return null;
		}
	}

	protected function _write_redis($key, $payload, $ttl)
	{
		$cache = $this->_redis_cache();
		if ($cache === null) {
			return false;
		}
		try {
			return (bool) $cache->save($key, $payload, $ttl);
		} catch (Throwable $e) {
			$this->supported['redis'] = false;
			return false;
		}
	}

	protected function _read_memcached($key)
	{
		$cache = $this->_memcached_cache();
		if ($cache === null) {
			return null;
		}
		try {
			$data = $cache->get($key);
			return ($data === false || $data === null) ? null : $data;
		} catch (Throwable $e) {
			$this->supported['memcached'] = false;
			return null;
		}
	}

	protected function _write_memcached($key, $payload, $ttl)
	{
		$cache = $this->_memcached_cache();
		if ($cache === null) {
			return false;
		}
		try {
			return (bool) $cache->save($key, $payload, $ttl);
		} catch (Throwable $e) {
			$this->supported['memcached'] = false;
			return false;
		}
	}

	protected function _redis_cache()
	{
		if ($this->supported['redis'] === false) {
			return null;
		}
		if ($this->redis_cache !== null) {
			return $this->redis_cache;
		}
		if (!extension_loaded('redis')) {
			$this->supported['redis'] = false;
			return null;
		}
		try {
			require_once BASEPATH . 'libraries/Cache/Cache.php';
			$cache = new CI_Cache(['adapter' => 'redis', 'backup' => 'dummy', 'key_prefix' => '']);
			if (!$cache->is_supported('redis')) {
				$this->supported['redis'] = false;
				return null;
			}
			$this->supported['redis'] = true;
			return $this->redis_cache = $cache;
		} catch (Throwable $e) {
			$this->supported['redis'] = false;
			return null;
		}
	}

	protected function _memcached_cache()
	{
		if ($this->supported['memcached'] === false) {
			return null;
		}
		if ($this->memcached_cache !== null) {
			return $this->memcached_cache;
		}
		if (!extension_loaded('memcached') && !extension_loaded('memcache')) {
			$this->supported['memcached'] = false;
			return null;
		}
		try {
			require_once BASEPATH . 'libraries/Cache/Cache.php';
			$cache = new CI_Cache(['adapter' => 'memcached', 'backup' => 'dummy', 'key_prefix' => '']);
			if (!$cache->is_supported('memcached')) {
				$this->supported['memcached'] = false;
				return null;
			}
			$this->supported['memcached'] = true;
			return $this->memcached_cache = $cache;
		} catch (Throwable $e) {
			$this->supported['memcached'] = false;
			return null;
		}
	}
}
