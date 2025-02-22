<?php
// -------------------------------------------------------
// Description :
// Ce fichier contient des fonctions permettant d'afficher un formulaire de publication sur un fil d'actualités,
// ainsi que la gestion de la sécurité via le token CSRF. Il permet aussi de contrôler les permissions d'accès
// en fonction du rôle de l'utilisateur (Administrateur, Membre) et de l'état de la validation de l'utilisateur.
// -------------------------------------------------------

// Fonction pour générer un token CSRF pour sécuriser les formulaires
function generateCsrfToken() {
    if (session_status() != PHP_SESSION_ACTIVE) {
        session_start(); // Ensure session is active
    }

    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); // Generate token if not already present
    }
    return $_SESSION['csrf_token'];
}

// Fonction pour afficher le formulaire de publication d'un message sur le fil d'actualités
function feed_formulaire_champ_pour_poster($session) {
    // Vérifie si l'utilisateur est connecté (présence des variables 'pseudo' et 'id' dans la session)
    if (isset($session['pseudo']) && isset($session['id'])) :
        // Génère un token CSRF pour le formulaire
        $csrf_token = generateCsrfToken(); ?>
        
        <div class="feed-posting-box">
            <!-- Formulaire de publication d'un message -->
            <form id="postForm" onsubmit="confirmPost(event)" action="../controller/post.php?redirect=../view/feed.php" method="post" enctype="multipart/form-data">
                
                <!-- Token CSRF pour sécuriser la requête -->
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">

                <!-- Zone de texte pour rédiger le message -->
                <textarea name="post" id="post" wrap="hard" placeholder="What's on your mind? <?php echo htmlspecialchars($session['pseudo']); ?>" class="feed-post-box-textarea"></textarea>
                
                <!-- Champs cachés pour transmettre les informations utilisateur -->
                <input type="hidden" name="user_id" id="user_id" value="<?php echo htmlspecialchars($session['id']); ?>">
                <input type="hidden" name="pseudo" id="pseudo" value="<?php echo htmlspecialchars($session['pseudo']); ?>">
                <input type="hidden" name="redirect" id="redirect" value="feed.php">
                
                <!-- Champ de sélection de fichier pour ajouter une image au message -->
                <input type="file" name="postimage" accept=".jpg, .png, .jpeg, .webp" id="postimage">
                <label for="postimage" class="postimage-custom">Choisir un fichier</label> <!-- CSS personnalisé -->

                <!-- Section de visibilité publique (activation/désactivation) -->
                <div class="public-toggle">
                    <label for="public">Public:</label>
                    <input type="checkbox" name="public" id="public" value="0">
                    <span class="toggle"></span>
                    
                    <!-- Bouton pour soumettre le formulaire -->
                    <button type="submit" class="post-btn">Publier</button>
                </div>
            </form>
        </div>
    <?php endif;
}

// Fonction pour afficher le formulaire de publication spécifique à un compte utilisateur
function account_feed_formulaire_champ_pour_poster($connection, $session, $prenom, $pseudo, $user_id) {
    // Vérifie le rôle de l'utilisateur à partir de la base de données
    $role = getUserRole($connection, $session['pseudo'] ?? '');

    // Vérifie si l'utilisateur est connecté et s'il correspond au compte
    if ((isset($session['pseudo']) && $session['pseudo'] == $pseudo || 
         isset($session['pseudo']) && $session['pseudo'] !== $pseudo) && 
        ($role === 'Administrateur' || $role === 'Membre')) :
        
        // Génère un token CSRF
        $csrf_token = generateCsrfToken(); ?>
        
        <div class="feed-post-box">
            <!-- Formulaire pour publier un message spécifique à l'utilisateur -->
            <form id="postForm" onsubmit="confirmPost(event)" action="<?php echo __DIR__ . '/../controller/post.php'; ?>"
                  method="post" enctype="multipart/form-data">
                
                <!-- Token CSRF pour sécuriser la requête -->
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">

                <!-- Zone de texte pour rédiger le message -->
                <textarea name="post" id="post" wrap="hard" placeholder="Ecrit ... <?php echo htmlspecialchars($prenom); ?>" class="feed-post-box-textarea"></textarea>
                
                <!-- Champs cachés pour transmettre les informations utilisateur -->
                <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($user_id); ?>">
                <input type="hidden" name="pseudo" value="<?php echo htmlspecialchars($pseudo); ?>">
                
                <!-- Champ de sélection de fichier pour ajouter une image au message -->
                <input type="file" name="postimage" accept=".jpg, .png, .jpeg, .webp" class="postimage">
                
                <!-- Bouton pour soumettre le formulaire -->
                <button type="submit" class="post-btn" style="cursor: pointer;">Publier</button>
            </form>
        </div>
    <?php else : ?>
        <p style="color: red; font-weight: bolder; font-size: 24px;">/!\ En attente de validation /!\</p>
    <?php endif;
}
?>
