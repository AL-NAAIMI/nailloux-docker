<?php
// Démarre la session pour accéder aux données de session
session_start();

// Inclusion des fichiers nécessaires : connexion à la base de données et variables d'environnement
include __DIR__ . '/../db/connection.php';
include __DIR__ . '/../../back/env.php';


// Vérifie si l'utilisateur est connecté (pseudo présent dans la session)
if (!isset($_SESSION['pseudo'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized.']);
    exit();
}

// Récupère l'ID de l'utilisateur à partir de la session
$user_id = $_SESSION['id'];

// Vérifie si le rôle de l'utilisateur est défini dans la session
if (!isset($_SESSION['role'])) {
    $_SESSION['role'] = getUserRole($pdo, $_SESSION['pseudo']); // Utilisation de la fonction avec PDO
}

// Récupère l'ID du post à supprimer depuis la requête POST
$pid = $_POST['post_id'];

try {
    // Vérifie si le post existe dans la base de données
    $stmt_check = $pdo->prepare("SELECT * FROM publication WHERE pid = :pid");
    $stmt_check->execute([':pid' => $pid]);
    $post = $stmt_check->fetch(PDO::FETCH_ASSOC);

    if ($post) {
        // Vérifie si l'utilisateur est le propriétaire du post ou un administrateur
        if ($post['uid'] == $user_id || ($_SESSION['role'] === 'Administrateur')) {
            // Récupère le chemin de l'image associée au post
            $image_path = $post['image'];

            // Supprime l'image originale si elle existe
            $image_full_path = __DIR__ . '/../upload/publication/' . $image_path;
            if (!empty($image_path) && file_exists($image_full_path)) {
                unlink($image_full_path); // Supprime l'image du serveur
            }

            // Supprime l'image miniaturisée au format AVIF si elle existe
            $avif_image_path = __DIR__ . '/../upload/publication/' . pathinfo($image_path, PATHINFO_FILENAME) . '_mini.avif';
            if (file_exists($avif_image_path)) {
                unlink($avif_image_path); // Supprime l'image AVIF miniaturisée
            }

            // Supprime les commentaires associés au post
            $stmt_delete_comments = $pdo->prepare("DELETE FROM commentaire_p WHERE pid = :pid");
            $stmt_delete_comments->execute([':pid' => $pid]);

            // Supprime le post de la base de données
            $stmt_delete_post = $pdo->prepare("DELETE FROM publication WHERE pid = :pid");
            if ($stmt_delete_post->execute([':pid' => $pid])) {
                echo json_encode(['status' => 'success', 'message' => 'Post and associated comments deleted successfully']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to delete post']);
            }
        } else {
            // L'utilisateur n'a pas les permissions nécessaires
            echo json_encode(['status' => 'error', 'message' => 'You do not have permission to delete this post.']);
        }
    } else {
        // Le post n'existe pas dans la base de données
        echo json_encode(['status' => 'error', 'message' => 'Post not found.']);
    }
} catch (PDOException $e) {
    // Gestion des erreurs
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
