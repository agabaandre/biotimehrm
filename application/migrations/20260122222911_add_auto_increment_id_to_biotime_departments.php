<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Add_auto_increment_id_to_biotime_departments extends CI_Migration {

    public function up()
    {
		// Check if biotime_departments table exists
		if (!$this->db->table_exists('biotime_departments')) {
			// Create table with auto-increment id if it doesn't exist
			$this->dbforge->add_field(array(
				'id' => array(
					'type' => 'INT',
					'constraint' => 11,
					'unsigned' => TRUE,
					'auto_increment' => TRUE
				),
				'dept_code' => array(
					'type' => 'VARCHAR',
					'constraint' => '100',
					'null' => FALSE,
				),
				'dept_name' => array(
					'type' => 'VARCHAR',
					'constraint' => '255',
					'null' => TRUE,
				),
			));
			$this->dbforge->add_key('id', TRUE);
			$this->dbforge->add_key('dept_code');
			$this->dbforge->create_table('biotime_departments');
		} else {
			// Table exists, check if id column exists
			if (!$this->db->field_exists('id', 'biotime_departments')) {
				// Add id column as auto-increment
				$fields = array(
					'id' => array(
						'type' => 'INT',
						'constraint' => 11,
						'unsigned' => TRUE,
						'auto_increment' => TRUE,
						'first' => TRUE
					),
				);
				$this->dbforge->add_column('biotime_departments', $fields);
				
				// Make it the primary key
				// Check if there's already a primary key on dept_code
				$query = $this->db->query("SHOW KEYS FROM biotime_departments WHERE Key_name = 'PRIMARY'");
				if ($query->num_rows() > 0) {
					// Drop existing primary key and recreate with id
					$this->db->query("ALTER TABLE biotime_departments DROP PRIMARY KEY, ADD PRIMARY KEY (id), ADD UNIQUE KEY dept_code (dept_code)");
				} else {
					// No primary key exists, add id as primary key
					$this->db->query("ALTER TABLE biotime_departments ADD PRIMARY KEY (id)");
				}
			} else {
				// Column exists, modify it to be auto-increment
				// Check if it's already auto-increment
				$query = $this->db->query("SHOW COLUMNS FROM biotime_departments WHERE Field = 'id' AND Extra LIKE '%auto_increment%'");
				
				if ($query->num_rows() == 0) {
					// Not auto-increment, modify it
					// Check if it's currently a primary key
					$keyQuery = $this->db->query("SHOW KEYS FROM biotime_departments WHERE Column_name = 'id' AND Key_name = 'PRIMARY'");
					$isPrimary = $keyQuery->num_rows() > 0;
					
					if ($isPrimary) {
						$this->db->query("ALTER TABLE biotime_departments MODIFY COLUMN id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT");
					} else {
						// Make it primary key and auto-increment
						// First check if dept_code is primary key
						$deptCodeKey = $this->db->query("SHOW KEYS FROM biotime_departments WHERE Column_name = 'dept_code' AND Key_name = 'PRIMARY'");
						if ($deptCodeKey->num_rows() > 0) {
							$this->db->query("ALTER TABLE biotime_departments DROP PRIMARY KEY, MODIFY COLUMN id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT, ADD PRIMARY KEY (id), ADD UNIQUE KEY dept_code (dept_code)");
						} else {
							$this->db->query("ALTER TABLE biotime_departments MODIFY COLUMN id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT, ADD PRIMARY KEY (id)");
						}
					}
				}
			}
		}
    }

    public function down()
    {
		// Rollback: Remove auto-increment (but keep the column)
		if ($this->db->table_exists('biotime_departments')) {
			if ($this->db->field_exists('id', 'biotime_departments')) {
				// Remove auto-increment but keep the column
				$this->db->query("ALTER TABLE biotime_departments MODIFY COLUMN id INT(11) UNSIGNED NOT NULL");
    }
}
	}
}
