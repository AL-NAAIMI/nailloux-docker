<?php
include __DIR__ . '/../../../backend/db/connection.php';
include __DIR__ . '/../../../backend/sql/utilisateur.php';
if(session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Vérifier si l'ID de l'événement a été fourni
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("ID de l'événement manquant ou invalide.");
}

$eventId = (int)$_GET['id'];
$userId = $_SESSION['id'] ?? null; // Assurez-vous que l'utilisateur est connecté

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

    // Récupérer les photos de l'utilisateur pour cet événement
    $queryPhotos = "SELECT * FROM photos_evenement WHERE id_evenement = :id_evenement AND uid = :uid";
    $stmtPhotos = $pdo->prepare($queryPhotos);
    $stmtPhotos->execute([
        ':id_evenement' => $eventId,
        ':uid' => $userId,
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
        <p><strong>Officiel :</strong> <?php echo $event['officiel'] ? 'Oui' : 'Non'; ?></p>
        <?php if ($_SESSION['role'] === 'Administrateur' && $event['type'] === 'Visionnage'): ?>
            <form action="slideshow.php" method="get">
                <input type="hidden" name="id" value="<?php echo $event['id_evenement']; ?>">
                <button type="submit" class="launch-slideshow-button">Lancer l'événement</button>
            </form>
        <?php endif; ?>

        <a href="../about-us.php?tab=event" class="back-button">Retour</a>
        <hr class ="division">
        <?php if ($event['type'] === 'Visionnage'): ?>
    <?php if ($estInscrit): ?>
        <div class="upload-photos">
            <h2>Déposer vos photos</h2>
            <p>Maintenez la touche Ctrl enfoncée lors de la sélection pour en sélectionner plusieurs.</p>
            <form action="../../../backend/controller/upload_photo_visionnage.php" method="post" enctype="multipart/form-data">
                <input type="hidden" name="id_evenement" value="<?php echo $event['id_evenement']; ?>">
                <label for="photos">Sélectionnez jusqu'à 10 photos :</label>
                <input type="file" name="photos[]" id="photos" accept="image/*" multiple>
                <p>Formats acceptés : JPG, PNG, GIF. Taille maximale : 5 Mo par fichier.</p>
                <button type="submit">Téléverser</button>
            </form>
        </div>
    <?php else: ?>
        <div class="upload-photos">
            <h2>Déposer vos photos</h2>
            <p style="color: red; font-weight: bold;">Vous devez être inscrit à cet événement pour pouvoir téléverser des photos.</p>
        </div>
    <?php endif; ?>
<?php endif; ?>


        <!-- Modifier button -->
        <?php if (isset($_SESSION['role']) && ($_SESSION['role'] === 'Administrateur' || $event['uid'] == $_SESSION['id'])): ?>
            <h2>Modifier l'événement</h2>
            <form action="../../../backend/controller/modifier_evenement.php" method="get">
                <input type="hidden" name="id" value="<?php echo $event['id_evenement']; ?>">
                <button type="submit" class="modify-button">Modifier l'Événement</button>
            </form>
        <?php endif; ?>

        <?php if ($event['type'] === 'Visionnage'): ?>
            <div class="user-photos">
                <h2>Vos photos téléchargées</h2>
                <?php if (empty($userPhotos)): ?>
                    <p>Aucune photo téléchargée pour cet événement.</p>
                <?php else: ?>
                    <ul class="photo-gallery">
                        <?php foreach ($userPhotos as $photo): ?>
                            <li>
                                <img src="../../../upload/photos_evenement/<?php echo htmlspecialchars($photo['chemin_photo']); ?>" alt="Photo de l'événement">
                                <form action="../../../backend/controller/delete_photo_visionnage.php" method="post" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette photo ?');">
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



        <!-- Boutons pour s'inscrire ou annuler l'inscription -->

        <?php
            // Verifier si l'utilisateur est inscrit a l'evenement
            $estInscrit = false;

            if (isset($_SESSION['id'])) {
                $checkInscriptionReq = "SELECT * FROM evenement_participants WHERE id_evenement = :id_evenement AND uid = :id_utilisateur";
                $checkInscriptionStmt = $pdo->prepare($checkInscriptionReq);
                $checkInscriptionStmt->execute([
                    ':id_evenement' => $event['id_evenement'],
                    ':id_utilisateur' => $_SESSION['id']
                ]);
                $estInscrit = $checkInscriptionStmt->rowCount() > 0;
            }
        ?>
        
        <?php if (isset($_SESSION['id'])): ?>
            <?php if ($estInscrit): ?>
                <form action="../../../backend/controller/annuler_iscription_evenement.php" method="post">
                    <input type="hidden" name="id_evenement" value="<?php echo $event['id_evenement']; ?>">
                    <button type="submit" class="cancel-signup-button">Annuler l'Inscription</button>
                </form>
            <?php else: ?>
                <form action="../../../backend/controller/inscription_evenement.php" method="post">
                    <input type="hidden" name="id_evenement" value="<?php echo $event['id_evenement']; ?>">
                    <button type="submit" class="sign-up-button">S'inscrire à l'Événement</button>
                </form>
            <?php endif; ?>
        <?php endif; ?>


                        
        <!-- Formulaire de suppression de l'événement -->
        <?php if (isset($_SESSION['role']) && ($_SESSION['role'] === 'Administrateur' || $event['uid'] == $_SESSION['id'])): ?>
            <form action="../../../backend/controller/delete_event.php" method="post" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cet événement ?');">
                <!-- ID de l'événement passé en tant que champ caché -->
                <input type="hidden" name="id" value="<?php echo $event['id_evenement']; ?>">
                <button type="submit" class="delete-button">Supprimer l'Événement</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
