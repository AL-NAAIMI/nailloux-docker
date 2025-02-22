<?php

// ********************************************************************
// Ce fichier contient des fonctions permettant d'interagir avec la 
// table des utilisateurs de la base de données. Les fonctions incluent
// la récupération des détails d'un utilisateur, son rôle, son ID,
// ainsi que la possibilité de rechercher des utilisateurs par pseudo,
// prénom ou nom.
// ********************************************************************

/**
 * Récupère les détails d'un utilisateur (pseudo, prénom, photo de profil) à partir de son ID.
 * 
 * @param PDO $pdo - La connexion à la base de données.
 * @param int $userId - L'ID de l'utilisateur dont on veut récupérer les informations.
 * @return array - Un tableau associatif contenant les détails de l'utilisateur.
 */
function fetchUserDetails($pdo, $userId) {
    // Requête SQL pour récupérer les détails d'un utilisateur en fonction de son ID
    $sql = "SELECT `pseudo`, `prenom`, `photo_profil` FROM `utilisateur` WHERE `id` = :userId";
    
    try {
        // Préparation et exécution de la requête
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
        $stmt->execute();
        
        // Retourne les détails de l'utilisateur sous forme de tableau associatif
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        die("Erreur de requête SQL : " . $e->getMessage()); // Affiche l'erreur si la requête échoue
    }
}

/**
 * Récupère le rôle d'un utilisateur à partir de son pseudo.
 * 
 * @param PDO $pdo - La connexion à la base de données.
 * @param string $pseudo - Le pseudo de l'utilisateur dont on veut connaître le rôle.
 * @return string|null - Le rôle de l'utilisateur ou null si non trouvé.
 */
function getUserRole($pdo, $pseudo) {
    // Requête SQL préparée pour récupérer le rôle de l'utilisateur à partir du pseudo
    $sql = "SELECT role FROM utilisateur WHERE pseudo = :pseudo";
    
    try {
        // Préparation de la requête
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':pseudo', $pseudo, PDO::PARAM_STR);
        $stmt->execute();
        
        // Récupère le rôle et le stocke dans la variable $role
        $role = $stmt->fetchColumn();
        
        // Retourne le rôle trouvé ou null si aucune correspondance
        return $role ? $role : null;
    } catch (PDOException $e) {
        die("Erreur de requête SQL : " . $e->getMessage());
    }
}

/**
 * Récupère l'ID d'un utilisateur à partir de son pseudo.
 * 
 * @param PDO $pdo - La connexion à la base de données.
 * @param string $pseudo - Le pseudo de l'utilisateur dont on veut récupérer l'ID.
 * @return int - L'ID de l'utilisateur.
 */
function getUserIdFromPseudo($pdo, $pseudo) {
    // Requête SQL préparée pour récupérer l'ID de l'utilisateur à partir de son pseudo
    $sql = "SELECT id FROM utilisateur WHERE pseudo = :pseudo";
    
    try {
        // Préparation de la requête
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':pseudo', $pseudo, PDO::PARAM_STR);
        $stmt->execute();
        
        // Récupère l'ID et le retourne
        return $stmt->fetchColumn();
    } catch (PDOException $e) {
        die("Erreur de requête SQL : " . $e->getMessage());
    }
}

/**
 * Recherche des utilisateurs en fonction de leur pseudo, prénom ou nom.
 * 
 * @param PDO $pdo - La connexion à la base de données.
 * @param string $pseudo - Le terme de recherche utilisé pour rechercher dans le pseudo, prénom ou nom.
 * @return PDOStatement - Le résultat de la requête de recherche.
 */
function searchUsers($pdo, $pseudo) {
    // Requête SQL préparée pour rechercher les utilisateurs par pseudo, prénom ou nom
    $sql = "SELECT `id`, `pseudo`, `prenom`, `nom`, `email` 
            FROM `utilisateur` 
            WHERE `pseudo` LIKE :searchTerm 
            OR `prenom` LIKE :searchTerm 
            OR `nom` LIKE :searchTerm";
    
    try {
        // Préparation du terme de recherche avec des jokers (%) pour la recherche floue
        $searchTerm = '%' . $pseudo . '%';
        
        // Préparation et exécution de la requête
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':searchTerm', $searchTerm, PDO::PARAM_STR);
        $stmt->execute();
        
        // Retourne le résultat de la requête de recherche
        return $stmt;
    } catch (PDOException $e) {
        die("Erreur de requête SQL : " . $e->getMessage());
    }
}

