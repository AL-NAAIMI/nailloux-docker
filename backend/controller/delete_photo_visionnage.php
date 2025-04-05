<?php
include __DIR__ . '/../db/connection.php';
session_start();

// Vérifier si le formulaire a été soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $photoId = $_POST['photo_id'];
    $eventId = $_POST['event_id'];
    $userId = $_SESSION['id'] ?? null;

    if (!$userId) {
        die("Vous devez être connecté pour supprimer une photo.");
    }

    try {
        // Vérifier si la photo appartient bien à cet utilisateur
        $queryCheck = "SELECT chemin_photo FROM photos_evenement WHERE id_photo = :id_photo AND uid = :uid";
        $stmtCheck = $pdo->prepare($queryCheck);
        $stmtCheck->execute([
            ':id_photo' => $photoId,
            ':uid' => $userId,
        ]);
        $photo = $stmtCheck->fetch(PDO::FETCH_ASSOC);

        if (!$photo) {
            die("Photo introuvable ou non autorisée.");
        }

        // Supprimer le fichier du système
        $filePath = __DIR__ . '/../../upload/photos_evenement/' . $photo['chemin_photo'];
        if (file_exists($filePath)) {
            unlink($filePath);
        }

        // Supprimer l'entrée de la base de données
        $queryDelete = "DELETE FROM photos_evenement WHERE id_photo = :id_photo AND uid = :uid";
        $stmtDelete = $pdo->prepare($queryDelete);
        $stmtDelete->execute([
            ':id_photo' => $photoId,
            ':uid' => $userId,
        ]);

        // Redirection
        header("Location: /frontend/view/about-us/event_details.php?id=$eventId&photo_deleted=1");
        exit;
    } catch (PDOException $e) {
        die("Erreur lors de la suppression : " . $e->getMessage());
    }
} else {
    die("Requête invalide.");
}
