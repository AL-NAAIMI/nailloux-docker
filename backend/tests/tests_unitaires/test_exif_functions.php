<?php
// tests/test_exif_functions.php

include __DIR__ . '/../../db/connection.php';
include __DIR__ . '/../../controller/exif_functions.php';

/**
 * Nettoyage des données avant et après les tests
 */
function cleanupExifTests($pdo) {
    $pdo->exec("DELETE FROM `publication` WHERE `msg` LIKE 'Test EXIF%'");
    echo "Cleanup Completed: Toutes les publications de test liées à EXIF ont été supprimées.\n";
}

/**
 * Test contre les injections de chemin
 */
function testPathInjection() {
    echo "\n--- Test contre les injections de chemin ---\n";

    $malicious_path = '../../etc/passwd';
    $post_id = 1;
    $result = collecterExif($malicious_path, $post_id);

    if (isset($result['error'])) {
        echo "Test Passed: L'injection de chemin a été bloquée.\n";
    } else {
        echo "Test Failed: L'injection de chemin a réussi !\n";
    }
}

/**
 * Test d'injection JSON dans les métadonnées
 */
function testJSONInjection($pdo) {
    echo "\n--- Test d'injection JSON dans les métadonnées ---\n";

    $malicious_metadata = '{"EXIF Data":"<script>alert(\'XSS\');</script>"}';
    $post_id = 1;

    $sql = "UPDATE `publication` 
            SET `donnees_exif` = :donnees_exif 
            WHERE `pid` = :pid";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':donnees_exif' => $malicious_metadata,
        ':pid' => $post_id,
    ]);

    $sql = "SELECT `donnees_exif` FROM `publication` WHERE `pid` = :pid";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':pid' => $post_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result !== false && isset($result['donnees_exif']) && strpos($result['donnees_exif'], '<script>') !== false) {
        echo "Test Failed: L'injection JSON a fonctionné !\n";
    } else {
        echo "Test Passed: L'injection JSON a été bloquée.\n";
    }
}

/**
 * Test d'injection de commande via shell_exec
 */
function testShellExecInjection($pdo) {
    echo "\n--- Test d'injection de commande via shell_exec ---\n";

    $malicious_command = "'; rm -rf /; '"; // Commande malveillante
    $post_id = 1;
    $result = collecterExif($malicious_command, $post_id);

    if (isset($result['error'])) {
        echo "Test Passed: L'injection de commande a été bloquée.\n";
    } else {
        echo "Test Failed: L'injection de commande a réussi !\n";
    }
}

/**
 * Test pour vérifier les caractères dangereux dans les chemins de fichier
 */
function testInvalidCharactersInPath() {
    echo "\n--- Test pour les caractères invalides dans les chemins de fichier ---\n";

    $invalid_path = "/var/www/uploads/; rm -rf /";
    $post_id = 1;
    $result = collecterExif($invalid_path, $post_id);

    if (isset($result['error'])) {
        echo "Test Passed: Les caractères invalides dans le chemin ont été bloqués.\n";
    } else {
        echo "Test Failed: Les caractères invalides dans le chemin ont réussi à passer !\n";
    }
}

/**
 * Test pour la restriction des commandes exécutées via shell_exec
 */
function testRestrictedShellCommands() {
    echo "\n--- Test pour la restriction des commandes shell_exec ---\n";

    $allowed_command = "ls -l";
    $disallowed_command = "cat /etc/passwd";

    $result_allowed = shell_exec_restricted($allowed_command);
    $result_disallowed = shell_exec_restricted($disallowed_command);

    if ($result_allowed && strpos($result_allowed, 'total') !== false) {
        echo "Test Passed: La commande autorisée s'est exécutée correctement.\n";
    } else {
        echo "Test Failed: La commande autorisée n'a pas fonctionné comme prévu.\n";
    }

    if ($result_disallowed === null) {
        echo "Test Passed: La commande non autorisée a été bloquée.\n";
    } else {
        echo "Test Failed: La commande non autorisée a été exécutée !\n";
    }
}

/**
 * Fonction sécurisée pour exécuter des commandes shell avec restrictions
 *
 * @param string $command
 * @return string|null
 */
function shell_exec_restricted($command) {
    $allowed_commands = ['ls', 'exiftool', 'file']; // Liste blanche des commandes
    $command_name = strtok($command, " ");

    if (!in_array($command_name, $allowed_commands)) {
        return null; // Bloquer les commandes non autorisées
    }

    return shell_exec($command);
}

/**
 * Démarrage des tests
 */
echo "\n--- DÉMARRAGE DES TESTS EXIF ---\n";

// Nettoyage avant les tests
cleanupExifTests($pdo);

// Test contre les injections de chemin
testPathInjection();

// Test d'injection JSON dans les métadonnées
testJSONInjection($pdo);

// Test d'injection de commande via shell_exec
testShellExecInjection($pdo);

// Test pour les caractères dangereux dans les chemins de fichier
testInvalidCharactersInPath();

// Test pour la restriction des commandes exécutées via shell_exec
testRestrictedShellCommands();

// Nettoyage après les tests
cleanupExifTests($pdo);

echo "\nFin des tests EXIF.\n";
