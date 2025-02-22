<?php
include __DIR__ . '/../../../backend/db/connection.php';
session_start();

// Vérifier si l'utilisateur est un administrateur
if ($_SESSION['role'] !== 'Administrateur') {
    die("Accès refusé. Vous n'êtes pas autorisé à lancer cet événement.");
}

// Vérifier si l'ID de l'événement est fourni
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("ID de l'événement manquant ou invalide.");
}

$eventId = (int)$_GET['id'];

try {
    // Récupérer toutes les photos pour cet événement
    $queryPhotos = "
        SELECT p.chemin_photo, u.pseudo 
        FROM photos_evenement p
        JOIN utilisateur u ON p.uid = u.id
        WHERE p.id_evenement = :id_evenement
    ";
    $stmt = $pdo->prepare($queryPhotos);
    $stmt->execute([':id_evenement' => $eventId]);
    $photos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($photos)) {
        die("Aucune photo disponible pour cet événement.");
    }

    // Mélanger les photos de manière aléatoire
    shuffle($photos);
} catch (PDOException $e) {
    die("Erreur lors de la récupération des données : " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Diaporama de l'Événement</title>
    <link rel="stylesheet" href="../style/slideshow.css">
    <script>
        // Données des photos et auteurs
        let photos = <?php echo json_encode($photos); ?>;
        let currentIndex = 0;

        // Afficher la diapositive (début, image, auteur ou fin)
        function showSlide(index) {
            const slideElement = document.getElementById("slideshow");
            const totalSlides = photos.length * 2 + 2; // Début, photos/auteurs, et fin
                
            if (index === 0) {
                // Diapositive de début
                slideElement.innerHTML = `
                    <div class="start-slide">
                        <h1>Bienvenue au Diaporama</h1>
                        <p>Préparez-vous à découvrir les photos !</p>
                    </div>`;
            } else if (index === totalSlides - 1) {
                // Diapositive de fin
                slideElement.innerHTML = `
                    <div class="end-slide">
                        <h1>Fin du Diaporama</h1>
                        <button onclick="goBack()">Retour</button>
                    </div>`;
            } else {
                // Alternance photo/auteur
                const photoIndex = Math.floor((index - 1) / 2);
                if (index % 2 === 1) {
                    // Diapositive impaire : photo
                    slideElement.innerHTML = `<img src="/upload/photos_evenement/${photos[photoIndex].chemin_photo}" alt="Diapositive" class="slideshow-image">`;
                } else {
                    // Diapositive paire : auteur
                    slideElement.innerHTML = `<div class="photo-author">Auteur : ${photos[photoIndex].pseudo}</div>`;
                }
            }
        
            // Ajouter les flèches de navigation (visuelles uniquement)
            slideElement.innerHTML += `
                <div class="nav-arrow left-arrow">&#10094;</div>
                <div class="nav-arrow right-arrow">&#10095;</div>
            `;

        }
        

        // Passer à la diapositive suivante
        function nextSlide() {
            currentIndex++;
            const totalSlides = photos.length * 2 + 2;
            if (currentIndex >= totalSlides) {
                currentIndex = 0; // Recommencer depuis le début
            }
            showSlide(currentIndex);
        }

        // Revenir à la diapositive précédente
        function prevSlide() {
            currentIndex--;
            const totalSlides = photos.length * 2 + 2;
            if (currentIndex < 0) {
                currentIndex = totalSlides - 1; // Aller à la dernière diapositive
            }
            showSlide(currentIndex);
        }

        // Gérer les clics pour naviguer
        function handleNavigation(event) {
            const screenWidth = window.innerWidth;
            if (event.clientX > screenWidth / 2) {
                nextSlide(); // Cliquez sur la partie droite
            } else {
                prevSlide(); // Cliquez sur la partie gauche
            }
        }

        // Fonction pour retourner à la page précédente
        function goBack() {
            window.history.back();
        }

        window.onload = () => {
            showSlide(currentIndex); // Afficher la première diapositive
            document.addEventListener("click", handleNavigation); // Ajouter le gestionnaire de clics
        };
    </script>
</head>
<body>
    <div id="slideshow" class="slideshow-container"></div>
</body>
</html>
