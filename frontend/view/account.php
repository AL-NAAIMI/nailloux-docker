<?php
session_start();
include __DIR__ . '/../../back/env.php';
include __DIR__ . '/../../backend/db/connection.php';
include __DIR__ . '/../../backend/sql/publication.php';
include __DIR__ . '/../../backend/sql/utilisateur.php';

// Vérification de la connexion de l'utilisateur
if (!isset($_SESSION["pseudo"])) {
    header("Location: index.php"); // Redirigez vers la page de connexion si non connecté
    exit();
}

// Utiliser le pseudo de la session si aucun pseudo n'est passé dans l'URL
$pseudo = isset($_GET["pseudo"]) ? $_GET["pseudo"] : $_SESSION["pseudo"];

// Recherche d'utilisateurs si un terme de recherche est défini
$result = [];
if (isset($_GET["search"]) && !empty(trim($_GET["search"]))) {
    $pseudo = '%' . strtolower(trim($_GET["search"])) . '%';
    
    // Remplacer la fonction searchUsers avec PDO
    $stmt = $pdo->prepare("SELECT * FROM utilisateur 
                           WHERE LOWER(pseudo) LIKE :search1
                              OR LOWER(prenom) LIKE :search2
                              OR LOWER(nom) LIKE :search3
                           LIMIT 10");
    $stmt->bindParam(":search1", $pseudo, PDO::PARAM_STR);
    $stmt->bindParam(":search2", $pseudo, PDO::PARAM_STR);
    $stmt->bindParam(":search3", $pseudo, PDO::PARAM_STR);
    $stmt->execute();
    
    // Récupérer les résultats
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    // Si aucun terme de recherche, on récupère tous les utilisateurs (limités à 10)
    $stmt = $pdo->query("SELECT * FROM utilisateur LIMIT 10");
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<html>
<title>Nailloux</title>

<!-- header -->
<?php include "header.php"; ?>

<!-- rajoute un espace entre le header et la banniere -->
<div class="seperate_header"></div>

<!-- partie de recherche d'un utilisateur -->
<?php if (isset($_GET["search"])): ?>
    <?php include "account/account_search.php"; ?>
<?php else: ?>
    <!-- partie compte d'un utilisateur -->
    <?php include "account/account.php"; ?>
<?php endif; ?>

<!-- footer -->
<?php include "footer.php"; ?>

</body>
</html>
