<?php
session_start();
if (isset($_SESSION["pseudo"])) {
    header("Location: account.php");
    exit();
}
?>
<html>
    <title>Nailloux - Social Networking Site</title>
    <?php include __DIR__ . '/header.php'; ?>

    <div class="separate_header"></div>
    <div class="login-signup">
        <center><img class="login-logo" src="./logo/Nailloux logo3.png" alt="logo"></center>
        <center>
            <small>
                <button class="btn" onclick="document.getElementById('login-form').style.display=''; document.getElementById('regst-form').style.display='none';">Connexion</button>
                OU
                <button class="btn" onclick="document.getElementById('login-form').style.display='none'; document.getElementById('regst-form').style.display='';">Inscription</button>
            </small>
        </center>

        <div class="login">
            <form action="/backend/db/validate.php" method="post" class="login-form" id="login-form">
                <input type="text" id="pseudo" name="pseudo" placeholder="Nom d'utilisateur" autocomplete="off" required>
                <input type="password" id="password" name="password" placeholder="Mot de passe" autocomplete="off" required>
                <button class="login-btn" name="lgn" id="lgn">Se connecter</button>
                <a href="forgot_password.php">Mot de passe oublié ?</a>
            </form>
        </div>

        <div class="register">
            <form action="/backend/db/validate.php" method="post" class="regst-form" id="regst-form" style="display: none;">
                <input type="text" id="usrname" name="pseudo" placeholder="Nom d'utilisateur" autocomplete="off" required>
                <section class="name">
                    <input type="text" id="prenom" name="prenom" placeholder="Prénom" required pattern="[a-zA-Z]{2,}$">
                    <input type="text" id="nom" name="nom" placeholder="Nom de Famille" required pattern="[a-zA-Z]{2,}$">
                </section>
                <input type="email" id="email" name="email" placeholder="Email" required>
                <input type="password" id="pass" name="password" placeholder="Mot de passe" required>

                <div class="div-toggle-password">
                    <button id="togglePassword" hidden>Afficher</button>
                    <small id="kindOfPassword" hidden>
                        <span>🔒 Taille > 8 </span>
                        <span>🔠 Au moins une majuscule </span>
                        <span>🔡 Au moins une minuscule </span>
                        <span>🔢 Au moins un nombre </span>
                        <span>@!$# Au moins un caractère spécial</span>
                    </small>
                </div>

                <small>Vos données seront utilisées pour vous offrir une expérience fluide. Nous respectons votre vie privée.</small>
                <button class="rgst-btn" name="regst" id="regst" style="cursor: not-allowed;" disabled>S'inscrire</button>

                <script>
                    const passwordInput = document.getElementById('pass');
                    const registerButton = document.getElementById('regst');
                    const toggleButton = document.getElementById('togglePassword');
                    const kindOfPassword = document.getElementById('kindOfPassword');

                    passwordInput.addEventListener("input", () => {
                        const passwordPattern = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/;
                        if (passwordPattern.test(passwordInput.value)) {
                            registerButton.disabled = false;
                            registerButton.style.cursor = "pointer";
                            toggleButton.hidden = false;
                            kindOfPassword.hidden = false;
                        } else {
                            registerButton.disabled = true;
                            registerButton.style.cursor = "not-allowed";
                            toggleButton.hidden = false;
                            kindOfPassword.hidden = false;
                        }
                    });

                    toggleButton.addEventListener('click', (e) => {
                        e.preventDefault();
                        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                        passwordInput.setAttribute('type', type);
                        toggleButton.textContent = type === 'password' ? 'Show' : 'Hide';
                    });
                </script>
            </form>
        </div>
    </div>
</html>