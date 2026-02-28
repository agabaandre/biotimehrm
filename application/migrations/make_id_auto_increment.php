<?php
/**
 * Direct script to make id column auto-increment
 * Usage: php application/migrations/make_id_auto_increment.php
 */

if (!defined('BASEPATH')) {
    define('BASEPATH', __DIR__ . '/../../system/');
}
if (!defined('APPPATH')) {
    define('APPPATH', __DIR__ . '/../');
}

require_once __DIR__ . '/../../index.php';

$CI =& get_instance();
$CI->load->database();

echo "Making id column auto-increment in biotime_departments...\n\n";

try {
    // Check if id column exists
    $check = $CI->db->query("SHOW COLUMNS FROM biotime_departments WHERE Field = 'id'")->result();
    
    if (empty($check)) {
        echo "Id column does not exist. Adding it...\n";
        $CI->db->query("ALTER TABLE biotime_departments ADD COLUMN id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT FIRST, ADD PRIMARY KEY (id)");
        echo "✓ Id column added as auto-increment primary key\n";
    } else {
        $col = $check[0];
        echo "Id column exists. Type: {$col->Type}, Extra: {$col->Extra}\n";
        
        if (strpos($col->Extra, 'auto_increment') === false) {
            echo "Making id column auto-increment...\n";
            $CI->db->query("ALTER TABLE biotime_departments MODIFY COLUMN id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT");
            echo "✓ Id column is now auto-increment\n";
        } else {
            echo "✓ Id column is already auto-increment\n";
        }
    }
    
    // Ensure it's primary key
    $key_check = $CI->db->query("SHOW KEYS FROM biotime_departments WHERE Column_name = 'id' AND Key_name = 'PRIMARY'")->num_rows();
    if ($key_check == 0) {
        echo "Making id the primary key...\n";
        $CI->db->query("ALTER TABLE biotime_departments ADD PRIMARY KEY (id)");
        echo "✓ Id is now primary key\n";
    }
    
    // Reset auto-increment
    $CI->db->query("ALTER TABLE biotime_departments AUTO_INCREMENT = 1");
    
    // Show final structure
    echo "\nFinal table structure:\n";
    echo str_repeat("-", 80) . "\n";
    printf("%-15s %-20s %-8s %-10s %-12s %s\n", "Field", "Type", "Null", "Key", "Default", "Extra");
    echo str_repeat("-", 80) . "\n";
    
    $result = $CI->db->query("DESCRIBE biotime_departments")->result();
    foreach ($result as $row) {
        printf("%-15s %-20s %-8s %-10s %-12s %s\n", 
            $row->Field, 
            $row->Type, 
            $row->Null, 
            $row->Key, 
            $row->Default ?: 'NULL', 
            $row->Extra
        );
    }
    
    echo "\n✓ Done! Id column is now auto-increment.\n";
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}

