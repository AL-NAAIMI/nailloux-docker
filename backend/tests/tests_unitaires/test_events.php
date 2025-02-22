<?php
/**
 * tests/test_events.php
 *
 * Exemple de fichier de tests pour les événements.
 * Inspiré de votre test_publications.php, adapté pour les événements.
 */

// Inclusion du fichier de connexion
include __DIR__ . '/../../db/connection.php';

/**
 * Nettoyage des données avant et après les tests
 * Supprime tous les événements et données créées pour les tests.
 */
function cleanup($pdo) {
    // Supprime les événements et données associées portant la mention "Test" dans le titre
    $pdo->exec("
        DELETE FROM `photos_evenement` 
        WHERE `id_evenement` IN (
            SELECT `id_evenement`
            FROM `evenement`
            WHERE `titre` LIKE 'Test%'
        )
    ");

    $pdo->exec("
        DELETE FROM `evenement_participants`
        WHERE `id_evenement` IN (
            SELECT `id_evenement`
            FROM `evenement`
            WHERE `titre` LIKE 'Test%'
        )
    ");

    $pdo->exec("
        DELETE FROM `evenement`
        WHERE `titre` LIKE 'Test%'
    ");

    echo "Cleanup Completed: Tous les événements de test ont été supprimés.\n";
}

/**
 * Fonction pour insérer un événement de test
 *
 * @param PDO         $pdo
 * @param string      $titre
 * @param string      $type
 * @param int         $uid
 * @param bool        $officiel
 * @param string|null $csrf_token
 *
 * @return bool
 */
function addTestEvent($pdo, $titre, $type, $uid, $officiel, $csrf_token = null) 
{
    if ($csrf_token === null) {
        // Simule une protection CSRF : rejet si le token est absent
        return false;
    }

    // Échapper/valider si nécessaire (XSS, taille, etc.)
    $titre = htmlspecialchars($titre, ENT_QUOTES, 'UTF-8');
    $type  = htmlspecialchars($type, ENT_QUOTES, 'UTF-8');

    // Insertion de l'événement
    // Ajustez date_heure, lieu et descriptif selon vos besoins
    $sql = "
        INSERT INTO `evenement` 
        (`titre`, `date_heure`, `lieu`, `descriptif`, `type`, `officiel`, `uid`) 
        VALUES (:titre, NOW(), 'Lieu Test', 'Descriptif Test', :type, :officiel, :uid)
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':titre'    => $titre,
        ':type'     => $type,
        ':officiel' => $officiel ? 1 : 0,
        ':uid'      => $uid,
    ]);

    return $stmt->rowCount() > 0;
}

/**
 * Test de l'insertion d'un événement
 */
function testInsertionEvent($pdo) {
    echo "\n--- Test d'insertion d'un événement ---\n";
    $inserted = addTestEvent($pdo, 'Test Insertion', 'Visionnage', 1, true, 'valid_token');
    if ($inserted) {
        echo "Test Passed: L'événement a bien été inséré.\n";
    } else {
        echo "Test Failed: Impossible d'insérer l'événement.\n";
    }
}

/**
 * Test de recherche d'événements par titre
 */
function testRechercheEvent($pdo, $searchTerm, $expectedCount) {
    echo "\n--- Test de recherche d'événements ---\n";

    // On effectue la requête
    $sql = "SELECT * FROM `evenement` WHERE `titre` LIKE :search_query";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':search_query' => '%' . $searchTerm . '%']);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Vérification du nombre de résultats
    if (count($results) === $expectedCount) {
        echo "Test Passed: Recherche pour '$searchTerm' a retourné $expectedCount résultats.\n";
    } else {
        echo "Test Failed: Recherche pour '$searchTerm' a retourné " . count($results) . " résultats au lieu de $expectedCount.\n";
    }
}

/**
 * Test contre les injections SQL (basique)
 */
function testSQLInjectionEvent($pdo) {
    echo "\n--- Test contre les injections SQL (événements) ---\n";
    $malicious_input = "' OR '1'='1";
    $sql = "SELECT * FROM `evenement` WHERE `titre` LIKE :titre";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':titre' => '%' . $malicious_input . '%']);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($results) > 0) {
        echo "Test Failed: L'injection SQL a retourné des événements !\n";
    } else {
        echo "Test Passed: L'injection SQL a été bloquée.\n";
    }
}

/**
 * Test contre les injections SQL (UNION SELECT)
 */
function testSQLInjectionUnionEvent($pdo) {
    echo "\n--- Test contre les injections SQL (UNION SELECT) ---\n";
    $malicious_union = "Test' UNION SELECT 1, 'HACKED', NOW(), 'HACKED', 'HACKED', 1, 9999 FROM DUAL -- ";
    $sql = "SELECT * FROM `evenement` WHERE `titre` LIKE :titre";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':titre' => '%' . $malicious_union . '%']);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($results) > 0) {
        echo "Test Failed: L'injection UNION SELECT semble avoir fonctionné !\n";
    } else {
        echo "Test Passed: L'injection UNION SELECT a été bloquée.\n";
    }
}

