<?php
// Inclusion des fichiers nécessaires
include __DIR__ . '/../db/connection.php'; // Connexion à la base de données
include __DIR__ . '/../../back/env.php';     // Fichier d'environnement

// Démarrage de la session si nécessaire
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Récupération et sécurisation des données POST
$pseudo = isset($_POST['pseudo']) ? strtolower(trim(htmlspecialchars($_POST['pseudo']))) : null;
$password = isset($_POST['password']) ? trim(htmlspecialchars($_POST['password'])) : null;

// Si l'utilisateur s'inscrit, récupération des autres données nécessaires
if (isset($_POST['regst'])) {
    $prenom = isset($_POST['prenom']) ? htmlspecialchars(trim($_POST['prenom'])) : null;
    $nom = isset($_POST['nom']) ? htmlspecialchars(trim($_POST['nom'])) : null;
    $email = isset($_POST['email']) ? htmlspecialchars(trim($_POST['email'])) : null;

    // Vérification des champs nécessaires pour l'inscription
    if (!$pseudo || !$password || !$prenom || !$nom || !$email) {
        echo "Erreur : Tous les champs doivent être remplis.";
        exit();
    }
}

// Variable pour l'état de la connexion
$login = 0;

// Fonction de validation pour la connexion
function validate($pseudo, $password, $pdo) {
    // Requête préparée pour éviter les injections SQL
    $sql = "SELECT `id`, `pseudo`, `password` FROM `utilisateur` WHERE `pseudo` = :pseudo";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':pseudo', $pseudo, PDO::PARAM_STR);
    $stmt->execute();

    // Vérifie si l'utilisateur existe
    if ($stmt->rowCount() === 1) {
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        // Vérifie si le mot de passe est correct
        if (password_verify($password, $row['password'])) {
            $_SESSION['pseudo'] = $row['pseudo'];
            $_SESSION['id'] = $row['id'];
            return true;
        }
    }

    return false;
}

// Fonction d'inscription d'un utilisateur
function register_user($pseudo, $password, $email, $prenom, $nom, $pdo) {
    // Validation du format de l'email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return "Erreur : Format d'email invalide.";
    }
    // Vérification des champs vides
    if (empty($pseudo) || empty($password) || empty($email) || empty($prenom) || empty($nom)) {
        return "Erreur : Tous les champs doivent être remplis.";
    }
    // Validation du mot de passe
    $password_pattern = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/';
    if (!preg_match($password_pattern, $password)) {
        return "Erreur : Le mot de passe doit contenir au moins 8 caractères, une majuscule, un chiffre et un caractère spécial.";
    }

    // Validation du pseudo
    if (!preg_match('/^[a-zA-Z0-9_]{3,20}$/', $pseudo)) {
        return "Erreur : Le pseudo contient des caractères invalides ou est trop court.";
    }

    // Vérification si le pseudo existe déjà
    $sql = "SELECT `pseudo` FROM `utilisateur` WHERE `pseudo` = :pseudo";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':pseudo', $pseudo, PDO::PARAM_STR);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        return "Erreur : Le pseudo est déjà pris.";
    }

    // Hachage du mot de passe
    $hashed_pswd = password_hash($password, PASSWORD_BCRYPT);
    $default_photo = '';  // Définit une valeur par défaut pour photo_profil

    // Insertion de l'utilisateur
    $sql = "INSERT INTO `utilisateur` (`pseudo`, `prenom`, `nom`, `email`, `password`, `photo_profil`) 
            VALUES (:pseudo, :prenom, :nom, :email, :password, :photo_profil)";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':pseudo', $pseudo, PDO::PARAM_STR);
    $stmt->bindParam(':prenom', $prenom, PDO::PARAM_STR);
    $stmt->bindParam(':nom', $nom, PDO::PARAM_STR);
    $stmt->bindParam(':email', $email, PDO::PARAM_STR);
    $stmt->bindParam(':password', $hashed_pswd, PDO::PARAM_STR);
    $stmt->bindParam(':photo_profil', $default_photo, PDO::PARAM_STR);

    if ($stmt->execute()) {
        return "Inscription réussie.";
    }

    return "Erreur : Inscription échouée.";
}

// Traitement des requêtes (connexion ou inscription)
if (isset($_POST['lgn']) && $pseudo && $password) {
    // Connexion
    if (validate($pseudo, $password, $pdo) === true) {
        // Redirection après connexion réussie
        echo '<script>';
        echo 'window.location.href = "/frontend/view/account.php?pseudo=' . urlencode($pseudo) . '"';
        echo '</script>';
    } else {
        // Message d'erreur si la connexion échoue
        alert_message("Nom d'utilisateur ou mot de passe incorrect.", '/frontend/view/index.php');
    }
} elseif (isset($_POST['regst'])) {
    // Inscription
    $result = register_user($pseudo, $password, $email, $prenom, $nom, $pdo);

    if ($result === "Inscription réussie.") {
        // Auto-login: Set session variables after successful registration
        $_SESSION['pseudo'] = $pseudo;
        
        // Get the user ID from database
        $stmt = $pdo->prepare("SELECT `id` FROM `utilisateur` WHERE `pseudo` = :pseudo");
        $stmt->bindParam(':pseudo', $pseudo, PDO::PARAM_STR);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            $_SESSION['id'] = $user['id'];
        }
        
        // Redirection après inscription réussie
        echo '<script>';
        echo 'alert("Inscription réussie !");';
        echo 'window.location.href = "/frontend/view/account.php?pseudo=' . urlencode($pseudo) . '"';
        echo '</script>';
    } else {
        // Affichage des erreurs d'inscription
        alert_message($result, '/frontend/view/index.php');
    }
}

// Fonction pour afficher un message d'alerte et rediriger
function alert_message($message, $location) {
    echo '<script>';
    echo 'alert("' . $message . '");';
    echo 'window.location.href = "' . $location . '";';
    echo '</script>';
}
?>
