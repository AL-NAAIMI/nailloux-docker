<?php
// Fichier : ajouter_commentaire.php
// Description : Ce fichier gère l'ajout de commentaires à un post en utilisant PDO pour les interactions avec la base de données.

// Inclusion du fichier de connexion à la base de données
include __DIR__ . '/../db/connection.php';

// Vérification de la méthode de requête, s'il s'agit d'une soumission en POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Récupération des données envoyées par le formulaire
        $userId = $_POST['user_id'];       // ID de l'utilisateur (UID)
        $postId = $_POST['post_id'];       // ID du post auquel le commentaire est lié
        $commentText = $_POST['comment']; // Texte du commentaire
        $dateTime = date('Y-m-d H:i:s');   // Date et heure actuelles, au format 'YYYY-MM-DD HH:MM:SS'

        // Requête d'insertion dans la base de données
        $insertSql = "INSERT INTO commentaire_p (texte, date_heure, uid, pid) VALUES (:texte, :date_heure, :uid, :pid)";

        // Préparation de la requête avec PDO
        $stmt = $pdo->prepare($insertSql);

        // Exécution de la requête avec des paramètres sécurisés
        $stmt->execute([
            ':texte' => $commentText,
            ':date_heure' => $dateTime,
            ':uid' => $userId,
            ':pid' => $postId
        ]);

        // Si l'insertion est réussie, rediriger vers la page du fil d'actualités
        header("Location: /frontend/view/feed.php");
        exit();
    } catch (PDOException $e) {
        // Gestion des erreurs et affichage d'un message
        die("Erreur lors de l'ajout du commentaire : " . $e->getMessage());
    }
}
?>
