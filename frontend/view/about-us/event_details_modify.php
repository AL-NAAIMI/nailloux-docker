<?php if (!isset($event)) {
    die("Les détails de l'événement ne sont pas disponibles.");
} ?>
<div class="edit-event-form">
    <h1>Modifier l'Événement</h1>
    <form action="../../backend/controller/update_evenement.php" method="post">
        <!-- ID de l'événement -->
        <input type="hidden" name="id_evenement" value="<?php echo htmlspecialchars($event['id_evenement']); ?>">

        <label for="titre">Titre :</label>
        <input type="text" name="titre" id="titre" value="<?php echo htmlspecialchars($event['titre']); ?>" required>

        <label for="date_heure">Date et Heure :</label>
        <input type="datetime-local" name="date_heure" id="date_heure" value="<?php echo htmlspecialchars(date('Y-m-d\TH:i', strtotime($event['date_heure']))); ?>" required>

        <label for="lieu">Lieu :</label>
        <input type="text" name="lieu" id="lieu" value="<?php echo htmlspecialchars($event['lieu']); ?>" required>

        <label for="descriptif">Descriptif :</label>
        <textarea name="descriptif" id="descriptif" rows="5" required><?php echo htmlspecialchars($event['descriptif']); ?></textarea>

        <label for="type">Type :</label>
        <select name="type" id="type">
            <option value="Cours" <?php echo ($event['type'] === 'Cours') ? 'selected' : ''; ?>>Cours</option>
            <option value="Sortie à thème" <?php echo ($event['type'] === 'Sortie à thème') ? 'selected' : ''; ?>>Sortie à thème</option>
            <option value="Expo" <?php echo ($event['type'] === 'Expo') ? 'selected' : ''; ?>>Expo</option>
            <option value="Réunion" <?php echo ($event['type'] === 'Réunion') ? 'selected' : ''; ?>>Réunion</option>
            <option value="Info ext" <?php echo ($event['type'] === 'Info ext') ? 'selected' : ''; ?>>Info ext</option>
            <option value="Collaboration ext" <?php echo ($event['type'] === 'Collaboration ext') ? 'selected' : ''; ?>>Collaboration ext</option>
            <option value="Visionnage" <?php echo ($event['type'] === 'Visionnage') ? 'selected' : ''; ?>>Visionnage</option>
        </select>

        <label for="officiel">Officiel :</label>
        <input type="checkbox" name="officiel" id="officiel" value="1" <?php echo ($event['officiel']) ? 'checked' : ''; ?>>

        <button type="submit" class="submit-button">Mettre à jour</button>
    </form>
</div>
