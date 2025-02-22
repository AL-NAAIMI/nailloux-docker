<?php
// Inclusion des fichiers nécessaires
include __DIR__ . '/../../db/connection.php';
include __DIR__ . '/../../../back/env.php'; // Fichier d'environnement 

// Fonction pour ajouter une publication pour préparer les tests
function addPublication($msg, $uid, $public, $image, $pdo) {
    // Préparer la requête SQL pour insérer une publication
    $sql = $image
        ? "INSERT INTO `publication` (`msg`, `uid`, `public`, `image`) VALUES (:msg, :uid, :public, :image)"
        : "INSERT INTO `publication` (`msg`, `uid`, `public`, `image`) VALUES (:msg, :uid, :public, NULL)";

    $stmt = $pdo->prepare($sql);

    // Lier les paramètres
    $stmt->bindParam(':msg', $msg, PDO::PARAM_STR);
    $stmt->bindParam(':uid', $uid, PDO::PARAM_INT);
    $stmt->bindParam(':public', $public, PDO::PARAM_INT);

    if ($image) {
        $stmt->bindParam(':image', $image, PDO::PARAM_STR);
    }

    // Exécuter la requête et retourner l'ID de la dernière insertion
    $stmt->execute();
    return $pdo->lastInsertId(); 
}

// Fonction pour ajouter un commentaire
function addComment($comment, $pid, $uid, $pdo) {
    // Vérifier que le commentaire n'est pas vide
    if (empty(trim($comment))) {
        return "Erreur : Le commentaire ne peut pas être vide.";
    }

    // Neutraliser les caractères spéciaux pour prévenir les attaques XSS
    $comment = htmlspecialchars($comment, ENT_QUOTES, 'UTF-8');

    // Vérifier si la publication existe
    $sql = "SELECT * FROM `publication` WHERE `pid` = :pid";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':pid' => $pid]);
    if ($stmt->rowCount() === 0) {
        return "Erreur : La publication n'existe pas.";
    }

    // Insérer le commentaire dans la base de données
    $sql = "INSERT INTO `commentaire_p` (`texte`, `pid`, `uid`) VALUES (:comment, :pid, :uid)";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([':comment' => $comment, ':pid' => $pid, ':uid' => $uid]);
}

// Fonction de nettoyage
function cleanup($pdo, $pid = null) {
    try {
        if ($pid !== null) {
            // Supprimer tous les commentaires associés à la publication de test
            $stmt = $pdo->prepare("DELETE FROM `commentaire_p` WHERE `pid` = :pid");
            $stmt->execute([':pid' => $pid]);

            // Supprimer la publication de test
            $stmt = $pdo->prepare("DELETE FROM `publication` WHERE `pid` = :pid");
            $stmt->execute([':pid' => $pid]);
        } else {
            // Supprimer tous les commentaires et publications de test basés sur le texte
            $pdo->exec("DELETE FROM `commentaire_p` WHERE `texte` LIKE 'Test commentaire%'");
            $pdo->exec("DELETE FROM `publication` WHERE `msg` LIKE 'Test publication%'");
        }
        echo "Cleanup Completed: Toutes les publications et commentaires de test ont été supprimés.\n";
    } catch (PDOException $e) {
        echo "Erreur lors du nettoyage: " . $e->getMessage() . "\n";
    }
}

// Nettoyage avant les tests
cleanup($pdo);

// Préparer une publication pour les tests
$pid = addPublication("Test publication pour commentaires", 1, 1, null, $pdo);
if (!$pid) {
    die("Erreur : Impossible de créer une publication pour les tests.\n");
}

// TEST 1 - Ajouter un commentaire sur une publication existante
$_POST = [
    'comment' => 'Test commentaire sur la publication',
    'pid' => $pid, // ID de la publication créée
    'uid' => 1,    // Utilisateur existant
];

$result = addComment($_POST['comment'], $_POST['pid'], $_POST['uid'], $pdo);

