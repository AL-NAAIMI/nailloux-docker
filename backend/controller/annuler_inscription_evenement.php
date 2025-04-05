<?php
include $_SERVER['DOCUMENT_ROOT'] . '/backend/db/connection.php';
include $_SERVER['DOCUMENT_ROOT'] . '/backend/sql/utilisateur.php';
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['id'])) {
    echo "<script>
        alert('Vous devez être connecté pour annuler votre inscription.');
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
    // Supprimer l'inscription de l'utilisateur
    $deleteQuery = "DELETE FROM evenement_participants WHERE id_evenement = :id_evenement AND uid = :id_utilisateur";
    $deleteStmt = $pdo->prepare($deleteQuery);
    $deleteStmt->execute([
        ':id_evenement' => $id_evenement,
        ':id_utilisateur' => $id_utilisateur
    ]);

    if ($deleteStmt->rowCount() > 0) {
        echo "<script>
            alert('Votre inscription a été annulée avec succès.');
            window.location.href = '/frontend/view/about-us.php?tab=event';
        </script>";
    } else {
        echo "<script>
            alert('Vous n\'étiez pas inscrit à cet événement.');
            window.location.href = '/frontend/view/about-us.php?tab=event';
        </script>";
    }
} catch (PDOException $e) {
    echo "<script>
        alert('Erreur lors de l\'annulation de l\'inscription : " . addslashes($e->getMessage()) . "');
        window.location.href = '/frontend/view/about-us.php?tab=event';
    </script>";
}
?>
