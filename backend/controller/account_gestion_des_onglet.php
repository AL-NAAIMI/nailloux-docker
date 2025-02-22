<?php
// Fichier : gestion_onglets.php
// Description : Ce fichier inclut différents sous-onglets en fonction de la valeur de la variable $tab.
// Si la valeur de $tab ne correspond à aucun des cas, un message d'erreur "Page not found" est affiché.

// Vérification de la valeur de la variable $tab et inclusion du fichier correspondant
if ($tab == "feed") {
    // Si l'onglet sélectionné est "feed", inclure le fichier correspondant à la section du fil d'actualités
    include __DIR__ . '/../../frontend/view/account/sous_onglet_account/feed.php';
} elseif ($tab == "info") {
    // Si l'onglet sélectionné est "info", on vérifie si l'utilisateur consulte sa propre page ou s'il est administrateur
    if ($pseudo === $_SESSION['pseudo'] || (isset($_SESSION['role']) && $_SESSION['role'] === 'Administrateur')) {
        // Si l'utilisateur consulte sa propre page ou est administrateur, inclure le fichier 'info_modifieur.php'
        include __DIR__ . '/../../frontend/view/account/sous_onglet_account/info_modifieur.php';
    } else {
        // Sinon, inclure le fichier 'info.php' pour afficher les informations de l'utilisateur consulté
        include __DIR__ . '/../../frontend/view/account/sous_onglet_account/info.php';
    }
} elseif ($tab == "photo") {
    // Si l'onglet sélectionné est "photo", inclure le fichier correspondant à la gestion des photos
    include __DIR__ . '/../../frontend/view/account/sous_onglet_account/photo.php';
} elseif ($tab == "administration") {
    // Si l'onglet sélectionné est "administration", inclure le fichier correspondant à la section d'administration
    include __DIR__ . '/../../frontend/view/account/sous_onglet_account/administration.php';
}  elseif ($tab == "contact") {
    // Si l'onglet sélectionné est "administration", inclure le fichier correspondant à la section d'administration
    include __DIR__ . '/../../frontend/view/account/sous_onglet_account/contact.php';
}else {
    // Si la valeur de $tab ne correspond à aucun des cas ci-dessus, afficher un message d'erreur
    echo "<p>Page not found</p>";
}
?>
