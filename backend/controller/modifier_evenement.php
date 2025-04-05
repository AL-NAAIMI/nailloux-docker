<?php
include $_SERVER['DOCUMENT_ROOT'] . '/backend/db/connection.php';
include $_SERVER['DOCUMENT_ROOT'] . '/backend/sql/utilisateur.php';
session_start();

// Vérifier si l'utilisateur est authentifié
if (!isset($_SESSION['id'])) {
    header("Location: /frontend/view/about-us.php?tab=event");
    exit;
}

// Récupération des paramètres
$eventId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($eventId <= 0) {
    header("Location: /frontend/view/about-us.php?tab=event");
    exit;
}

// Vérifier si l'utilisateur a les droits de modifier (admin ou propriétaire)
$userId = $_SESSION['id'];
$isAdmin = isset($_SESSION['admin']) && $_SESSION['admin'] == true;

try {
    // Vérifier si l'utilisateur est autorisé à modifier l'événement
    $query = "SELECT * FROM evenement WHERE id_evenement = :id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':id', $eventId, PDO::PARAM_INT);
    $stmt->execute();
    $event = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$event) {
        header("Location: /frontend/view/about-us.php?tab=event&error=event_not_found");
        exit;
    }

    // Si l'utilisateur n'est pas admin et n'est pas le créateur
    if (!$isAdmin && $event['uid'] != $userId) {
        header("Location: /frontend/view/about-us.php?tab=event&error=not_authorized");
        exit;
    }
} catch (PDOException $e) {
    header("Location: /frontend/view/about-us.php?tab=event&error=db_error");
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier l'Événement</title>
    <link rel="stylesheet" href="/frontend/view/style/lighttheme_css/light_update_event_details.css?v=<?php echo time(); ?>">
</head>
<body>
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/frontend/view/about-us/event_details_modify.php'; ?>
</body>
</html>
