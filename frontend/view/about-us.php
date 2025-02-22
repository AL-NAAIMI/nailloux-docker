<?php
session_start();
include __DIR__ . '/../../back/env.php';
include __DIR__ . '/../../backend/db/connection.php';
include __DIR__ . '/../../backend/sql/utilisateur.php';
?>

<html>
<title>Nailloux - About Us</title>
<?php include('header.php');

// Gestion de l'onglet actif
$tab = isset($_GET['tab']) ? $_GET['tab'] : 'about-us';

// Récupération des informations de l'utilisateur
$pseudo = $_SESSION['pseudo'] ?? null; 
$role = null;


if ($pseudo) {
    try {
        $stmt = $pdo->prepare("SELECT `role` FROM `utilisateur` WHERE `pseudo` = :pseudo LIMIT 1");
        $stmt->bindParam(":pseudo", $pseudo, PDO::PARAM_STR);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        $role = $user['role'] ?? null;
    } catch (PDOException $e) {
        echo "Erreur : " . $e->getMessage();
    }
}
?>
<br><br><br>
<div class="seperate_header"></div>

<!-- Navigation des onglets -->
<div class="account-tabs">
    <ul>
        <li class="acc-tabs-item">
            <a href="about-us.php?tab=about-us" class="acc-tabs-link <?php echo ($tab == 'about-us') ? 'active' : ''; ?>">Notre club</a>
        </li>
        <?php if ($role === 'Administrateur' || $role === 'Membre'): ?>
            <li class="acc-tabs-item">
                <a href="about-us.php?tab=event" class="acc-tabs-link <?php echo ($tab == 'event') ? 'active' : ''; ?>">Événements</a>
            </li>
            <li class="acc-tabs-item">
                <a href="about-us.php?tab=file" class="acc-tabs-link <?php echo ($tab == 'file') ? 'active' : ''; ?>">Documents</a>
            </li>
        <?php endif; ?>
    </ul>
</div>

 
<div class="acc-tabs-page">
    <?php include __DIR__ . '/../../backend/controller/about-us_gestion_des_onglet.php'; ?>
</div>

</body>
</html>
<?php include('footer.php');
