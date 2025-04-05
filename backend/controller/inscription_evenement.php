<?php
include $_SERVER['DOCUMENT_ROOT'] . '/backend/db/connection.php';
include $_SERVER['DOCUMENT_ROOT'] . '/backend/sql/utilisateur.php';
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['id'])) {
    echo "<script>
        alert('Vous devez être connecté pour vous inscrire à un événement.');
        window.location.href = '/frontend/view/about-us.php?tab=event';
    </script>";
    exit;
}

$id_utilisateur = $_SESSION['id'];
$id_evenement = $_POST['id_evenement'] ?? null;

// Vérifier si l'ID de l'événement est fourni
if (!$id_evenement) {
    echo "<script>
        alert('ID de l\'événement manquant.');
        window.location.href = '/frontend/view/about-us.php?tab=event';
    </script>";
    exit;
}

try {
    // Ajouter l'inscription de l'utilisateur
    $insertQuery = "INSERT INTO evenement_participants (id_evenement, uid) VALUES (:id_evenement, :id_utilisateur)";
    $insertStmt = $pdo->prepare($insertQuery);
    $insertStmt->execute([
        ':id_evenement' => $id_evenement,
        ':id_utilisateur' => $id_utilisateur
    ]);

    echo "<script>
        alert('Inscription réussie à l\'événement.');
        window.location.href = '/frontend/view/about-us.php?tab=event';
    </script>";
} catch (PDOException $e) {
    // Verif si l'utilisateur est deja inscrit
    if ($e->getCode() === '23000') {
        echo "<script>
            alert('Vous êtes déjà inscrit à cet événement.');
            window.location.href = '/frontend/view/about-us.php?tab=event';
        </script>";
    } else {
        echo "<script>
            alert('Erreur lors de l\'inscription : " . addslashes($e->getMessage()) . "');
            window.location.href = '/frontend/view/about-us.php?tab=event';
        </script>";
    }
}
?>
