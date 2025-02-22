<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calendrier des Événements</title>
    <link rel="stylesheet" href="../../style/lighttheme_css/light_event.css">
</head>
<body>
    <div class="calendar-page">
        <h1>Calendrier des Événements</h1>
        
        <!-- Barre de navigation pour le calendrier -->
        <div class="calendar-nav">
            <div class="nav-buttons">
                <button id="prevMonthBtn"><</button>
                <button id="todayBtn">Aujourd'hui</button>
                <button id="nextMonthBtn">></button>
            </div>
            <div class="month-year-display" id="monthYearDisplay"></div>
            <button class="btn" onclick="showModal()">Créer un Événement</button>
        </div>

        <div id="calendar" class="calendar"></div>
    </div>

    <!-- Modal pour la création d'événement -->
    <div class="modal" id="eventModal">
        <div class="modal-content">
            <form id="eventForm">
                <div class="modal-header">
                    <h5 class="modal-title">Créer un Événement</h5>
                    <button type="button" class="close" onclick="closeModal()">X</button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="titre" class="form-label">Titre</label>
                        <input type="text" id="titre" name="titre" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="date_heure" class="form-label">Date et Heure</label>
                        <input type="datetime-local" id="date_heure" name="date_heure" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="lieu" class="form-label">Lieu</label>
                        <input type="text" id="lieu" name="lieu" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="descriptif" class="form-label">Descriptif</label>
                        <textarea id="descriptif" name="descriptif" class="form-control" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="type" class="form-label">Type</label>
                        <select id="type" name="type" class="form-select" required>
                            <option value="Cours">Cours</option>
                            <option value="Sortie à thème">Sortie à thème</option>
                            <option value="Expo">Expo</option>
                            <option value="Réunion">Réunion</option>
                            <option value="Info ext">Info ext</option>
                            <option value="Collaboration ext">Collaboration ext</option>
                            <option value="Visionnage">Visionnage</option>
                        </select>
                    </div>
                    <?php
                    // Vérifier si l'utilisateur est connecté et a le rôle Administrateur
                    if (isset($_SESSION['role']) && $_SESSION['role'] === 'Administrateur'): ?>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="officiel" name="officiel">
                        <label class="form-check-label" for="officiel">Officiel</label>
                    </div>
                    <?php endif; ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal()">Annuler</button>
                    <button type="submit" class="btn btn-primary">Créer</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        let currentMonth = new Date().getMonth();
        let currentYear = new Date().getFullYear();

        // Événements chargés depuis le backend
        let events = [];

        // ***** 1) Charger la liste des événements depuis fetch_events.php *****
        async function loadEventsFromBackend() {
            try {
                // Ajuster le chemin si nécessaire
                const response = await fetch('/backend/controller/fetch_events.php');
                const result = await response.json();
                if (result.status === 'success') {
                    events = result.events; // on stocke le tableau d'événements
                } else {
                    console.error('Erreur lors de la récupération des événements:', result.message);
                }
            } catch (err) {
                console.error('Erreur réseau:', err);
            }
        }

        function generateCalendar(year, month) {
            const calendarElement = document.getElementById('calendar');
            calendarElement.innerHTML = '';

            const dayNames = ['Dim', 'Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam'];

            // En-têtes (jours)
            dayNames.forEach(day => {
                const headerElement = document.createElement('div');
                headerElement.classList.add('calendar-header');
                headerElement.textContent = day;
                calendarElement.appendChild(headerElement);
            });

            const daysInMonth = new Date(year, month + 1, 0).getDate();
            const firstDayOfMonth = new Date(year, month, 1).getDay();

            let dayCounter = 1;
            for (let row = 0; row < 6; row++) {
                for (let col = 0; col < 7; col++) {
                    const dayElement = document.createElement('div');
                    dayElement.classList.add('day');

                    if ((row === 0 && col < firstDayOfMonth) || dayCounter > daysInMonth) {
                        // Hors du mois
                        dayElement.classList.add('disabled');
                        dayElement.textContent = '';
                    } else {
                        dayElement.textContent = dayCounter;
                        const thisDay = dayCounter;

                        // Surligner si "aujourd'hui"
                        const today = new Date();
                        if (year === today.getFullYear() && month === today.getMonth() && thisDay === today.getDate()) {
                            dayElement.classList.add('today');
                        }
                        dayElement.setAttribute('data-day', thisDay);

                        dayCounter++;
                    }
                    calendarElement.appendChild(dayElement);
                }
            }

            updateMonthYearDisplay(year, month);
            displayEventsOnCalendar(year, month);
        }

        // Afficher les événements dans la grille
        function displayEventsOnCalendar(year, month) {
            // Retirer d'anciens marquages
            document.querySelectorAll('.day-event').forEach(div => div.remove());

            // Parcourir events
            events.forEach(evt => {
                const eventDate = new Date(evt.date_heure);
                if (eventDate.getFullYear() === year && eventDate.getMonth() === month) {
                    const dayNumber = eventDate.getDate();
                    const dayCell = document.querySelector(`.day[data-day="${dayNumber}"]`);
                    if (dayCell && !dayCell.classList.contains('disabled')) {
                        const eventMarker = document.createElement('div');
                        eventMarker.classList.add('day-event');
                        eventMarker.textContent = evt.titre;

                        // Utiliser le bon identifiant d'événement
                        eventMarker.setAttribute('data-id', evt.id_evenement);

                        // Ajouter un gestionnaire d'événements pour rediriger vers detail.php
                        eventMarker.addEventListener('click', function() {
                            const eventId = eventMarker.getAttribute('data-id');
                            // Redirection vers detail.php avec l'ID de l'événement
                            window.location.href = `/view/about-us/event_details.php?id=${eventId}`;
                        });

                        dayCell.appendChild(eventMarker);
                    }
                }
            });
        }


        function updateMonthYearDisplay(year, month) {
            const monthYearDisplay = document.getElementById('monthYearDisplay');
            const moisFr = [
                'Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin',
                'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'
            ];
            monthYearDisplay.textContent = `${moisFr[month].toUpperCase()} ${year}`;
        }

        function goToNextMonth() {
            currentMonth++;
            if (currentMonth > 11) {
                currentMonth = 0;
                currentYear++;
            }
            generateCalendar(currentYear, currentMonth);
        }

        function goToPrevMonth() {
            currentMonth--;
            if (currentMonth < 0) {
                currentMonth = 11;
                currentYear--;
            }
            generateCalendar(currentYear, currentMonth);
        }

        function goToToday() {
            const now = new Date();
            currentYear = now.getFullYear();
            currentMonth = now.getMonth();
            generateCalendar(currentYear, currentMonth);
        }

        function showModal() {
            document.getElementById('eventModal').style.display = 'block';
        }
        function closeModal() {
            document.getElementById('eventModal').style.display = 'none';
        }

        // Au chargement
        document.addEventListener('DOMContentLoaded', async () => {
            const now = new Date();
            const localDateTime = now.toISOString().slice(0, 16);
            document.getElementById('date_heure').setAttribute('min', localDateTime);

            // Boutons
            document.getElementById('prevMonthBtn').addEventListener('click', goToPrevMonth);
            document.getElementById('nextMonthBtn').addEventListener('click', goToNextMonth);
            document.getElementById('todayBtn').addEventListener('click', goToToday);

            // Charger la liste des événements depuis fetch_events.php
            await loadEventsFromBackend();

            // Générer le calendrier
            generateCalendar(currentYear, currentMonth);
        });

        // Créer un événement
        document.getElementById('eventForm').onsubmit = async function (e) {
            e.preventDefault();
            const formData = new FormData(e.target);

            try {
                const response = await fetch('/backend/controller/create_event.php', {
                    method: 'POST',
                    body: formData,
                });
                const result = await response.json();

                if (result.status === 'success') {
                    alert('Événement créé avec succès.');
                    
                    // Fermer la modale
                    closeModal();

                    // Recharger la liste depuis la DB
                    await loadEventsFromBackend();

                    // Re-générer la grille
                    generateCalendar(currentYear, currentMonth);

                } else {
                    alert('Erreur : ' + result.message);
                }
            } catch (error) {
                console.error('Erreur lors de la création de l\'événement :', error);
                alert('Une erreur réseau s\'est produite.');
            }
        };
    </script>
</body>
</html>
