<div class="accueil-compte">
    <?php
        echo '<p id="accueil-txt">Bonjour, ' . htmlspecialchars($prenom) . '</p>';
    ?>
</div>
<div class="acc-feed">
    <?php
    // Vérification si le pseudo est passé dans l'URL
    if (isset($_GET['pseudo']) && !empty($_GET['pseudo'])) {
        $viewed_pseudo = htmlspecialchars($_GET['pseudo']); // Nettoyage du pseudo
    } else {
        echo '<p>Utilisateur introuvable.</p>';
        exit;
    }

    // Récupérer les données de l'utilisateur basé sur le pseudo
    $profilePicSql = "SELECT id, photo_profil, prenom, pseudo FROM utilisateur WHERE pseudo = :pseudo";
    $stmt = $pdo->prepare($profilePicSql);
    $stmt->bindParam(":pseudo", $viewed_pseudo, PDO::PARAM_STR);
    $stmt->execute();
    $userDetails = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$userDetails) {
        echo '<p>Utilisateur introuvable.</p>';
        exit;
    }

    // Extraire les détails de l'utilisateur
    $viewed_user_id = $userDetails['id'];
    $photo_profil = $userDetails['photo_profil'];
    $prenom = $userDetails['prenom'];
    $pseudo = $userDetails['pseudo'];

    // Récupérer les publications de l'utilisateur visualisé
    $postsql = "SELECT `msg`, `image`, `pid`, `dop`, `titre` FROM `publication` WHERE `uid` = :uid ORDER BY `dop` DESC";
    $stmt = $pdo->prepare($postsql);
    $stmt->bindParam(":uid", $viewed_user_id, PDO::PARAM_INT);
    $stmt->execute();
    $postresult = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Affichage des publications
    if (count($postresult) > 0) {
        foreach ($postresult as $postrow) {
            $pid = $postrow['pid'];
            $checkPostSql = "SELECT COUNT(*) FROM `publication` WHERE `pid` = :pid";
            $checkStmt = $pdo->prepare($checkPostSql);
            $checkStmt->bindParam(":pid", $pid, PDO::PARAM_INT);
            $checkStmt->execute();
            $postExists = $checkStmt->fetchColumn();

            if ($postExists > 0) {
                echo '<div class="feed-container">';
                echo '<div class="feed-post-display-box" id="post-' . htmlspecialchars($postrow['pid']) . '">
                        <div class="feed-post-display-box-head">
                        </div>';

                        // Affichage du message
                echo '<div class="feed-post-display-box-message">' . nl2br(htmlspecialchars($postrow['titre'])) . '</div>';

                // Affichage du message
                echo '<div class="feed-post-display-box-message">' . nl2br(htmlspecialchars($postrow['msg'])) . '</div>';

                // Affichage de l'image
                if ($postrow['image'] != NULL) {
                    echo '<div class="feed-post-display-box-image">
                            <a href="#" onclick="openLightbox(\'/upload/publication/' . htmlspecialchars($postrow['image']) . '\'); return false;">
                                <img class="img-feed-acc" src="/upload/publication/' . htmlspecialchars($postrow['pid']) . '_mini.png" alt="' . htmlspecialchars($postrow['image']) . '" >
                            </a>
                          </div>';
                }

                // Vérification si l'utilisateur connecté peut supprimer
                if ($pseudo === $_SESSION['pseudo'] || (isset($_SESSION['role']) && $_SESSION['role'] === 'Administrateur')) {
                    echo '<button onclick="removePost(' . htmlspecialchars($postrow['pid']) . ')" class="remove-btn">Supprimer</button>';
                }

                echo '</div>'; // Clôture de feed-post-display-box

                // Afficher les commentaires
                $commentrows = fetchComments($pdo, $postrow['pid']);
                echo '<div class="comments-section">';
                if (count($commentrows) > 0) {
                    foreach ($commentrows as $commentrow) {
                        $commentUser = fetchUserDetails($pdo, $commentrow['uid']);
                        $commentUserPhoto = !empty($commentUser['photo_profil']) && file_exists($commentUser['photo_profil']) ? htmlspecialchars($commentUser['photo_profil']) : 'https://api.dicebear.com/6.x/initials/png?seed=' . htmlspecialchars($commentUser['prenom']) . '&size=40';

                        echo '<div class="comment-box">
                                <a class="rien" href="account.php?pseudo=' . htmlspecialchars($commentUser['pseudo']) . '">
                                    <img src="' . $commentUserPhoto . '" alt="profile" class="account-profpic-comment">
                                </a>
                                <strong class="comment-prenom">' . htmlspecialchars($commentUser['prenom']) . ' :</strong> <br> 
                                <a class="comment">' . htmlspecialchars($commentrow['texte']) . '</a>
                                <small> - ' . htmlspecialchars($commentrow['date_heure']) . '</small>';

                        // Vérifier si l'utilisateur est un administrateur
                        if (isset($_SESSION['role']) && $_SESSION['role'] === 'Administrateur') {
                            echo ' <button class="remove-btn" onclick="removeComment(' . htmlspecialchars($commentrow['id_commentaire_p']) . ')">Supprimer</button>';
                        }

                        echo '</div>';
                    }
            } else {
                echo '<p>Aucun commentaire</p>';
            }
                echo '</div>'; // Clôture de comments-section
                echo '</div>'; // Clôture de feed-container
            }
        }
    } else {
        echo '<p>Aucune publication trouvée.</p>';
    }
    ?>
</div>
<script>
function removeComment(commentId) {
    if (!commentId) {
        console.error("Erreur: ID du commentaire non valide.");
        return;
    }

    if (confirm("Voulez-vous vraiment supprimer ce commentaire ?")) {
        console.log("Tentative de suppression du commentaire ID:", commentId); // Debug log

        fetch('/backend/controller/delete_comment.php', { // Fixed path
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'comment_id=' + encodeURIComponent(commentId)
        })
        .then(response => response.text()) // Debugging: get raw response
        .then(data => {
            console.log("Réponse brute du serveur:", data); // Debug log

            try {
                const jsonData = JSON.parse(data);
                if (jsonData.success) {
                    alert("Commentaire supprimé !");
                    location.reload();
                } else {
                    alert("Erreur: " + jsonData.message);
                }
            } catch (e) {
                console.error("Erreur JSON:", e, data);
                alert("Réponse invalide du serveur. Vérifiez la console.");
            }
        })
        .catch(error => console.error("Erreur:", error));
    }
}

</script>

