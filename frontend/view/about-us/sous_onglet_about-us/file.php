<?php
// Only start a session if one isn't already active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Vérification de l'accès en fonction du rôle
if (!isset($_SESSION['pseudo']) || !isset($_SESSION['role']) || ($_SESSION['role'] !== 'Administrateur' && $_SESSION['role'] !== 'Membre')) {
    echo "<script>window.location.href = '/backend/index.php';</script>"; // Redirige sans utiliser header
    exit();
}

include('../../backend/controller/about-us_upload_document.php');
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Déposer un Document</title>
    <link rel="stylesheet" href="styles.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

<div class="separate_header"></div>
<br><br><br><br>

<div class="upload-section">
    <button class="open-modal-btn" onclick="openModal()">Déposer un document</button>
</div>

<div id="uploadModal" class="modal">
    <div class="modal-content">
        <span class="close-btn" onclick="closeModal()" aria-label="Fermer">&times;</span>
        <h2>Déposer un document</h2>
        <form id="uploadForm" action="" method="POST" enctype="multipart/form-data">
            <input type="file" name="document" required style="min-height: 70px;" onchange="updateNomDocument(this)" aria-label="Choisir un fichier">
            <input type="text" name="nom_document" id="nom_document" required placeholder="Nom du document" aria-label="Nom du document">
            <div style="display: flex; gap: 10px; justify-content: center; margin-top: 10px;">
                <button type="submit" class="upload-btn" onclick="confirmUpload(event)">Déposer</button>
                <button type="button" class="cancel-btn" onclick="closeModal()" style="background-color: #f44336; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer;">Retour</button>
            </div>
        </form>
    </div>
</div>

<div class="document-list-section">
    <h2>Liste des documents déposés</h2>
    <table border="1" cellpadding="10" cellspacing="0">
        <thead>
            <tr>
                <th>Nom du document</th>
                <th>Date de dépôt</th>
                <th>Chemin</th>
                <th>Utilisateur</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php 
                // Utilisation de PDO pour récupérer les documents
                $documents = fetchDocuments($pdo); // Fonction pour récupérer la liste des documents
                if (count($documents) > 0): 
            ?>
                <?php foreach ($documents as $doc): ?>
                    <?php $usrrow = fetchUserDetails($pdo, $doc['uid']); ?>
                    <tr>
                        <td><?php echo htmlspecialchars($doc['nom']); ?></td>
                        <td><?php echo htmlspecialchars($doc['date_depot']); ?></td>
                        <td><a href="<?php echo htmlspecialchars($doc['chemin']); ?>" target="_blank">Voir le document</a></td>
                        <td><?php echo htmlspecialchars($usrrow['pseudo'] . ' (' . $usrrow['prenom'] . ')'); ?></td>
                        <td>
                            <?php if ($doc['uid'] == $_SESSION['id'] || $_SESSION['role'] === 'Administrateur'): ?>
                                <form action="../../backend/controller/about-us_upload_document.php" method="POST" style="display: inline;">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id_document" value="<?php echo $doc['id_doccument']; ?>">
                                    <button type="submit" class="delete-btn" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce document ?')">Supprimer</button>
                                </form>
                            <?php else: ?>
                                <span>Non autorisé</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5">Aucun document déposé pour le moment.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<script>
    function openModal() {
        document.getElementById('uploadModal').style.display = 'block';
    }

    function closeModal() {
        document.getElementById('uploadModal').style.display = 'none';
    }

    function updateNomDocument(input) {
        var nomFichier = input.files[0].name;
        var nomSansExtension = nomFichier.substring(0, nomFichier.lastIndexOf('.'));
        document.getElementById('nom_document').value = nomSansExtension;
    }

    function confirmUpload(event) {
        event.preventDefault();

        const fileInput = document.querySelector('input[name="document"]');

        if (!fileInput.files.length) {
            Swal.fire({
                title: 'Erreur',
                text: "Veuillez sélectionner un fichier à déposer.",
                icon: 'error'
            });
            return;
        }

        Swal.fire({
            title: 'Êtes-vous sûr ?',
            text: "Voulez-vous vraiment déposer ce document ?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Oui, déposer!',
            cancelButtonText: 'Non, annuler'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('uploadForm').submit();
            }
        });
    }
</script>

</body>
</html>
