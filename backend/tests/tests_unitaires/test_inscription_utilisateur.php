<?php
// Démarrer le buffering de sortie pour empêcher les sorties précoces
ob_start();

// Inclusion des fichiers nécessaires
include __DIR__ . '/../../db/connection.php';
include __DIR__ . '/../../db/validate.php';
include __DIR__ . '/../../../back/env.php'; // Fichier d'environnement    

// Vérifiez que la connexion à la base de données fonctionne
if (!$pdo) {
    die("Erreur : Connexion à la base de données échouée.");
}

// TEST 1 - Inscription d'un utilisateur
$_POST = [
    'pseudo' => 'testuser1',
    'password' => 'StrongPass123!',
    'email' => 'testuser1@example.com',
    'prenom' => 'Test',
    'nom' => 'User',
    'regst' => true
];

// Appel de la fonction d'inscription (register_user)
$result = register_user(
    $_POST['pseudo'],
    $_POST['password'],
    $_POST['email'],
    $_POST['prenom'],
    $_POST['nom'],
    $pdo
);

// Vérification du résultat de l'inscription
assert($result === "Inscription réussie.", "Test 1 Failed: L'utilisateur n'a pas été inscrit !");
echo "Test 1 Passed: Utilisateur inscrit avec succès !\n";

// Vérification dans la base de données si l'utilisateur a été créé
$sql = "SELECT * FROM `utilisateur` WHERE `pseudo` = :pseudo";
$stmt = $pdo->prepare($sql);
$stmt->execute([':pseudo' => 'testuser1']);
assert($stmt->rowCount() === 1, "Test 1 Failed: L'utilisateur n'a pas été trouvé dans la base de données !");
echo "Test 1 Passed: Utilisateur trouvé dans la base de données !\n";

// TEST 2 - Vérification des doublons
$_POST = [
    'pseudo' => 'testuser1', // Pseudo déjà existant
    'password' => 'AnotherPass123!',
    'email' => 'newuser@example.com',
    'prenom' => 'Another',
    'nom' => 'User',
    'regst' => true
];

// Appel de la fonction d'inscription
$result = register_user(
    $_POST['pseudo'],
    $_POST['password'],
    $_POST['email'],
    $_POST['prenom'],
    $_POST['nom'],
    $pdo
);

// Vérification que le doublon est détecté
assert($result === "Erreur : Le pseudo est déjà pris.", "Test 2 Failed: Doublon de pseudo accepté !");
echo "Test 2 Passed: Doublon détecté et inscription refusée !\n";

// TEST 3 - Format d'email invalide
$_POST = [
    'pseudo' => 'invalidemailuser',
    'password' => 'ValidPass123!',
    'email' => 'invalid-email-format', // Email invalide
    'prenom' => 'Invalid',
    'nom' => 'Email',
    'regst' => true
];

// Appel de la fonction d'inscription
$result = register_user(
    $_POST['pseudo'],
    $_POST['password'],
    $_POST['email'],
    $_POST['prenom'],
    $_POST['nom'],
    $pdo
);

// Vérification que le format d'email est détecté comme invalide
assert($result === "Erreur : Format d'email invalide.", "Test 3 Failed: Email invalide accepté !");
echo "Test 3 Passed: Format d'email invalide détecté !\n";

// TEST 4 - Validation de mot de passe faible
$_POST = [
    'pseudo' => 'weakpassworduser',
    'password' => 'weak', // Mot de passe faible
    'email' => 'weakpassword@example.com',
    'prenom' => 'Weak',
    'nom' => 'Password',
    'regst' => true
];

// Appel de la fonction d'inscription
$result = register_user(
    $_POST['pseudo'],
    $_POST['password'],
    $_POST['email'],
    $_POST['prenom'],
    $_POST['nom'],
    $pdo
);

// Vérification que le mot de passe faible est refusé
assert($result === "Erreur : Le mot de passe doit contenir au moins 8 caractères, une majuscule, un chiffre et un caractère spécial.", "Test 4 Failed: Mot de passe faible accepté !");
echo "Test 4 Passed: Mot de passe faible détecté !\n";

// TEST 5 - Injection SQL
$_POST = [
    'pseudo' => "' OR 1=1 --", // SQL Injection payload
    'password' => 'Injection123!',
    'email' => 'sqlinjection@example.com',
    'prenom' => 'SQL',
    'nom' => 'Injection',
    'regst' => true
];

