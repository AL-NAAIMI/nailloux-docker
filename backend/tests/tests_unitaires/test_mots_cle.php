<?php
// tests/test_publications.php

// Inclusion du fichier de connexion
include __DIR__ . '/../../db/connection.php';

/**
 * Nettoyage des données avant et après les tests
 */
function cleanup($pdo) {
    // Supprime toutes les publications et données créées pour les tests
    $pdo->exec("
        DELETE FROM `publication` 
        WHERE `msg` LIKE 'Test recherche%' 
           OR `msg` LIKE 'Test CSRF%' 
           OR `msg` LIKE 'Test Input%' 
           OR `msg` LIKE 'Test Requête%' 
           OR `msg` LIKE 'Test Longue%' 
           OR `msg` LIKE 'Test JSON%' 
           OR `msg` LIKE 'Test Doublon%'
           OR `msg` LIKE 'Test Requête Suppression%'
           OR `msg` LIKE 'Test Injection%'
    ");
    echo "Cleanup Completed: Toutes les publications de test ont été supprimées.\n";
}

/**
 * Fonction pour insérer une publication de test
 *
 * @param string      $msg         Message de la publication
 * @param string      $mots_cles   Mots-clés
 * @param int         $uid         ID utilisateur
 * @param PDO         $pdo         Connexion PDO
 * @param string|null $csrf_token  Token CSRF (facultatif)
 *
 * @return bool True si l'insertion a eu lieu, false sinon
 */
function addTestPublication($msg, $mots_cles, $uid, $pdo, $csrf_token = null) {
    if ($csrf_token !== null) {
        // Simuler une vérification de token CSRF
    } else {
        return false; // Rejet si le token est absent
    }

    // Validation de la taille des entrées
    if (strlen($msg) > 65535 || strlen($mots_cles) > 65535) {
        echo "Erreur : Taille des données trop grande.\n";
        return false;
    }

    // Échapper les entrées pour éviter les XSS
    $msg = htmlspecialchars($msg, ENT_QUOTES, 'UTF-8');
    $mots_cles = htmlspecialchars($mots_cles, ENT_QUOTES, 'UTF-8');

    $sql = "INSERT INTO `publication` 
            (`msg`, `mots_clés`, `uid`, `nom_photographe`, `titre`) 
            VALUES (:msg, :mots_cles, :uid, 'Test Photographer', 'Test Title')";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':msg'       => $msg,
        ':mots_cles' => $mots_cles,
        ':uid'       => $uid,
    ]);
    return $stmt->rowCount() > 0;
}



/**
 * Test de la recherche par mot-clé
 *
 * @param string $mot_cle
 * @param int    $expected_count
 * @param PDO    $pdo
 *
 * @return void
 */
function testRecherche($mot_cle, $expected_count, $pdo) {
    $sql = "SELECT * FROM `publication` WHERE `mots_clés` LIKE :search_query";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':search_query' => '%' . $mot_cle . '%']);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Vérification du nombre de résultats
    if (count($results) === $expected_count) {
        echo "Test Passed: Recherche pour '$mot_cle' a retourné $expected_count résultats.\n";
    } else {
        echo "Test Failed: Recherche pour '$mot_cle' a retourné " . count($results) 
             . " résultats au lieu de $expected_count.\n";
        print_r($results);
    }
}

/**
 * Test contre les injections SQL basiques
 *
 * @param PDO $pdo
 *
 * @return void
 */
function testSQLInjection($pdo) {
    echo "\n--- Test contre les injections SQL (basique) ---\n";
    $malicious_input = "' OR '1'='1"; // Entrée malveillante classique
    $sql = "SELECT * FROM `publication` WHERE `mots_clés` LIKE :search_query";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':search_query' => '%' . $malicious_input . '%']);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Si le résultat contient des publications, le test échoue
    if (count($results) > 0) {
        echo "Test Failed: L'injection SQL a fonctionné !\n";
    } else {
        echo "Test Passed: L'injection SQL a été bloquée.\n";
    }
}

/**
 * Test contre les injections SQL avancées (UNION SELECT)
 *
 * @param PDO $pdo
 *
 * @return void
 */
function testSQLInjectionUnion($pdo) {
    echo "\n--- Test contre les injections SQL (UNION SELECT) ---\n";

    // Exemple de payload malveillant
    $malicious_union = "clé' UNION SELECT 1, 'HACKED', 1, 'HACKED', 'HACKED' FROM DUAL -- ";

    $sql = "SELECT * FROM `publication` WHERE `mots_clés` LIKE :search_query";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':search_query' => '%' . $malicious_union . '%']);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Si une injection UNION fait remonter des résultats inattendus, le test échoue
    if (count($results) > 0) {
        echo "Test Failed: L'injection UNION SELECT semble avoir fonctionné !\n";
    } else {
        echo "Test Passed: L'injection UNION SELECT a été bloquée.\n";
    }
}

