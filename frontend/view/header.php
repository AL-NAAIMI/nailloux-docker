
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./style/lighttheme_css/light_style.css?v=<?php echo time();?>">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" integrity="sha512-ZvHjXoebDRUrTnKh9WKpWV/A0Amd+fjub5TkBXrPxe5F7WfDZL0slJ6a0mvg7VSN3qdpgqq2y1blz06Q8W2Y8A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="shortcut icon" href="./logo/Nailloux logo4.png" type="image/png">
    <script src="https://kit.fontawesome.com/17a4e5185f.js" crossorigin="anonymous"></script>

    <?php
    // Inclure les fichiers CSS selon la page
    $page = basename($_SERVER['PHP_SELF']);

    switch ($page) {
        case 'account.php':
            echo '<link rel="stylesheet" href="style/lighttheme_css/light_account.css?t=' . time() . '" id="theme">';
            break;

        case 'feed.php':
            echo '<link rel="stylesheet" href="style/lighttheme_css/light_feed.css?t=' . time() . '" id="theme">';
            break;

        case 'about-us.php':
            echo '<link rel="stylesheet" href="style/lighttheme_css/light_about.css?t=' . time() . '" id="theme">';
            break;

            case 'pagep.php':
              echo '<link rel="stylesheet" href="style/lighttheme_css/light_pageaccueil.css?t=' . time() . '" id="theme">';
              break;

              case 'index.php':
                echo '<link rel="stylesheet" href="style/lighttheme_css/light_connec.css?t=' . time() . '" id="theme">';
                break;


                case 'recherche_cle.php':
                  echo '<link rel="stylesheet" href="style/lighttheme_css/light_recherchecle.css?t=' . time() . '" id="theme">';
                  break;
        // Ajoutez d'autres pages si nécessaire
    }
    ?>
</head>

