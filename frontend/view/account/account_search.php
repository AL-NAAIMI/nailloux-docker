<div class="user-search">
<?php
// Vérifier si la requête a retourné des résultats
try {
    // Préparer la requête avec PDO
    // $sql = "SELECT `pseudo`, `id`, `prenom`, `nom`, `email` FROM `utilisateur`";
    // $stmt = $pdo->prepare($sql);

    // Exécuter la requête
    // $stmt->execute();

    // Récupérer les résultats sous forme de tableau associatif
    // $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Vérifier si des utilisateurs ont été trouvés

    if (count($result) >= 1) :
        // Parcours des résultats pour afficher chaque utilisateur trouvé
        foreach ($result as $row) {
            // Récupération des informations de l'utilisateur depuis la base de données
            $username_row = $row['pseudo']; // Le pseudo de l'utilisateur
            $user_id = $row['id']; // L'ID de l'utilisateur
            $prenom = $row['prenom']; // Le prénom de l'utilisateur
            $nom = $row['nom']; // Le nom de l'utilisateur
            $email = $row['email']; // L'email de l'utilisateur

            // Utilisation de la fonction getUserRole pour récupérer le rôle actuel de l'utilisateur
            $role = getUserRole($pdo, $username_row);

            // Récupérer la photo de profil de l'utilisateur via une fonction
            $photo_profil = getUserProfilePic($pdo, $user_id);

            // Génération d'une image par défaut si la photo n'est pas disponible
            $profile_image = (!empty($photo_profil) && file_exists(__DIR__ . "/../../" . $photo_profil))
                ? htmlspecialchars("../" . $photo_profil)
                : "https://api.dicebear.com/6.x/initials/png?seed=" . urlencode($prenom) . "&size=128";
        ?>
            <!-- Affichage de l'utilisateur sous forme de carte -->
            <a id="pseudo-user" href="account.php?pseudo=<?php echo htmlspecialchars($username_row); ?>">
                <div class="user-profile">
                    <!-- Affichage de la photo de profil de l'utilisateur -->
                    <img src="<?php echo $profile_image; ?>" 
                         alt="profile" class="account-profpic">
                    <div class="user-info">
                        <div class="user-pseudo">
                            <!-- Affichage du pseudo de l'utilisateur et de son rôle -->
                            <?php echo htmlspecialchars($username_row); ?>
                            <span class="user-role">(<?php echo htmlspecialchars($role); ?>)</span>
                        </div>
                        <div class="user-name"><?php echo htmlspecialchars($prenom) . ' ' . htmlspecialchars($nom); ?></div>
                    </div>
                </div>
            </a>
        <?php 
        }
    // Si aucun utilisateur n'a été trouvé
    elseif (count($result) == 0) :
        ?>
        <div class="user-not-found">
            <!-- Affichage d'un message si aucun utilisateur n'est trouvé -->
            <h2>Aucun utilisateur trouvé</h2>
            <p>L'utilisateur que vous recherchez n'existe pas. Veuillez réessayer avec un autre pseudo.</p>
        </div>
    <?php endif;
} catch (PDOException $e) {
    // Gestion des erreurs
    echo "Erreur lors de la récupération des utilisateurs : " . $e->getMessage();
}
?>
</div>
