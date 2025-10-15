<?php
// Simple script to check users in database
include __DIR__ . '/backend/db/connection.php';

try {
    // Count total users
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM utilisateur");
    $count = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "<h2>Total Users: " . $count['total'] . "</h2>";
    
    // List all users
    $stmt = $pdo->query("SELECT id, pseudo, prenom, nom, email, DATE_FORMAT(created_at, '%Y-%m-%d %H:%i') as created FROM utilisateur ORDER BY id DESC LIMIT 10");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>Recent Users:</h3>";
    echo "<table border='1' style='border-collapse: collapse; padding: 10px;'>";
    echo "<tr><th>ID</th><th>Pseudo</th><th>Prénom</th><th>Nom</th><th>Email</th><th>Created</th></tr>";
    
    foreach ($users as $user) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($user['id']) . "</td>";
        echo "<td>" . htmlspecialchars($user['pseudo']) . "</td>";
        echo "<td>" . htmlspecialchars($user['prenom']) . "</td>";
        echo "<td>" . htmlspecialchars($user['nom']) . "</td>";
        echo "<td>" . htmlspecialchars($user['email']) . "</td>";
        echo "<td>" . htmlspecialchars($user['created']) . "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
