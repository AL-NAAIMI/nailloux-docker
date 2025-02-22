<?php
// Fichier : logout.php 
// Description : Ce fichier gère la déconnexion de l'utilisateur en supprimant ses données de session
// et en le redirigeant vers la page d'accueil

// Inclusion du fichier de configuration contenant les variables d'environnement (par exemple : URL de la page d'accueil)
include __DIR__ . "/env.php";

//session_start
// Démarrage de la session pour pouvoir manipuler les données de session.
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Vérification si un utilisateur est connecté en vérifiant la présence d'une variable de session 'pseudo'
if (isset($_SESSION['pseudo'])) {
    // Si l'utilisateur est connecté, on supprime toutes les variables de session
    session_unset();
    
    // On détruit la session pour réellement déconnecter l'utilisateur
    session_destroy();

    // Redirection vers la page d'accueil après la déconnexion
    echo '
    <script>
        window.location="'.$page_accueil.'";
    </script>
    ';
    exit; // On termine l'exécution du script après la redirection
} else {
    // Si l'utilisateur n'était pas connecté (pas de session active), on le redirige simplement vers la page d'accueil
    echo '
    <script>
        window.location="'.$home_page.'";
    </script>
    ';
    exit; // On termine l'exécution du script après la redirection
}
?>
