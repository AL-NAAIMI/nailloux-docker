<?php
include('../../backend/controller/account_linking_utilisateur.php'); // Inclure le fichier pour charger les données utilisateur

// Vérification de l'accès en fonction du rôle
if (
    !isset($_SESSION["pseudo"]) ||
    !isset($_SESSION["role"])
) {
    header("Location: index.php"); // Rediriger si l'utilisateur n'est pas autorisé
    exit();
}

// Retrieve user role using PDO if not set in the session
if (!isset($_SESSION['role'])) {
    $stmt = $pdo->prepare("SELECT `role` FROM `utilisateur` WHERE `pseudo` = :pseudo LIMIT 1");
    $stmt->bindParam(":pseudo", $_SESSION['pseudo'], PDO::PARAM_STR);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    $_SESSION['role'] = $user ? $user['role'] : null;
}

// Vérifiez si l'utilisateur est connecté
if (isset($_SESSION['pseudo'])) {
    // Utiliser PDO pour récupérer les informations de l'utilisateur
    $stmt = $pdo->prepare("SELECT * FROM utilisateur WHERE pseudo = :pseudo LIMIT 1");
    $stmt->bindParam(':pseudo', $_SESSION['pseudo'], PDO::PARAM_STR);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Vérifier si l'utilisateur existe
    if ($user) {
        $username_row = $user['pseudo'];
        $prenom = $user['prenom'];
        $photo_profil = $user['photo_profil'];
        $user_id = $user['id'];
    } else {
        // Rediriger si l'utilisateur n'existe pas dans la base de données
        header('Location: index.php');
        exit();
    }
?>
    <div class="account">
        <div class="account-body">
            <!-- bannière et pp -->
            <div class="account-banner" style="background-image: url('logo/banner3.jpg');">
                <div class="account-img">
                    <ul id="img-nom-compte">
                        <li>
                            <a href="#" <?php if ($username_row == $_SESSION['pseudo']) echo 'onclick="openPopup()"'; ?>>
                                <img src="<?php echo !empty($photo_profil) && file_exists($photo_profil) ? ($photo_profil) : 'https://api.dicebear.com/6.x/initials/png?seed=' . urlencode($prenom ?? 'User') . '&size=128'; ?>" alt="profile" class="big-account-profpic" id="profile-pic">
                            </a>
                        </li>
                        <!-- Affichage des infos utilisateur -->
                        <li>
                            <div class="message-buttons-name">
                                <?php echo "<b>" . htmlspecialchars($prenom) . "</b>"; ?>
                                <small>@<?php echo htmlspecialchars($username_row); ?></small><br>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
            <!-- fin de bannière et pp -->

            <!-- Popup HTML -->
            <div id="popup" class="popup" style="display:none;">
                <div class="popup-content">
                    <span class="close" onclick="closePopup()">&times;</span>
                    <h2>Changer la Photo de Profil</h2>
                    <form action="../../backend/controller/upload_profile_pic.php" method="POST" enctype="multipart/form-data" id="profile-form">
                        <div class="drop-zone" id="drop-zone" onclick="document.getElementById('file-input').click();">
                            <span class="upload-pp">Glissez-déposez votre image ici ou cliquez pour sélectionner</span>
                            <input type="file" name="photo_profil" accept="image/*" required id="file-input" style="display:none;">
                        </div>
                        <input type="hidden" name="pseudo" value="<?php echo htmlspecialchars($username_row); ?>">
                        <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($user_id); ?>">
                        <button type="submit" class="upload-pp-btn">Uploader</button>
                    </form>
                </div>
            </div>
            <!-- Fin de la popup -->

            <!-- gestion des onglet -->
            <div class="account-tabs">
                <ul>
                    <li class="acc-tabs-item">
                        <a href="account.php?pseudo=<?php echo htmlspecialchars($username_row); ?>&tab=feed" class="acc-tabs-link">Feed</a>
                    </li>
                    <li class="acc-tabs-item">
                        <a href="account.php?pseudo=<?php echo htmlspecialchars($username_row); ?>&tab=info" class="acc-tabs-link">Info</a>
                    </li>
                    <li class="acc-tabs-item">
                        <a href="account.php?pseudo=<?php echo htmlspecialchars($username_row); ?>&tab=photo" class="acc-tabs-link">Photo</a>
                    </li>
                    
                    <?php 
                    if ($_SESSION['role'] === 'Administrateur' && $username_row === $_SESSION['pseudo']) { ?>
                        <li class="acc-tabs-item">
                            <a href="account.php?pseudo=<?php echo htmlspecialchars($username_row); ?>&tab=administration" class="acc-tabs-link">Administration</a>
                        </li>
                    <?php } ?>

                    <li class="acc-tabs-item">
                        <a href="account.php?pseudo=<?php echo htmlspecialchars($username_row); ?>&tab=contact" class="acc-tabs-link">Contact</a>
                    </li>

                </ul>
            </div>
            
            <div class="acc-tabs-page">
                <?php include('../../backend/controller/account_gestion_des_onglet.php'); ?>
            </div>
            <!-- fin de la gestion des onglet -->

        </div>
    </div>
<?php
} else {
    // Rediriger l'utilisateur vers la page de connexion ou afficher un message d'erreur
    header('Location: index.php');
    exit();
}
?>
