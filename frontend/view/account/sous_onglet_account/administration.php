<?php
// ================================================================
// Fichier de gestion des utilisateurs et modification des rôles
// ================================================================
// Ce fichier permet de gérer la liste des utilisateurs et de 
// modifier leurs rôles. Seuls les administrateurs ont la possibilité
// de modifier les rôles des autres utilisateurs. Les autres utilisateurs
// peuvent seulement voir la liste des utilisateurs et leurs informations.
// ================================================================

// Récupérer le rôle de l'utilisateur connecté à partir de la session
$connected_user_role = getUserRole($pdo, $_SESSION['pseudo']);
$current_user_id = $user_id; // L'ID de l'utilisateur actuellement connecté

// Vérification de la méthode de la requête (POST) pour la modification de rôle
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Seuls les administrateurs peuvent modifier les rôles
    if ($connected_user_role === 'Administrateur') {
        $user_id = $_POST['user_id']; // ID de l'utilisateur dont on veut modifier le rôle
        $new_role = $_POST['role'];   // Nouveau rôle à attribuer à l'utilisateur

        // Préparation et exécution de la requête SQL pour mettre à jour le rôle de l'utilisateur
        $update_query = "UPDATE utilisateur SET role = :role WHERE id = :id";
        $stmt = $pdo->prepare($update_query);
        $stmt->bindParam(':role', $new_role, PDO::PARAM_STR);
        $stmt->bindParam(':id', $user_id, PDO::PARAM_INT);
        $stmt->execute();
    } else {
        // Message d'erreur si un utilisateur non administrateur tente de modifier un rôle
        echo "<p>Seuls les administrateurs peuvent modifier les rôles.</p>";
    }
}

// Récupérer la liste de tous les utilisateurs pour l'affichage
$query = "SELECT * FROM utilisateur"; // Requête SQL pour sélectionner tous les utilisateurs
$stmt = $pdo->prepare($query);       // Préparation de la requête
$stmt->execute();                    // Exécution de la requête

// Récupérer les résultats sous forme de tableau associatif
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Si des utilisateurs sont trouvés, on les affiche un par un
if (count($users) >= 1) :
    // Parcours des utilisateurs dans la base de données
    foreach ($users as $row) {
        $user_id = $row['id'];
        $pseudo = $row['pseudo'];
        $prenom = $row['prenom'];
        $nom = $row['nom'];
        $email = $row['email'];
        $role = $row['role'];
        $photo_profil = getUserProfilePic($pdo, $user_id); // Récupérer la photo de profil de l'utilisateur

        // Ne pas afficher l'utilisateur connecté dans la liste
        if ($user_id == $current_user_id) {
            continue; // Passer à l'utilisateur suivant
        }
?>
        <!-- Affichage du profil utilisateur -->
        <div class="user-profile">
            <!-- Photo de profil avec un fallback si la photo n'existe pas -->
            <img src="<?php echo (!empty($photo_profil) && file_exists($photo_profil)) ? htmlspecialchars($photo_profil) : 'https://api.dicebear.com/6.x/initials/png?seed=' . urlencode($prenom) . '&size=128'; ?>" alt="profile" class="account-profpic">
            <div class="user-info">
                <div class="user-pseudo">
                    <?php echo htmlspecialchars($pseudo); ?>
                    <!-- Afficher le rôle actuel de l'utilisateur à côté de son pseudo -->
                    <span class="user-role">(<?php echo htmlspecialchars($role); ?>)</span>
                </div>
                <div class="user-name"><?php echo htmlspecialchars($prenom) . ' ' . htmlspecialchars($nom); ?></div>
            </div>

            <?php if ($connected_user_role === 'Administrateur') : ?>
                <!-- Formulaire permettant à l'administrateur de modifier le rôle d'un utilisateur -->
                <form method="POST" action="">
                    <input type="hidden" name="user_id" value="<?php echo $user_id; ?>"> <!-- ID de l'utilisateur à modifier -->
                    <label for="role-<?php echo $user_id; ?>">Modifier le rôle :</label>
                    <!-- Sélecteur pour choisir un nouveau rôle -->
                    <select name="role" id="role-<?php echo $user_id; ?>">
                        <option value="Membre" <?php echo ($role == 'Membre') ? 'selected' : ''; ?>>Membre</option>
                        <option value="Administrateur" <?php echo ($role == 'Administrateur') ? 'selected' : ''; ?>>Administrateur</option>
                        <option value="Invite" <?php echo ($role == 'Invite') ? 'selected' : ''; ?>>Invité</option>
                    </select>
                    <button type="submit">Mettre à jour</button>
                </form>

                <!-- Bouton pour supprimer l'utilisateur -->
                <form method="POST" action="../../backend/controller/delete_user.php" onsubmit="return confirm('Voulez-vous vraiment supprimer cet utilisateur ? Cette action est définitive.');">
                    <input type="hidden" name="user_id" value="<?php echo $user_id; ?>"> <!-- ID de l'utilisateur à supprimer -->
                    <button type="submit" style="color: red;">Supprimer utilisateur</button>
                </form>
            <?php endif; ?>

        </div>
<?php
    }
elseif (count($users) == 0) :
    // Si aucun utilisateur n'est trouvé, afficher un message d'erreur
    readfile(__DIR__ . '/../../backend/error/user_not_found.html');
endif;
?>
