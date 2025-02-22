<!DOCTYPE html>
<html lang="fr">
<head>
    <!-- Lien vers la feuille de style CSS externe -->
    <link rel="stylesheet" href="/frontend/view/style/lighttheme_css/light_style.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search & Mingle</title>
</head>
<body>
    <!-- Titre principal centré de la page -->
    <center><h3 style="margin-top:5%;">Search & Mingle</h3></center>
    
    <!-- Conteneur pour le formulaire de recherche -->
    <div class="container">
        <!-- Formulaire de recherche -->
        <form action="account.php" method="GET">
            <!-- Champ de texte pour entrer le pseudo à rechercher -->
            <input type="text" id="pseudo" name="search" class="search-bar" autocomplete="off" placeholder="search pseudo...">
            
            <!-- Bouton pour soumettre la recherche -->
            <button type="submit" class="search-btn">search</button>
        </form>
    </div>
</body>
</html>
