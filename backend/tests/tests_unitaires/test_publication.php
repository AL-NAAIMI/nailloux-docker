<!-- <?php
// Inclusion des fichiers nécessaires
include __DIR__ . '/../../db/connection.php';
include __DIR__ . '/../../../back/env.php';// Fichier d'environnement 

// Vérifiez que la connexion à la base de données fonctionne
if (!$pdo) {
    die("Erreur : Connexion à la base de données échouée.");
}

// Fonction pour ajouter une publication
function addPublication($msg, $uid, $public, $image, $pdo) {
    if (empty(trim($msg))) {
        return "Erreur : Le message ne peut pas être vide.";
    }

    $sql = $image
        ? "INSERT INTO `publication` (`msg`, `uid`, `public`, `image`) VALUES (:msg, :uid, :public, :image)"
        : "INSERT INTO `publication` (`msg`, `uid`, `public`, `image`) VALUES (:msg, :uid, :public, NULL)";

    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':msg', $msg, PDO::PARAM_STR);
    $stmt->bindParam(':uid', $uid, PDO::PARAM_INT);
    $stmt->bindParam(':public', $public, PDO::PARAM_INT);
    if ($image) {
        $stmt->bindParam(':image', $image, PDO::PARAM_STR);
    }

    if (!$stmt->execute()) {
        echo "SQL Error: " . implode(", ", $stmt->errorInfo()) . "\n";
        return "Erreur : Échec de l'ajout de la publication.";
    }

    return "Publication ajoutée avec succès.";
}

// Fonction de suppression de publication
function deletePublication($pid, $uid, $pdo) {
    // Verify that the user is the owner of the publication
    $sql = "SELECT * FROM `publication` WHERE `pid` = :pid AND `uid` = :uid";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':pid' => $pid, ':uid' => $uid]);

    // If no rows are returned, the user is not the owner
    if ($stmt->rowCount() === 0) {
        return "Erreur : Suppression interdite pour cet utilisateur.";
    }

    // Delete the publication
    $sql = "DELETE FROM `publication` WHERE `pid` = :pid";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([':pid' => $pid]) ? "Publication supprimée avec succès." : "Erreur : Échec de la suppression.";
}


// Fonction de nettoyage
function cleanup($pdo) {
    // Delete all comments related to test publications
    $sql = "DELETE FROM `commentaire_p` WHERE `pid` IN (SELECT `pid` FROM `publication` WHERE `msg` LIKE 'Test publication%')";
    $pdo->exec($sql);

    // Delete all test publications
    $sql = "DELETE FROM `publication` WHERE `msg` LIKE 'Test publication%'";
    $pdo->exec($sql);

    echo "Cleanup Completed: Toutes les publications de test et leurs commentaires associés ont été supprimées.\n";
}


// Nettoyage avant les tests
cleanup($pdo);

// TEST 1 - Créer une publication sans image
$_POST = [
    'msg' => 'Test publication sans image',
    'uid' => 1, // Utilisateur avec ID 1
    'public' => 1,
    'image' => null,
];

$result = addPublication($_POST['msg'], $_POST['uid'], $_POST['public'], $_POST['image'], $pdo);
assert($result === "Publication ajoutée avec succès.", "Test 1 Failed: La publication sans image n'a pas été créée !");
echo "Test 1 Passed: Publication sans image créée avec succès !\n";

// Vérification dans la base de données
$sql = "SELECT * FROM `publication` WHERE `msg` = :msg AND `uid` = :uid";
$stmt = $pdo->prepare($sql);
$stmt->execute([':msg' => $_POST['msg'], ':uid' => $_POST['uid']]);

if ($stmt->rowCount() === 0) {
    echo "Debug: Query returned no rows.\n";
    print_r($stmt->errorInfo());
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
    exit;
}

assert($stmt->rowCount() === 1, "Test 1 Failed: La publication sans image n'a pas été trouvée dans la base de données !");
echo "Test 1 Passed: Publication sans image trouvée dans la base de données !\n";

// TEST 2 - Créer une publication avec image
$_POST = [
    'msg' => 'Test publication avec image',
    'uid' => 1,
    'public' => 1,
    'image' => 'test_image.jpg',
];

$result = addPublication($_POST['msg'], $_POST['uid'], $_POST['public'], $_POST['image'], $pdo);
assert($result === "Publication ajoutée avec succès.", "Test 2 Failed: La publication avec image n'a pas été créée !");
echo "Test 2 Passed: Publication avec image créée avec succès !\n";

// Vérification dans la base de données
$sql = "SELECT * FROM `publication` WHERE `msg` = :msg AND `image` = :image AND `uid` = :uid";
$stmt = $pdo->prepare($sql);
$stmt->execute([':msg' => $_POST['msg'], ':image' => $_POST['image'], ':uid' => $_POST['uid']]);
assert($stmt->rowCount() === 1, "Test 2 Failed: La publication avec image n'a pas été trouvée dans la base de données !");
echo "Test 2 Passed: Publication avec image trouvée dans la base de données !\n";

// TEST 3 - Rejeter une publication avec un message vide
$_POST = [
    'msg' => '',
    'uid' => 1,
    'public' => 1,
    'image' => null,
];

$result = addPublication($_POST['msg'], $_POST['uid'], $_POST['public'], $_POST['image'], $pdo);
assert($result === "Erreur : Le message ne peut pas être vide.", "Test 3 Failed: La publication avec message vide a été acceptée !");
echo "Test 3 Passed: Publication avec message vide rejetée !\n";

// TEST 4 - Supprimer une publication par le propriétaire
$sql = "SELECT pid FROM `publication` WHERE `msg` = 'Test publication sans image' AND `uid` = 1";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$publication = $stmt->fetch(PDO::FETCH_ASSOC);

$_POST['pid'] = $publication['pid'];
$_POST['uid'] = 1; // Owner's UID

$result = deletePublication($_POST['pid'], $_POST['uid'], $pdo);
assert($result === "Publication supprimée avec succès.", "Test 4 Failed: La publication n'a pas été supprimée par le propriétaire !");
echo "Test 4 Passed: Publication supprimée avec succès par le propriétaire !\n";

// TEST 5 - Tentative de suppression par un non-propriétaire
$_POST['uid'] = 2; // Non-owner UID

$result = deletePublication($_POST['pid'], $_POST['uid'], $pdo);
assert($result === "Erreur : Suppression interdite pour cet utilisateur.", "Test 5 Failed: Un non-propriétaire a pu supprimer la publication !");
echo "Test 5 Passed: Un non-propriétaire n'a pas pu supprimer la publication !\n";


// Nettoyage après les tests
cleanup($pdo);
?>
