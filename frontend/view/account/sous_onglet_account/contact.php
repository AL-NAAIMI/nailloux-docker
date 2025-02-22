<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = htmlspecialchars($_POST['name']);
    $email = htmlspecialchars($_POST['email']);
    $subject = htmlspecialchars($_POST['subject']);
    $message = htmlspecialchars($_POST['message']);

    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        $error = "Tous les champs sont obligatoires.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "L'adresse e-mail est invalide.";
    } else {
        // Ici, tu ne fais rien d'autre. Il n'y a pas d'envoi d'e-mail.
        $success = "Votre message a été reçu !";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact - Club Photo Nailloux</title>
    <link rel="stylesheet" href="style.css">
    <script>
        function validateForm() {
            const name = document.getElementById("name").value;
            const email = document.getElementById("email").value;
            const subject = document.getElementById("subject").value;
            const message = document.getElementById("message").value;

            if (!name || !email || !subject || !message) {
                alert("Tous les champs doivent être remplis.");
                return false;
            }
            const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!email.match(emailPattern)) {
                alert("Veuillez entrer une adresse email valide.");
                return false;
            }
            return true;
        }

        // Fonction pour afficher le popup et rediriger après soumission
        function handleSuccess() {
            alert("Votre message a été envoyé avec succès !");
            window.history.back(); // Rediriger vers la page précédente
        }
    </script>
</head>
<body>
    <div class="container1">
        <h1>Contactez-nous</h1>
    </div>
    <div class="container">
        <div class="contact-form">
            <?php
            if (isset($error)) {
                echo '<div class="message error">' . $error . '</div>';
            }
            if (isset($success)) {
                echo '<div class="message success">' . $success . '</div>';
                echo '<script>handleSuccess();</script>'; // Afficher le popup et rediriger
            }
            ?>
            <form action="" method="POST" onsubmit="return validateForm()">
                <div class="form-group">
                    <label for="name">Nom</label>
                    <input type="text" id="name" name="name" required>
                </div>
                <div class="form-group">
                    <label for="email">E-mail</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="subject">Sujet</label>
                    <input type="text" id="subject" name="subject" required>
                </div>
                <div class="form-group">
                    <label for="message">Message</label>
                    <textarea id="message" name="message" required></textarea>
                </div>
                <div class="form-group">
                    <button type="submit">Envoyer</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
