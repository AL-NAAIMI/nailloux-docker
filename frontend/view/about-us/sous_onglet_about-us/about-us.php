<?php
// Inclure les fichiers nécessaires une seule fois
include_once(__DIR__ . '/../../../../backend/db/connection.php');

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

<section class="photographers-section">
    <center><h1>Fondateurs Nailloux</h1></center>
    <div class="photographer-cards">
        <div class="photographer-card">
            <img src="path/to/dominique-image.jpg" alt="" class="profile-img">
            <h2>Nom Prenom</h2>
            <p>Je me suis intéressé à la photographie assez jeune, par curiosité, puis cette pratique a pris de plus en plus de place...</p>
            <button>En savoir plus</button>
        </div>
        <div class="photographer-card">
            <img src="path/to/edouige-image.jpg" alt="" class="profile-img">
            <h2>Nom Prenom</h2>
            <p>Edouige est un photographe de mode professionnel avec plusieurs années d'expérience dans les domaines de la mode...</p>
            <button>En savoir plus</button>
        </div>
        <div class="photographer-card">
            <img src="path/to/alex-image.jpg" alt="" class="profile-img">
            <h2>Nom Prenom  </h2>
            <p>Photographe professionnel basé à Nice, Alex commence son activité en 2015. Il est initialement remarqué sur les réseaux sociaux...</p>
            <button>En savoir plus</button>
        </div>
    </div>
</section>
