<?php
session_set_cookie_params(0, '/');
session_start();

include __DIR__ . '/../../back/env.php';
include __DIR__ . '/../../backend/sql/publication.php';
include __DIR__ . '/../../backend/sql/utilisateur.php';
include __DIR__ . '/../../backend/db/connection.php';

// Vérification de l'accès en fonction du rôle
if (!isset($_SESSION["pseudo"]) || !isset($_SESSION["role"]) || ($_SESSION["role"] !== "Administrateur" && $_SESSION["role"] !== "Membre")) {
    header("Location: ../index.php");
    exit();
}

// Récupérer le rôle utilisateur si non défini dans la session
if (!isset($_SESSION['role'])) {
    $stmt = $pdo->prepare("SELECT `role` FROM `utilisateur` WHERE `pseudo` = :pseudo LIMIT 1");
    $stmt->bindParam(":pseudo", $_SESSION['pseudo'], PDO::PARAM_STR);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    $_SESSION['role'] = $user ? $user['role'] : null;
}
?>

<html>
<head>
    <title>Nailloux</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <?php include "header.php"; ?>
</head>
<body>
    <div class="separate_header"></div>
    <br><br><br><br>
    <div class="feed-page-body">
        <div class="feed-page-head">
            <h2>Fil de Publication</h2>
        </div>

        <?php include __DIR__ . '/../../backend/controller/feed_formulaire_champ_pour_poster.php'; ?>

        <div class="feeds">
            <?php
            // Récupérer les publications
            $stmt = $pdo->query("SELECT * FROM `publication` ORDER BY `dop` DESC");
            $postrows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (count($postrows) > 0) {
                foreach ($postrows as $postrow) {
                    // Récupérer les informations utilisateur
                    $stmt = $pdo->prepare("SELECT * FROM `utilisateur` WHERE `id` = :uid LIMIT 1");
                    $stmt->bindParam(":uid", $postrow["uid"], PDO::PARAM_INT);
                    $stmt->execute();
                    $usrrow = $stmt->fetch(PDO::FETCH_ASSOC);

                    if (!$usrrow) {
                        continue; // Ignorez les publications sans utilisateur valide
                    }

                    $photo_profil = (!empty($usrrow["photo_profil"]) && file_exists($usrrow["photo_profil"]))
                        ? htmlspecialchars($usrrow["photo_profil"])
                        : "https://api.dicebear.com/6.x/initials/png?seed=" .
                            urlencode($usrrow["prenom"] ?? "User") .
                            "&size=128";

                    // Récupérer le nombre de commentaires
                    $stmt = $pdo->prepare("SELECT COUNT(*) FROM `commentaire_p` WHERE `pid` = :pid");
                    $stmt->bindParam(":pid", $postrow["pid"], PDO::PARAM_INT);
                    $stmt->execute();
                    $commentCount = $stmt->fetchColumn();

                    echo '<div class="feed-container">';
                    echo '<div class="feed-post" id="feed-post-' . htmlspecialchars($postrow["pid"]) . '">';
                    
                    echo '<div class="feed-post-display-box-head">
                            <ul>
                                <li>
                                <a href="account.php?pseudo=' . htmlspecialchars($usrrow["pseudo"]) . '">
                                    <img src="' . $photo_profil . '" alt="profile" class="account-profpic">
                                </a>
                                </li>
                                <li>
                                    <a href="account.php?pseudo=' . htmlspecialchars($usrrow["pseudo"]) . '" class="feed-post-display-pseudo">' .
                                        htmlspecialchars($usrrow["prenom"]) .
                                    '</a>
                                </li>
                                <li>
                                <small> a posté le : </small>
                                    <small>' . htmlspecialchars($postrow["dop"]) . '</small>
                                </li>
                            </ul>
                        </div>';

                    echo '<div class="feed-post-display-box-message">';
                    if (!empty($postrow["titre"])) {
                        echo '<h3 class="feed-post-title">' . htmlspecialchars($postrow["titre"]) . '</h3>';
                    }
                    echo nl2br(htmlspecialchars($postrow["msg"])) . '</div>';

                    // Prépare les chemins pour l'image originale et la miniature
                    $originalPath = __DIR__ . '/../../upload/publication/' . $postrow["image"];
                    $miniPath     = __DIR__ . '/../../upload/publication/' . $postrow["pid"] . '_mini.png';

                    // Vérifie qu'on a bien un nom d'image ET que le fichier original existe
                    if (
                        !empty($postrow["image"]) &&
                        file_exists($originalPath)
                    ) {
                        // Optionnel : Vérifie si la miniature existe, sinon peut-être générer une miniature ou utiliser l'original
                        if (!file_exists($miniPath)) {
                            // Générer une miniature si nécessaire
                            // Par exemple, utiliser une fonction ou un script pour créer la miniature
                            // Ici, nous supposons que les miniatures sont déjà créées
                            // Si non, commenter la vérification de la miniature
                            // Pour éviter que le bouton soit caché, nous allons ne pas vérifier la miniature
                        }

                        // Affichage de l'image (utiliser la miniature si elle existe, sinon l'original)
                        $imageToShow = file_exists($miniPath) ? $postrow["pid"] . '_mini.png' : $postrow["image"];

                        echo '<div class="feed-post-display-box-image">
                                <a href="#" onclick="openLightbox(\'/upload/publication/' .
                                htmlspecialchars($postrow["image"]) .
                                '\'); return false;">
                                    <img src="/upload/publication/' .
                                htmlspecialchars($imageToShow) .
                                '" alt="' .
                                htmlspecialchars($postrow["image"]) . '" >
                                </a>
                              </div>';

                        // Bouton "Afficher les informations" (EXIF) -- seulement si on a l'image
                        echo '<button class="show-info-btn post-btn" onclick="showInfoModal(' . htmlspecialchars($postrow["pid"]) . ')">
                                Afficher les informations
                              </button>';
                    }

                    // Bouton de commentaires
                    echo '<button class="toggle-comments-btn" onclick="toggleComments(' . htmlspecialchars($postrow["pid"]) . ')">
                            <img src="./logo/logocomment.png" alt="Logo" style="width: 40px; height: 40px;">
                            <span class="comment-count">' . $commentCount . ' commentaire(s)</span>
                          </button>';
                    echo '</div>';

                    // Affichage des commentaires
                    $stmt = $pdo->prepare("SELECT * FROM `commentaire_p` WHERE `pid` = :pid ORDER BY `date_heure` ASC");
                    $stmt->bindParam(":pid", $postrow["pid"], PDO::PARAM_INT);
                    $stmt->execute();
                    $commentrows = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    echo '<div class="comments-section hidden" id="comments-section-' . htmlspecialchars($postrow["pid"]) . '">';
                    if (isset($_SESSION["pseudo"]) && isset($_SESSION["id"])) {
                        echo '<div class="comment-form">
                                    <form action="/backend/controller/comment.php" method="post">
                                        <input type="hidden" name="post_id" value="' . htmlspecialchars($postrow["pid"]) . '">
                                        <input type="hidden" name="user_id" value="' . htmlspecialchars($_SESSION["id"]) . '">
                                        <textarea class="comment-form-zone" name="comment" placeholder="Ajouter un commentaire..." required></textarea>
                                        <button class="post-comment-btn" type="submit">Commenter</button>
                                    </form>
                              </div>';
                    }

                    if (count($commentrows) > 0) {
                        foreach ($commentrows as $commentrow) {
                            $stmt = $pdo->prepare("SELECT * FROM `utilisateur` WHERE `id` = :uid LIMIT 1");
                            $stmt->bindParam(":uid", $commentrow["uid"], PDO::PARAM_INT);
                            $stmt->execute();
                            $commentUser = $stmt->fetch(PDO::FETCH_ASSOC);

                            $commentUserPhoto =
                                (!empty($commentUser["photo_profil"]) && file_exists($commentUser["photo_profil"]))
                                ? htmlspecialchars($commentUser["photo_profil"])
                                : "https://api.dicebear.com/6.x/initials/png?seed=" .
                                    urlencode($commentUser["prenom"] ?? "User") .
                                    "&size=128";   

                            echo '<div class="comment-box">
                                    <a href="account.php?pseudo=' . htmlspecialchars($commentUser["pseudo"]) . '">
                                        <img src="' . $commentUserPhoto . '" alt="profile" class="account-profpic-comment">
                                    </a>
                                    <div class="comment-content">
                                        <div class="comment-header">
                                            <strong class="comment-prenom">' . htmlspecialchars($commentUser["prenom"]) . '</strong>
                                            <small class="comment-date">' . htmlspecialchars($commentrow["date_heure"]) . '</small>
                                        </div>
                                        <span class="comment">' . nl2br(htmlspecialchars($commentrow["texte"])) . '</span>
                                    </div>
                                </div>';
                        }
                    } else {
                        echo "<p class='comment-count-zero'>Aucun commentaire</p>";
                    }

                    echo '</div>'; // comments-section
                    echo '</div>'; // feed-container
                }
            } else {
                echo "<p>Aucun post trouvé</p>";
            }
            ?>
        </div>
    </div>

    <script>
    function toggleComments(postId) {
        const commentsSection = document.getElementById(`comments-section-${postId}`);
        commentsSection.classList.toggle('hidden');
    }
    </script>

    <?php include "footer.php"; ?>
    <!-- Boîte modale pour afficher les informations -->
