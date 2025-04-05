<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Inclure le fichier de connexion à la base de données
require_once(__DIR__ . '/../../backend/db/connection.php');

// Fichier : traitement_depot_document.php
// Description : Ce fichier traite le dépôt de documents par les utilisateurs en utilisant PDO.

// Types de fichiers autorisés
$typesAutorises = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'image/jpeg', 'image/png'];

// Vérification si une requête de suppression a été envoyée
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    if (isset($_POST['id_document']) && is_numeric($_POST['id_document'])) {
        $id_document = intval($_POST['id_document']);

        try {
            // Supprimer le document de la base de données
            $stmt = $pdo->prepare("DELETE FROM document WHERE id_doccument = :id_document");
            $stmt->execute([':id_document' => $id_document]);

            // Rediriger après suppression avec un message de succès
            echo "<script>alert('Document supprimé avec succès.'); window.location.href = '/frontend/view/about-us.php?tab=file';</script>";
            exit();
        } catch (PDOException $e) {
            // Gestion des erreurs PDO lors de la suppression
            echo "<script>alert('Erreur lors de la suppression du document : " . $e->getMessage() . "'); window.location.href = '/frontend/view/about-us.php?tab=file';</script>";
            exit();
        }
    } else {
        // Si l'ID du document n'est pas valide
        echo "<script>alert('ID du document invalide.'); window.location.href = '/frontend/view/about-us.php?tab=file';</script>";
        exit();
    }
}

// Vérification si le formulaire a été soumis en méthode POST et si un fichier a été téléchargé
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['document'])) {

    // Récupération des données du formulaire
    $nomDocument = trim($_POST['nom_document']);  // Nom modifié du document (entré par l'utilisateur)
    $fichierTmp = $_FILES['document']['tmp_name'];
    $fichierOriginal = $_FILES['document']['name'];  // Nom original du fichier téléchargé
    $chemin = '../../upload/file/' . basename($fichierOriginal);  // Chemin du fichier de destination
    
    // Assure-toi que la session est démarrée, ce qui rend $_SESSION['id'] disponible
    $uid = isset($_SESSION['id']) ? $_SESSION['id'] : 0; // ID de l'utilisateur connecté
    $dateDepot = date('Y-m-d H:i:s');  // Date et heure du dépôt du document

    // Vérification du type MIME du fichier
    $typeFichier = mime_content_type($fichierTmp);
    if (!in_array($typeFichier, $typesAutorises)) {
        echo "<script>alert('Type de fichier non autorisé. Seuls les PDF, Word, JPG et PNG sont acceptés.'); window.location.href = '/view/about-us.php?tab=file';</script>";
        exit();
    }

    try {
        // Vérification si un document avec le même nom existe déjà
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM document WHERE nom = :nom");
        $stmt->execute([':nom' => $nomDocument]);
        $count = $stmt->fetchColumn();

        if ($count > 0) {
            // Si un document avec le même nom existe, empêcher la création et afficher un message
            echo "<script>alert('Impossible de créer le fichier. Un document avec ce nom existe déjà.');</script>";
        } else {
            // Tentative de déplacement du fichier téléchargé vers le répertoire de destination
            if (move_uploaded_file($fichierTmp, $chemin)) {
                // Si le fichier est correctement déplacé, insertion des données dans la base de données
                $stmt = $pdo->prepare("INSERT INTO document (nom, chemin, date_depot, uid) VALUES (:nom, :chemin, :date_depot, :uid)");
                $stmt->execute([
                    ':nom' => $nomDocument,
                    ':chemin' => $chemin,
                    ':date_depot' => $dateDepot,
                    ':uid' => $uid
                ]);

                // Afficher une alerte de succès
                echo "<script>alert('Document téléchargé avec succès.');</script>";
            } else {
                // Si une erreur survient lors du téléchargement, afficher une alerte
                echo "<script>alert('Erreur lors du téléchargement du document.');</script>";
            }
        }

        // Redirection après traitement
        echo "<script>window.location.href = '" . $_SERVER['REQUEST_URI'] . "';</script>";
        exit();  // Fin du script après la redirection
    } catch (PDOException $e) {
        // Gestion des erreurs PDO
        echo "<script>alert('Erreur lors de l\'insertion dans la base de données : " . $e->getMessage() . "');</script>";
        echo "<script>window.location.href = '" . $_SERVER['REQUEST_URI'] . "';</script>";
        exit();
    }
}

// Fonction pour récupérer les documents depuis la base de données
if (!function_exists('fetchDocuments')) {
    // Récupération des documents depuis la base de données pour les afficher
    function fetchDocuments($pdo) {
        try {
            $stmt = $pdo->prepare("SELECT * FROM document ORDER BY date_depot DESC");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo "<script>alert('Erreur lors de la récupération des documents : " . $e->getMessage() . "');</script>";
            return [];
        }
    }
}

if (!function_exists('fetchUserDetails')) {
    function fetchUserDetails($pdo, $uid) {
        try {
            $stmt = $pdo->prepare("SELECT pseudo, prenom FROM utilisateur WHERE id = :uid");
            $stmt->bindParam(':uid', $uid, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: ['pseudo' => 'Inconnu', 'prenom' => ''];
        } catch (PDOException $e) {
            return ['pseudo' => 'Inconnu', 'prenom' => ''];
        }
    }
}
?>
