<?php
// Inclusion des fichiers de connexion et de requêtes sur l'utilisateur
include __DIR__ . '/../../../backend/db/connection.php';
include __DIR__ . '/../../../backend/sql/utilisateur.php';

// Vérifier et démarrer la session si elle n’est pas déjà démarrée
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Déclaration initiale
$estInscrit = false;
$peutUploader = false;

// Vérifier si l'ID de l'événement a été fourni
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("ID de l'événement manquant ou invalide.");
}

$eventId = (int)$_GET['id'];
$userId = $_SESSION['id'] ?? null; // Assurez-vous que l'utilisateur est connecté

// Vérifier si l'utilisateur est un administrateur
$isAdmin = (isset($_SESSION['role']) && $_SESSION['role'] === 'Administrateur');

// Si l'utilisateur n'est pas connecté, on arrête tout
if (!$userId) {
    die("Vous devez être connecté pour accéder à cette page.");
}

// Récupérer les détails de l'utilisateur connecté
$userDetails = fetchUserDetails($pdo, $userId);
$pseudo = $userDetails['pseudo'] ?? null;

// Requête pour récupérer les détails de l'événement
$query = "SELECT * FROM evenement WHERE id_evenement = :eventId";

try {
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':eventId', $eventId, PDO::PARAM_INT);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $event = $stmt->fetch(PDO::FETCH_ASSOC);
    } else {
        die("Événement non trouvé.");
    }

    // Vérifier si l'utilisateur est déjà inscrit à l'événement
    $checkInscriptionReq = "SELECT * FROM evenement_participants WHERE id_evenement = :id_evenement AND uid = :id_utilisateur";
    $checkInscriptionStmt = $pdo->prepare($checkInscriptionReq);
    $checkInscriptionStmt->execute([
        ':id_evenement'    => $eventId,
        ':id_utilisateur'  => $userId
    ]);
    $estInscrit = ($checkInscriptionStmt->rowCount() > 0);

    // Autoriser le téléversement si l'utilisateur est inscrit ou s'il est administrateur
    $peutUploader = ($estInscrit || $isAdmin);

    // Récupérer les photos de l'utilisateur pour cet événement (si c'est un événement de type Visionnage)
    $queryPhotos = "SELECT * FROM photos_evenement WHERE id_evenement = :id_evenement AND uid = :uid";
    $stmtPhotos = $pdo->prepare($queryPhotos);
    $stmtPhotos->execute([
        ':id_evenement' => $eventId,
        ':uid'          => $userId,
    ]);
    $userPhotos = $stmtPhotos->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Erreur lors de la récupération des données : " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Détails de l'Événement</title>
    <link rel="stylesheet" href="../style/lighttheme_css/light_event_details.css?v=<?php echo time(); ?>">
</head>
<body>

<!-- Notification d'erreur ou de succès -->
<?php if (isset($_GET['error']) || isset($_GET['success'])): ?>
    <div class="notification <?php echo isset($_GET['error']) ? 'error' : 'success'; ?>">
        <?php echo htmlspecialchars($_GET['error'] ?? $_GET['success']); ?>
        <button class="close-btn" onclick="this.parentElement.style.display='none';">&times;</button>
    </div>
<?php endif; ?>

