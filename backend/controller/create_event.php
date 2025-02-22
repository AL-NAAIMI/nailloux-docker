<?php
// -------------------------------------------------------
// create_event.php
// Description : Ce script reçoit les données d'un nouvel événement via POST
//               et insère l'événement dans la table `evenement`. Il renvoie
//               une réponse JSON en cas de succès ou d'erreur.
// -------------------------------------------------------
session_start();
// On envoie une réponse JSON
header('Content-Type: application/json; charset=utf-8');


// Inclusion du fichier de connexion à la base de données
include __DIR__ . '/../db/connection.php'; // Adapter le chemin si besoin

// Récupérer l'id de l'utilisateur 

$uid = $_SESSION['id'] ?? null;

// Récupérer les données POST
$titre      = $_POST['titre']      ?? '';
$date_heure = $_POST['date_heure'] ?? '';
$lieu       = $_POST['lieu']       ?? '';
$descriptif = $_POST['descriptif'] ?? '';
$type       = $_POST['type']       ?? 'Réunion'; 
$officiel   = isset($_POST['officiel']) ? 1 : 0;

// Vérification minimale des champs requis
if (empty($titre) || empty($date_heure) || empty($lieu)) {
    echo json_encode([
        'status'  => 'error',
        'message' => 'Champs requis manquants (titre, date_heure ou lieu).'
    ]);
    exit;
}

try {
    // Préparation de la requête INSERT
    $stmt = $pdo->prepare("
        INSERT INTO evenement (titre, date_heure, lieu, descriptif, type, officiel, uid)
        VALUES (:titre, :date_heure, :lieu, :descriptif, :type, :officiel, :uid)
    ");

    // Exécution avec les paramètres
    $stmt->execute([
        ':titre'      => $titre,
        ':date_heure' => $date_heure,  // format 'YYYY-MM-DD HH:MM:SS' attendu
        ':lieu'       => $lieu,
        ':descriptif' => $descriptif,
        ':type'       => $type,
        ':officiel'   => $officiel,
        ':uid'        => $uid
    ]);

    // Récupérer l'ID auto-incrémenté
    $id_evenement = $pdo->lastInsertId();

    // Réponse JSON de succès
    echo json_encode([
        'status'  => 'success',
        'message' => 'Événement créé avec succès.',
        'id_evenement' => $id_evenement
    ]);
    
} catch (PDOException $e) {
    // Réponse JSON d'erreur
    echo json_encode([
        'status'  => 'error',
        'message' => 'Erreur lors de la création de l\'événement : '.$e->getMessage()
    ]);
}
?>