/**
 * Test contre les injections SQL “time-based” (simulation)
 */
function testSQLInjectionTimeBasedEvent($pdo) {
    echo "\n--- Test contre les injections SQL (Time-Based) ---\n";
    $malicious_time = "' OR IF(1=1, SLEEP(2), 0) -- ";
    $start_time = microtime(true);

    $sql = "SELECT * FROM `evenement` WHERE `titre` LIKE :titre";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':titre' => '%' . $malicious_time . '%']);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $end_time = microtime(true);
    $duration = $end_time - $start_time;

    if (count($results) > 0) {
        echo "Test Failed: L'injection time-based semble avoir fonctionné !\n";
    } else {
        echo "Test Passed: L'injection time-based n'a pas produit d'effet visible.\n";
    }
    echo "Durée de la requête : " . round($duration, 2) . " secondes.\n";
}

/**
 * Test de validation de l'entrée utilisateur (XSS)
 */
function testValidationEntreeEvent($pdo) {
    echo "\n--- Test de validation des entrées utilisateur (XSS) ---\n";
    $invalid_input = "<script>alert('XSS');</script>";

    $sql = "SELECT * FROM `evenement` WHERE `titre` LIKE :titre";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':titre' => '%' . $invalid_input . '%']);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($results) > 0) {
        echo "Test Failed: Des résultats ont été retournés pour une entrée XSS !\n";
    } else {
        echo "Test Passed: Les entrées malveillantes semblent être filtrées.\n";
    }
}

/**
 * Test de validation des entrées utilisateur (XSS stocké)
 * On va insérer un événement avec un contenu malveillant et vérifier s'il est stocké tel quel.
 */
function testValidationEntreeStockeeEvent($pdo) {
    echo "\n--- Test de validation des entrées utilisateur (XSS stocké) ---\n";
    $malicious_content = "<img src=x onerror=alert('XSS Stocké')>";
    $csrf_token        = 'valid_token';

    // Insère un événement potentiellement dangereux
    $inserted = addTestEvent($pdo, "Test Injection XSS stocké", $malicious_content, 1, false, $csrf_token);

    if (!$inserted) {
        echo "Test Insertion XSS Stocké Failed: Impossible d'insérer l'événement.\n";
        return;
    }

    // Récupère l'événement pour voir si le contenu malveillant est stocké tel quel
    $sql = "SELECT * FROM `evenement` WHERE `titre` = 'Test Injection XSS stocké'";
    $stmt = $pdo->query($sql);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result && strpos($result['type'], $malicious_content) !== false) {
        echo "Test Failed: Le contenu XSS stocké est présent tel quel dans la base !\n";
    } else {
        echo "Test Passed: Le contenu XSS a été filtré ou échappé.\n";
    }

    // Nettoyage de ce test
    $pdo->exec("DELETE FROM `evenement` WHERE `titre` = 'Test Injection XSS stocké'");
}

/**
 * Test de gestion des permissions : vérifier qu'un utilisateur 
 * ne peut pas supprimer ou modifier un événement qui ne lui appartient pas.
 */
function testPermissionsEvent($pdo) {
    echo "\n--- Test de gestion des permissions pour les événements ---\n";

    // 1) Insertion d'un événement appartenant à l'utilisateur 1
    addTestEvent($pdo, 'Test Permissions', 'Visionnage', 1, true, 'valid_token');

    // 2) Tentative de suppression par un autre utilisateur (ex: user ID=999)
    $sql = "DELETE FROM `evenement` WHERE `titre` = :titre AND `uid` = :uid";
    $stmt = $pdo->prepare($sql);
    // 'uid' = 999 ne devrait pas pouvoir supprimer l'événement appartenant au user 1
    $stmt->execute([':titre' => 'Test Permissions', ':uid' => 999]);

    if ($stmt->rowCount() > 0) {
        echo "Test Failed: Un utilisateur non autorisé a pu supprimer un événement !\n";
    } else {
        echo "Test Passed: Les permissions pour la suppression fonctionnent correctement.\n";
    }
}

/**
 * Test de gestion des erreurs
 */
function testGestionErreursEvent($pdo) {
    echo "\n--- Test de gestion des erreurs (événements) ---\n";
    $old_errmode = $pdo->getAttribute(PDO::ATTR_ERRMODE);

    // Désactiver temporairement l'affichage des erreurs (mode silencieux)
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);

    // Requête invalide
    $sql = "SELECT * FROM `table_inexistante`";
    $pdo->query($sql);

    $error = $pdo->errorInfo();

    // Rétablir le mode d'erreur précédent
    $pdo->setAttribute(PDO::ATTR_ERRMODE, $old_errmode);

    if ($error[0] != '00000') {
        echo "Test Passed: Erreur correctement détectée pour une requête invalide.\n";
    } else {
        echo "Test Failed: Aucune erreur détectée pour une requête invalide !\n";
    }
}

/**
 * Test de protection contre les attaques CSRF
 */
