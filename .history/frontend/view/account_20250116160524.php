<?php
session_start();
include __DIR__ . '/../../back/env.php';
include __DIR__ . '/../../backend/db/connection.php';
include __DIR__ . '/../../backend/sql/utilisateur.php';

// Vérification de la connexion de l'utilisateur
if (!isset($_SESSION["pseudo"])) {
    header("Location: index.php"); // Redirigez vers la page de connexion si non connecté
    exit();
}

// Utiliser le pseudo de l'URL si défini, sinon celui de la session
$pseudo = isset($_GET["pseudo"]) ? $_GET["pseudo"] : $_SESSION["pseudo"];

// Récupérer les informations de l'utilisateur
$userData = getUserInfo($pdo, $pseudo);

// Vérifier si l'utilisateur existe
if (!$userData) {
    echo "Utilisateur introuvable.";
    exit();
}
?>

<html>
<title>Nailloux</title>

<!-- header -->
<?php include "header.php"; ?>

<!-- rajoute un espace entre le header et la banniere -->
<div class="seperate_header"></div>

<!-- Afficher les informations de l'utilisateur -->
<div class="user-banner">
    <h1>Profil de <?= htmlspecialchars($userData['pseudo']) ?> (@<?= htmlspecialchars($userData['email']) ?>)</h1>
</div>

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