// Appel de la fonction d'inscription
$result = register_user(
    $_POST['pseudo'],
    $_POST['password'],
    $_POST['email'],
    $_POST['prenom'],
    $_POST['nom'],
    $pdo
);

// Vérification que l'injection SQL est bloquée
$sql = "SELECT * FROM `utilisateur` WHERE `pseudo` = :pseudo";
$stmt = $pdo->prepare($sql);
$stmt->execute([':pseudo' => $_POST['pseudo']]);
assert($stmt->rowCount() === 0, "Test 5 Failed: Injection SQL acceptée !");
echo "Test 5 Passed: Injection SQL bloquée !\n";

// TEST 6 - Connexion d'un utilisateur
$_POST = [
    'pseudo' => 'testuser1',
    'password' => 'StrongPass123!',
    'lgn' => true
];

// Appel de la fonction de connexion
$login_success = validate($_POST['pseudo'], $_POST['password'], $pdo);

// Vérification si la session est bien créée après une connexion réussie
assert($login_success && isset($_SESSION['pseudo']) && $_SESSION['pseudo'] === 'testuser1', "Test 6 Failed: Connexion échouée !");
echo "Test 6 Passed: Connexion réussie !\n";

// TEST 7 - Mise à jour des informations utilisateur
$_POST = [
    'pseudo' => 'testuser1', // Utilisateur existant
    'password' => 'StrongPass123!',
    'nouveau_nom' => 'UpdatedUser',
    'nouvel_email' => 'updateduser@example.com'
];

// Mise à jour du nom et de l'email
$sql = "UPDATE `utilisateur` SET `nom` = :nouveau_nom, `email` = :nouvel_email WHERE `pseudo` = :pseudo";
$stmt = $pdo->prepare($sql);
$stmt->execute([
    ':nouveau_nom' => $_POST['nouveau_nom'],
    ':nouvel_email' => $_POST['nouvel_email'],
    ':pseudo' => $_POST['pseudo']
]);

