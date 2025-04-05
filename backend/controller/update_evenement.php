<?php
include $_SERVER['DOCUMENT_ROOT'] . '/backend/db/connection.php';
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['id'])) {
    die("Vous devez être connecté pour effectuer cette action.");
}

$userId = $_SESSION['id'];
$role = $_SESSION['role'] ?? null;

// Récupérer les données POST
$id_evenement = $_POST['id_evenement'] ?? null;
$titre = $_POST['titre'] ?? null;
$date_heure = $_POST['date_heure'] ?? null;
$lieu = $_POST['lieu'] ?? null;
$descriptif = $_POST['descriptif'] ?? null;
$type = $_POST['type'] ?? null;
$officiel = isset($_POST['officiel']) ? 1 : 0;

if (!$id_evenement || !$titre || !$date_heure || !$lieu || !$descriptif || !$type) {
    die("Données manquantes ou invalides.");
}

try {
    // Vérifier si l'utilisateur est autorisé à modifier l'événement
    $checkQuery = "SELECT uid FROM evenement WHERE id_evenement = :id_evenement";
    $checkStmt = $pdo->prepare($checkQuery);
    $checkStmt->bindParam(':id_evenement', $id_evenement, PDO::PARAM_INT);
    $checkStmt->execute();
    $event = $checkStmt->fetch(PDO::FETCH_ASSOC);

    if (!$event || ($event['uid'] != $userId && $role !== 'Administrateur')) {
        die("Vous n'êtes pas autorisé à modifier cet événement.");
    }

    // Mettre à jour l'événement
    $updateQuery = "
        UPDATE evenement 
        SET titre = :titre, date_heure = :date_heure, lieu = :lieu, descriptif = :descriptif, type = :type, officiel = :officiel
        WHERE id_evenement = :id_evenement
    ";
    $updateStmt = $pdo->prepare($updateQuery);
    $updateStmt->execute([
        ':titre' => $titre,
        ':date_heure' => $date_heure,
        ':lieu' => $lieu,
        ':descriptif' => $descriptif,
        ':type' => $type,
        ':officiel' => $officiel,
        ':id_evenement' => $id_evenement
    ]);

    // Redirection après succès
    header("Location: /frontend/view/about-us.php?tab=event");
    exit;
} catch (PDOException $e) {
    die("Erreur lors de la mise à jour de l'événement : " . $e->getMessage());
}
?>
