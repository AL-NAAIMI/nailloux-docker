// ********************************************************************
// Ce fichier contient des fonctionnalités JavaScript pour interagir
// avec l'interface utilisateur de la page. Il gère le menu mobile, 
// la fonctionnalité lightbox pour les images, la gestion des popups,
// le téléchargement de fichiers par glisser-déposer, ainsi que la gestion
// des publications et des commentaires (ajout, suppression, confirmation).
// ********************************************************************

// Gestion du bouton de menu pour les vues mobiles
const menuBTN = document.querySelector('.menu-btn'); // Le bouton du menu
const menuItems = document.querySelector('.menu-items'); // Les éléments du menu

// Fonction pour basculer l'état du bouton et afficher/masquer les éléments du menu
function toggleBtn() {
    menuBTN.classList.toggle("change"); // Ajoute ou retire la classe "change" au bouton du menu
    menuItems.classList.toggle("active"); // Ajoute ou retire la classe "active" aux éléments du menu
}

// Événement pour déclencher la fonction toggleBtn au clic sur le bouton du menu
menuBTN.addEventListener('click', toggleBtn);

// ********************************************************************
// Fonctionnalité Lightbox pour l'affichage d'une image en plein écran
// ********************************************************************

/**
 * Ouvre la lightbox avec l'image spécifiée en paramètre.
 * @param {string} imageSrc - L'URL de l'image à afficher dans la lightbox.
 */
function openLightbox(imageSrc) {
    var lightboxOverlay = document.getElementById('lightbox-overlay'); // L'overlay de la lightbox
    var lightboxImage = document.getElementById('lightbox-image'); // L'image dans la lightbox

    lightboxImage.src = imageSrc; // Définit la source de l'image
    lightboxOverlay.style.display = 'block'; // Affiche l'overlay
}

/**
 * Ferme la lightbox.
 */
function closeLightbox() {
    var lightboxOverlay = document.getElementById('lightbox-overlay'); // L'overlay de la lightbox
    lightboxOverlay.style.display = 'none'; // Cache l'overlay
}

// Événement au chargement du DOM
document.addEventListener("DOMContentLoaded", function() {
    var images = document.querySelectorAll('.feed-post-display-box-image img'); // Sélectionne toutes les images

    // Ajoute un événement pour ouvrir la lightbox lorsqu'une image est cliquée
    images.forEach(function(image) {
        image.addEventListener('click', function(event) {
            event.preventDefault(); // Empêche le comportement par défaut
            openLightbox(this.src); // Ouvre la lightbox avec la source de l'image
        });
    });
});

// ********************************************************************
// Gestion de la position de défilement lors de l'envoi d'un formulaire
// ********************************************************************

document.addEventListener('DOMContentLoaded', function() {
    // Vérifie si la position de défilement a été stockée dans localStorage
    if (localStorage.getItem('scrollPosition')) {
        window.scrollTo(0, localStorage.getItem('scrollPosition')); // Restaure la position de défilement
        localStorage.removeItem('scrollPosition'); // Supprime la position après l'avoir utilisée
    }

    // Écoute l'événement de soumission du formulaire
    document.querySelectorAll('.comment-form form').forEach(form => {
        form.addEventListener('submit', function() {
            // Stocke la position de défilement avant de soumettre le formulaire
            localStorage.setItem('scrollPosition', window.scrollY);
        });
    });
});

// ********************************************************************
// Fonctionnalité Popup (affichage et fermeture)
// ********************************************************************

/**
 * Affiche un popup.
 */
function openPopup() {
    document.getElementById("popup").style.display = "flex"; // Affiche la popup
}

/**
 * Ferme le popup.
 */
function closePopup() {
    document.getElementById('popup').style.display = 'none'; // Cache le popup
}

// Écouteur d'événements pour fermer le pop-up avec la touche Échap
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closePopup(); // Ferme le popup si la touche Échap est pressée
    }
});

