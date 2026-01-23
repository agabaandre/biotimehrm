<?php
/**
 * Script to ensure id column is auto-increment in biotime_departments
 * Run: php application/migrations/fix_id_auto_increment.php
 */

require_once __DIR__ . '/../../index.php';

$CI =& get_instance();
$CI->load->database();
$CI->load->dbforge();

echo "Checking biotime_departments table structure...\n";

// Check current structure
$result = $CI->db->query("SHOW COLUMNS FROM biotime_departments WHERE Field = 'id'")->result();

if (empty($result)) {
    echo "Id column does not exist. Adding it as auto-increment primary key...\n";
    
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
    
    // Make it primary key
    $CI->db->query("ALTER TABLE biotime_departments ADD PRIMARY KEY (id)");
    
    echo "✓ Id column added successfully\n";
} else {
    $id_col = $result[0];
    echo "Id column exists. Type: {$id_col->Type}, Extra: {$id_col->Extra}\n";
    
    if (strpos($id_col->Extra, 'auto_increment') === false) {
        echo "Making id column auto-increment...\n";
        
        // Check if it's already a primary key
        $key_check = $CI->db->query("SHOW KEYS FROM biotime_departments WHERE Column_name = 'id' AND Key_name = 'PRIMARY'")->num_rows();
        
        if ($key_check > 0) {
            // Already primary key, just modify to auto-increment
            $CI->db->query("ALTER TABLE biotime_departments MODIFY COLUMN id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT");
        } else {
            // Not primary key, make it primary and auto-increment
            // First check if dept_code is primary key
            $dept_code_key = $CI->db->query("SHOW KEYS FROM biotime_departments WHERE Column_name = 'dept_code' AND Key_name = 'PRIMARY'")->num_rows();
            
            if ($dept_code_key > 0) {
                // Drop dept_code primary key and make id primary
                $CI->db->query("ALTER TABLE biotime_departments DROP PRIMARY KEY, MODIFY COLUMN id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT, ADD PRIMARY KEY (id), ADD UNIQUE KEY dept_code (dept_code)");
            } else {
                // No primary key, just add id as primary
                $CI->db->query("ALTER TABLE biotime_departments MODIFY COLUMN id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT, ADD PRIMARY KEY (id)");
            }
        }
        
        echo "✓ Id column is now auto-increment\n";
    } else {
        echo "✓ Id column is already auto-increment\n";
    }
}

// Reset auto-increment counter
$CI->db->query("ALTER TABLE biotime_departments AUTO_INCREMENT = 1");

// Show final structure
echo "\nFinal table structure:\n";
$result = $CI->db->query("DESCRIBE biotime_departments")->result();
foreach ($result as $row) {
    echo sprintf("%-15s | %-20s | %-5s | %-10s | %-10s | %s\n", 
        $row->Field, 
        $row->Type, 
        $row->Null, 
        $row->Key, 
        $row->Default ?: 'NULL', 
        $row->Extra
    );
}

echo "\n✓ Done!\n";

