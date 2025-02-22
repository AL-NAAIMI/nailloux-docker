<?php
session_start();
include __DIR__ . '/../../backend/db/connection.php'; // Connexion à la base de données

// Variables pour afficher les résultats de recherche
$publication = [];
$search_query = '';

// Vérification si une recherche est effectuée
$search_query = filter_input(INPUT_GET, 'search_query', FILTER_SANITIZE_STRING) ?? '';

if (!empty($search_query)) {
    try {
        // Préparer la requête SQL pour rechercher dans la colonne mots_clés
        $stmt = $pdo->prepare("SELECT * FROM publication WHERE mots_clés LIKE :search_query");
        $stmt->execute(['search_query' => '%' . $search_query . '%']);
        $publication = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Erreur lors de la recherche : " . $e->getMessage());
        echo "Une erreur est survenue. Veuillez réessayer plus tard.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nailloux - About Us</title>
    <?php include('header.php'); // Inclusion de l'en-tête ?>
</head>
<body>

<?php
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

<!-- Formulaire de recherche -->
<div class="search-form-box">
    <form action="" method="GET" class="search-form">
        <input type="text" name="search_query" placeholder="Entrer un mot clé ..." required class="search-input" value="<?php echo htmlspecialchars($search_query, ENT_QUOTES, 'UTF-8'); ?>">
        <button type="submit" class="search-btn">
            <img src="../view/logo/1377.png" alt="Search">
        </button>
    </form>
</div>

<?php if ($search_query): ?>
    <div class="search-results">
        <?php if (count($publication) > 0): ?>
            <?php foreach ($publication as $pub): ?>
                <div class="publication">
                    <!-- Vérification si l'image existe -->
                    <?php if (!empty($pub['image'])): ?>
                        <div class="feed-post-display-box-image">
                            <img src="<?php echo "/upload/publication/" . htmlspecialchars($pub['image'], ENT_QUOTES, 'UTF-8'); ?>" 
                                alt="Image de la publication" class="thumbnail"
                                onclick="openLightbox('<?php echo "/upload/publication/" . htmlspecialchars($pub['image'], ENT_QUOTES, 'UTF-8'); ?>')">
                        </div>
                    <?php endif; ?>

                    <p><?php echo htmlspecialchars($pub['mots_clés'], ENT_QUOTES, 'UTF-8'); ?></p>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>Aucune publication trouvée pour les mots-clés "<?php echo htmlspecialchars($search_query, ENT_QUOTES, 'UTF-8'); ?>".</p>
        <?php endif; ?>
    </div>
<?php endif; ?>
<div id="lightbox" class="lightbox">
    <span class="close-btn" onclick="closeLightbox()">&times;</span>
    <img id="lightbox-img" class="lightbox-img">
</div>
<script>
function openLightbox(imageSrc) {
    document.getElementById("lightbox-img").src = imageSrc;
    document.getElementById("lightbox").style.display = "flex";
}

// Close the lightbox when clicking the "×" button
function closeLightbox() {
    document.getElementById("lightbox").style.display = "none";
}

// Close the lightbox when clicking outside the image
document.getElementById("lightbox").addEventListener("click", function(event) {
    if (event.target === this) {
        closeLightbox();
    }
});
</script>

</body>
</html>
