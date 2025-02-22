<?php
// -------------------------------------------------------
// Fichier : update_profile_picture.php
// Description :
// Ce fichier gère le téléchargement, le redimensionnement et la mise à jour de la photo de profil d'un utilisateur.
// L'utilisateur peut envoyer une image via un formulaire. Cette image est ensuite vérifiée, redimensionnée à une taille de 128x128,
// et sauvegardée dans un dossier spécifique. Ensuite, l'URL de la photo est mise à jour dans la base de données de l'utilisateur.
// -------------------------------------------------------

// Démarre la session pour accéder aux variables de session
session_start();

// Inclusion du fichier de connexion à la base de données
include __DIR__ . '/../db/connection.php'; // Inclure la connexion à la base de données

// Vérifie que la méthode de la requête est POST (ce qui signifie que l'utilisateur a soumis un formulaire)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Récupérer les données envoyées depuis le formulaire
    $pseudo = $_POST['pseudo']; // Pseudo de l'utilisateur
    $user_id = $_POST['user_id']; // ID de l'utilisateur (pour lier l'image à cet utilisateur)

    // Dossier où l'image sera stockée
    $targetDir = "../upload/pp/"; // Dossier de stockage des photos de profil
    $fileExtension = strtolower(pathinfo($_FILES["photo_profil"]["name"], PATHINFO_EXTENSION)); // Extension du fichier envoyé

    // Crée un nom de fichier unique basé sur l'ID de l'utilisateur
    $targetFile = "../".$targetDir . $user_id . '.' . $fileExtension;

    // Si un fichier existe déjà, le supprimer pour le remplacer
    if (file_exists($targetFile)) {
        unlink($targetFile); // Supprime le fichier existant
    }

    $uploadOk = 1; // Variable de contrôle pour vérifier si le téléchargement est autorisé

    // Vérifie si le fichier est bien une image
    $check = getimagesize($_FILES["photo_profil"]["tmp_name"]);
    if ($check === false) {
        echo "Ce n'est pas une image."; // Message d'erreur si le fichier n'est pas une image
        $uploadOk = 0;
    }

    // Vérifie la taille du fichier (ici limite à 5 Mo)
    if ($_FILES["photo_profil"]["size"] > 5000000) {
        echo "Désolé, votre fichier est trop gros."; // Message d'erreur si l'image est trop grande
        $uploadOk = 0;
    }

    // Autorise certains types d'extensions pour l'image
    if (!in_array($fileExtension, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
        echo "Désolé, seuls les fichiers JPG, JPEG, PNG, GIF & WebP sont autorisés."; // Message d'erreur pour les extensions non autorisées
        $uploadOk = 0;
    }

    // Si une erreur est survenue, arrêter le processus
    if ($uploadOk == 0) {
        echo "Désolé, votre fichier n'a pas été téléchargé."; // Message d'erreur générique
    } else {
        // Fonction pour redimensionner et recadrer l'image à 128x128 pixels
        function resizeAndCropImage($sourcePath, $destinationPath, $targetWidth, $targetHeight, $fileExtension) {
            // Charger l'image selon son format (JPEG, PNG, GIF, WebP)
            switch($fileExtension) {
                case 'jpg':
                case 'jpeg':
                    $srcImage = imagecreatefromjpeg($sourcePath);
                    break;
                case 'png':
                    $srcImage = imagecreatefrompng($sourcePath);
                    break;
                case 'gif':
                    $srcImage = imagecreatefromgif($sourcePath);
                    break;
                case 'webp':
                    $srcImage = imagecreatefromwebp($sourcePath);
                    break;
                default:
                    return false; // Format non pris en charge
            }

            if (!$srcImage) {
                return false; // Si l'image ne peut pas être chargée
            }

            // Obtenir la largeur et la hauteur de l'image d'origine
            $srcWidth = imagesx($srcImage);
            $srcHeight = imagesy($srcImage);

            // Calculer la taille du carré le plus grand possible à partir du centre de l'image
            $minSize = min($srcWidth, $srcHeight);

            // Calculer les coordonnées du recadrage (centré)
            $cropX = ($srcWidth - $minSize) / 2;
            $cropY = ($srcHeight - $minSize) / 2;

            // Créer une nouvelle image carrée recadrée
            $croppedImage = imagecreatetruecolor($minSize, $minSize);
            imagecopyresampled($croppedImage, $srcImage, 0, 0, $cropX, $cropY, $minSize, $minSize, $minSize, $minSize);

            // Redimensionner l'image recadrée à la taille cible (128x128 pixels)
            $resizedImage = imagecreatetruecolor($targetWidth, $targetHeight);
            imagecopyresampled($resizedImage, $croppedImage, 0, 0, 0, 0, $targetWidth, $targetHeight, $minSize, $minSize);

            // Sauvegarder l'image dans le dossier cible selon son format
            switch($fileExtension) {
                case 'jpg':
                case 'jpeg':
                    imagejpeg($resizedImage, $destinationPath);
                    break;
                case 'png':
                    imagepng($resizedImage, $destinationPath);
                    break;
                case 'gif':
                    imagegif($resizedImage, $destinationPath);
                    break;
                case 'webp':
                    imagewebp($resizedImage, $destinationPath);
                    break;
            }

            // Libération de la mémoire
            imagedestroy($srcImage);
            imagedestroy($croppedImage);
            imagedestroy($resizedImage);

            return true; // Succès
        }

        // Essayez de déplacer le fichier téléchargé dans le dossier cible
        if (move_uploaded_file($_FILES["photo_profil"]["tmp_name"], $targetFile)) {
            // Si l'image a été déplacée, redimensionner et recadrer l'image à 128x128
            if (resizeAndCropImage($targetFile, $targetFile, 128, 128, $fileExtension)) {
                // Mettre à jour l'URL de la photo de profil dans la base de données
                $sql = "UPDATE utilisateur SET photo_profil = :photo_profil WHERE id = :user_id";
                $stmt = $pdo->prepare($sql);

                // Lier les paramètres
                $stmt->bindParam(':photo_profil', $targetFile, PDO::PARAM_STR);
                $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);

                // Exécuter la requête
                if ($stmt->execute()) {
                    echo "La photo de profil a été mise à jour."; // Message de succès
                    // Redirige vers la page précédente
                    header('Location: ' . $_SERVER['HTTP_REFERER']);
                    exit(); // Terminer le script après la redirection
                } else {
                    echo "Erreur: La mise à jour de la photo a échoué."; // Erreur si la mise à jour échoue
                }
            } else {
                echo "Erreur lors du redimensionnement de l'image."; // Message d'erreur si le redimensionnement échoue
            }
        } else {
            echo "Désolé, il y a eu une erreur lors du téléchargement de votre fichier."; // Message d'erreur si le téléchargement échoue
        }
    }
}
?>