// Vérification de la mise à jour
$sql = "SELECT `nom`, `email` FROM `utilisateur` WHERE `pseudo` = :pseudo";
$stmt = $pdo->prepare($sql);
$stmt->execute([':pseudo' => $_POST['pseudo']]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

assert($row['nom'] === $_POST['nouveau_nom'], "Test 7 Failed: Nom non mis à jour !");
assert($row['email'] === $_POST['nouvel_email'], "Test 7 Failed: Email non mis à jour !");
echo "Test 7 Passed: Mise à jour des informations réussie !\n";

// TEST 8 - Suppression d'un utilisateur
$sql = "DELETE FROM `utilisateur` WHERE `pseudo` = :pseudo";
$stmt = $pdo->prepare($sql);
$stmt->execute([':pseudo' => 'testuser1']);

// Vérification de la suppression
$sql = "SELECT * FROM `utilisateur` WHERE `pseudo` = :pseudo";
$stmt = $pdo->prepare($sql);
$stmt->execute([':pseudo' => 'testuser1']);
assert($stmt->rowCount() === 0, "Test 8 Failed: Utilisateur non supprimé !");
echo "Test 8 Passed: Suppression de l'utilisateur réussie !\n";

// TEST 9 - Connexion avec un mot de passe incorrect
$login_success = validate('testuser1', 'WrongPass123!', $pdo);
assert(!$login_success, "Test 9 Failed: Connexion réussie avec un mot de passe incorrect !");
echo "Test 9 Passed: Connexion échouée avec un mot de passe incorrect !\n";

// TEST 10 - Injection SQL avancée
$_POST = [
    'pseudo' => "'; DROP TABLE `utilisateur`; --", // Injection SQL dangereuse
    'password' => 'Injection123!',
    'email' => 'sqlinjection@example.com',
    'prenom' => 'SQL',
    'nom' => 'Injection',
    'regst' => true
];

// Appel de la fonction d'inscription
$result = register_user(
    $_POST['pseudo'],
    $_POST['password'],
    $_POST['email'],
    $_POST['prenom'],
    $_POST['nom'],
    $pdo
);

// Vérification que l'injection SQL est bloquée
$sql = "SHOW TABLES LIKE 'utilisateur'";
$stmt = $pdo->query($sql);
assert($stmt->rowCount() === 1, "Test 10 Failed: Injection SQL avancée a réussi !");
echo "Test 10 Passed: Injection SQL avancée bloquée !\n";

// TEST 11 - Protection des sessions
// Vérifier si une session est déjà active avant d'appeler session_start()
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
$_SESSION['pseudo'] = 'testuser1';
$_SESSION['role'] = 'Utilisateur';

// Simuler un détournement de session en modifiant l'ID de session
$original_session_id = session_id();
session_regenerate_id(); // Regénération de l'ID de session
assert($original_session_id !== session_id(), "Test 11 Failed: L'ID de session n'a pas été régénéré !");
echo "Test 11 Passed: Sécurisation de session activée !\n";

// TEST 13 - Vérification du hachage des mots de passe
$_POST = [
    'pseudo' => 'hashuser',
    'password' => 'HashPass123!',
    'email' => 'hashuser@example.com',
    'prenom' => 'Hash',
    'nom' => 'User',
    'regst' => true
];

// Appel de la fonction d'inscription
$result = register_user(
    $_POST['pseudo'],
    $_POST['password'],
    $_POST['email'],
    $_POST['prenom'],
    $_POST['nom'],
    $pdo
);

// Vérification du résultat de l'inscription
assert($result === "Inscription réussie.", "Test 13 Failed: L'utilisateur Hash n'a pas été inscrit !");
echo "Test 13 Passed: Utilisateur Hash inscrit avec succès !\n";

// Récupération du mot de passe stocké
$sql = "SELECT `password` FROM `utilisateur` WHERE `pseudo` = :pseudo";
$stmt = $pdo->prepare($sql);
$stmt->execute([':pseudo' => 'hashuser']);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

// Vérification que le mot de passe n'est pas stocké en clair
assert($row['password'] !== 'HashPass123!', "Test 13 Failed: Le mot de passe est stocké en clair !");
echo "Test 13 Passed: Le mot de passe est correctement haché !\n";

// Vérification que le mot de passe est haché avec bcrypt (ou autre algorithme sécurisé)
assert(password_verify('HashPass123!', $row['password']), "Test 13 Failed: Le mot de passe haché ne correspond pas !");
echo "Test 13 Passed: Le mot de passe haché est valide !\n";

// TEST 14   - Validation des données lors de la mise à jour
$_POST = [
    'pseudo' => 'testuser_update',
    'password' => 'StrongPass123!',
    'nouveau_nom' => '<b>UpdatedUser</b>', // Tentative d'injection HTML
    'nouvel_email' => 'updateduser@example.com'
];

// Inscription de l'utilisateur pour le test
register_user(
    $_POST['pseudo'],
    $_POST['password'],
    'initialemail@example.com',
    'Initial',
    'User',
    $pdo
);

// Mise à jour avec des données potentiellement malveillantes
$sql = "UPDATE `utilisateur` SET `nom` = :nouveau_nom, `email` = :nouvel_email WHERE `pseudo` = :pseudo";
$stmt = $pdo->prepare($sql);
$stmt->execute([
    ':nouveau_nom' => htmlspecialchars($_POST['nouveau_nom'], ENT_QUOTES, 'UTF-8'),
    ':nouvel_email' => $_POST['nouvel_email'],
    ':pseudo' => $_POST['pseudo']
]);

// Vérification que les balises HTML sont échappées
$sql = "SELECT `nom` FROM `utilisateur` WHERE `pseudo` = :pseudo";
$stmt = $pdo->prepare($sql);
$stmt->execute([':pseudo' => 'testuser_update']);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

assert($row['nom'] === '&lt;b&gt;UpdatedUser&lt;/b&gt;', "Test 14 Failed: Injection HTML dans la mise à jour acceptée !");
echo "Test 14 Passed: Validation des données lors de la mise à jour !\n";

// TEST 15 - Injection SQL via la mise à jour de l'utilisateur
$_POST = [
    'pseudo' => 'sqluser1',
    'password' => 'StrongPass123!',
    'email' => 'sqluser1@example.com',
    'prenom' => 'SQL',
    'nom' => 'User',
    'regst' => true
];

// Inscription de l'utilisateur pour le test
$result = register_user(
    $_POST['pseudo'],
    $_POST['password'],
    $_POST['email'],
    $_POST['prenom'],
    $_POST['nom'],
    $pdo
);
assert($result === "Inscription réussie.", "Test 15 Failed: L'utilisateur SQL n'a pas été inscrit !");
echo "Test 15 Passed: Utilisateur SQL inscrit avec succès !\n";

// Injection via le champ 'nouveau_nom' lors de la mise à jour
$_POST = [
    'pseudo' => 'sqluser1',
    'password' => 'StrongPass123!',
    'nouveau_nom' => "UpdatedUser', email='hacker@example.com", // Tentative d'injection
    'nouvel_email' => 'updatedsqluser1@example.com'
];

// Mise à jour de l'utilisateur avec une tentative d'injection
$sql = "UPDATE `utilisateur` SET `nom` = :nouveau_nom, `email` = :nouvel_email WHERE `pseudo` = :pseudo";
$stmt = $pdo->prepare($sql);
$stmt->execute([
    ':nouveau_nom' => $_POST['nouveau_nom'],
    ':nouvel_email' => $_POST['nouvel_email'],
    ':pseudo' => $_POST['pseudo']
]);

// Vérification que l'injection n'a pas réussi à modifier l'email
$sql = "SELECT `nom`, `email` FROM `utilisateur` WHERE `pseudo` = :pseudo";
$stmt = $pdo->prepare($sql);
$stmt->execute([':pseudo' => 'sqluser1']);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

// Vérification que les champs ont été mis à jour correctement et que l'injection a échoué
assert($row['nom'] === "UpdatedUser', email='hacker@example.com", "Test 15 Failed: Injection SQL lors de la mise à jour a réussi !");
assert($row['email'] === 'updatedsqluser1@example.com', "Test 15 Failed: Email non mis à jour correctement !");
echo "Test 15 Passed: Injection SQL lors de la mise à jour bloquée et données mises à jour correctement !\n";

// TEST 16 - Injection SQL via le formulaire de suppression
$_POST = [
    'pseudo' => 'sqluser2',
    'password' => 'StrongPass123!',
    'email' => 'sqluser2@example.com',
    'prenom' => 'SQL',
    'nom' => 'User',
    'regst' => true
];

// Inscription de l'utilisateur pour le test
$result = register_user(
    $_POST['pseudo'],
    $_POST['password'],
    $_POST['email'],
    $_POST['prenom'],
    $_POST['nom'],
    $pdo
);
assert($result === "Inscription réussie.", "Test 16 Failed: L'utilisateur SQL2 n'a pas été inscrit !");
echo "Test 16 Passed: Utilisateur SQL2 inscrit avec succès !\n";

// Suppression avec une tentative d'injection via le pseudo
$pseudo_injection = "sqluser2' OR '1'='1";
$sql = "DELETE FROM `utilisateur` WHERE `pseudo` = :pseudo";
$stmt = $pdo->prepare($sql);
$stmt->execute([':pseudo' => $pseudo_injection]);

// Vérification que l'utilisateur SQL2 n'a pas été supprimé
$sql = "SELECT * FROM `utilisateur` WHERE `pseudo` = :pseudo";
$stmt = $pdo->prepare($sql);
$stmt->execute([':pseudo' => 'sqluser2']);
assert($stmt->rowCount() === 1, "Test 16 Failed: Injection SQL lors de la suppression a réussi !");
echo "Test 16 Passed: Injection SQL lors de la suppression bloquée et utilisateur non supprimé !\n";

// TEST 17 - Injection SQL via le login
$_POST = [
    'pseudo' => "sqluser3' OR '1'='1",
    'password' => 'AnyPassword',
    'lgn' => true
];

// Appel de la fonction de connexion avec une injection SQL dans le pseudo
$login_success = validate($_POST['pseudo'], $_POST['password'], $pdo);

// Vérification que la connexion échoue
assert(!$login_success, "Test 17 Failed: Connexion réussie avec une injection SQL !");
echo "Test 17 Passed: Connexion échouée avec une injection SQL dans le pseudo !\n";

// TEST 18 - Injection SQL via l'inscription multiple
for ($i = 1; $i <= 5; $i++) {
    $_POST = [
        'pseudo' => "sqluser_injection_$i'; DROP TABLE `utilisateur`; --",
        'password' => 'InjectionPass123!',
        'email' => "sqlinjection$i@example.com",
        'prenom' => 'SQL',
        'nom' => "Injection$i",
        'regst' => true
    ];
    
    // Tentative d'inscription avec une injection SQL dans le pseudo
    $result = register_user(
        $_POST['pseudo'],
        $_POST['password'],
        $_POST['email'],
        $_POST['prenom'],
        $_POST['nom'],
        $pdo
    );
    
    // Vérification que l'injection SQL a été bloquée et que l'utilisateur n'a pas été créé
    assert($result === "Erreur : Le pseudo contient des caractères invalides ou est trop court." || 
           $result === "Erreur : Le pseudo est déjà pris." || 
           $result === "Erreur : Tous les champs doivent être remplis.",
           "Test 18 Failed: Injection SQL lors de l'inscription multiple a réussi !");
    echo "Test 18 Passed: Injection SQL lors de l'inscription multiple bloquée pour l'utilisateur sqluser_injection_$i !\n";
}


// TEST 19 - Prévention des attaques XSS lors de l'inscription
$_POST = [
    'pseudo' => '<script>alert("XSS")</script>',
    'password' => 'SecurePass123!',
    'email' => 'xssuser@example.com',
    'prenom' => 'XSS',
    'nom' => 'User',
    'regst' => true
];

// Appel de la fonction d'inscription
$result = register_user(
    $_POST['pseudo'],
    $_POST['password'],
    $_POST['email'],
    $_POST['prenom'],
    $_POST['nom'],
    $pdo
);

// Vérification que les balises HTML sont rejetées ou échappées
assert($result === "Erreur : Le pseudo contient des caractères invalides ou est trop court.", "Test 19 Failed: XSS dans le pseudo accepté !");
echo "Test 19 Passed: Prévention des attaques XSS lors de l'inscription !\n";

// TEST 20 - Vérification de la protection contre la manipulation des champs cachés
$_POST = [
    'pseudo' => 'hiddenfielduser',
    'password' => 'HiddenPass123!',
    'email' => 'hiddenfield@example.com',
    'prenom' => 'Hidden',
    'nom' => 'Field',
    'regst' => true,
    'role' => 'admin' // Champ caché que l'utilisateur tente de modifier
];

// Modification de la fonction d'inscription pour ignorer les champs non autorisés
function register_user_secure($pseudo, $password, $email, $prenom, $nom, $pdo) {
    // Ignorer le champ 'role' passé via POST
    return register_user($pseudo, $password, $email, $prenom, $nom, $pdo);
}

// Appel de la fonction d'inscription sécurisée
$result = register_user_secure(
    $_POST['pseudo'],
    $_POST['password'],
    $_POST['email'],
    $_POST['prenom'],
    $_POST['nom'],
    $pdo
);

// Vérification que l'inscription est réussie
assert($result === "Inscription réussie.", "Test 20 Failed: L'inscription a échoué !");
echo "Test 20 Passed: Utilisateur inscrit avec succès !\n";

// Vérification que le rôle n'est pas 'admin' mais 'Invite'
$sql = "SELECT `role` FROM `utilisateur` WHERE `pseudo` = :pseudo";
$stmt = $pdo->prepare($sql);
$stmt->execute([':pseudo' => 'hiddenfielduser']);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

// Assumer que le rôle par défaut est 'Invite'
assert($row['role'] === 'Invite', "Test 20 Failed: Champ 'role' non autorisé modifié via POST !");
echo "Test 20 Passed: Protection contre la manipulation des champs cachés vérifiée !\n";


// Fonction de nettoyage pour supprimer les utilisateurs de test
function cleanup($pdo) {
    $test_users = [
        'testuser1', 
        'invalidemailuser', 
        'weakpassworduser', 
        "' OR 1=1 --", 
        "'; DROP TABLE `utilisateur`; --",
        'hashuser', 
        'testuser_update',
        'deletetestuser',
        'adminuser',
        'sqluser1',
        'sqluser2',
        'sqluser3',
        'hiddenfielduser'
    ];
    foreach ($test_users as $pseudo) {
        $sql = "DELETE FROM `utilisateur` WHERE `pseudo` = :pseudo";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':pseudo' => $pseudo]);
    }
}

// Nettoyage à la fin de tous les tests
cleanup($pdo);

// Envoyer le contenu tamponné
ob_end_flush();
?>