// ********************************************************************
// Fonctionnalité de téléchargement de fichiers (glisser-déposer)
// ********************************************************************

const dropZone = document.getElementById('drop-zone'); // Zone de dépôt des fichiers
const fileInput = document.getElementById('file-input'); // Champ de sélection de fichiers
let isFileInputOpened = false; // Variable pour éviter l'ouverture multiple du sélecteur

// Ouvre le sélecteur de fichiers lorsqu'on clique sur la zone de dépôt
dropZone.addEventListener('click', () => {
    if (!isFileInputOpened) { // Vérifie si le sélecteur n'est pas déjà ouvert
        fileInput.click(); // Ouvre le sélecteur de fichiers
        isFileInputOpened = true; // Marque que le sélecteur est ouvert
    }
});

// Réinitialise la variable lorsque le sélecteur de fichiers est fermé
fileInput.addEventListener('change', () => {
    isFileInputOpened = false; // Réinitialise lorsque le fichier est sélectionné
});

// Lorsqu'un fichier est glissé dans la zone
dropZone.addEventListener('dragover', (event) => {
    event.preventDefault(); // Empêche le comportement par défaut
    dropZone.classList.add('dragover'); // Ajoute une classe pour styliser la zone
});

// Lorsqu'on quitte la zone de glisser-déposer
dropZone.addEventListener('dragleave', () => {
    dropZone.classList.remove('dragover'); // Retire la classe quand le fichier quitte la zone
});

// Lorsqu'un fichier est déposé dans la zone
dropZone.addEventListener('drop', (event) => {
    event.preventDefault(); // Empêche le comportement par défaut
    dropZone.classList.remove('dragover'); // Retire la classe de la zone

    const files = event.dataTransfer.files; // Récupère les fichiers déposés
    if (files.length > 0) {
        fileInput.files = files; // Assigne le fichier à l'input caché
        dropZone.querySelector('span').textContent = files[0].name; // Affiche le nom du fichier
    }
});

// Met à jour le texte si un fichier est sélectionné via le bouton
fileInput.addEventListener('change', () => {
    if (fileInput.files.length > 0) {
        dropZone.querySelector('span').textContent = fileInput.files[0].name; // Affiche le nom du fichier sélectionné
    }
});

// ********************************************************************
// Gestion de la suppression d'une publication
// ********************************************************************

/**
 * Demande confirmation avant de supprimer une publication.
 * @param {number} postId - L'ID de la publication à supprimer.
 */