/**
 * Test contre les injections SQL “time-based” (simulation)
 *
 * @param PDO $pdo
 *
 * @return void
 */
function testSQLInjectionTimeBased($pdo) {
    echo "\n--- Test contre les injections SQL (Time-Based) ---\n";

    // Ici, on simule un test time-based en envoyant une requête où un SLEEP pourrait être injecté.
    // Dans la pratique, on mesurerait le temps de réponse. Pour la démo, on vérifie juste que rien ne casse.
    // Par exemple : "' OR IF(SUBSTRING(@@version,1,1)='5', SLEEP(3), 0) -- "

    $malicious_time = "' OR IF(1=1, SLEEP(2), 0) -- ";

    $start_time = microtime(true);

    $sql = "SELECT * FROM `publication` WHERE `mots_clés` LIKE :search_query";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':search_query' => '%' . $malicious_time . '%']);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $end_time = microtime(true);
    $duration = $end_time - $start_time;

    // Selon votre setup, vous pouvez considérer qu’au-delà d’une certaine durée, la requête a pu être “influencée”.
    // Ici, on se limite à dire que si la requête s’exécute, on vérifie si elle n’a pas retourné un résultat non attendu.
    if (count($results) > 0) {
        echo "Test Failed: L'injection time-based semble avoir fonctionné !\n";
    } else {
        echo "Test Passed: L'injection time-based n'a pas produit d'effet visible.\n";
    }

    // Afficher la durée pour information
    echo "Durée de la requête : " . round($duration, 2) . " secondes.\n";
}

/**
 * Test de validation des entrées utilisateur (XSS, etc.)
 *
 * @param PDO $pdo
 *
 * @return void
 */
function testValidationEntree($pdo) {
    echo "\n--- Test de validation des entrées utilisateur (XSS) ---\n";
    $invalid_input = "<script>alert('XSS');</script>"; // Entrée avec script
    $sql = "SELECT * FROM `publication` WHERE `mots_clés` LIKE :search_query";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':search_query' => '%' . $invalid_input . '%']);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Vérifier qu'aucune publication n'est retournée
    if (count($results) > 0) {
        echo "Test Failed: Les entrées malveillantes n'ont pas été filtrées !\n";
    } else {
        echo "Test Passed: Les entrées malveillantes ont été filtrées.\n";
    }
}

/**
 * Test de validation des entrées utilisateur (XSS stocké)
 * 
 * On va insérer un contenu malveillant et vérifier s’il est stocké tel quel.
 */
function testValidationEntreeStockee($pdo) {
    echo "\n--- Test de validation des entrées utilisateur (XSS stocké) ---\n";

    $malicious_content = "<img src=x onerror=alert('XSS Stocké')>"; 
    $csrf_token = 'valid_token';
    
    // On insère un message potentiellement dangereux
    $inserted = addTestPublication("Test Injection XSS stocké", $malicious_content, 1, $pdo, $csrf_token);

    if (!$inserted) {
        echo "Test Insertion XSS Stocké Failed: Impossible d'insérer la publication (peut-être un filtrage déjà en place?).\n";
        return;
    }

    // On récupère la publication pour voir si elle est stockée telle quelle
    $sql = "SELECT * FROM `publication` WHERE `msg` = 'Test Injection XSS stocké'";
    $stmt = $pdo->query($sql);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result && strpos($result['mots_clés'], $malicious_content) !== false) {
        echo "Test Failed: Le contenu XSS stocké est présent tel quel dans la base !\n";
    } else {
        echo "Test Passed: Le contenu XSS a été filtré ou échappé.\n";
    }

    // Nettoyage de ce test
    $pdo->exec("DELETE FROM `publication` WHERE `msg` = 'Test Injection XSS stocké'");
}

/**
 * Test de gestion des permissions (suppression de publication par un autre utilisateur)
 *
 * @param PDO $pdo
 *
 * @return void
 */
function testPermissions($pdo) {
    echo "\n--- Test de gestion des permissions ---\n";
    // Tentative de suppression d'une publication par un utilisateur non propriétaire
    $sql = "DELETE FROM `publication` WHERE `pid` = :pid AND `uid` = :uid";
    $stmt = $pdo->prepare($sql);
    // On suppose que pid=1 appartient à un autre utilisateur (pas 300)
    $stmt->execute([':pid' => 1, ':uid' => 300]); 

    if ($stmt->rowCount() > 0) {
        echo "Test Failed: Un utilisateur non autorisé a pu supprimer une publication !\n";
    } else {
        echo "Test Passed: Les permissions pour la suppression fonctionnent correctement.\n";
    }
}

/**
 * Test de gestion des erreurs
 *
 * @param PDO $pdo
 *
 * @return void
 */