function testCSRFProtectionEvent($pdo) {
    echo "\n--- Test de protection contre les attaques CSRF (événements) ---\n";
    // Tentative d'ajouter un événement sans token CSRF
    $result = addTestEvent($pdo, 'Test CSRF', 'Visionnage', 1, false);
    
    if (!$result) {
        echo "Test Passed: L'événement sans token CSRF a été rejeté.\n";
    } else {
        echo "Test Failed: L'événement sans token CSRF a été accepté !\n";
    }
}

/**
 * Test d'inscription à un événement
 */
function testInscriptionEvent($pdo) {
    echo "\n--- Test d'inscription à un événement ---\n";
    // 1) On insère un événement de test
    addTestEvent($pdo, 'Test Inscription', 'Visionnage', 1, false, 'valid_token');

    // 2) Récupère l'ID de l'événement (celui qu'on vient d'insérer)
    $sql = "SELECT `id_evenement` FROM `evenement` WHERE `titre` = 'Test Inscription' LIMIT 1";
    $stmt = $pdo->query($sql);
    $event = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$event) {
        echo "Test Failed: Impossible de récupérer l'événement 'Test Inscription'.\n";
        return;
    }

    // 3) Simuler une inscription d'un autre utilisateur (ex : uid=2)
    $sql = "INSERT INTO `evenement_participants` (`id_evenement`, `uid`) VALUES (:id_evenement, :uid)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id_evenement' => $event['id_evenement'], ':uid' => 2]);

    if ($stmt->rowCount() > 0) {
        echo "Test Passed: L'utilisateur 2 s'est inscrit avec succès.\n";
    } else {
        echo "Test Failed: Échec de l'inscription de l'utilisateur 2.\n";
    }
}

/**
 * Test de désinscription d'un événement
 */
function testDesinscriptionEvent($pdo) {
    echo "\n--- Test de désinscription d'un événement ---\n";
    // On récupère l'événement de test 'Test Inscription'
    $sql = "SELECT `id_evenement` FROM `evenement` WHERE `titre` = 'Test Inscription' LIMIT 1";
    $stmt = $pdo->query($sql);
    $event = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$event) {
        echo "Test Failed: Impossible de récupérer l'événement 'Test Inscription' pour le test de désinscription.\n";
        return;
    }

    // Désinscription de l'utilisateur 2
    $sql = "DELETE FROM `evenement_participants` WHERE `id_evenement` = :id_evenement AND `uid` = :uid";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id_evenement' => $event['id_evenement'], ':uid' => 2]);

    if ($stmt->rowCount() > 0) {
        echo "Test Passed: L'utilisateur 2 s'est désinscrit avec succès.\n";
    } else {
        echo "Test Failed: Échec de la désinscription de l'utilisateur 2.\n";
    }
}

/**
 * Test de suppression sécurisée d'un événement
 */
function testSuppressionEvent($pdo) {
    echo "\n--- Test de suppression sécurisée d'un événement ---\n";

    // Insère un événement appartenant à l'utilisateur 1
    addTestEvent($pdo, 'Test Suppression', 'Visionnage', 1, false, 'valid_token');

    // Tentative de suppression par un autre utilisateur (UID=2, par exemple)
    $sql = "DELETE FROM `evenement` WHERE `titre` = :titre AND `uid` = :uid";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':titre' => 'Test Suppression', ':uid' => 2]);

    if ($stmt->rowCount() === 0) {
        echo "Test Passed: Suppression sécurisée fonctionnelle (utilisateur non autorisé bloqué).\n";
    } else {
        echo "Test Failed: Suppression non sécurisée détectée !\n";
    }
}

// -----------------------------------------------------------------------------------------
//                               DÉBUT DE L'EXÉCUTION DES TESTS
// -----------------------------------------------------------------------------------------

// 1) Nettoyage avant tests
cleanup($pdo);

// 2) Test d'insertion
testInsertionEvent($pdo);

// 3) Ajout d'événements pour tests de recherche
addTestEvent($pdo, 'Test Recherche 1', 'Visionnage', 1, false, 'valid_token');
addTestEvent($pdo, 'Test Recherche 2', 'Randonnée', 1, false, 'valid_token');
addTestEvent($pdo, 'Test Recherche 3', 'Conférence', 1, false, 'valid_token');

echo "\nÉvénements de test ajoutés pour la recherche.\n";

// 4) Tests de recherche
testRechercheEvent($pdo, 'Recherch', 3); // 'Recherch' devrait correspondre aux 3 événements
testRechercheEvent($pdo, 'XYZ', 0);

// 5) Tests de sécurité
testSQLInjectionEvent($pdo);
testSQLInjectionUnionEvent($pdo);
testSQLInjectionTimeBasedEvent($pdo);

testValidationEntreeEvent($pdo);
testValidationEntreeStockeeEvent($pdo);

testPermissionsEvent($pdo);
testGestionErreursEvent($pdo);
testCSRFProtectionEvent($pdo);

// 6) Tests inscription/désinscription
testInscriptionEvent($pdo);
testDesinscriptionEvent($pdo);

// 7) Test suppression sécurisée
testSuppressionEvent($pdo);

// 8) Nettoyage après tests
cleanup($pdo);

echo "\nFin des tests (events).\n";
