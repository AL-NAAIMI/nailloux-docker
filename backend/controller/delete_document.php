<?php
// Démarre la session pour pouvoir accéder aux données de session si nécessaire
session_start();

// Inclusion de la connexion à la base de données
require __DIR__ . '/../db/connection.php';// Assurez-vous que le fichier connection.php contient les informations nécessaires pour se connecter à la base de données

// Vérification que la requête est de type POST et que l'ID du document est fourni
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['document_id'])) {
    // Récupération de l'ID du document à supprimer depuis la requête POST
    $documentId = $_POST['document_id'];

    // Étape 1 : Récupérer le chemin du document à partir de la base de données
     $stmt = $pdo->prepare("SELECT chemin FROM document WHERE id_document = :documentId");
    $stmt->bindParam(':documentId', $documentId, PDO::PARAM_INT); // Utilisation de bindParam avec le bon type
    $stmt->execute(); // Exécution de la requête

    // Récupérer le résultat de la requête
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    // Si le document existe dans la base de données
    if ($result) {
        $cheminDocument = $result['chemin']; // Chemin du fichier à supprimer

        // Étape 2 : Construire le chemin absolu pour la suppression
        // Assurez-vous que le chemin est correct pour la suppression
        $cheminAbsolu = $cheminDocument; // Chemin absolu du fichier (le chemin doit être valide)

        // Vérification si le fichier existe sur le serveur
        if (file_exists($cheminAbsolu)) {
            // Si le fichier existe, essayer de le supprimer
            if (unlink($cheminAbsolu)) {
                // Étape 3 : Si la suppression du fichier réussit, supprimer l'enregistrement de la base de données
                $stmt = $pdo->prepare("DELETE FROM document WHERE id_document = :documentId");
                $stmt->bindParam(':documentId', $documentId, PDO::PARAM_INT);

                // Exécution de la requête de suppression de l'enregistrement de la base de données
                if ($stmt->execute()) {
                    // Si la suppression dans la base de données réussit
                    echo json_encode(['success' => true, 'message' => 'Document supprimé avec succès.']);
                } else {
                    // Si la suppression dans la base de données échoue
                    echo json_encode(['success' => false, 'message' => 'Erreur lors de la suppression du document de la base de données.']);
                }
            } else {
                // Si l'échec de suppression du fichier
                echo json_encode(['success' => false, 'message' => 'Erreur lors de la suppression du fichier.']);
            }
        } else {
            // Si le fichier n'existe pas sur le serveur
            echo json_encode(['success' => false, 'message' => 'Le fichier n\'existe pas.']);
        }
    } else {
        // Si le document n'est pas trouvé dans la base de données
        echo json_encode(['success' => false, 'message' => 'Document non trouvé.']);
    }
} else {
    // Si aucun document n'est spécifié dans la requête POST
    echo json_encode(['success' => false, 'message' => 'Aucun document spécifié.']);
}
?>