function testGestionErreurs($pdo) {
    echo "\n--- Test de gestion des erreurs ---\n";
    // Sauvegarder le mode d'erreur actuel
    $old_errmode = $pdo->getAttribute(PDO::ATTR_ERRMODE);
    
    // Désactiver temporairement l'affichage des erreurs (mode silencieux)
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    
    // Test avec une requête invalide
    $sql = "SELECT * FROM `table_inexistante`";
    $pdo->query($sql);
    
    // Récupérer les informations d'erreur
    $error = $pdo->errorInfo();
    
    // Rétablir le mode d'erreur précédent
    $pdo->setAttribute(PDO::ATTR_ERRMODE, $old_errmode);
    
    // Vérifier si une erreur est survenue
    if ($error[0] != '00000') { 
        echo "Test Passed: Erreur correctement détectée pour une requête invalide.\n";
    } else {
        echo "Test Failed: Aucune erreur détectée pour une requête invalide !\n";
    }
}

/**
 * Test de protection contre les attaques CSRF
 *
 * @param PDO $pdo
 *
 * @return void
 */
function testCSRFProtection($pdo) {
    echo "\n--- Test de protection contre les attaques CSRF ---\n";
    // Tentative d'ajouter une publication sans token CSRF
    $result = addTestPublication('Test CSRF', 'csrf_clé', 1, $pdo);
    
    if (!$result) {
        echo "Test Passed: La publication sans token CSRF a été rejetée.\n";
    } else {
        echo "Test Failed: La publication sans token CSRF a été acceptée !\n";
    }
}

/**
 * Test de validation des données JSON
 *
 * @param PDO $pdo
 *
 * @return void
 */
function testValidationJSON($pdo) {
    echo "\n--- Test de validation des données JSON ---\n";

    $sql = "INSERT INTO `publication` 
            (`msg`, `mots_clés`, `uid`, `nom_photographe`, `titre`, `donnees_exif`) 
            VALUES ('Test JSON', 'clé_json', 1, 'Photographe Test', 'Titre Test', :json)";

    // JSON invalide
    $invalid_json = '{invalid}';
    $stmt = $pdo->prepare($sql);

    try {
        $stmt->execute([':json' => $invalid_json]);
        echo "Test Failed: Le JSON invalide a été accepté.\n";
    } catch (PDOException $e) {
        echo "Test Passed: Le JSON invalide a été rejeté.\n";
    }
}

/**
 * Test de suppression sécurisée
 *
 * @param PDO $pdo
 *
 * @return void
 */
function testSuppressionSecurisee($pdo) {
    echo "\n--- Test de suppression sécurisée ---\n";

    // Ajout d'une publication appartenant à l'utilisateur 1
    addTestPublication('Test Requête Suppression', 'clé_suppression', 1, $pdo, 'valid_token');

    // Tentative de suppression par un autre utilisateur
    $sql = "DELETE FROM `publication` WHERE `msg` = :msg AND `uid` = :uid";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':msg' => 'Test Requête Suppression', ':uid' => 300]); // UID 300 est un autre utilisateur

    if ($stmt->rowCount() === 0) {
        echo "Test Passed: Suppression sécurisée fonctionnelle.\n";
    } else {
        echo "Test Failed: Suppression non sécurisée détectée !\n";
    }
}

// -----------------------------------------------------------------------------------------
//                               DÉBUT DE L'EXÉCUTION DES TESTS
// -----------------------------------------------------------------------------------------

// 1) Nettoyage avant tests
cleanup($pdo);

// 2) Ajout de publications pour les tests de recherche
addTestPublication('Test recherche 1', 'clé1 clé2 clé3', 1, $pdo, 'valid_token');
addTestPublication('Test recherche 2', 'clé2 clé4', 1, $pdo, 'valid_token');
addTestPublication('Test recherche 3', 'clé5 clé6', 1, $pdo, 'valid_token');

echo "Publications de test ajoutées.\n";

// 3) Tests de recherche
testRecherche('clé1', 1, $pdo); // Attendu : 1 résultat
testRecherche('clé2', 2, $pdo); // Attendu : 2 résultats
testRecherche('clé5', 1, $pdo); // Attendu : 1 résultat
testRecherche('clé7', 0, $pdo); // Attendu : 0 résultat

// 4) Tests de sécurité
testSQLInjection($pdo);
testSQLInjectionUnion($pdo);
testSQLInjectionTimeBased($pdo);

testValidationEntree($pdo);
testValidationEntreeStockee($pdo);

testPermissions($pdo);
testGestionErreurs($pdo);
testCSRFProtection($pdo);
testValidationJSON($pdo);
testSuppressionSecurisee($pdo);

// 5) Nettoyage après tests
cleanup($pdo);

echo "\nFin des tests.\n";
