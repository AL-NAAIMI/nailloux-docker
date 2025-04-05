<?php if (isset($_SESSION['pseudo']) && isset($_SESSION['id'])) : ?>
    <?php
    // if (empty($_SESSION['csrf_token'])) {
    //     $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    // }
    ?>
 
    <!-- Stylesheet du pop-up pour les détails de la photo sélectionnée -->
    <link rel="stylesheet" type="text/css" href="/frontend/view/style/lighttheme_css/light_script_formulaire_pour_poster.css">

    <div class="feed-posting-box">
    <form id="postForm" onsubmit="confirmPost(event)" action="/backend/controller/post.php" method="post" enctype="multipart/form-data">
        <input type="hidden" name="user_id" id="user_id" value="<?php echo htmlspecialchars($_SESSION['id']); ?>">
        <input type="hidden" name="pseudo" id="pseudo" value="<?php echo htmlspecialchars($_SESSION['pseudo']); ?>">
        <input type="hidden" name="redirect" id="redirect" value="feed.php">
        <!-- <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>"> -->

        <!-- File input -->
        <input type="file" name="postimage" accept=".jpg, .png, .jpeg, .webp" id="postimage" onchange="openPopUp()">
        <label for="postimage" class="postimage-custom">+</label>

        <!-- Pop-up for details -->
        <div id="photoDetailsPopup" class="modal" style="display: none;">
            <div class="modal-content">
                <h2>Détails de la Photo</h2>
                <!-- Conteneur pour l'image -->
                <div class="image-container">
                            <img id="selectedImage" src="" alt="Aperçu de la photo">
                        </div>
                <!-- Form fields -->
                <div class="form-group">
                    <label for="photographe">Nom du photographe:</label>
                    <input type="text" id="photographe" name="photographe" required>
                </div>
                <div class="form-group">
                    <label for="titre">Titre:</label>
                    <input type="text" id="titre" name="titre" required>
                </div>
                <div class="form-group">
                    <label for="description">Description:</label>
                    <textarea id="description" name="post" required></textarea>
                </div>
                <div class="form-group">
                    <label for="datePrisePhoto">Date de capture:</label>
                    <input type="date" id="datePrisePhoto" name="datePrisePhoto" required max="">
                </div>
                <div class="form-group">
                    <label for="motsCles">Mots-clés:</label>
                    <input type="text" id="motsCles" name="motsCles">
                </div>
                <div class="form-group">
                    <label for="auteur">Nom de l'auteur (optionnel):</label>
                    <input type="text" id="auteur" name="auteur">
                </div>
                <div class="form-group">
                    <div class="public-checkbox">
                        <label for="public">Public:</label>
                        <input type="checkbox" name="public" id="public" value="1">
                    </div>
                </div>

                <!-- Buttons -->
                <div class="public-toggle">
                    <button type="submit" class="post-btn">Publier</button>
                    <button type="button" class="cancel-btn" onclick="closePopUp()">Retour</button>
                </div>
            </div>
        </div>
    </form>
</div>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        let today = new Date().toISOString().split("T")[0]; // Date du jour au format YYYY-MM-DD
        let dateField = document.getElementById("datePrisePhoto");
        if (dateField) {
            dateField.setAttribute("max", today);
        }
    });
</script>

<script src="/backend/js/script_formulaire_pour_poster_feed.js"></script>

<?php endif; ?>
