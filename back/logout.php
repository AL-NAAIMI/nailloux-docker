<?php
// Fichier : logout.php 
// Description : Ce fichier gère la déconnexion de l'utilisateur en supprimant ses données de session
// et en le redirigeant vers la page d'accueil.

// Inclusion du fichier d'environnement
include __DIR__ . "/env.php";

// Démarrage de la session si nécessaire
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Si l'utilisateur est connecté, on supprime toutes les variables de session et on détruit la session
if (isset($_SESSION['pseudo'])) {
    session_unset();
    session_destroy();
    // Redirection vers page_accueil après la déconnexion
    echo '<script>window.location.href = "' . $page_accueil . '";</script>';
    exit;
} else {
    // Si l'utilisateur n'était pas connecté, redirige vers home_page
    echo '<script>window.location.href = "' . $home_page . '";</script>';
    exit;
}
?>
