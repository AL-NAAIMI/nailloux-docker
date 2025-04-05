<?php
// Informations de connexion à la base de données
$host = "db"; // Service name in docker-compose
$db = "nailloux";
$user = "root";
$pswd = "rootpassword";

try {
    // Connexion à la base de données avec PDO
    // Force TCP connection with explicit TCP protocol
    $dsn = "mysql:host=$host;port=3306;dbname=$db;charset=utf8mb4";
    
    // Set PDO options with longer timeout and disable persistent connections
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, 
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, 
        PDO::ATTR_EMULATE_PREPARES => false, 
        PDO::ATTR_TIMEOUT => 5, 
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4",
        // Disable socket connections
        PDO::MYSQL_ATTR_DIRECT_QUERY => true,
    ];
    
    // Create new PDO connection
    $pdo = new PDO($dsn, $user, $pswd, $options);

} catch (PDOException $e) {
    // Gestion des erreurs : on affiche l'erreur et arrête l'exécution
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}
?>
