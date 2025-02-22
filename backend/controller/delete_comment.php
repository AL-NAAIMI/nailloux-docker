<?php
session_start();
include __DIR__ . '/../db/connection.php'; // Connect to the database

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Administrateur') {
        echo json_encode(['success' => false, 'message' => 'Accès refusé.']);
        exit;
    }

    $comment_id = filter_input(INPUT_POST, 'comment_id', FILTER_VALIDATE_INT);
    if (!$comment_id) {
        echo json_encode(['success' => false, 'message' => 'ID du commentaire invalide.']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("DELETE FROM commentaire_p WHERE id_commentaire_p = :id_commentaire_p");
        $stmt->bindParam(":id_commentaire_p", $comment_id, PDO::PARAM_INT);
        $stmt->execute();

        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Erreur lors de la suppression.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Requête invalide.']);
}
?>
