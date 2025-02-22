<?php
// Fichier : recuperation_info_utilisateur.php
// Description : Ce fichier récupère les informations d'un utilisateur (pseudo, prénom, nom, etc.)
// Si l'utilisateur n'est pas trouvé, un message d'erreur est affiché.

// Récupération du pseudo depuis l'URL ou la session (si non spécifié dans l'URL)
$pseudo = isset($_GET['pseudo']) ? $_GET['pseudo'] : $_SESSION['pseudo']; // Si pseudo n'est pas dans l'URL, prendre celui de la session

// Récupération de l'onglet sélectionné dans l'URL, avec 'feed' comme valeur par défaut
$tab = isset($_GET['tab']) ? $_GET['tab'] : 'feed'; // Onglet par défaut

// Connexion PDO à la base de données (assurez-vous que $pdo est bien défini dans votre fichier de connexion)
include __DIR__ . '/../db/connection.php';

// Préparer la requête pour récupérer les informations de l'utilisateur depuis la base de données
$query = "SELECT id, pseudo, prenom, nom, email, role, telephone FROM utilisateur WHERE pseudo = :pseudo LIMIT 1";

// Exécuter la requête avec PDO
$stmt = $pdo->prepare($query);
$stmt->bindParam(':pseudo', $pseudo, PDO::PARAM_STR);
$stmt->execute();

// Vérifier si l'utilisateur a
// été trouvé
$user_info = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user_info) {
    // Si l'utilisateur est trouvé, on récupère ses informations dans des variables
    $username_row = $user_info['pseudo'];  // Pseudo de l'utilisateur
    $user_id = $user_info['id'];           // ID de l'utilisateur
    $prenom = $user_info['prenom'];        // Prénom de l'utilisateur
    $nom = $user_info['nom'];              // Nom de l'utilisateur
    $email = $user_info['email'];          // Email de l'utilisateur
    $role = $user_info['role'];            // Rôle de l'utilisateur (ex: Administrateur, Utilisateur)
    $telephone = $user_info['telephone'] ?? ''; // Téléphone de l'utilisateur, s'il existe. Sinon, valeur par défaut vide

    // Récupérer la photo de profil de l'utilisateur (si elle existe)
    $photo_profil = getUserProfilePic($pdo, $user_id);

} else {
    // Si l'utilisateur n'est pas trouvé dans la base de données, on affiche un message d'erreur
    echo "Utilisateur non trouvé.";
    exit();  // Terminer le script pour éviter toute exécution supplémentaire
}
?>
