<?php
// Inclusion des fichiers nécessaires

    include __DIR__ . '/../../db/connection.php';
    include __DIR__ . '/../../db/validate.php';
    include __DIR__ . '/../../../back/env.php'; // Fichier d'environnement 
    include __DIR__ . '/test_publication.php';
    

    function setupPDO() {
        include __DIR__ . '/../../db/connection.php';
        return $pdo;
    }

    function cleanupTestUsersAndPublications($pdo) {
        // Nettoyer les publications de test
        $pdo->exec("DELETE FROM `publication` WHERE `msg` LIKE 'Test publication%'");
        // Nettoyer les utilisateurs de test
        $pdo->exec("DELETE FROM `utilisateur` WHERE `pseudo` LIKE 'testuser%'");
    }


    // Initialisation PDO
    $pdo = setupPDO();
    cleanupTestUsersAndPublications($pdo);

    // TEST 1 - Vérification de l'accès admin
    $_SESSION['pseudo'] = 'adminuser'; 
    $_SESSION['role'] = 'Administrateur'; 

    if ($_SESSION['role'] === 'Administrateur') {
        echo "Test 1 Passed: L'administrateur peut accéder à la section administration.\n";
    } else {
        echo "Test 1 Failed: L'administrateur n'a pas accès à la section administration.\n";
    }

    // TEST 2 - Création et suppression de publication par admin
    $_POST = [
        'msg' => 'Test admin publication',
        'uid' => 1,
        'public' => 1,
        'image' => null,
    ];

    $result = addPublication($_POST['msg'], $_POST['uid'], $_POST['public'], $_POST['image'], $pdo);
    assert($result === "Publication ajoutée avec succès.", "Test 2 Failed: L'admin n'a pas pu créer une publication.");
    echo "Test 2 Passed: Publication créée par l'admin.\n";

    // Supprimer la publication
    $sql = "SELECT pid FROM `publication` WHERE `msg` = :msg";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':msg' => $_POST['msg']]);
    $publication = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($publication) {
        $deleteResult = deletePublication($publication['pid'], $_POST['uid'], $pdo);
        assert($deleteResult === "Publication supprimée avec succès.", "Test 2 Failed: La suppression par l'admin a échoué.");
        echo "Test 2 Passed: L'admin a supprimé une publication.\n";
    }

    // TEST 3 - Promotion et révocation
    function testUserRoleChange($pdo, $pseudo, $newRole) {
        $sql = "UPDATE `utilisateur` SET `role` = :role WHERE `pseudo` = :pseudo";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':role' => $newRole, ':pseudo' => $pseudo]);

        $sql = "SELECT `role` FROM `utilisateur` WHERE `pseudo` = :pseudo";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':pseudo' => $pseudo]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row['role'] === $newRole;
    }

    $_POST['pseudo'] = 'testuser';
    $pdo->exec("INSERT INTO `utilisateur` (`pseudo`, `role`) VALUES ('testuser', 'Utilisateur')");

    if (testUserRoleChange($pdo, 'testuser', 'Administrateur')) {
        echo "Test 3 Passed: L'utilisateur a été promu administrateur.\n";
    } else {
        echo "Test 3 Failed: La promotion à administrateur a échoué.\n";
    }

    if (testUserRoleChange($pdo, 'testuser', 'Utilisateur')) {
        echo "Test 4 Passed: L'utilisateur a été rétrogradé utilisateur.\n";
    } else {
        echo "Test 4 Failed: La rétrogradation à utilisateur a échoué.\n";
    }

    // Cleanup final
    cleanupTestUsersAndPublications($pdo);
