document.addEventListener("DOMContentLoaded", function () {
    let today = new Date().toISOString().split("T")[0]; // Obtient la date du jour au format YYYY-MM-DD
    let dateField = document.getElementById("date_capture");
    if (dateField) {
        dateField.setAttribute("max", today);
    }
});
    
function openPopUp() {
    const fileInput = document.getElementById('postimage');
    const file = fileInput.files[0];
    const reader = new FileReader();

    reader.onload = function (e) {
        document.getElementById('selectedImage').src = e.target.result;
        document.getElementById('photoDetailsPopup').style.display = 'block';
    };

    if (file) {
        reader.readAsDataURL(file);
    }
}

function closePopUp() {
    document.getElementById('photoDetailsPopup').style.display = 'none';
}

function confirmPost(event) {
    event.preventDefault(); // Prevent default form submission

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
            submitForm(); // Call submitForm if confirmed
        }
    });
}

function submitForm() {
    const postForm = document.getElementById('postForm');
    const dateField = document.getElementById("date_capture");
    let today = new Date().toISOString().split("T")[0]; // Date du jour au format YYYY-MM-DD

    // Vérifier si la date est future
    if (dateField && dateField.value > today) {
        Swal.fire({
            title: 'Erreur',
            text: "La date de capture ne peut pas être dans le futur.",
            icon: 'error',
            confirmButtonColor: '#d33',
            confirmButtonText: 'OK'
        });
        return;
    }

    // Vérifier si tous les champs requis sont valides
    if (!postForm.checkValidity()) {
        postForm.reportValidity();
        return;
    }

    // Soumettre le formulaire si tout est correct
    postForm.submit();
}
