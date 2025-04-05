<?php
include __DIR__ . '/../db/connection.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Fonction pour redimensionner une image
function resizeImage($filePath, $maxWidth, $maxHeight) {
    list($originalWidth, $originalHeight, $imageType) = getimagesize($filePath);
    $ratio = $originalWidth / $originalHeight;

    if ($originalWidth > $maxWidth || $originalHeight > $maxHeight) {
        if ($ratio > 1) {
            $newWidth = $maxWidth;
            $newHeight = $maxWidth / $ratio;
        } else {
            $newHeight = $maxHeight;
            $newWidth = $maxHeight * $ratio;
        }

        $imageResized = imagecreatetruecolor($newWidth, $newHeight);

        switch ($imageType) {
            case IMAGETYPE_JPEG:
                $imageOriginal = imagecreatefromjpeg($filePath);
                break;
            case IMAGETYPE_PNG:
                $imageOriginal = imagecreatefrompng($filePath);
                imagealphablending($imageResized, false);
                imagesavealpha($imageResized, true);
                break;
            case IMAGETYPE_GIF:
                $imageOriginal = imagecreatefromgif($filePath);
                break;
            default:
                return false;
        }

        imagecopyresampled($imageResized, $imageOriginal, 0, 0, 0, 0, $newWidth, $newHeight, $originalWidth, $originalHeight);

        switch ($imageType) {
            case IMAGETYPE_JPEG:
                imagejpeg($imageResized, $filePath, 85);
                break;
            case IMAGETYPE_PNG:
                imagepng($imageResized, $filePath, 6);
                break;
            case IMAGETYPE_GIF:
                imagegif($imageResized, $filePath);
                break;
        }

        imagedestroy($imageOriginal);
        imagedestroy($imageResized);
    }

    return true;
}

// Vérification de l'envoi du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $eventId = $_POST['id_evenement'] ?? null;
    $userId = $_SESSION['id'] ?? null;

    if (!$userId) {
        header("Location: /frontend/view/about-us/event_details.php?id=$eventId&error=Vous devez être connecté pour déposer des photos.");
        exit;
    }
    // Vérifier si l'utilisateur est inscrit à l'événement
    $checkInscriptionReq = "SELECT * FROM evenement_participants WHERE id_evenement = :id_evenement AND uid = :id_utilisateur";
    $checkInscriptionStmt = $pdo->prepare($checkInscriptionReq);
    $checkInscriptionStmt->execute([
        ':id_evenement' => $eventId,
        ':id_utilisateur' => $userId
    ]);
    $estInscrit = $checkInscriptionStmt->rowCount() > 0;

    if (!$estInscrit) {
        header("Location: /frontend/view/about-us/event_details.php?id=$eventId&error=Vous devez être inscrit à cet événement pour téléverser des photos.");
        exit;
    }

    if (!$eventId || !is_numeric($eventId)) {
        header("Location: /frontend/view/about-us/event_details.php?error=ID d'événement invalide.");
        exit;
    }

    if (!isset($_FILES['photos']) || empty($_FILES['photos']['name'][0])) {
        header("Location: /frontend/view/about-us/event_details.php?id=$eventId&error=Aucun fichier sélectionné.");
        exit;
    }

    // Contraintes
    $maxFiles = 10;
    $maxSize = 5 * 1024 * 1024; // 5 Mo
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];

    // Vérification du nombre de photos déjà uploadées
    $query = "SELECT COUNT(*) as total_photos FROM photos_evenement WHERE id_evenement = :id_evenement AND uid = :uid";
    $stmt = $pdo->prepare($query);
    $stmt->execute([':id_evenement' => $eventId, ':uid' => $userId]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $currentPhotoCount = (int) $result['total_photos'];

    if ($currentPhotoCount >= $maxFiles) {
        header("Location: /frontend/view/about-us/event_details.php?id=$eventId&error=Vous avez déjà atteint la limite de 10 photos.");
        exit;
    }

    $files = $_FILES['photos'];
    $uploadedCount = 0;

    for ($i = 0; $i < count($files['name']); $i++) {
        if ($currentPhotoCount + $uploadedCount >= $maxFiles) {
            break;
        }

        $fileName = $files['name'][$i];
        $fileTmp = $files['tmp_name'][$i];
        $fileSize = $files['size'][$i];
        $fileType = $files['type'][$i];
        $fileError = $files['error'][$i];

        if ($fileError !== UPLOAD_ERR_OK) {
            continue;
        }
        if ($fileSize > $maxSize) {
            continue;
        }
        if (!in_array($fileType, $allowedTypes)) {
            continue;
        }

        $uploadDir = __DIR__ . '/../../upload/photos_evenement/';
        $fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);
        $uniqueName = uniqid('photo_', true) . '.' . $fileExtension;
        $uploadPath = $uploadDir . $uniqueName;

        if (move_uploaded_file($fileTmp, $uploadPath)) {
            resizeImage($uploadPath, 1024, 1024);

            $query = "INSERT INTO photos_evenement (id_evenement, uid, chemin_photo) VALUES (:id_evenement, :uid, :chemin_photo)";
            $stmt = $pdo->prepare($query);
            $stmt->execute([
                ':id_evenement' => $eventId,
                ':uid' => $userId,
                ':chemin_photo' => $uniqueName,
            ]);
            $uploadedCount++;
        }
    }

    header("Location: /frontend/view/about-us/event_details.php?id=$eventId&success=$uploadedCount photo(s) téléversée(s) avec succès.");
    exit;
} else {
    header("Location: /frontend/view/about-us/event_details.php?error=Requête invalide.");
    exit;
}
