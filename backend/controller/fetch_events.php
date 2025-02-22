<?php
header('Content-Type: application/json');

include __DIR__ . '/../db/connection.php';

try {
    $sql = "SELECT id_evenement, titre, date_heure, lieu, descriptif, type, officiel FROM evenement";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['status' => 'success', 'events' => $events]);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Erreur lors de la récupération des événements.']);
}
