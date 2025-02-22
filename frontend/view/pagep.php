<?php
session_start();
include __DIR__ . '/../../back/env.php';
include __DIR__ . '/../../backend/db/connection.php';
include __DIR__ . '/../../backend/sql/utilisateur.php';

// Récupérer les images des publications publiques avec PDO
$postsqlimg = "SELECT `pid`, `image`, `dop`, `uid` FROM `publication` WHERE `public` = 1 AND `image` IS NOT NULL ORDER BY `dop` DESC";

try {
    // Préparer la requête
    $stmt = $pdo->prepare($postsqlimg);

    // Exécuter la requête
    $stmt->execute();

    // Récupérer les résultats
    $postresultimgs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fermer la requête
    $stmt->closeCursor();
} catch (PDOException $e) {
    // Gérer les erreurs de la requête
    die("Erreur lors de la récupération des images : " . $e->getMessage());
}

?>


<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nailloux - Page principale</title>

    <!-- Inclusion du fichier header -->
    <?php include('header.php'); ?>

</head>
<body>
    <!-- Section principale -->
    <section class="hero">
    <center><img class="about-us-logo" src="logo/Nailloux%20logo3.png" alt="logo"></center>

        <div class="hero-content">
            <h1>Nailloux, l'avenir dans la photo</h1>
        </div>
    </section>

    <!-- Section "À propos" -->
    <section class="about">
        <div class="about-image">
            <img src="../view/img/25525-180731145019569-0-960x640.jpg" alt="Photographes">
        </div>
        <div class="about-content">
            <h2>À propos de nous</h2>
            <ul>
                <li><strong>Le Club Photo Nailloux </strong> est une association de passionnés de photographie, ouverte à tous les niveaux. Nous souhaitons partager des moments enrichissants autour de cette passion commune qu'est la
                photographie.</li>
                <li>Nos activités se déroulent à Nailloux, un village entre <strong>Toulouse et Carcassonne</strong>, où nous organisons formations et sorties.</li>
                <li>Ces sorties sont des sorties thématiques (eau, portrait, macro, etc.), des cours sur la <strong>technique photographique </strong>et le traitement des images. Nous proposons aussi des sessions dans notre laboratoire de développement noir et blanc et des équipements de studio sont à disposition pour emprunt.</li>
                <li><strong>Fondé en 2002</strong>, Nailloux organise des séances de visionnage de photos dans lesquels vos photos
                seront jugées par des membres du club, vous permettant
                d'avoir un avis externe et de vous améliorer.</li>
                <li><strong>Le club</strong> est basé sur l'engagement bénévole de ses membres. Si vous êtes intéressé, venez partager vos créations avec nous et laissez libre cours à votre créativité !</li>
                <li> Le club photo de Nailloux sera ravi de vous accueillir! </li>
            </ul>
        </div>
    </section>
    
</body>
</html>

<div class="acc-photo">
    <center><h1>Galerie</h1></center>
    <div class="gallery">
        <?php
        if (count($postresultimgs) > 0) {
            foreach ($postresultimgs as $postrow) {
                echo '<div class="gallery-item">';
                
                // Affichage de l'image
                if ($postrow["image"] != null) {
                    echo '<div class="feed-post-display-box-image">
                          <a href="#" onclick="openLightbox(\'/upload/publication/' .
                                htmlspecialchars($postrow["image"]) . 
                                '\'); return false;">
                                <img src="/upload/publication/' .
                                htmlspecialchars($postrow["pid"]) . 
                                '_mini.png" alt="' . 
                                htmlspecialchars($postrow["image"]) . 
                                '" >
                          </a>
                        </div>';
                }
                
                echo '</div>';
            }
        } else {
            echo '<p>Aucune publication trouvée.</p>';
        }
        ?>
    </div>
</div>