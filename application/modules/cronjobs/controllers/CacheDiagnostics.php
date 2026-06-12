<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * CLI diagnostics for Redis / Memcached cache tiers.
 *
 *   php index.php cronjobs/CacheDiagnostics/redisPing
 */
class CacheDiagnostics extends MX_Controller {

	public function redisPing()
	{
		if (!$this->input->is_cli_request()) {
			show_404();
			return;
		}

		$result = [
			'php_version'      => PHP_VERSION,
			'redis_extension'  => extension_loaded('redis'),
			'memcached_ext'    => extension_loaded('memcached') || extension_loaded('memcache'),
			'redis_write_ok'   => false,
			'redis_read_ok'    => false,
			'dashboard_layer'  => null,
			'dropdown_layer'   => null,
			'redis_keys_hint'  => 'Scan with: redis-cli --scan --pattern "attend_*"',
		];

		try {
			$this->load->library('dashboard_cache_store', null, 'dash_cache');
			$test_key = 'diag_' . date('YmdHis');
			$payload = ['ok' => true, 'ts' => time()];
			$result['dashboard_layer'] = $this->dash_cache->availability();
			$this->dash_cache->write($test_key, $payload, 120);
			$read = $this->dash_cache->read($test_key);
			$result['redis_write_ok'] = !empty($result['dashboard_layer']['redis']);
			$result['redis_read_ok'] = is_array($read) && !empty($read['ok']);
			$result['sample_key'] = 'attend_dash_' . $test_key;
		} catch (Throwable $e) {
			$result['dashboard_error'] = $e->getMessage();
		}

		try {
			$this->load->library('dropdown_cache_store', ['key_prefix' => 'attend_df_'], 'dropdown_cache');
			$result['dropdown_layer'] = $this->dropdown_cache->availability();
		} catch (Throwable $e) {
			$result['dropdown_error'] = $e->getMessage();
		}

		echo json_encode($result, JSON_PRETTY_PRINT) . PHP_EOL;
	}
}
