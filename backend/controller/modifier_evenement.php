<?php
include __DIR__ . '../../db/connection.php';
include __DIR__ . '../../sql/utilisateur.php';
session_start();

// Vérifier si l'utilisateur est authentifié
if (!isset($_SESSION['id'])) {
    die("Vous devez être connecté pour accéder à cette page.");
}

$userId = $_SESSION['id'];
$role = $_SESSION['role'] ?? null;

// Vérifier si l'ID de l'événement est fourni
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("ID de l'événement manquant ou invalide.");
}

$eventId = (int)$_GET['id'];

try {
    // Récupérer les détails de l'événement
    $query = "SELECT * FROM evenement WHERE id_evenement = :id_evenement";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':id_evenement', $eventId, PDO::PARAM_INT);
    $stmt->execute();

    $event = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$event) {
        die("Événement introuvable.");
    }

    // Vérifier si l'utilisateur est autorisé à modifier l'événement
    if ($event['uid'] != $userId && $role !== 'Administrateur') {
        die("Vous n'êtes pas autorisé à modifier cet événement.");
    }
} catch (PDOException $e) {
    die("Erreur lors de la récupération des données : " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier l'Événement</title>
    <link rel="stylesheet" href="/view/style/lighttheme_css/light_update_event_details.css?v=<?php echo time(); ?>">
</head>
<body>
    <?php include __DIR__ . '../../../frontend/view/about-us/event_details_modify.php'; ?>
</body>
</html>
