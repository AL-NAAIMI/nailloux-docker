<div class="acc-info">
    <!-- Introduction de l'utilisateur -->
    <p>En savoir plus sur <?php echo htmlspecialchars($prenom); ?>...</p>
    
    <div class="acc-info-content">
        <!-- Titre de la section d'informations -->
        <h3 class="acc-info-content-head">Informations de base</h3>
        
        <!-- Liste des informations de l'utilisateur -->
        <ul class="acc-info-content-lst">
            
            <!-- Information sur le prénom -->
            <li>
                <ul class="acc-info-content-list">
                    <li class="acc-info-content-list">
                        <p>Prénom :</p> <!-- Label pour le prénom -->
                    </li>
                    <li class="acc-info-content-list">
                        <?php echo htmlspecialchars($prenom); ?> <!-- Affichage du prénom -->
                    </li>
                </ul>
            </li>
            
            <!-- Information sur le nom -->
            <li>
                <ul class="acc-info-content-list">
                    <li class="acc-info-content-list">
                        <p>Nom :</p> <!-- Label pour le nom -->
                    </li>
                    <li class="acc-info-content-list">
                        <?php echo htmlspecialchars($nom); ?> <!-- Affichage du nom -->
                    </li>
                </ul>
            </li>
            
            <!-- Information sur le pseudo -->
            <li>
                <ul class="acc-info-content-list">
                    <li class="acc-info-content-list">
                        <p>Pseudo :</p> <!-- Label pour le pseudo -->
                    </li>
                    <li class="acc-info-content-list">
                        <?php echo htmlspecialchars($pseudo); ?> <!-- Affichage du pseudo -->
                    </li>
                </ul>
            </li>
            
            <!-- Information sur l'email -->
            <li>
                <ul class="acc-info-content-list">
                    <li class="acc-info-content-list">
                        <p>Email :</p> <!-- Label pour l'email -->
                    </li>
                    <li class="acc-info-content-list">
                        <?php echo htmlspecialchars($email); ?> <!-- Affichage de l'email -->
                    </li>
                </ul>
            </li>
            
            <!-- Information sur le téléphone -->
            <li>
                <ul class="acc-info-content-list">
                    <li class="acc-info-content-list">
                        <p>Téléphone :</p> <!-- Label pour le téléphone -->
                    </li>
                    <li class="acc-info-content-list">
                        <?php echo htmlspecialchars($telephone); ?> <!-- Affichage du téléphone -->
                    </li>
                </ul>
            </li>
        </ul>
    </div>
</div>