<body>
    <nav>
    <label class="logo">
      <a href="account.php?pseudo=<?php echo isset($_GET['pseudo']) ? urlencode($_GET['pseudo']) : (isset($_SESSION['pseudo']) ? urlencode($_SESSION['pseudo']) : 'defaultPseudo'); ?>">
          <img class="logo" src="./logo/Nailloux logo1.png">
      </a>
    </label>
      <div class="menu-btn">
        <div class="bar bar1"></div>
        <div class="bar bar2"></div>
        <div class="bar bar3"></div>
      </div>
      
      <ul class="menu-items">

      <div class="nav-btns">
        <ul class="menu-items">
          <!-- Search -->
      <?php if (isset($_SESSION['pseudo']) && (isset($_SESSION['role']) && ($_SESSION['role'] === 'Administrateur' || $_SESSION['role'] === 'Membre'))) : ?>
            <li class="menu-items-li search-item">
                <form action="account.php" method="GET" class="search-form">
                    <input type="text" name="search" class="search-bar" placeholder="rechercher quelqu'un..." autocomplete="off">
                    <button type="submit" class="search-btn">
                        <i class="fas fa-search"></i>
                    </button>
                </form>
            </li>
          <?php endif; ?>


          <!-- Feed -->
          <?php 
          if (isset($_SESSION['pseudo'])) {
              // Using PDO to get the user's role if not already set in session
              if (!isset($_SESSION['role'])) {
                  // Assuming $pdo is your PDO connection object
                  $stmt = $pdo->prepare("SELECT `role` FROM `utilisateur` WHERE `pseudo` = :pseudo LIMIT 1");
                  $stmt->bindParam(":pseudo", $_SESSION['pseudo'], PDO::PARAM_STR);
                  $stmt->execute();
                  $user = $stmt->fetch(PDO::FETCH_ASSOC);
                  $_SESSION['role'] = $user ? $user['role'] : null;
              }
          }
          
          if (isset($_SESSION['pseudo']) && (isset($_SESSION['role']) && ($_SESSION['role'] === 'Administrateur' || $_SESSION['role'] === 'Membre'))) : ?>
              <li class="menu-items-li">
                  <a class="navv-item <?php echo basename($_SERVER['PHP_SELF']) == 'feed.php' ? 'active' : ''; ?>" href="feed.php">
                      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="menu-icon">
                          <path fill-rule="evenodd" d="M3.75 4.5a.75.75 0 0 1 .75-.75h.75c8.284 0 15 6.716 15 15v.75a.75.75 0 0 1-.75.75h-.75a.75.75 0 0 1-.75-.75v-.75C18 11.708 12.292 6 5.25 6H4.5a.75.75 0 0 1-.75-.75V4.5Zm0 6.75a.75.75 0 0 1 .75-.75h.75a8.25 8.25 0 0 1 8.25 8.25v.75a.75.75 0 0 1-.75.75H12a.75.75 0 0 1-.75-.75v-.75a6 6 0 0 0-6-6H4.5a.75.75 0 0 1-.75-.75v-.75Zm0 7.5a1.5 1.5 0 1 1 3 0 1.5 1.5 0 0 1-3 0Z" clip-rule="evenodd" />
                      </svg>Fil
                  </a>
              </li>
          <?php endif; ?>


           <!-- Keywords Section -->
           <?php 
            if (isset($_SESSION['pseudo'])) {
                // Utilisation de PDO pour récupérer le rôle de l'utilisateur si non déjà défini en session
                if (!isset($_SESSION['role'])) {
                    // Assurez-vous que $pdo est votre objet de connexion PDO
                    $stmt = $pdo->prepare("SELECT `role` FROM `utilisateur` WHERE `pseudo` = :pseudo LIMIT 1");
                    $stmt->bindParam(":pseudo", $_SESSION['pseudo'], PDO::PARAM_STR);
                    $stmt->execute();
                    $user = $stmt->fetch(PDO::FETCH_ASSOC);
                    $_SESSION['role'] = $user ? $user['role'] : null;
                }
            }

            if (isset($_SESSION['pseudo']) && (isset($_SESSION['role']) && ($_SESSION['role'] === 'Administrateur' || $_SESSION['role'] === 'Membre'))) : ?>
                <li class="menu-items-li">
                    <a class="navv-item <?php echo basename($_SERVER['PHP_SELF']) == 'recherche_cle.php' ? 'active' : ''; ?>" href="recherche_cle.php">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="menu-icon">
                            <path fill-rule="evenodd" d="M12 2.25a.75.75 0 0 1 .75.75v6.75h6.75a.75.75 0 0 1 .75.75c0 1.243-.694 2.308-1.75 2.868v2.232a2.25 2.25 0 0 1-.75 1.75c-1.056.56-1.75 1.625-1.75 2.868 0 .414.336.75.75.75H16.5V21a.75.75 0 0 1-.75.75h-9a.75.75 0 0 1-.75-.75v-8.25h-1.5a.75.75 0 0 1-.75-.75c0-1.243.694-2.308 1.75-2.868v-2.232a2.25 2.25 0 0 1 .75-1.75c1.056-.56 1.75-1.625 1.75-2.868 0-.414-.336-.75-.75-.75H7.5V3a.75.75 0 0 1 .75-.75h9a.75.75 0 0 1 .75.75zM11.25 3v3h1.5V3h-1.5zM7.5 8.25h9c.413 0 .75-.337.75-.75s-.337-.75-.75-.75H7.5c-.413 0-.75.337-.75.75s.337.75.75.75zM9 13.5h6c.413 0 .75-.337.75-.75s-.337-.75-.75-.75H9c-.413 0-.75.337-.75.75s.337.75.75.75z" clip-rule="evenodd"/>
                        </svg>Photo
                    </a>
                </li>
            <?php endif; ?>

          <!-- About Us -->
          <?php if (isset($_SESSION['pseudo'])) : ?>
          <li class="menu-items-li">
            <a class="navv-item <?php echo basename($_SERVER['PHP_SELF']) == 'about-us.php' ? 'active' : ''; ?>" href="about-us.php">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="menu-icon">
                <path d="M4.5 6.375a4.125 4.125 0 1 1 8.25 0 4.125 4.125 0 0 1-8.25 0ZM14.25 8.625a3.375 3.375 0 1 1 6.75 0 3.375 3.375 0 0 1-6.75 0ZM1.5 19.125a7.125 7.125 0 0 1 14.25 0v.003l-.001.119a.75.75 0 0 1-.363.63 13.067 13.067 0 0 1-6.761 1.873c-2.472 0-4.786-.684-6.76-1.873a.75.75 0 0 1-.364-.63l-.001-.122ZM17.25 19.128l-.001.144a2.25 2.25 0 0 1-.233.96 10.088 10.088 0 0 0 5.06-1.01.75.75 0 0 0 .42-.643 4.875 4.875 0 0 0-6.957-3.995A5.995 5.995 0 0 1 17.25 19.128Z" />
              </svg>Notre club
            </a>
          </li>
        <?php endif; ?>
        


              <?php if (!isset($_SESSION['pseudo'])) : ?>
        <li class="menu-items-li">
          <a class="navv-item <?php echo basename($_SERVER['PHP_SELF']) == 'pagep.php' ? 'active' : ''; ?>" href="pagep.php">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="menu-icon">
              <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 9V5.25A2.25 2.25 0 0 1 10.5 3h6a2.25 2.25 0 0 1 2.25 2.25v13.5A2.25 2.25 0 0 1 16.5 21h-6a2.25 2.25 0 0 1-2.25-2.25V15M12 9l3 3m0 0-3 3m3-3H2.25" />
            </svg>Accueil</a>
        </li>
      <?php endif; ?>


          <!-- Account -->
          <?php if (isset($_SESSION['pseudo'])): ?>
            <li class="menu-items-li">
              <a class="navv-item <?php echo basename($_SERVER['PHP_SELF']) == 'account.php' ? 'active' : ''; ?>" href="./account.php?pseudo=<?php echo $_SESSION['pseudo']; ?>">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="menu-icon">
                  <path fill-rule="evenodd" d="M7.5 6a4.5 4.5 0 1 1 9 0 4.5 4.5 0 0 1-9 0ZM3.751 20.105a8.25 8.25 0 0 1 16.498 0 .75.75 0 0 1-.437.695A18.683 18.683 0 0 1 12 22.5c-2.786 0-5.433-.608-7.812-1.7a.75.75 0 0 1-.437-.695Z" clip-rule="evenodd" />
                </svg>compte
              </a>
            </li>
          <?php endif; ?>
          
          <!-- Login/Logout -->
          <li class="menu-items-li">
            <?php
            if (!isset($_SESSION['pseudo'])) {
              echo '<a class="navv-item ' . (basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : '') . '" href="./index.php">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="menu-icon">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 9V5.25A2.25 2.25 0 0 1 10.5 3h6a2.25 2.25 0 0 1 2.25 2.25v13.5A2.25 2.25 0 0 1 16.5 21h-6a2.25 2.25 0 0 1-2.25-2.25V15M12 9l3 3m0 0-3 3m3-3H2.25" />
                </svg>Se connecter</a>';
            } else {
                echo '<a class="navv-item" href="../../back/logout.php">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="menu-icon">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9" />
                </svg>Déconnexion</a>';
            }
            ?>
          </li>
        </ul>
      </div>
    </nav>
</body>
