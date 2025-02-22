<?php

// ********************************************************************
// Ce fichier contient des fonctions pour interagir avec la base de données
// afin de récupérer les publications et les commentaires associés.
// Les fonctions utilisent PDO pour interroger la base de données.
// ********************************************************************

/**
 * Récupère toutes les publications depuis la base de données, triées par date de publication.
 * 
 * @param PDO $pdo - La connexion à la base de données.
 * @return array - Un tableau associatif contenant les publications.
 */
function fetchPosts($pdo) {
    // Requête SQL pour récupérer les publications
    $postsql = "SELECT `pid`, `msg`, `image`, `uid`, `dop` FROM `publication` ORDER BY `dop` DESC;";
    
    try {
        // Exécution de la requête
        $stmt = $pdo->query($postsql);

        // Récupère toutes les publications sous forme de tableau associatif
        $postrows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return $postrows; // Retourne le tableau des publications
    } catch (PDOException $e) {
        die("Erreur de requête SQL : " . $e->getMessage()); // Affiche l'erreur si la requête échoue
    }
}

/**
 * Récupère les commentaires associés à une publication donnée.
 * 
 * @param PDO $pdo - La connexion à la base de données.
 * @param int $postId - L'ID de la publication pour laquelle on veut récupérer les commentaires.
 * @return array - Un tableau associatif contenant les commentaires de la publication.
 */
function fetchComments($pdo, $postId) {
    // Requête SQL préparée pour récupérer les commentaires pour une publication spécifique
    $commentSql = "SELECT `id_commentaire_p`, `texte`, `date_heure`, `uid` FROM `commentaire_p` WHERE `pid` = :postId ORDER BY `date_heure` DESC;";
    
    try {
        // Préparation de la requête
        $stmt = $pdo->prepare($commentSql);
        // Lier l'ID de la publication au paramètre de la requête
        $stmt->bindParam(':postId', $postId, PDO::PARAM_INT);
        // Exécution de la requête
        $stmt->execute();
        
        // Récupérer les résultats sous forme de tableau associatif
        $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return $comments; // Retourne le tableau des commentaires associés à la publication
    } catch (PDOException $e) {
        die("Erreur de requête SQL : " . $e->getMessage()); // Affiche l'erreur si la requête échoue
    }
}
?>
