<?php
echo "<h1>Database Connection Test</h1>";

// Connection parameters
$host = "db"; // This is the service name defined in docker-compose.yml
$db = "nailloux";
$user = "root";
$pswd = "rootpassword";

// Display connection parameters
echo "<p>Attempting to connect to MySQL with:</p>";
echo "<ul>";
echo "<li>Host: $host</li>";
echo "<li>Database: $db</li>";
echo "<li>User: $user</li>";
echo "<li>Password: [hidden]</li>";
echo "</ul>";

// Let's add a small delay to ensure the database container is fully ready
sleep(2);

try {
    // Try connection with TCP explicit protocol and longer timeout
    echo "<p>Attempting connection with TCP protocol...</p>";
    $dsn = "mysql:host=$host;port=3306;dbname=$db;charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::ATTR_TIMEOUT => 10, // Increased timeout
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4",
    ];
    $pdo = new PDO($dsn, $user, $pswd, $options);
    
    // Test the connection
    $stmt = $pdo->query("SELECT 'Connection successful!' as message");
    $result = $stmt->fetch();
    echo "<p style='color:green;font-weight:bold;'>" . $result['message'] . "</p>";
    
    // Show tables
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll();
    
    echo "<p>Tables in database:</p>";
    echo "<ul>";
    foreach ($tables as $table) {
        echo "<li>" . $table["Tables_in_$db"] . "</li>";
    }
    echo "</ul>";
    
} catch (PDOException $e) {
    echo "<p style='color:red;font-weight:bold;'>Connection failed: " . $e->getMessage() . "</p>";
    
    // Try alternative connection method
    echo "<p>Trying alternative connection method with hostname 127.0.0.1...</p>";
    try {
        // Try with IP address instead of hostname
        $altHost = "127.0.0.1";
        $altPort = 33060; // This is the exposed port in docker-compose.yml
        $dsn = "mysql:host=$altHost;port=$altPort;dbname=$db;charset=utf8mb4";
        $pdo = new PDO($dsn, $user, $pswd, $options);
        echo "<p style='color:green;font-weight:bold;'>Alternative connection successful!</p>";
        
        // Show tables
        $stmt = $pdo->query("SHOW TABLES");
        $tables = $stmt->fetchAll();
        
        echo "<p>Tables in database:</p>";
        echo "<ul>";
        foreach ($tables as $table) {
            echo "<li>" . $table["Tables_in_$db"] . "</li>";
        }
        echo "</ul>";
        
    } catch (PDOException $e2) {
        echo "<p style='color:red;font-weight:bold;'>Alternative connection failed: " . $e2->getMessage() . "</p>";
    }
}

// Display PHP info for debugging
echo "<h2>PHP Info</h2>";
echo "<p>PHP Version: " . phpversion() . "</p>";
echo "<p>PDO Drivers: " . implode(", ", PDO::getAvailableDrivers()) . "</p>";

// Display Docker environment info
echo "<h2>Docker Environment</h2>";
echo "<pre>";
print_r($_ENV);
echo "</pre>";

// Show loaded PHP extensions
echo "<p>Loaded Extensions: " . implode(", ", get_loaded_extensions()) . "</p>";
?>
