<?php
// Description : Ce fichier inclut différents sous-onglets en fonction de la valeur de la variable $tab.
// Si $tab ne correspond à aucun des onglets définis, un message d'erreur est affiché.

// Vérification de la valeur de la variable $tab pour inclure le bon fichier en fonction du sous-onglet sélectionné
if ($tab == "about-us") {
    // Inclusion du fichier pour la section "À propos"
    include __DIR__ . '/../../frontend/view/about-us/sous_onglet_about-us/about-us.php';
} elseif ($tab == "event") {
    // Inclusion du fichier pour la section "Événements"
    include __DIR__ . '/../../frontend/view/about-us/sous_onglet_about-us/event.php';
} elseif ($tab == "file") {
    // Inclusion du fichier pour la section "Fichiers"
    include __DIR__ . '/../../frontend/view/about-us/sous_onglet_about-us/file.php';
} elseif ($tab == "contact") {
    // Inclusion du fichier pour la section "Contact"
    include __DIR__ . '/../../frontend/view/about-us/sous_onglet_about-us/contact.php'; // Assurez-vous que ce fichier existe
} else {
    // Si la valeur de $tab ne correspond à aucun des cas, affichage d'un message d'erreur
    echo "<p>Page not found</p>";
}
?>
