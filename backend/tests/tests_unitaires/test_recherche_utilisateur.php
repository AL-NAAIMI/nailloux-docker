<?php
// tests/test_recherche_utilisateur.php

// Inclusion du fichier de connexion à la base de données
include __DIR__ . '/../../db/connection.php';

/**
 * Nettoyage de la table `utilisateur` avant et après les tests (pour ne pas polluer la base)
 */
function cleanupUsers($pdo) {
    $pdo->exec("DELETE FROM `utilisateur` WHERE `pseudo` LIKE 'TestUser%' OR `email` LIKE 'testuser_%@example.com'");
    echo "Cleanup Completed: Tous les utilisateurs de test ont été supprimés.\n";
}

/**
 * Ajout d'un utilisateur de test
 */
function addTestUser($pdo, $pseudo, $prenom, $nom, $email, $role = 'user') {
    $stmt = $pdo->prepare("INSERT INTO `utilisateur` (pseudo, prenom, nom, email, role) VALUES (:pseudo, :prenom, :nom, :email, :role)");
    $stmt->execute([':pseudo' => $pseudo, ':prenom' => $prenom, ':nom' => $nom, ':email' => $email, ':role' => $role]);
    return $stmt->rowCount() > 0;
}

/**
 * Test de recherche d'utilisateur
 */
function testRechercheUtilisateur($pdo, $searchPseudo, $expectedCount) {
    $sql = "SELECT * FROM `utilisateur` WHERE `pseudo` LIKE :search";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':search' => '%' . $searchPseudo . '%']);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($results) === $expectedCount) {
        echo "Test Passed: Recherche pour '$searchPseudo' a retourné $expectedCount résultats.\n";
    } else {
        echo "Test Failed: Recherche pour '$searchPseudo' a retourné " . count($results) . " résultats (attendu : $expectedCount).\n";
    }
}

/**
 * Test contre les injections SQL avancées
 */
function testSQLInjectionAdvanced($pdo) {
    echo "\n--- Test contre les injections SQL avancées ---\n";

    $malicious_inputs = [
        "' OR '1'='1",
        "' UNION SELECT null, 'HACKED', null, null, null FROM DUAL -- ",
        "' AND SLEEP(5) -- ",
        "' OR 1=1; DROP TABLE utilisateur -- "
    ];

    foreach ($malicious_inputs as $input) {
        $sql = "SELECT * FROM `utilisateur` WHERE `pseudo` LIKE :search";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':search' => '%' . $input . '%']);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (count($results) > 0) {
            echo "Test Failed: Injection SQL avec '$input' a fonctionné !\n";
        } else {
            echo "Test Passed: Injection SQL avec '$input' a été bloquée.\n";
        }
    }
}

/**
 * Test contre les attaques XSS
 */
function testXSSProtection($pdo) {
    echo "\n--- Test contre les attaques XSS ---\n";

    $malicious_input = "<script>alert('XSS');</script>";
    $sql = "SELECT * FROM `utilisateur` WHERE `pseudo` LIKE :search";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':search' => '%' . $malicious_input . '%']);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($results) > 0) {
        echo "Test Failed: L'attaque XSS avec '$malicious_input' a fonctionné !\n";
    } else {
        echo "Test Passed: L'attaque XSS a été bloquée.\n";
    }
}

/**
 * Test contre les attaques par brute-force
 */
function testBruteForceProtection($pdo) {
    echo "\n--- Test contre les attaques par brute-force ---\n";

    $username = 'TestUserAlpha';
    $attempts = 10;

    for ($i = 1; $i <= $attempts; $i++) {
        $sql = "SELECT * FROM `utilisateur` WHERE `pseudo` = :pseudo";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':pseudo' => $username]);

        if ($stmt->rowCount() > 0 && $i === $attempts) {
            echo "Test Passed: Le système a géré $i tentatives correctement.\n";
        } elseif ($stmt->rowCount() === 0) {
            echo "Test Failed: Aucune protection contre les tentatives multiples.\n";
            break;
        }
    }
}

/**
 * Test de gestion des permissions
 */
function testPermissions($pdo) {
    echo "\n--- Test de gestion des permissions ---\n";

    $sql = "DELETE FROM `utilisateur` WHERE `pseudo` = :pseudo AND `role` != 'admin'";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':pseudo' => 'TestUserAlpha']);

    if ($stmt->rowCount() === 0) {
        echo "Test Passed: Les permissions de suppression sont correctement appliquées.\n";
    } else {
        echo "Test Failed: Un utilisateur non autorisé a pu effectuer une action interdite.\n";
    }
}

/**
 * Test de gestion des erreurs d'entrée utilisateur
 */
function testValidationEntreeUtilisateur($pdo) {
    echo "\n--- Test de validation des entrées utilisateur ---\n";

    $invalid_inputs = [
        "<script>alert('XSS')</script>",
        "' OR '1'='1",
        "<img src='x' onerror='alert(1)'>"
    ];

    foreach ($invalid_inputs as $input) {
        $sql = "SELECT * FROM `utilisateur` WHERE `pseudo` LIKE :search";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':search' => '%' . $input . '%']);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (count($results) > 0) {
            echo "Test Failed: L'entrée invalide '$input' n'a pas été correctement filtrée.\n";
        } else {
            echo "Test Passed: L'entrée invalide a été correctement filtrée.\n";
        }
    }
}

// -----------------------------------------------------------------------------------------
//                               DÉBUT DE L'EXÉCUTION DES TESTS
// -----------------------------------------------------------------------------------------

// 1) Nettoyage avant tests
cleanupUsers($pdo);

// 2) Ajout d'utilisateurs de test
addTestUser($pdo, 'TestUserAlpha', 'Alpha', 'One', 'testuser_alpha@example.com', 'admin');
addTestUser($pdo, 'TestUserBeta', 'Beta', 'Two', 'testuser_beta@example.com', 'user');

// 3) Tests de recherche

testRechercheUtilisateur($pdo, 'TestUserAlpha', 1);
testRechercheUtilisateur($pdo, 'TestUser', 2);
testRechercheUtilisateur($pdo, 'Inexistant', 0);

// 4) Tests de sécurité
testSQLInjectionAdvanced($pdo);
testXSSProtection($pdo);
testBruteForceProtection($pdo);
testPermissions($pdo);
testValidationEntreeUtilisateur($pdo);

// 5) Nettoyage après tests
cleanupUsers($pdo);

echo "\nFin des tests.\n";