function removePost(postId) {
    Swal.fire({
        title: 'Êtes-vous sûr ?',
        text: "Voulez-vous vraiment supprimer cette publication ?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Oui, supprimer !',
        cancelButtonText: 'Annuler'
    }).then((result) => {
        if (result.isConfirmed) {
            var xhr = new XMLHttpRequest();
            xhr.open("POST", "../../backend/controller/delete_post.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

            xhr.onreadystatechange = function () {
                if (xhr.readyState == 4 && xhr.status == 200) {
                    var response = JSON.parse(xhr.responseText);
                    console.log('Réponse:', response);

                    if (response.status === 'success') {
                        // Sélectionner le post et sa section de commentaires
                        var postElement = document.getElementById('post-' + postId);
                        if (postElement) {
                            // Supprimer tous les commentaires associés
                            var commentsSection = postElement.querySelector('.comments-section');
                            if (commentsSection) {
                                commentsSection.remove(); // Suppression de la section des commentaires
                            }
                            // Supprimer le post lui-même
                            postElement.remove();
                            console.log('Post supprimé:', postId);
                        }
                        Swal.fire('Supprimé!', 'La publication a été supprimée.', 'success');
                    } else {
                        Swal.fire('Erreur', 'Erreur lors de la suppression de la publication : ' + response.message, 'error');
                    }
                }
            };

            xhr.send("post_id=" + postId);
        } else {
            console.log("La suppression a été annulée.");
        }
    });
}

// ********************************************************************
// Gestion des commentaires : ajout de commentaires via formulaire
// ********************************************************************

document.querySelectorAll('[id^="commentForm_"]').forEach(form => {
    form.addEventListener('submit', function (event) {
        event.preventDefault(); // Empêche le rechargement de la page
        const formData = new FormData(this); // Récupère les données du formulaire

        fetch('../controller/comment.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.text()) // Traite la réponse
        .then(data => {
            // Ajoute le commentaire à la section des commentaires
            const commentsSection = this.closest('.comments-section');
            const newComment = document.createElement('div');
            newComment.innerHTML = data; // Ajoute le commentaire à partir de la réponse
            commentsSection.insertBefore(newComment, commentsSection.firstChild);
            this.reset(); // Réinitialise le formulaire
        })
        .catch(error => console.error('Erreur:', error));
    });
});

// ********************************************************************
// Confirmation avant publication d'un post
// ********************************************************************

/**
 * Demande une confirmation avant de publier un post.
 * @param {Event} event - L'événement déclencheur de la soumission.
 */
function confirmPost(event) {
    event.preventDefault(); // Empêche la soumission immédiate du formulaire

    Swal.fire({
        title: 'Êtes-vous sûr ?',
        text: "Vous allez publier ce post !",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Oui, publier !',
        cancelButtonText: 'Non, annuler !'
    }).then((result) => {
        if (result.isConfirmed) {
            // Si l'utilisateur confirme, soumet le formulaire
            document.getElementById('postForm').submit();
        }
    });
}

// ********************************************************************
// Fonction de confirmation pour la mise à jour des informations utilisateur
// ********************************************************************

/**
 * Demande une confirmation avant de mettre à jour les informations utilisateur.
 */
function confirmUpdate() {
    const prenom = document.querySelector('input[name="prenom"]').value.trim();
    const nom = document.querySelector('input[name="nom"]').value.trim();
    const email = document.querySelector('input[name="email"]').value.trim();

    // Vérification des champs obligatoires
    if (!prenom || !nom || !email) {
        Swal.fire({
            title: 'Erreur',
            text: 'Veuillez remplir tous les champs obligatoires.',
            icon: 'error',
            confirmButtonColor: '#3085d6',
            confirmButtonText: 'Ok'
        });
        return; // Empêche la soumission du formulaire
    }

    // Si tous les champs sont remplis, demander confirmation
    Swal.fire({
        title: 'Êtes-vous sûr ?',
        text: "Voulez-vous vraiment modifier vos informations ?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Oui, mettre à jour !',
        cancelButtonText: 'Annuler'
    }).then((result) => {
        if (result.isConfirmed) {
            // Soumet le formulaire si l'utilisateur confirme
            document.getElementById("updateForm").submit();
        }
    });
}

// ********************************************************************
// Confirmation avant déconnexion
// ********************************************************************

/**
 * Demande une confirmation avant de se déconnecter.
 */
function confirmLogout(event) {
    event.preventDefault(); // Empêche le comportement par défaut du lien
    Swal.fire({
        title: 'Êtes-vous sûr ?',
        text: "Vous allez vous déconnecter.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Oui, déconnectez-moi !',
        cancelButtonText: 'Annuler'
    }).then((result) => {
        if (result.isConfirmed) {
            // Si l'utilisateur confirme, redirige vers le script de déconnexion
            window.location.href = '/back/logout.php'; // Chemin mis à jour pour Docker
        }
    });



 // Js pour l'event popup

 document.addEventListener('DOMContentLoaded', function () {
    // Récupérer l'élément du bouton qui ouvre le modal
    const openModalButton = document.querySelector('[data-bs-toggle="modal"]');
    
    // Récupérer l'élément modal
    const modalElement = document.getElementById('eventModal');
    
    // Lorsque le bouton est cliqué, afficher le modal
    openModalButton.addEventListener('click', function () {
        var modal = new bootstrap.Modal(modalElement);
        modal.show(); // Affiche le modal
    });

    // Ajouter d'autres fonctionnalités si nécessaire
});

}