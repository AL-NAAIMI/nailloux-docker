<?php
// -------------------------------------------------------
// Fichier : update_profile.php
// Description :
// Ce fichier gère la mise à jour des informations personnelles de l'utilisateur (prénom, nom, email, téléphone).
// Lors de la soumission du formulaire, il vérifie la validité des données envoyées, met à jour les informations dans la base de données,
// et redirige l'utilisateur vers la page précédente avec un message de succès ou d'erreur.
// -------------------------------------------------------

// Démarre la session pour accéder aux variables de session
session_start();

// Inclusion du fichier de connexion à la base de données
include __DIR__ . '/../db/connection.php';// Assurez-vous d'inclure la connexion à la BDD

// Vérification si l'utilisateur est bien connecté et que le pseudo est présent dans la requête
if (isset($_POST['pseudo'])) {
    // Récupération du pseudo de l'utilisateur dont les informations doivent être mises à jour
    $pseudo = $_POST['pseudo'];

    // Récupération des nouvelles informations envoyées via le formulaire
    $prenom = $_POST['prenom'];  // Nouveau prénom de l'utilisateur
    $nom = $_POST['nom'];        // Nouveau nom de l'utilisateur
    $email = $_POST['email'];    // Nouvel email de l'utilisateur
    $telephone = $_POST['telephone']; // Nouveau champ pour le téléphone

    // Préparation de la requête SQL pour mettre à jour les informations de l'utilisateur dans la base de données
    $query = "UPDATE utilisateur SET prenom = :prenom, nom = :nom, email = :email, telephone = :telephone WHERE pseudo = :pseudo";

    // Préparer la requête avec PDO
    $stmt = $pdo->prepare($query);

    // Lier les paramètres aux valeurs
    $stmt->bindParam(':prenom', $prenom, PDO::PARAM_STR);
    $stmt->bindParam(':nom', $nom, PDO::PARAM_STR);
    $stmt->bindParam(':email', $email, PDO::PARAM_STR);
    $stmt->bindParam(':telephone', $telephone, PDO::PARAM_STR);
    $stmt->bindParam(':pseudo', $pseudo, PDO::PARAM_STR);

    // Exécuter la requête
    $stmt->execute();

    // Vérification si la mise à jour a bien eu lieu
    if ($stmt->rowCount() > 0) {
        // Redirige vers la page précédente avec un message de succès
        $redirect_url = $_SERVER['HTTP_REFERER'] ?? 'account.php?pseudo=' . $pseudo; // Valeur par défaut si le référent n'est pas disponible
        header('Location: ' . $redirect_url . '&update=success');
    } else {
        // Si aucune ligne n'a été affectée, redirige avec un message d'erreur
        $redirect_url = $_SERVER['HTTP_REFERER'] ?? 'account.php?pseudo=' . $pseudo; // Valeur par défaut si le référent n'est pas disponible
        header('Location: ' . $redirect_url . '&update=error');
    }

    // Fermeture de la requête et de la connexion à la base de données (non nécessaire avec PDO, mais pour la clarté)
    $stmt = null;
    $pdo = null;
} else {
    // Si le pseudo n'est pas trouvé dans la requête, rediriger vers la page d'accueil
    header('Location: index.php');
    exit();
}
?>