// Vérification du commentaire dans la base de données
$sql = "SELECT * FROM `commentaire_p` WHERE `texte` = :comment AND `pid` = :pid AND `uid` = :uid";
$stmt = $pdo->prepare($sql);
$stmt->execute([':comment' => $_POST['comment'], ':pid' => $_POST['pid'], ':uid' => $_POST['uid']]);
if ($stmt->rowCount() == 1) {
    echo "Test 1 Passed: Commentaire ajouté avec succès\n";
} else {
    echo "Test 1 Failed: Le commentaire n'a pas été ajouté correctement!\n";
}

// TEST 2 - Tentative de commentaire sur une publication inexistante
$_POST = [
    'comment' => 'Commentaire sur une publication inexistante',
    'pid' => 9999, // ID de publication qui n'existe pas
    'uid' => 1,    // Utilisateur existant
];

$result = addComment($_POST['comment'], $_POST['pid'], $_POST['uid'], $pdo);

// Vérification que le commentaire n'a pas été ajouté
$sql = "SELECT * FROM `commentaire_p` WHERE `texte` = :comment AND `pid` = :pid";
$stmt = $pdo->prepare($sql);
$stmt->execute([':comment' => $_POST['comment'], ':pid' => $_POST['pid']]);
if ($stmt->rowCount() == 0) {
    echo "Test 2 Passed: Commentaire sur une publication inexistante rejeté\n";
} else {
    echo "Test 2 Failed: Commentaire ajouté à une publication inexistante!\n";
}

// TEST 3 - Tentative de commentaire avec un texte vide
$_POST = [
    'comment' => '', // Commentaire vide
    'pid' => $pid,   // ID de publication existante
    'uid' => 1,      // Utilisateur existant
];

$result = addComment($_POST['comment'], $_POST['pid'], $_POST['uid'], $pdo);
$sql = "SELECT * FROM `commentaire_p` WHERE `texte` = ''";
$stmt = $pdo->prepare($sql);
$stmt->execute();
if ($stmt->rowCount() == 0) {
    echo "Test 3 Passed: Commentaire vide rejeté\n";
} else {
    echo "Test 3 Failed: Commentaire vide ajouté!\n";
}

// TEST 4 - Tentative d'injection SQL
$_POST = [
    'comment' => "'; DROP TABLE commentaire_p; --", // Injection SQL
    'pid' => $pid, // Publication existante
    'uid' => 1,    // Utilisateur existant
];

$result = addComment($_POST['comment'], $_POST['pid'], $_POST['uid'], $pdo);

// Vérification que la requête a été exécutée sans erreur (grâce aux requêtes préparées)
if ($result) {
    echo "Test 4 Passed: Tentative d'injection SQL a été bloquée\n";
} else {
    echo "Test 4 Failed: L'injection SQL n'a pas été correctement bloquée!\n";
}

// Vérification que la table commentaire_p n'est pas affectée (aucune suppression ou modification inattendue)
try {
    // Vérifier si la table commentaire_p existe toujours
    $stmt = $pdo->query("DESCRIBE `commentaire_p`");
    if ($stmt) {
        echo "Test 4 Confirmed: La table 'commentaire_p' est intacte.\n";
    } else {
        echo "Test 4 Failed: La table 'commentaire_p' a été modifiée ou supprimée.\n";
    }
} catch (PDOException $e) {
    echo "Test 4 Failed: La table 'commentaire_p' a été supprimée ou n'existe pas.\n";
}

// TEST 5 - Prévention XSS
$_POST = [
    'comment' => "<script>alert('XSS');</script>", // Injection XSS
    'pid' => $pid, // Publication existante
    'uid' => 1,    // Utilisateur existant
];

$result = addComment($_POST['comment'], $_POST['pid'], $_POST['uid'], $pdo);

// Vérification que le script injecté est neutralisé
$sql = "SELECT * FROM `commentaire_p` WHERE `texte` = :comment";
$stmt = $pdo->prepare($sql);
$stmt->execute([':comment' => htmlspecialchars($_POST['comment'], ENT_QUOTES, 'UTF-8')]);
if ($stmt->rowCount() == 1) {
    echo "Test 5 Passed: Attaque XSS rejeté\n";
} else {
    echo "Test 5 Failed: Attaque XSS détectée\n";
}

// Nettoyage après les tests
cleanup($pdo, $pid);
?>
