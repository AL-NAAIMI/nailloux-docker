<?php
// Inclure les fichiers nécessaires une seule fois pour éviter les inclusions multiples
include_once('../../backend/db/connection.php'); // Connexion à la base de données

// Récupérer les publications de l'utilisateur avec une image associée
$postsqlimg = "SELECT `pid`, `image`, `dop`, `uid` FROM `publication` WHERE `uid` = :uid AND `image` IS NOT NULL ORDER BY `dop` DESC";

// Préparation de la requête SQL avec un paramètre de type entier pour l'ID de l'utilisateur
$stmt = $pdo->prepare($postsqlimg);
$stmt->bindParam(":uid", $user_id, PDO::PARAM_INT); // Liaison du paramètre user_id
$stmt->execute(); // Exécution de la requête
$postresultimgs = $stmt->fetchAll(PDO::FETCH_ASSOC); // Récupération des résultats
?>

<div class="acc-photo">
    <!-- Section pour afficher les photos de l'utilisateur -->
    <p>Voir les photos de <?php echo htmlspecialchars($prenom); ?>...</p>
    
    <div class="gallery">
        <?php
        // Vérifier si des publications avec des images existent pour cet utilisateur
        if (count($postresultimgs) > 0) {
            // Parcours des résultats de la requête
            foreach ($postresultimgs as $postrow) {
                echo '<div class="gallery-item">'; // Début de chaque élément de la galerie
                
                // Vérification et affichage de l'image de la publication
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
                
                echo '</div>'; // Fermeture de l'élément de la galerie
            }
        } else {
            // Message affiché si aucune publication avec image n'est trouvée
            echo '<p>Aucune publication trouvée</p>';
        }
        ?>
    </div>
</div>
