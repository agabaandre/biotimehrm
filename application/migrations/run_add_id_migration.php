<?php
/**
 * Standalone script to add auto-increment id to biotime_departments table
 * 
 * Run this directly: php application/migrations/run_add_id_migration.php
 */

require_once __DIR__ . '/../../index.php';

$CI =& get_instance();
$CI->load->database();

echo "Starting migration: Add auto-increment id to biotime_departments\n";

try {
    if (!$CI->db->table_exists('biotime_departments')) {
        echo "Creating biotime_departments table with auto-increment id...\n";
        $CI->load->dbforge();
        $CI->dbforge->add_field(array(
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
        $CI->dbforge->add_key('id', TRUE);
        $CI->dbforge->add_key('dept_code');
        $CI->dbforge->create_table('biotime_departments');
        echo "✓ Table created successfully\n";
    } else {
        echo "Table exists. Checking id column...\n";
        
        if (!$CI->db->field_exists('id', 'biotime_departments')) {
            echo "Adding id column as auto-increment...\n";
            $CI->load->dbforge();
            $fields = array(
                'id' => array(
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => TRUE,
                    'auto_increment' => TRUE,
                    'first' => TRUE
                ),
            );
            $CI->dbforge->add_column('biotime_departments', $fields);
            
            // Check for existing primary key
            $query = $CI->db->query("SHOW KEYS FROM biotime_departments WHERE Key_name = 'PRIMARY'");
            if ($query->num_rows() > 0) {
                echo "Replacing primary key with id...\n";
                $CI->db->query("ALTER TABLE biotime_departments DROP PRIMARY KEY, ADD PRIMARY KEY (id), ADD UNIQUE KEY dept_code (dept_code)");
            } else {
                $CI->db->query("ALTER TABLE biotime_departments ADD PRIMARY KEY (id)");
            }
            echo "✓ Id column added successfully\n";
        } else {
            echo "Id column exists. Checking if it's auto-increment...\n";
            $query = $CI->db->query("SHOW COLUMNS FROM biotime_departments WHERE Field = 'id' AND Extra LIKE '%auto_increment%'");
            
            if ($query->num_rows() == 0) {
                echo "Making id column auto-increment...\n";
                $keyQuery = $CI->db->query("SHOW KEYS FROM biotime_departments WHERE Column_name = 'id' AND Key_name = 'PRIMARY'");
                $isPrimary = $keyQuery->num_rows() > 0;
                
                if ($isPrimary) {
                    $CI->db->query("ALTER TABLE biotime_departments MODIFY COLUMN id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT");
                } else {
                    $deptCodeKey = $CI->db->query("SHOW KEYS FROM biotime_departments WHERE Column_name = 'dept_code' AND Key_name = 'PRIMARY'");
                    if ($deptCodeKey->num_rows() > 0) {
                        $CI->db->query("ALTER TABLE biotime_departments DROP PRIMARY KEY, MODIFY COLUMN id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT, ADD PRIMARY KEY (id), ADD UNIQUE KEY dept_code (dept_code)");
                    } else {
                        $CI->db->query("ALTER TABLE biotime_departments MODIFY COLUMN id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT, ADD PRIMARY KEY (id)");
                    }
                }
                echo "✓ Id column is now auto-increment\n";
            } else {
                echo "✓ Id column is already auto-increment\n";
            }
        }
    }
    
    echo "\nMigration completed successfully!\n";
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}

