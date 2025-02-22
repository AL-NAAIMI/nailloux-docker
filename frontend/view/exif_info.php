<?php
if (!isset($_GET['pid'])) {
    die("Paramètre 'pid' manquant.");
}

$pid = htmlspecialchars($_GET['pid']);
$exifFile = __DIR__ . "//upload/exif/publication_{$pid}_exif.txt";

if (!file_exists($exifFile)) {
    die("Fichier EXIF introuvable.");
}

// Lire le fichier EXIF
$exifData = [];
$file = fopen($exifFile, 'r');
if ($file) {
    while (($line = fgets($file)) !== false) {
        $parts = explode(':', $line, 2);
        if (count($parts) == 2) {
            $exifData[trim($parts[0])] = trim($parts[1]);
        }
    }
    fclose($file);
} else {
    die("Erreur lors de la lecture du fichier EXIF.");
}

// Inclure les mots-clés depuis la base de données
require_once __DIR__ . '/../../backend/db/connection.php';
$stmt = $pdo->prepare("SELECT `mots_clés` FROM publication WHERE pid = :pid");
$stmt->bindParam(":pid", $pid, PDO::PARAM_INT);
$stmt->execute();
$motsCles = $stmt->fetchColumn();
$exifData["Mots-clés"] = $motsCles;
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Informations sur l'image</title>
    <link rel="stylesheet" href="../style/lighttheme_css/exif_popup.css">
</head>
<body>
    <div class="popup-container">
        <div class="popup-header">
            <h2>Informations sur l'image</h2>
            <button class="close-btn" onclick="window.close();">Fermer</button>
        </div>
        <div class="popup-content">
            <?php foreach ($exifData as $key => $value): ?>
                <p><strong><?php echo htmlspecialchars($key); ?>:</strong> <?php echo htmlspecialchars($value); ?></p>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>