<div class="event-details">
    <h1><?php echo htmlspecialchars($event['titre']); ?></h1>
    <p><strong>Date et Heure :</strong> <?php echo htmlspecialchars($event['date_heure']); ?></p>
    <p><strong>Lieu :</strong> <?php echo htmlspecialchars($event['lieu']); ?></p>
    <p><strong>Descriptif :</strong> <?php echo nl2br(htmlspecialchars($event['descriptif'])); ?></p>
    <p><strong>Type :</strong> <?php echo htmlspecialchars($event['type']); ?></p>
    <p><strong>Officiel :</strong> <?php echo ($event['officiel'] ? 'Oui' : 'Non'); ?></p>

    <!-- Bouton pour lancer le diaporama si l'utilisateur est admin et que l'événement est de type "Visionnage" -->
    <?php if ($isAdmin && $event['type'] === 'Visionnage'): ?>
        <form action="slideshow.php" method="get">
            <input type="hidden" name="id" value="<?php echo $event['id_evenement']; ?>">
            <button type="submit" class="launch-slideshow-button">Lancer l'événement</button>
        </form>
    <?php endif; ?>

    <a href="../about-us.php?tab=event" class="back-button">Retour</a>
    <hr class="division">

    <!-- Section pour déposer des photos (uniquement si Visionnage) -->
    <?php if ($event['type'] === 'Visionnage'): ?>
        <?php if ($peutUploader): ?>
            <div class="upload-photos">
                <h2>Déposer vos photos</h2>
                <p>Maintenez la touche Ctrl (ou Cmd sur Mac) enfoncée pour sélectionner plusieurs photos.</p>
                <form action="/backend/controller/upload_photo_visionnage.php" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="id_evenement" value="<?php echo $event['id_evenement']; ?>">
                    <label for="photos">Sélectionnez jusqu'à 10 photos :</label>
                    <input type="file" name="photos[]" id="photos" style="display:block;" accept="image/*" multiple>
                    <p>Formats acceptés : JPG, PNG, GIF. Taille maximale : 5 Mo par fichier.</p>
                    <button type="submit">Téléverser</button>
                </form>
            </div>
        <?php else: ?>
            <div class="upload-photos">
                <h2>Déposer vos photos</h2>
                <p style="color: red; font-weight: bold;">
                    Vous devez être inscrit à cet événement pour pouvoir téléverser des photos.
                </p>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <!-- Bouton de modification de l'événement si l'utilisateur est admin ou propriétaire de l'événement -->
    <?php if ($isAdmin || $event['uid'] == $userId): ?>
        <h2>Modifier l'événement</h2>
        <form action="/backend/controller/modifier_evenement.php" method="get">
            <input type="hidden" name="id" value="<?php echo $event['id_evenement']; ?>">
            <button type="submit" class="modify-button">Modifier l'Événement</button>
        </form>
    <?php endif; ?>

    <!-- Affichage des photos déjà uploadées par l'utilisateur (si événement Visionnage) -->
    <?php if ($event['type'] === 'Visionnage'): ?>
        <div class="user-photos">
            <h2>Vos photos téléchargées</h2>
            <?php if (empty($userPhotos)): ?>
                <p>Aucune photo téléchargée pour cet événement.</p>
            <?php else: ?>
                <ul class="photo-gallery">
                    <?php foreach ($userPhotos as $photo): ?>
                        <li>
                            <img src="/upload/photos_evenement/<?php echo htmlspecialchars($photo['chemin_photo']); ?>" alt="Photo de l'événement">
                            <form action="/backend/controller/delete_photo_visionnage.php" method="post" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette photo ?');">
                                <input type="hidden" name="photo_id" value="<?php echo $photo['id_photo']; ?>">
                                <input type="hidden" name="event_id" value="<?php echo $eventId; ?>">
                                <button type="submit">Supprimer</button>
                            </form>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <!-- Boutons pour s'inscrire ou annuler l'inscription (selon que l'utilisateur est déjà inscrit) -->
    <?php if (isset($_SESSION['id'])): ?>
        <?php if ($estInscrit): ?>
            <form action="/backend/controller/annuler_inscription_evenement.php" method="post">
                <input type="hidden" name="id_evenement" value="<?php echo $event['id_evenement']; ?>">
                <button type="submit" class="cancel-signup-button">Annuler l'inscription</button>
            </form>
        <?php else: ?>
            <form action="/backend/controller/inscription_evenement.php" method="post">
                <input type="hidden" name="id_evenement" value="<?php echo $event['id_evenement']; ?>">
                <button type="submit" class="sign-up-button">S'inscrire à l'Événement</button>
            </form>
        <?php endif; ?>
    <?php endif; ?>

    <!-- Formulaire de suppression de l'événement si l'utilisateur est admin ou propriétaire -->
    <?php if ($isAdmin || $event['uid'] == $userId): ?>
        <form action="/backend/controller/delete_event.php" method="post" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cet événement ?');">
            <input type="hidden" name="id" value="<?php echo $event['id_evenement']; ?>">
            <button type="submit" class="delete-button">Supprimer l'Événement</button>
        </form>
    <?php endif; ?>
</div>
</body>
</html>
