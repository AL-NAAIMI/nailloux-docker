<?php
// Fichier : env.php
// Description : Ce fichier contient les variables d'environnement utilisées dans le projet.

// Détermine dynamiquement le protocole (http ou https)
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' 
             || $_SERVER['SERVER_PORT'] == 443) ? "https" : "http";

// Construit l'URL de base à partir de l'hôte actuel
$base_url = $protocol . "://" . $_SERVER['HTTP_HOST'];

// Définition des variables d'environnement dynamiques
$home_page = $base_url . "/frontend/view/about-us.php";
$page_accueil = $base_url . "/frontend/view/pagep.php";
?>
