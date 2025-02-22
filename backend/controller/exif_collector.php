<?php
// exif_collector.php
header('Content-Type: application/json');
include __DIR__ . '/../../backend/db/connection.php'; // Ensure the correct path to the database connection

// Retrieve 'pid' from GET or POST
$pid_raw = isset($_GET['pid']) ? $_GET['pid'] : (isset($_POST['pid']) ? $_POST['pid'] : null);

// Validate 'pid'
if (!$pid_raw || !filter_var($pid_raw, FILTER_VALIDATE_INT, ["options" => ["min_range" => 1]])) {
    echo json_encode(['error' => 'Invalid or missing "pid" parameter.']);
    exit;
}

$pid = (int)$pid_raw;

try {
    // Fetch EXIF data from the database
    $stmt = $pdo->prepare("SELECT `format_image`, `mode_image`, `taille_image`, `donnees_exif`, `mots_clés` FROM `publication` WHERE `pid` = :pid LIMIT 1");
    $stmt->bindParam(":pid", $pid, PDO::PARAM_INT);
    $stmt->execute();
    $publication = $stmt->fetch(PDO::FETCH_ASSOC);

    // Check if publication exists
    if (!$publication) {
        echo json_encode(['error' => "No EXIF data found for ID $pid."]);
        exit;
    }

    // Decode the JSON-stored EXIF data
    $donnees_exif = json_decode($publication['donnees_exif'], true);

    // Handle invalid JSON
    if (json_last_error() !== JSON_ERROR_NONE) {
        $donnees_exif = [];
    }

    // Prepare the response
    $response = [
        'format_image' => $publication['format_image'] ?? 'Non disponible',
        'mode_image' => $publication['mode_image'] ?? 'Non disponible',
        'taille_image' => $publication['taille_image'] ?? 'Non disponible',
        'donnees_exif' => [
            'Make' => $donnees_exif['Make'] ?? 'Non disponible',
            'Model' => $donnees_exif['Model'] ?? 'Non disponible',
            'Software' => $donnees_exif['Software'] ?? 'Non disponible',
        ],
        'mots_clés' => $publication['mots_clés'] ?? 'Non disponible',
    ];

    echo json_encode($response);
    exit;
} catch (Exception $e) {
    echo json_encode(['error' => 'An error occurred while fetching EXIF data.', 'details' => $e->getMessage()]);
    exit;
}
?>
