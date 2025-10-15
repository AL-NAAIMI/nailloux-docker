<?php
// Docker-specific database connection file

// Database connection parameters
$host = 'db'; // Docker Compose service name - automatically resolved
$db = 'nailloux';
$user = 'root';
$pswd = 'rootpassword';
$port = 3306;

try {
    // Create DSN with explicit TCP connection
    $dsn = "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4";
    
    // Set connection options
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::ATTR_TIMEOUT => 10
    ];
    
    // Create PDO connection
    $pdo = new PDO($dsn, $user, $pswd, $options);
    
} catch (PDOException $e) {
    // Handle connection error
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}
?>
