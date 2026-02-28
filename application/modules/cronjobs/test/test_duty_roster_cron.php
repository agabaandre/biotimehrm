<?php
/**
 * Test Script for Duty Roster Summary Cron Job
 * This script tests the cron job functionality without running the actual job
 */

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Test database connection
function testDatabaseConnection() {
    echo "Testing database connection...\n";
    
    try {
        // You'll need to update these credentials
        $host = 'localhost';
        $dbname = 'your_database_name';
        $username = 'your_username';
        $password = 'your_password';
        
        $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        echo "✅ Database connection successful\n";
        return $pdo;
    } catch (PDOException $e) {
        echo "❌ Database connection failed: " . $e->getMessage() . "\n";
        return false;
    }
}

// Test table existence
function testTableExists($pdo, $tableName) {
    echo "Testing if table '$tableName' exists...\n";
    
    try {
        $stmt = $pdo->query("SHOW TABLES LIKE '$tableName'");
        if ($stmt->rowCount() > 0) {
            echo "✅ Table '$tableName' exists\n";
            return true;
        } else {
            echo "❌ Table '$tableName' does not exist\n";
            return false;
        }
    } catch (PDOException $e) {
        echo "❌ Error checking table: " . $e->getMessage() . "\n";
        return false;
    }
}

// Test source data availability
function testSourceData($pdo) {
    echo "Testing source data availability...\n";
    
    try {
        // Check duty_rosta table
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM duty_rosta");
        $dutyCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        echo "📊 duty_rosta records: $dutyCount\n";
        
        // Check ihrisdata table
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM ihrisdata");
        $employeeCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        echo "👥 ihrisdata records: $employeeCount\n";
        
        // Check sample duty roster data
        $stmt = $pdo->query("
            SELECT 
                r.ihris_pid,
                r.schedule_id,
                r.duty_date,
                COUNT(*) as count
            FROM duty_rosta r
            WHERE r.schedule_id IN (14, 15, 16, 17, 18, 19, 20, 21)
            GROUP BY r.ihris_pid, r.schedule_id, DATE_FORMAT(r.duty_date, '%Y-%m')
            LIMIT 5
        ");
        
        $sampleData = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "📋 Sample duty roster data:\n";
        foreach ($sampleData as $row) {
            echo "   - Employee: {$row['ihris_pid']}, Schedule: {$row['schedule_id']}, Month: {$row['duty_date']}, Count: {$row['count']}\n";
        }
        
        return true;
    } catch (PDOException $e) {
        echo "❌ Error checking source data: " . $e->getMessage() . "\n";
        return false;
    }
}

// Test summary table structure
function testSummaryTableStructure($pdo) {
    echo "Testing person_dut_final table structure...\n";
    
    try {
        $stmt = $pdo->query("DESCRIBE person_dut_final");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "📋 Table columns:\n";
        foreach ($columns as $column) {
            echo "   - {$column['Field']}: {$column['Type']} {$column['Null']} {$column['Key']}\n";
        }
        
        return true;
    } catch (PDOException $e) {
        echo "❌ Error checking table structure: " . $e->getMessage() . "\n";
        return false;
    }
}

// Test sample aggregation query
function testAggregationQuery($pdo) {
    echo "Testing aggregation query...\n";
    
    try {
        $query = "
            SELECT
                r.ihris_pid,
                r.facility_id,
                DATE_FORMAT(r.duty_date, '%Y-%m') AS yyyy_mm,
                SUM(r.schedule_id = 14) AS D_ct,
                SUM(r.schedule_id = 15) AS E_ct,
                SUM(r.schedule_id = 16) AS N_ct,
                SUM(r.schedule_id = 17) AS O_ct,
                SUM(r.schedule_id = 18) AS A_ct,
                SUM(r.schedule_id = 19) AS S_ct,
                SUM(r.schedule_id = 20) AS M_ct,
                SUM(r.schedule_id = 21) AS Z_ct
            FROM duty_rosta r
            WHERE r.schedule_id IN (14, 15, 16, 17, 18, 19, 20, 21)
                AND r.duty_date >= '2025-01-01'
            GROUP BY r.ihris_pid, r.facility_id, DATE_FORMAT(r.duty_date, '%Y-%m')
            LIMIT 3
        ";
        
        $stmt = $pdo->query($query);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "📊 Sample aggregation results:\n";
        foreach ($results as $row) {
            echo "   - Employee: {$row['ihris_pid']}, Month: {$row['yyyy_mm']}\n";
            echo "     D: {$row['D_ct']}, E: {$row['E_ct']}, N: {$row['N_ct']}, O: {$row['O_ct']}\n";
            echo "     A: {$row['A_ct']}, S: {$row['S_ct']}, M: {$row['M_ct']}, Z: {$row['Z_ct']}\n";
        }
        
        return true;
    } catch (PDOException $e) {
        echo "❌ Error testing aggregation: " . $e->getMessage() . "\n";
        return false;
    }
}

// Main test execution
function runTests() {
    echo "🚀 Starting Duty Roster Cron Job Tests\n";
    echo "=====================================\n\n";
    
    // Test database connection
    $pdo = testDatabaseConnection();
    if (!$pdo) {
        echo "\n❌ Cannot proceed without database connection\n";
        return;
    }
    
    echo "\n";
    
    // Test required tables
    $dutyRostaExists = testTableExists($pdo, 'duty_rosta');
    $ihrisdataExists = testTableExists($pdo, 'ihrisdata');
    
    if (!$dutyRostaExists || !$ihrisdataExists) {
        echo "\n❌ Required tables missing. Please create them first.\n";
        return;
    }
    
    echo "\n";
    
    // Test source data
    testSourceData($pdo);
    
    echo "\n";
    
    // Test summary table
    $summaryTableExists = testTableExists($pdo, 'person_dut_final');
    if ($summaryTableExists) {
        testSummaryTableStructure($pdo);
    } else {
        echo "⚠️  Summary table doesn't exist. You can create it using:\n";
        echo "   http://localhost/attend/cronjobs/DutyRosterSummaryCron/createTable\n";
    }
    
    echo "\n";
    
    // Test aggregation query
    testAggregationQuery($pdo);
    
    echo "\n";
    echo "✅ Tests completed!\n";
    echo "\nNext steps:\n";
    echo "1. Create summary table if it doesn't exist\n";
    echo "2. Set up cron job to run daily at 2 AM\n";
    echo "3. Test manual execution\n";
    echo "4. Monitor logs and performance\n";
}

// Run tests if script is executed directly
if (php_sapi_name() === 'cli') {
    runTests();
} else {
    echo "This script should be run from the command line.\n";
    echo "Usage: php test_duty_roster_cron.php\n";
}
?>
