<?php
// Simple database connection test script

// Try different connection methods
echo "<h1>Database Connection Test</h1>";

// Method 1: Using PDO with TCP/IP
try {
    echo "<h2>Method 1: PDO with TCP/IP</h2>";
    $host = 'db';
    $db = 'nailloux';
    $user = 'root';
    $pass = 'rootpassword';
    $port = 3306;
    
    echo "Connecting to: $host:$port, db: $db, user: $user<br>";
    
    $dsn = "mysql:host=$host;port=$port;dbname=$db";
    $pdo = new PDO($dsn, $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $result = $pdo->query("SELECT 'Connected successfully' AS message")->fetch();
    echo "<div style='color:green'>" . $result['message'] . "</div>";
} catch (PDOException $e) {
    echo "<div style='color:red'>Connection failed: " . $e->getMessage() . "</div>";
}

// Method 2: Using mysqli
try {
    echo "<h2>Method 2: MySQLi</h2>";
    $host = 'db';
    $db = 'nailloux';
    $user = 'root';
    $pass = 'rootpassword';
    $port = 3306;
    
    echo "Connecting to: $host:$port, db: $db, user: $user<br>";
    
    $mysqli = new mysqli($host, $user, $pass, $db, $port);
    
    if ($mysqli->connect_error) {
        echo "<div style='color:red'>Connection failed: " . $mysqli->connect_error . "</div>";
    } else {
        echo "<div style='color:green'>Connected successfully</div>";
        $mysqli->close();
    }
} catch (Exception $e) {
    echo "<div style='color:red'>Connection failed: " . $e->getMessage() . "</div>";
}

// Method 3: Check if we can ping the database container
echo "<h2>Method 3: Container Connectivity</h2>";
echo "<pre>";
passthru("ping -c 3 db 2>&1");
echo "</pre>";

// Show PHP configuration
echo "<h2>PHP Configuration</h2>";
echo "PHP Version: " . phpversion() . "<br>";
echo "PDO Drivers: " . implode(", ", PDO::getAvailableDrivers()) . "<br>";
echo "Loaded Extensions: " . implode(", ", get_loaded_extensions()) . "<br>";
?>
