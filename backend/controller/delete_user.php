<?php
require_once '../sql/utilisateur.php'; 
require_once '../db/connection.php';  

// Démarrer la session pour accéder à la variable de session
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['user_id'])) {
        $user_id = intval($_POST['user_id']);
        
        // Appeler la fonction pour supprimer l'utilisateur et ses données
        if (deleteUserAndUserData($pdo, $user_id)) {
            // Récupérer le pseudo depuis la session ou la chaîne de requête
            // Prioriser le pseudo de la session, et utiliser $_GET si disponible
            $pseudo = isset($_SESSION["pseudo"]) ? $_SESSION["pseudo"] : (isset($_GET['pseudo']) ? $_GET['pseudo'] : '');

            // Rediriger avec la valeur du pseudo
            echo "<script type='text/javascript'>
                    alert('Suppression réussie !');
                    window.location.href = '../../frontend/view/account.php?pseudo={$pseudo}&tab=administration';
                  </script>";
            exit();
        } else {
            // Définir un drapeau d'erreur
            echo "<script type='text/javascript'>
                    alert('Erreur lors de la suppression de l\'utilisateur !');
                    window.location.href = '../../frontend/view/account.php?pseudo={$pseudo}&tab=administration';
                  </script>";
            exit();
        }
    }
}
?>
