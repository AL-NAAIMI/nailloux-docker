<?php
// Inclusion de la connexion à la base de données avec PDO
include __DIR__ . '/../db/connection.php';

// Définit l'en-tête de la réponse HTTP en tant que JSON
header('Content-Type: application/json');

// Requête SQL pour récupérer les événements (id, titre et date/heure)
$query = "SELECT id_evenement, titre, date_heure FROM evenement";

// Tableau vide pour stocker les événements
$events = [];

try {
    // Exécution de la requête SQL avec PDO
    $stmt = $pdo->query($query);

    // Parcours du résultat de la requête et stockage des événements dans le tableau
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        // Ajout de l'événement au tableau $events
        $events[] = [
            'id' => $row['id_evenement'],    // ID de l'événement
            'title' => $row['titre'],        // Titre de l'événement
            'start' => $row['date_heure']    // Date et heure de l'événement
        ];
    }

    // Encodage du tableau $events en JSON et renvoi de la réponse
    echo json_encode($events);

} catch (PDOException $e) {
    // Gestion des erreurs
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
