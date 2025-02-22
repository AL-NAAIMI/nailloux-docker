<?php
// Inclure le fichier pour charger les données utilisateur
include('../../backend/controller/account_linking_utilisateur.php');

// Vérifiez si le pseudo est passé dans l'URL
if (isset($_GET['pseudo'])) {
    // Récupérer le pseudo de l'URL
    $pseudo = $_GET['pseudo'];
    
    // Récupérer les informations de l'utilisateur par pseudo depuis la base de données avec PDO
    $stmt = $pdo->prepare("SELECT id, prenom, nom, email, telephone FROM utilisateur WHERE pseudo = :pseudo LIMIT 1");
    $stmt->bindParam(':pseudo', $pseudo, PDO::PARAM_STR);
    $stmt->execute();
    
    // Si l'utilisateur existe, récupérer ses informations
    $userInfo = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($userInfo) {
        $user_id = $userInfo['id'];
        $prenom = $userInfo['prenom'];
        $nom = $userInfo['nom'];
        $email = $userInfo['email'];
        $telephone = $userInfo['telephone'] ?? ''; // Récupérer le téléphone si disponible, sinon une chaîne vide
    } else {
        // Si l'utilisateur n'existe pas, rediriger vers la page d'accueil
        header('Location: index.php');
        exit(); // Arrêter l'exécution du script pour éviter d'afficher des erreurs
    }
} else {
    // Si aucun pseudo n'est fourni dans l'URL, rediriger vers la page d'accueil
    header('Location: index.php');
    exit();
}
?>

<!-- Inclure SweetAlert pour la gestion des alertes et des confirmations -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>

<!-- Section de présentation des informations de l'utilisateur -->
<div class="acc-info">
    <p>En savoir plus sur <?php echo htmlspecialchars($prenom); ?>...</p>

    <div class="acc-info-content">
        <h3 class="acc-info-content-head">Informations</h3>
        
        <!-- Formulaire pour mettre à jour les informations de l'utilisateur -->
         <div class="acc-info-form">
            <form id="updateForm" action="../../backend/controller/update_account.php" method="POST">
                <!-- Champ caché pour envoyer le pseudo de l'utilisateur au formulaire -->
                <input type="hidden" name="pseudo" value="<?php echo htmlspecialchars($pseudo); ?>">
                
                <!-- Liste des informations à modifier -->
                <ul class="acc-info-content-lst">
                    
                    <!-- Champ pour modifier le prénom -->
                    <li class="acc-info-content-item">
                        <p>Prénom :</p>
                        <input id="prenom" style="min-width: 250px" type="text" name="prenom" value="<?php echo htmlspecialchars($prenom); ?>" required>
                    </li>
                    
                    <!-- Champ pour modifier le nom -->
                    <li class="acc-info-content-item">
                        <p>Nom :</p>
                        <input id="nom" style="min-width: 250px" type="text" name="nom" value="<?php echo htmlspecialchars($nom); ?>" required>
                    </li>
                    
                    <!-- Champ pour afficher et désactiver le pseudo (non modifiable) -->
                    <li class="acc-info-content-item">
                        <p>Pseudo :</p>
                        <input style="min-width: 250px" type="text" name="pseudo" value="<?php echo htmlspecialchars($pseudo); ?>" disabled>
                    </li>
                    
                    <!-- Champ pour modifier l'email -->
                    <li class="acc-info-content-item">
                        <p>Email :</p>
                        <input id="email" style="min-width: 250px" type="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
                    </li>
                    
                    <!-- Champ pour modifier le téléphone (facultatif) -->
                    <li class="acc-info-content-item">
                        <p>Téléphone :</p>
                        <input id="telephone" style="min-width: 250px" type="tel" name="telephone" value="<?php echo htmlspecialchars($telephone); ?>" placeholder="Optionnel">
                    </li>
                </ul>
                
                <!-- Bouton pour confirmer la mise à jour des informations -->
                <button type="button" class="update-btn" style="cursor: pointer;" onclick="confirmUpdate()">Mettre à jour les informations</button>
            </form>
         </div>
    </div>
</div>
