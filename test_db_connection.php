<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Attempting to connect to database 'fin'...\n";

// Include the db connection file
if (file_exists('db.php')) {
    include 'db.php';
    echo "db.php found and included.\n";
} else {
    die("Error: db.php not found.\n");
}

// Check if $conn is set (from db.php)
if (isset($conn) && $conn instanceof mysqli) {
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error . "\n");
    }
    echo "✅ Successfully connected to database 'fin'!\n";
    
    // Try to fetch specific tables to verify full connectivity
    $tables = ['fraud_alert', 'audit_report'];
    foreach ($tables as $table) {
        echo "Checking table '$table'...\n";
        $sql = "SELECT COUNT(*) as count FROM $table";
        $result = $conn->query($sql);
        
        if ($result) {
            $row = $result->fetch_assoc();
            echo " - Table '$table' exists and has " . $row['count'] . " records.\n";
        } else {
            echo " - ❌ Error querying table '$table': " . $conn->error . "\n";
        }
    }
    
} else {
    echo "Error: Database connection object \$conn not valid.\n";
}

echo "Test complete.\n";
?>