function fetchDocuments($pdo) {
    // Prepare the SQL query to fetch documents from the database
    $stmt = $pdo->prepare("SELECT * FROM document ORDER BY date_depot DESC");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
/**
 * Récupère la photo de profil d'un utilisateur à partir de son ID.
 * 
 * @param PDO $pdo - La connexion à la base de données.
 * @param int $user_id - L'ID de l'utilisateur dont on veut récupérer la photo de profil.
 * @return string - Le chemin vers la photo de profil de l'utilisateur.
 */
function getUserProfilePic($pdo, $user_id) {
    // Requête SQL préparée pour récupérer la photo de profil de l'utilisateur
    $sql = "SELECT photo_profil FROM utilisateur WHERE id = :user_id";
    
    try {
        // Préparation de la requête
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();
        
        // Récupère la photo de profil et la retourne
        return $stmt->fetchColumn();
    } catch (PDOException $e) {
        die("Erreur de requête SQL : " . $e->getMessage());
    }
}

/**
 * Récupère toutes les informations d'un utilisateur à partir de son pseudo.
 * 
 * @param PDO $pdo - La connexion à la base de données.
 * @param string $pseudo - Le pseudo de l'utilisateur dont on veut récupérer les informations.
 * @return array - Un tableau associatif contenant toutes les informations de l'utilisateur.
 */
function getUserInfo($pdo, $pseudo) {
    // Ensure $pdo is being passed correctly
    $sql = "SELECT `id`, `pseudo`, `prenom`, `nom`, `email`, `telephone`, `role`
            FROM `utilisateur` 
            WHERE `pseudo` = :pseudo";
    
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':pseudo', $pseudo, PDO::PARAM_STR);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC); // Returns the result
    } catch (PDOException $e) {
        die("Erreur de requête SQL : " . $e->getMessage());
    }
}

/**
 * Supprime un utilisateur ainsi que ses publications et commentaires associés.
 * 
 * @param PDO $pdo - La connexion à la base de données.
 * @param int $user_id - L'ID de l'utilisateur à supprimer.
 * @return bool - Retourne true si la suppression est réussie, sinon false.
 */
function deleteUserAndUserData($pdo, $user_id) {
    try {
        // Début d'une transaction
        $pdo->beginTransaction();

        // Suppression des commentaires de l'utilisateur
        $deleteCommentsSql = "DELETE FROM commentaire_p WHERE uid = :user_id";
        $stmt = $pdo->prepare($deleteCommentsSql);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();

        // Suppression des publications de l'utilisateur
        $deletePublicationsSql = "DELETE FROM publication WHERE uid = :user_id";
        $stmt = $pdo->prepare($deletePublicationsSql);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();

        // Suppression de l'utilisateur
        $deleteUserSql = "DELETE FROM utilisateur WHERE id = :user_id";
        $stmt = $pdo->prepare($deleteUserSql);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();

        // Suppression des documents de l'utilisateur
        $deleteDocumentsSql = "DELETE FROM document WHERE uid = :user_id";
        $stmt = $pdo->prepare($deleteDocumentsSql);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();

        // Suppression des photos des evenements de l'utilisateur (pour integrer dans le main)

        // $deletePhotosEvenementsSql = "DELETE FROM photos_evenement WHERE uid = :user_id";
        // $stmt = $pdo->prepare($deletePhotosEvenementsSql);
        // $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        // $stmt->execute();

        // Validation de la transaction
        $pdo->commit();

        return true;
    } catch (PDOException $e) {
        // Annulation de la transaction en cas d'erreur
        $pdo->rollBack();
        die("Erreur lors de la suppression de l'utilisateur et de ses données associées : " . $e->getMessage());
        return false;
    }
}

?>