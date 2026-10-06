<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Svariables_mdl extends CI_Model {

	
	protected $table;
	protected $user;

	public function __construct() {
		parent::__construct();
		$this->table = "variables";
		$this->user = $this->session->get_userdata();
	}

	public function update_variables($data){
		if (empty($data['id'])) {
			return false;
		}
		$id = (int) $data['id'];
		unset($data['id']);
		$this->db->where('id', $id);
		$query = $this->db->update('setting', $data);
		return $query ? true : false;
	}
	 public function getSettings(){
		$this->ensureRemoteClkColumns();
		return $this->db->get('setting')->row();
	 }

	/**
	 * Settings used to push clk_log to a peer Attend instance.
	 * Columns are added if missing so they appear on Settings → Remote Clock Sync.
	 */
	public function ensureRemoteClkColumns()
	{
		$cols = [
			'remote_clk_enabled' => "VARCHAR(8) NULL DEFAULT '0' COMMENT '1 to push clk_log to peer Attend'",
			'remote_clk_url' => "VARCHAR(255) NULL DEFAULT '' COMMENT 'Peer Attend base URL e.g. https://attend.health.go.ug'",
			'remote_clk_private_key' => "TEXT NULL COMMENT 'Shared one-time private key (same on sender and receiver)'",
			'remote_clk_last_id' => "BIGINT NULL DEFAULT 0 COMMENT 'Last clk_log.id successfully pushed'",
		];
		foreach ($cols as $name => $def) {
			if (!$this->db->field_exists($name, 'setting')) {
				$this->db->query("ALTER TABLE `setting` ADD `{$name}` {$def}");
			}
		}
		$this->ensureClkLogRemoteStatusColumn();
	}

	/**
	 * Per-row send tracking so a clock is not pushed twice.
	 */
	public function ensureClkLogRemoteStatusColumn()
	{
		if (!$this->db->table_exists('clk_log')) {
			return;
		}
		$added = false;
		if (!$this->db->field_exists('remote_sync_status', 'clk_log')) {
			$this->db->query("ALTER TABLE `clk_log` ADD `remote_sync_status` VARCHAR(16) NULL DEFAULT 'pending' COMMENT 'pending|sent|failed'");
			$added = true;
		}
		if (!$this->db->field_exists('remote_sync_at', 'clk_log')) {
			$this->db->query("ALTER TABLE `clk_log` ADD `remote_sync_at` DATETIME NULL DEFAULT NULL");
		}
		if ($added) {
			$settings = $this->db->get('setting')->row();
			$lastId = isset($settings->remote_clk_last_id) ? (int) $settings->remote_clk_last_id : 0;
			if ($lastId > 0) {
				$this->db->query("UPDATE `clk_log` SET `remote_sync_status` = 'sent', `remote_sync_at` = NOW() WHERE `id` <= ? AND (`remote_sync_status` IS NULL OR `remote_sync_status` IN ('', 'pending'))", [$lastId]);
			}
		}
	}

	/**
	 * Counts for the Remote Clock Sync settings page.
	 *
	 * @return array{pending:int,sent:int,failed:int,total:int}
	 */
	public function remoteClkSyncCounts()
	{
		$out = ['pending' => 0, 'sent' => 0, 'failed' => 0, 'total' => 0];
		if (!$this->db->table_exists('clk_log') || !$this->db->field_exists('remote_sync_status', 'clk_log')) {
			return $out;
		}
		$q = $this->db->query("
			SELECT
				SUM(CASE WHEN remote_sync_status IS NULL OR remote_sync_status IN ('', 'pending') THEN 1 ELSE 0 END) AS pending,
				SUM(CASE WHEN remote_sync_status = 'sent' THEN 1 ELSE 0 END) AS sent,
				SUM(CASE WHEN remote_sync_status = 'failed' THEN 1 ELSE 0 END) AS failed,
				COUNT(*) AS total
			FROM clk_log
		");
		if ($q && $q->num_rows()) {
			$r = $q->row();
			$out['pending'] = (int) $r->pending;
			$out['sent'] = (int) $r->sent;
			$out['failed'] = (int) $r->failed;
			$out['total'] = (int) $r->total;
		}
		return $out;
	}

	/**
	 * Persist last successfully pushed clk_log.id on the single setting row.
	 *
	 * @param int $lastId
	 * @return bool
	 */
	public function setRemoteClkLastId($lastId)
	{
		$row = $this->db->select('id')->get('setting')->row();
		if (!$row) {
			return false;
		}
		$this->db->where('id', $row->id);
		return (bool) $this->db->update('setting', ['remote_clk_last_id' => (int) $lastId]);
	}
}