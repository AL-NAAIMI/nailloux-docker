<?php
// -------------------------------------------------------
// run-all-tests.php
// Description : Lance chaque script de test dans un processus séparé,
//               évitant la redéclaration de fonction et le conflit de sessions.
// -------------------------------------------------------

// Affichage d'un en-tête
echo "=========================================\n";
echo "=== LANCEMENT DE TOUS LES TESTS EN UNE FOIS ===\n";
echo "=========================================\n\n";

// 1) Test Admin Functions
echo "======== Running test_admin_functions.php ========\n";
$output = shell_exec("php " . escapeshellarg(__DIR__ . "/test_admin_functions.php") . " 2>&1");
echo $output . "\n";

// 2) Test Commentaire
echo "======== Running test_commentaire.php ========\n";
$output = shell_exec("php " . escapeshellarg(__DIR__ . "/test_commentaire.php") . " 2>&1");
echo $output . "\n";

// 3) Test Inscription Utilisateur
echo "======== Running test_inscription_utilisateur.php ========\n";
$output = shell_exec("php " . escapeshellarg(__DIR__ . "/test_inscription_utilisateur.php") . " 2>&1");
echo $output . "\n";

// 4) Test Publication
echo "======== Running test_publication.php ========\n";
$output = shell_exec("php " . escapeshellarg(__DIR__ . "/test_publication.php") . " 2>&1");
echo $output . "\n";

// 5) Test Mots Clés
echo "======== Running test_mots_cle.php ========\n";
$output = shell_exec("php " . escapeshellarg(__DIR__ . "/test_mots_cle.php") . " 2>&1");
echo $output . "\n";

// 6) Test EXIF Functions
echo "======== Running test_exif_functions.php ========\n";
$output = shell_exec("php " . escapeshellarg(__DIR__ . "/test_exif_functions.php") . " 2>&1");
echo $output . "\n";

// 7) Test Recherche Utilisateur
echo "======== Running test_recherche_utilisateur.php ========\n";
$output = shell_exec("php " . escapeshellarg(__DIR__ . "/test_recherche_utilisateur.php") . " 2>&1");
echo $output . "\n";

// 8) Test Events
echo "======== Running test_events.php ========\n";
$output = shell_exec("php " . escapeshellarg(__DIR__ . "/test_events.php") . " 2>&1");
echo $output . "\n";

// Conclusion
echo "=========================================\n";
echo "=== TOUS LES TESTS ONT ÉTÉ EXÉCUTÉS  ===\n";
echo "=========================================\n";
?>
