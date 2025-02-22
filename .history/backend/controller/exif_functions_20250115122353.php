<?php
include __DIR__ . '/../../backend/db/connection.php';

/**
 * Collecte les métadonnées EXIF d'une image et les sauvegarde dans un fichier texte.
 *
 * @param string $image_path Chemin complet de l'image à analyser
 * @param int    $post_id    Identifiant de la publication
 * @return array             Retourne un tableau associatif contenant les métadonnées ou une erreur
 */
function collecterExif($image_path, $post_id) {
    $upload_dir = __DIR__ . '/../../upload/exif/';

    // Vérifie si le dossier d'upload existe et est accessible
    if (!is_dir($upload_dir) || !is_writable($upload_dir)) {
        return ['error' => "Problème d'accès au dossier EXIF : $upload_dir"];
    }

    $exif_file_path = $upload_dir . 'publication_' . $post_id . '_exif.txt';

    // Vérifie si le fichier d'image existe
    if (!file_exists($image_path)) {
        return ['error' => "L'image spécifiée est introuvable : $image_path"];
    }

    // Vérifie le type de l'image (JPEG ou PNG uniquement)
    $image_type = @exif_imagetype($image_path);
    if (!in_array($image_type, [IMAGETYPE_JPEG, IMAGETYPE_PNG])) {
        return ['error' => "Type d'image non supporté. Seuls JPEG et PNG sont autorisés."];
    }

    // Vérifie le script Python
    $python_executable = 'python3';
    $script_path = __DIR__ . "/extract_metadata.py";
    if (!file_exists($script_path)) {
        return ['error' => "Le script Python est introuvable : $script_path"];
    }

    // Exécute le script Python
    $command = escapeshellcmd($python_executable) . ' ' . escapeshellarg($script_path) . ' ' . escapeshellarg($image_path) . ' 2>&1';
    $output = shell_exec($command);

    if (empty($output)) {
        return ['error' => "Le script Python n'a retourné aucune donnée. Vérifiez l'image et le script."];
    }

    // Analyse les données retournées par le script Python
    $metadata = json_decode($output, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log("Erreur JSON pour l'image $image_path : " . json_last_error_msg());
        return ['error' => "Les métadonnées EXIF retournées par Python sont invalides."];
    }

    // Écrit les métadonnées dans un fichier texte
    if (file_put_contents($exif_file_path, json_encode($metadata, JSON_PRETTY_PRINT)) === false) {
        return ['error' => "Impossible d'écrire les métadonnées dans le fichier : $exif_file_path"];
    }

    return $metadata;
}

/**
 * Sauvegarde les métadonnées EXIF dans la base de données.
 *
 * @param array  $metadata Métadonnées EXIF extraites
 * @param int    $post_id  Identifiant de la publication
 * @param object $pdo      Instance PDO pour la connexion à la base de données
 */
function saveExifToDatabase($metadata, $post_id, $pdo) {
    $donnees_exif = json_encode($metadata['EXIF Data'] ?? 'Non disponible');
    if ($donnees_exif === false) {
        $donnees_exif = json_encode(['error' => 'Invalid EXIF data']);
    }

    $sql = "UPDATE `publication` 
            SET `format_image` = :format_image, 
                `mode_image` = :mode_image, 
                `taille_image` = :taille_image, 
                `donnees_exif` = :donnees_exif
            WHERE `pid` = :pid";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':format_image' => $metadata['Image Format'] ?? 'Non disponible',
        ':mode_image' => $metadata['Image Mode'] ?? 'Non disponible',
        ':taille_image' => isset($metadata['Image Size']) ? implode(' x ', $metadata['Image Size']) : 'Non disponible',
        ':donnees_exif' => $donnees_exif,
        ':pid' => $post_id
    ]);
}
?>
