<?php
include $_SERVER['DOCUMENT_ROOT'] . '/backend/db/connection.php';
session_start();

// Vérifier si l'utilisateur est authentifié
if (!isset($_SESSION['id'])) {
    header("Location: /frontend/view/about-us.php?tab=event&error=not_authenticated");
    exit;
}

// Récupérer l'ID de l'utilisateur connecté et son rôle
$uid = $_SESSION['id'];
$role = $_SESSION['role'] ?? null; // Récupérer le rôle (par défaut null si non défini)

// Vérifier si l'ID de l'événement a été fourni
if (!isset($_POST['id']) || empty($_POST['id'])) {
    header("Location: /frontend/view/about-us.php?tab=event&error=missing_id");
    exit;
}

$eventId = $_POST['id'];

try {
    // Vérifier si l'utilisateur est le créateur de l'événement ou un administrateur
    $checkQuery = "SELECT uid FROM evenement WHERE id_evenement = :id_evenement";
    $checkStmt = $pdo->prepare($checkQuery);
    $checkStmt->bindParam(':id_evenement', $eventId, PDO::PARAM_INT);
    $checkStmt->execute();
    $event = $checkStmt->fetch(PDO::FETCH_ASSOC);

    if (!$event || ($event['uid'] != $uid && $role !== 'Administrateur')) {
        header("Location: /frontend/view/about-us.php?tab=event&error=not_authorized");
        exit;
    }

    // Préparer la requête pour supprimer l'événement
    $deleteQuery = "DELETE FROM evenement WHERE id_evenement = :id_evenement";
    $deleteStmt = $pdo->prepare($deleteQuery);

    // Lier les paramètres
    $deleteStmt->bindParam(':id_evenement', $eventId, PDO::PARAM_INT);
$pdo->exec("SET FOREIGN_KEY_CHECKS=0");

    // Exécuter la requête
    $deleteStmt->execute();
$pdo->exec("SET FOREIGN_KEY_CHECKS=1");

    // Vérifier si la suppression a eu lieu
    if ($deleteStmt->rowCount() > 0) {
        // Redirection en cas de succès
        header("Location: /frontend/view/about-us.php?tab=event&success=event_deleted");
        exit;
    } else {
        header("Location: /frontend/view/about-us.php?tab=event&error=not_found");
        exit;
    }
} catch (PDOException $e) {
    // Gestion des erreurs
    header("Location: /frontend/view/about-us.php?tab=event&error=db_error");
    exit;
}
