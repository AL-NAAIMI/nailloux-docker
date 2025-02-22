<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mot de passe modifié</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f2f5;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        form {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
            max-width: 400px;
            width: 100%;
        }
        input, button {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        button {
            background-color: #007bff;
            color: white;
            border: none;
            cursor: pointer;
        }
        button:hover {
            background-color: #0056b3;
        }
        h2 {
            text-align: center;
        }
    </style>
</head>
<body>
    <form onsubmit="return validateForm()">
        <h2>Mot de passe oublié</h2>
        <input type="email" name="email" id="email" placeholder="Entrez votre e-mail" required>
        <input type="password" name="password" placeholder="Mot de passe actuel" required>
        <input type="password" name="new_password" id="new_password" placeholder="Nouveau mot de passe" required>
        <input type="password" name="confirm_password" id="confirm_password" placeholder="Confirmez le nouveau mot de passe" required>
        <button type="submit">Envoyer</button>
    </form>

    <script>
        function validateForm() {
            const email = document.getElementById('email').value;
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = document.getElementById('confirm_password').value;

            // Vérifier si l'email contient un "@"
            const emailPattern = /\S+@\S+\.\S+/;
            if (!emailPattern.test(email)) {
                alert("L'adresse e-mail doit comporter un '@'.");
                return false;
            }

            // Vérifier si les mots de passe correspondent
            if (newPassword !== confirmPassword) {
                alert("Le nouveau mot de passe et la confirmation ne correspondent pas.");
                return false;
            }

            alert('Mot de passe modifié avec succès !');
            window.location.href = 'index.php'; // Redirection après la confirmation
            return false; // Empêche le formulaire de se soumettre normalement
        }
    </script>
</body>
</html>