<div id="infoModal" class="modal" style="display: none;">
    <div class="modal-content">
        <span class="close-btn" onclick="closeModal()">&times;</span>
        <div id="modalBody">
            <p>Chargement des informations...</p>
        </div>
    </div>
</div>

<script>
function showInfoModal(postId) {
    const modal = document.getElementById('infoModal');
    const modalBody = document.getElementById('modalBody');

    // Verify postId validity
    if (!postId || isNaN(postId) || parseInt(postId) <= 0) {
        modalBody.innerHTML = '<p>Erreur : postId non défini ou invalide.</p>';
        modal.style.display = 'flex';
        return;
    }

    // Display the modal
    modal.style.display = 'flex';

    // Load EXIF data via fetch
    fetch('../../backend/controller/exif_collector.php?pid=' + postId)
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                modalBody.innerHTML = `<p>Error: ${data.error}</p>`;
            } else {
                const exifData = data.donnees_exif || {};
                modalBody.innerHTML = `
                    <p><strong>Format de l'image :</strong> ${data.format_image || "Non disponible"}</p>
                    <p><strong>Mode de l'image :</strong> ${data.mode_image || "Non disponible"}</p>
                    <p><strong>Taille de l'image :</strong> ${data.taille_image || "Non disponible"}</p>
                    <p><strong>Mots-clés :</strong> ${data.mots_clés || "Non disponible"}</p>
                    <p><strong>Marque :</strong> ${exifData.Make || "Non disponible"}</p>
                    <p><strong>Modèle :</strong> ${exifData.Model || "Non disponible"}</p>
                    <p><strong>Logiciel :</strong> ${exifData.Software || "Non disponible"}</p>
                `;
            }
        })
        .catch(error => {
            modalBody.innerHTML = '<p>Erreur lors du chargement des informations.</p>';
            console.error(error);
        });
}

function closeModal() {
    const modal = document.getElementById('infoModal');
    modal.style.display = 'none';
}

</script>

</body>
</html>
