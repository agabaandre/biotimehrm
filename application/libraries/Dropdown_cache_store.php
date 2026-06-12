<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Tiered cache for dropdown / filter payloads: Redis → Memcached → file → miss (caller uses DB).
 */
class Dropdown_cache_store {

	/** @var CI_Controller */
	protected $ci;

	/** @var string */
	protected $key_prefix = 'attend_df_';

	/** @var array<string, bool|null> */
	protected $supported = [
		'redis'     => null,
		'memcached' => null,
	];

	/** @var object|null */
	protected $redis_cache;

	/** @var object|null */
	protected $memcached_cache;

	public function __construct($config = [])
	{
		$this->ci =& get_instance();
		if (isset($config['key_prefix']) && $config['key_prefix'] !== '') {
			$this->key_prefix = (string) $config['key_prefix'];
		}
	}

	/**
	 * @param string $key   Logical cache key (prefix applied automatically).
	 * @param string $file  Absolute path to JSON file fallback.
	 * @return array|null   Decoded payload or null on complete miss.
	 */
	public function read($key, $file = '')
	{
		$key = $this->_key($key);

		$data = $this->_read_redis($key);
		if ($this->_is_payload($data)) {
			return $data;
		}

		$data = $this->_read_memcached($key);
		if ($this->_is_payload($data)) {
			$this->_write_redis($key, $data);
			return $data;
		}

		$data = $this->_read_file($file);
		if ($this->_is_payload($data)) {
			$this->_write_redis($key, $data);
			$this->_write_memcached($key, $data);
			return $data;
		}

		return null;
	}

	/**
	 * Persist payload to every available tier (best effort).
	 *
	 * @param string $key
	 * @param array  $payload
	 * @param int    $ttl
	 * @param string $file
	 * @return array{redis:bool,memcached:bool,file:bool}
	 */
	public function write($key, array $payload, $ttl, $file = '')
	{
		$key = $this->_key($key);

		return [
			'redis'     => $this->_write_redis($key, $payload, $ttl),
			'memcached' => $this->_write_memcached($key, $payload, $ttl),
			'file'      => $this->_write_file($file, $payload),
		];
	}

	/**
	 * @param string $key
	 * @param string $file
	 */
	public function delete($key, $file = '')
	{
		$key = $this->_key($key);
		$this->_delete_redis($key);
		$this->_delete_memcached($key);
		if ($file !== '' && is_file($file)) {
			@unlink($file);
		}
	}

	/**
	 * @return array{redis:bool,memcached:bool,file:bool}
	 */
	public function availability()
	{
		return [
			'redis'     => $this->_redis_supported(),
			'memcached' => $this->_memcached_supported(),
			'file'      => is_dir(APPPATH . 'cache') && is_writable(APPPATH . 'cache'),
		];
	}

	/**
	 * @return string|null  redis|memcached|file|null
	 */
	public function read_source($key, $file = '')
	{
		$key = $this->_key($key);

		if ($this->_is_payload($this->_read_redis($key))) {
			return 'redis';
		}
		if ($this->_is_payload($this->_read_memcached($key))) {
			return 'memcached';
		}
		if ($this->_is_payload($this->_read_file($file))) {
			return 'file';
		}

		return null;
	}

	protected function _key($key)
	{
		return $this->key_prefix . preg_replace('/[^a-zA-Z0-9_\-:.]/', '_', (string) $key);
	}

	protected function _is_payload($data)
	{
		return is_array($data)
			&& isset($data['districts'], $data['facilities_by_district'])
			&& is_array($data['districts'])
			&& is_array($data['facilities_by_district']);
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
			log_message('debug', 'Dropdown_cache_store redis read failed: ' . $e->getMessage());
			$this->supported['redis'] = false;
			return null;
		}
	}

	protected function _write_redis($key, array $payload, $ttl = null)
	{
		$cache = $this->_redis_cache();
		if ($cache === null) {
			return false;
		}
		try {
			$ttl = ($ttl === null) ? 604800 : (int) $ttl;
			return (bool) $cache->save($key, $payload, $ttl);
		} catch (Throwable $e) {
			log_message('debug', 'Dropdown_cache_store redis write failed: ' . $e->getMessage());
			$this->supported['redis'] = false;
			return false;
		}
	}

	protected function _delete_redis($key)
	{
		$cache = $this->_redis_cache();
		if ($cache === null) {
			return false;
		}
		try {
			return (bool) $cache->delete($key);
		} catch (Throwable $e) {
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
			log_message('debug', 'Dropdown_cache_store memcached read failed: ' . $e->getMessage());
			$this->supported['memcached'] = false;
			return null;
		}
	}

	protected function _write_memcached($key, array $payload, $ttl = null)
	{
		$cache = $this->_memcached_cache();
		if ($cache === null) {
			return false;
		}
		try {
			$ttl = ($ttl === null) ? 604800 : (int) $ttl;
			return (bool) $cache->save($key, $payload, $ttl);
		} catch (Throwable $e) {
			log_message('debug', 'Dropdown_cache_store memcached write failed: ' . $e->getMessage());
			$this->supported['memcached'] = false;
			return false;
		}
	}

	protected function _delete_memcached($key)
	{
		$cache = $this->_memcached_cache();
		if ($cache === null) {
			return false;
		}
		try {
			return (bool) $cache->delete($key);
		} catch (Throwable $e) {
			return false;
		}
	}

	protected function _read_file($file)
	{
		$file = trim((string) $file);
		if ($file === '' || !is_readable($file)) {
			return null;
		}
		$raw = @file_get_contents($file);
		if ($raw === false || $raw === '') {
			return null;
		}
		$data = json_decode($raw, true);
		return is_array($data) ? $data : null;
	}

	protected function _write_file($file, array $payload)
	{
		$file = trim((string) $file);
		if ($file === '') {
			return false;
		}
		$dir = dirname($file);
		if (!is_dir($dir)) {
			if (!@mkdir($dir, 0755, true)) {
				return false;
			}
		}
		if (!is_writable($dir)) {
			return false;
		}
		$json = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
		$tmp = $file . '.tmp';
		if (@file_put_contents($tmp, $json, LOCK_EX) === false) {
			return false;
		}
		if (!@rename($tmp, $file)) {
			@unlink($tmp);
			return false;
		}
		return true;
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
			$probe = 'dropdown_cache_probe_' . mt_rand();
			if (!$cache->save($probe, 1, 10)) {
				$this->supported['redis'] = false;
				return null;
			}
			$cache->delete($probe);
			$this->supported['redis'] = true;
			return $this->redis_cache = $cache;
		} catch (Throwable $e) {
			log_message('debug', 'Dropdown_cache_store redis unavailable: ' . $e->getMessage());
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
			$probe = 'dropdown_cache_probe_' . mt_rand();
			if (!$cache->save($probe, 1, 10)) {
				$this->supported['memcached'] = false;
				return null;
			}
			$cache->delete($probe);
			$this->supported['memcached'] = true;
			return $this->memcached_cache = $cache;
		} catch (Throwable $e) {
			log_message('debug', 'Dropdown_cache_store memcached unavailable: ' . $e->getMessage());
			$this->supported['memcached'] = false;
			return null;
		}
	}

	protected function _redis_supported()
	{
		return $this->_redis_cache() !== null;
	}

	protected function _memcached_supported()
	{
		return $this->_memcached_cache() !== null;
	}
}
