<x-app-layout>
    <x-slot:title>Planning - Réel</x-slot:title>

    <div class="container mt-4">
        <div id="no-data-for-user"></div>

        <script>
            function changeSalarie(salarieId) {
                window.location.href = `/planning/salarie/${salarieId}`;
            }
        </script>

        <div class="text-center mb-4">
            @if (Auth::user()->isA('admin'))
                <h1 class="fw-bold text-primary display-5">Planning des salariés</h1>
            @else
                <h1 class="fw-bold text-primary display-5">Mon planning</h1>
            @endif
        </div>

        @isset($isEmpty)
            @if ($isEmpty == true)
                <div class="alert alert-warning" role="alert">
                    Aucune donnée trouvée pour cet utilisateur cette semaine.
                </div>
            @endif
        @endisset
    </div>
    @if (auth()->user()->isA('salarie'))
        @if (isset($hoursThisWeek))
            @php
                $maxHours = 45 * 60;
                $hoursThisWeek = is_numeric($hoursThisWeek) ? $hoursThisWeek : 0;
                $percentage = ($hoursThisWeek / $maxHours) * 100;

                $progressClass = match (true) {
                    $hoursThisWeek > 40 * 60 => 'bg-danger',
                    $hoursThisWeek > 35 * 60 => 'bg-warning',
                    default => 'bg-success',
                };
            @endphp

            <div class="progress mb-4 shadow-sm"
                style="height: 30px; position: relative; background-color: #555; width: 60%; margin: 0 auto;"
                data-heures-prevues="{{ $heuresPrevues }}">
                <div class="progress-bar {{ $progressClass }} progress-bar-striped progress-bar-animated"
                    role="progressbar" style="width: {{ min($percentage, 100) }}%;" aria-valuenow="{{ $hoursThisWeek }}"
                    aria-valuemin="0" aria-valuemax="{{ $maxHours }}">
                    <span class="position-absolute w-100 text-center text-white"
                        style="top: 50%; transform: translateY(-50%);">
                        {{ round($hoursThisWeek / 60, 2) . '/' . round($heuresPrevues / 60, 2) }} heures
                    </span>
                </div>
            </div>
        @else
            <div class="alert alert-warning" role="alert">
                Les données pour les heures de cette semaine ne sont pas disponibles.
            </div>
        @endif
    @endif

    <div class="row mb-4">
        <div class="col-12 col-md-6 d-flex justify-content-center align-items-center">
            @if (Auth::user()->isA('admin'))
                @if (isset($salaries) && $salaries->isNotEmpty())
                    <div class="d-flex justify-content-center align-items-center w-100">
                        <x-inputs.input-select2 property="salarie_id" name="salarie_id" :entity="(object) ['salarie_id' => $currentUserId ?? null]"
                            label="salariés :" :values="$salaries" itemValue="id" itemLabel="identity"
                            :required="false" />
                        {{-- <select name="salarie_id" id="salarie" class="form-select"
                            onchange="changeSalarie(this.value)" style="max-width: 250px; flex: 1;">
                            <option value="" disabled {{ !isset($user) ? 'selected' : '' }}>-- Sélectionner un
                                salarié --</option>
                            @foreach ($salaries as $salarie)
                                <option value="{{ $salarie->id }}"
                                    {{ isset($user) && $salarie->id == $user->id ? 'selected' : '' }}>
                                    {{ $salarie->identity }}
                                </option>
                            @endforeach
                        </select> --}}
                    </div>
                @else
                    <p class="text-muted text-center">Aucun salarié disponible.</p>
                @endif
            @endif
        </div>
        <div class="col-12 col-md-6 d-flex justify-content-center align-items-center">
            <div class="d-flex justify-content-center mt-4">
                <a href="{{ route('planning.create') }}" class="btn btn-primary btn-sm text-white">
                    <i class="fas fa-plus text-white"></i> Créer une nouvelle tâche
                </a>
            </div>
        </div>
    </div>

    <div id="calendar" class="calendar-scrollable"></div>

    <script type="module">
        const calendarEl = document.getElementById('calendar');
        const progressBar = document.querySelector('.progress-bar');

        function updateProgress(hoursWorked) {
            const maxHours = 45 * 60;
            hoursWorked = isNaN(hoursWorked) ? 0 : parseFloat(hoursWorked);
            const percentage = (hoursWorked / maxHours) * 100;

            let progressClass = hoursWorked > 40 * 60 ? 'bg-danger' :
                hoursWorked > 35 * 60 ? 'bg-warning' :
                'bg-success';

            console.log(hoursWorked);
            console.log(hoursWorked / 60);

            const hoursDisplay = (hoursWorked / 60).toFixed(2);
            const heuresPrevues = document.querySelector('.progress').getAttribute('data-heures-prevues');
            progressBar.style.width = `${Math.min(percentage, 100)}%`;
            progressBar.setAttribute('aria-valuenow', hoursWorked);
            progressBar.innerText = `${hoursDisplay} / ${(heuresPrevues / 60).toFixed(2)} heures`;
            progressBar.className = `progress-bar ${progressClass} progress-bar-striped progress-bar-animated`;
        }
        window.confirmDelete = function(eventId) {
            Swal.fire({
                title: 'Êtes-vous sûr ?',
                text: 'Cette action est irréversible. Voulez-vous vraiment supprimer cet événement ?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Oui, supprimer',
                cancelButtonText: 'Annuler',
            }).then((result) => {
                if (result.isConfirmed) {
                    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                    fetch(`/planning/${eventId}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': csrfToken,
                                'Content-Type': 'application/json',
                            },
                        })
                        .then((response) => response.json())
                        .then((data) => {
                            if (data.success) {
                                Swal.fire({
                                        title: 'Succès',
                                        text: 'La tâche a été supprimée.',
                                        icon: 'success',
                                        timer: 2000,
                                        showConfirmButton: false
                                    }).then(() => {
                                    const event = calendar.getEventById(eventId);
                                    event.remove();
                                    window.location.reload();
                                });


                            } else {
                                Swal.fire('Erreur', data.message || 'Une erreur est survenue.', 'error');
                            }
                        })
                        .catch((error) => {
                            console.error('Erreur lors de la suppression :', error);
                            Swal.fire('Erreur', 'Une erreur est survenue.', 'error');
                        });
                }
            });
        };

        function formatTime(date) {
            const hours = String(date.getHours()).padStart(2, '0');
            const minutes = String(date.getMinutes()).padStart(2, '0');
            return `${hours}:${minutes}`;
        };

        function adjustHeaderResponsiveness() {
            const header = document.querySelector('.fc-header-toolbar');
            if (window.innerWidth <= 576) {
                header.style.flexDirection = 'column';
                header.style.textAlign = 'center';
                header.style.gap = '10px';
                header.style.fontSize = '0.7rem';
            } else {
                header.style.flexDirection = 'row';
                header.style.textAlign = 'initial';
                header.style.gap = '0';
                header.style.fontSize = '1rem';
            }
        };

        function addTasksToCalendar(tasks, calendar) {
            if (tasks && Array.isArray(tasks)) {
                tasks.forEach(task => {
                    const event = calendar.addEvent({
                        title: task.title,
                        start: task.start,
                        end: task.end,
                        location: task.location,
                        id: task.id,
                    });

                    // Ajouter l'attribut data-event-id à l'élément DOM de l'événement
                    const eventEl = document.querySelector(`.fc-event[data-fc-event-id="${event.id}"]`);
                    if (eventEl) {
                        eventEl.setAttribute('data-event-id', task.id);
                    }
                });
            }
        };

        function formatDate(date) {

            if (!(date instanceof Date)) {
                date = new Date(date);
            }

            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            return `${year}-${month}-${day}`;
        };

        function fetchEventsForCurrentWeek(startDate, endDate) {
            const formattedStartDate = formatDate(startDate);
            const formattedEndDate = formatDate(endDate);
            const isAdmin = @json(auth()->user()->isA('admin'));

            fetch(`/api/planning/events?start=${formattedStartDate}&end=${formattedEndDate}`)
                .then(response => response.json())
                .then(response => {
                    const importButton = document.querySelector('.fc-importerPlanning-button');
                    if (!isAdmin) {
                        if (importButton) {
                            importButton.style.display = response.data ? '' : 'none';
                        }
                    } else {
                        importButton.style.display = 'none';
                    }
                })
                .catch(error => {
                    console.error('Erreur :', error);
                    Swal.fire({
                        title: 'Erreur',
                        text: 'Impossible de vérifier le statut des événements.',
                        icon: 'error',
                    });
                });
        }

        const calendar = new Calendar(calendarEl, {
            plugins: [
                dayGridPlugin,
                timeGridPlugin,
                listPlugin,
                interactionPlugin,
            ],
            handleWindowResize: true,
            allDaySlot: false,
            editable: true,
            eventOverlap: false,
            initialView: 'timeGridDay',
            locale: 'fr',
            timezone: 'Europe/Paris',
            weekNumbers: true,
            nowIndicator: true,
            firstDay: 1,
            buttonText: {
                today: '📅',
                week: 'Semaine',
                day: 'Jour',
            },
            headerToolbar: {
                left: 'prev,today,next',
                center: 'title',
                right: 'importerPlanning,timeGridDay,timeGridWeek',
            },
            slotDuration: '00:15:00',
            slotLabelInterval: '01:00:00',
            slotMinTime: '00:00:00',
            slotMaxTime: '24:00:00',
            scrollTime: '08:00:00',
            datesSet: function(info) {
                adjustHeaderResponsiveness();
                fetchEventsForCurrentWeek(info.startStr, info.endStr);

                if (!this.initialLoadDone) {
                    this.initialLoadDone = true;
                    const urlParams = new URLSearchParams(window.location.search);
                    const eventUrlId = urlParams.get('event_id');

                    if (eventUrlId) {

                        setTimeout(() => {
                            const event = calendar.getEventById(eventUrlId);

                            if (event) {
                                calendar.gotoDate(event.start);

                                setTimeout(() => {
                                    const allEvents = document.querySelectorAll(
                                        '.fc-timegrid-event');

                                    let eventEl = null;
                                    allEvents.forEach(el => {
                                        if (el.textContent.includes(event.title)) {
                                            eventEl = el;
                                        }
                                    });


                                    if (eventEl) {
                                        eventEl.style.backgroundColor = '#ff9f89';
                                        eventEl.style.borderColor = '#ff6b6b';

                                        eventEl.scrollIntoView({
                                            behavior: 'smooth',
                                            block: 'center'
                                        });

                                        const newUrl = window.location.href.split('?')[0];
                                        history.replaceState(null, '', newUrl);

                                        setTimeout(() => {
                                            eventEl.style.backgroundColor = '';
                                            eventEl.style.borderColor = '';
                                        }, 3000);
                                    }
                                }, 200);
                            }
                        }, 100);
                    } else {
                        setTimeout(() => {
                            const nowIndicator = document.querySelector(
                                '.fc-timegrid-now-indicator-arrow');
                            if (nowIndicator) {
                                nowIndicator.scrollIntoView({
                                    behavior: 'smooth',
                                    block: 'center'
                                });
                            } else {
                                const now = new Date();
                                const currentHour = now.getHours();
                                const currentMinutes = now.getMinutes();

                                const timeGridContainer = document.querySelector('.fc-timegrid-slots');
                                if (timeGridContainer) {
                                    const slotHeight = timeGridContainer.scrollHeight / 24;
                                    const scrollPosition = (currentHour + currentMinutes / 60) *
                                        slotHeight;

                                    const containerHeight = document.querySelector('.fc-timegrid-body')
                                        .clientHeight;
                                    const finalPosition = Math.max(0, scrollPosition - (
                                        containerHeight / 2));

                                    timeGridContainer.closest('.fc-timegrid-body').scrollTop =
                                        finalPosition;
                                }
                            }
                        }, 300);
                    }
                }
            },
            mounted: function() {
                const importButton = document.querySelector('.fc-importerPlanning-button');
                if (importButton) {
                    importButton.style.display = 'none';
                }
            },
            eventDrop: function(info) {
                Swal.fire({
                    title: 'Confirmer le déplacement',
                    text: 'Voulez-vous vraiment déplacer cet événement ?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Oui, déplacer',
                    cancelButtonText: 'Annuler'
                }).then((result) => {
                    if (result.isConfirmed) {
                        const event = info.event;
                        const csrfToken = document.querySelector('meta[name="csrf-token"]')
                            .getAttribute('content');

                        const dateCliquee = event.start.toISOString().split('T')[
                            0]; // Date au format Y-m-d
                        const userId = @json(auth()->user()->isA('admin')) ?
                            event.extendedProps.user_id :
                            @json(auth()->user()->id);

                        fetch(`/planning/${event.id}`, {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': csrfToken,
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json'
                                },
                                body: JSON.stringify({
                                    _method: 'PUT',
                                    heure_debut: formatTime(event.start),
                                    heure_fin: formatTime(event.end),
                                    plannifier_le: dateCliquee,
                                    user_id: userId,
                                })
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    Swal.fire({
                                        title: 'Succès',
                                        text: 'L\'événement a été mis à jour',
                                        icon: 'success',
                                        timer: 2000,
                                        showConfirmButton: false
                                    });
                                    calendar.refetchEvents();
                                } else {
                                    info.revert();
                                    Swal.fire({
                                        title: 'Erreur',
                                        text: data.message ||
                                            'Une erreur est survenue lors de la mise à jour',
                                        icon: 'error'
                                    });
                                }
                            })
                            .catch(error => {
                                console.error('Erreur lors de la mise à jour :', error);
                                info.revert();
                                Swal.fire({
                                    title: 'Erreur',
                                    text: 'Une erreur est survenue lors de la mise à jour',
                                    icon: 'error'
                                });
                            });
                    } else {
                        info.revert();
                    }
                });
            },

            // Pour gérer le redimensionnement des événements
            eventResize: function(info) {
                Swal.fire({
                    title: 'Confirmer la modification',
                    text: 'Voulez-vous vraiment modifier la durée de cet événement ?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Oui, modifier',
                    cancelButtonText: 'Annuler'
                }).then((result) => {
                    if (result.isConfirmed) {
                        const event = info.event;
                        const csrfToken = document.querySelector('meta[name="csrf-token"]')
                            .getAttribute('content');

                        const userId = @json(auth()->user()->isA('admin')) ?
                            event.extendedProps.user_id :
                            @json(auth()->user()->id);

                        fetch(`/planning/${event.id}`, {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': csrfToken,
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json'
                                },
                                body: JSON.stringify({
                                    _method: 'PUT',
                                    heure_debut: formatTime(event.start),
                                    heure_fin: formatTime(event.end),
                                    user_id: userId,
                                })
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    Swal.fire({
                                        title: 'Succès',
                                        text: 'L\'événement a été mis à jour',
                                        icon: 'success',
                                        timer: 2000,
                                        showConfirmButton: false
                                    }).then(() => {
                                        const eventId = event.id;
                                        const currentUrl = new URL(window.location.href);
                                        currentUrl.searchParams.set('event_id', eventId);
                                        window.location.href = currentUrl
                                            .toString();
                                    });
                                } else {
                                    info.revert();
                                    Swal.fire({
                                        title: 'Erreur',
                                        text: data.message ||
                                            'Une erreur est survenue lors de la mise à jour 1',
                                        icon: 'error'
                                    });
                                }
                            })
                            .catch(error => {
                                console.error('Erreur lors de la mise à jour :', error);
                                info.revert();
                                Swal.fire({
                                    title: 'Erreur',
                                    text: 'Une erreur est survenue lors de la mise à jour 2',
                                    icon: 'error'
                                });
                            });
                    } else {
                        info.revert();
                    }
                });
            },
            dateClick: function(info) {
                const isAdmin = @json(auth()->user()->isA('admin'));
                var clickedDate = info.date;

                var heure_debut_heure = clickedDate.getHours();
                var heure_debut_minute = clickedDate.getMinutes();

                var heure_fin = new Date(clickedDate);
                heure_fin.setMinutes(heure_fin.getMinutes() + 30);

                var heure_debut = (heure_debut_heure < 10 ? '0' : '') + heure_debut_heure + ':' + (
                    heure_debut_minute < 10 ? '0' : '') + heure_debut_minute;

                var heure_fin_heure = heure_fin.getHours();
                var heure_fin_minute = heure_fin.getMinutes();
                var heure_fin_formatee = (heure_fin_heure < 10 ? '0' : '') + heure_fin_heure + ':' +
                    (
                        heure_fin_minute < 10 ? '0' : '') + heure_fin_minute;

                var plannifierLe = clickedDate.toISOString();
                var dayOfWeek = (clickedDate.getDay() === 0) ? 7 : clickedDate.getDay();

                if (isAdmin) {
                    var userId = document.getElementById('salarie').value;
                } else {
                    var userId = @json(auth()->user()->id)
                }

                Swal.fire({
                    title: 'Créer une nouvelle tâche',
                    html: `
                            <div style="text-align: center; font-size: 16px; line-height: 1.5;">
                              <p><strong>Souhaitez-vous créer une tâche pour <span style="color: #007bff;">${heure_debut}</span> ce jour ?</strong></p>
                              <p style="margin-top: 10px; color: #555;">
                                Si vous n'avez pas encore importé votre planning,
                                <br>
                                <span style="font-weight: bold; color: #dc3545;">il ne sera plus possible de le faire ensuite.</span>
                              </p>
                            </div>
                        `,
                    showCancelButton: true,
                    confirmButtonText: 'Oui, créer',
                    cancelButtonText: 'Annuler',
                    preConfirm: () => {
                        window.location.href = '/planning/create?heure_debut=' +
                            encodeURIComponent(
                                heure_debut) + '&heure_fin=' + encodeURIComponent(
                                heure_fin_formatee) + '&plannifier_le=' +
                            encodeURIComponent(
                                plannifierLe) + '&user_id=' + encodeURIComponent(
                                userId);
                    }
                });
            },
            customButtons: {
                importerPlanning: {
                    text: 'Importer mon planning',
                    click: function() {
                        const view = calendar.view;
                        console.log("Current Start Date:", view.currentStart);
                        console.log("Current End Date:", view.currentEnd);

                        const formattedStartDate = formatDate(view.currentStart);
                        const formattedEndDate = formatDate(view.currentEnd);
                        console.log("Formatted Start Date:", formattedStartDate);
                        console.log("Formatted End Date:", formattedEndDate);

                        fetch(
                                `/plannings/importer?start=${formattedStartDate}&end=${formattedEndDate}`
                            )
                            .then((response) => response.json())
                            .then((data) => {
                                if (data.error) {
                                    Swal.fire('Erreur', data.error, 'error');
                                } else {
                                    data.forEach((event) => {
                                        calendar.addEvent({
                                            title: `${event.title}`,
                                            start: event.start,
                                            end: event.end,
                                            location: event.location,
                                            description: event.user,
                                            id: event.id,
                                        });
                                    });
                                    Swal.fire({
                                            title: 'Succès',
                                            text: 'Le planning a été importé avec succès.',
                                            icon: 'success',
                                            timer: 2000,
                                            showConfirmButton: false
                                        })
                                        .then(() => {
                                            const importButton = document.querySelector(
                                                '.fc-importerPlanning-button');
                                            if (importButton) {
                                                importButton.style.display = 'none';
                                            }
                                            calendar.refetchEvents();
                                        });
                                }
                            })
                            .catch((error) => {
                                console.error('Erreur lors de l\'importation:', error);
                                Swal.fire('Erreur', 'Une erreur est survenue.', 'error');
                            });
                    },
                },
            },
            eventClick: function(info) {
                const eventId = info.event.id;

                if (!eventId) {
                    Swal.fire('Erreur', 'Cet événement n\'a pas d\'ID valide.', 'error');
                    return;
                }

                const eventEndDate = info.event.end;
                const today = new Date();

                fetch(`/planning/isTacheValidated/${eventId}`)
                    .then((response) => response.json())
                    .then((data) => {
                        const isAdmin = @json(auth()->user()->isA('admin'));
                        const isValidated = data.is_validated;

                        const csrfToken = document.querySelector('meta[name="csrf-token"]')
                            .getAttribute(
                                'content');

                        let popupHtml =
                            `<p><strong>Titre:</strong> ${info.event.title}</p>
                               <p><strong>Début:</strong> ${info.event.start.toLocaleString()}</p>
                               <p><strong>Fin:</strong> ${info.event.end.toLocaleString()}</p>
                               <p><strong>Lieu:</strong> ${info.event.extendedProps.location || 'Non spécifié'}</p>
                              `;
                        if (!isValidated || isAdmin) {
                            popupHtml +=
                                `<form id="delete-event-form-${info.event.id}" action="/planning/${info.event.id}" method="POST">
                                  @csrf
                                  <input type="hidden" name="_method" value="DELETE">
                                  <input type="hidden" name="_token" value="${csrfToken}">
                                  <button type="button" class="btn btn-danger" onclick="confirmDelete(${info.event.id})">Supprimer l'événement</button>
                                </form>
                              `;
                        }
                        if (!isValidated && eventEndDate <= today && !isAdmin) {
                            popupHtml += `
                                  <p class="text-danger"><strong>Cette tâche est terminée mais non validée.</strong></p>
                                `;
                        }

                        if (!isAdmin) {
                            Swal.fire({
                                title: 'Détails de l\'événement',
                                html: popupHtml,
                                icon: 'warning',
                                showCancelButton: true,
                                cancelButtonText: 'Retour',
                                confirmButtonText: isValidated ? 'Tâche déjà validée' :
                                    'Valider la tâche',
                                showDenyButton: true,
                                denyButtonText: 'Modifier',
                                customClass: {
                                    denyButton: 'btn btn-warning'
                                },
                                didOpen: () => {
                                    if (isValidated || eventEndDate > today) {
                                        const confirmButton = Swal
                                            .getConfirmButton();
                                        confirmButton.disabled =
                                            true;
                                    }
                                    if (isValidated) {
                                        const denyButton = Swal.getDenyButton();
                                        denyButton.hidden =
                                            true;
                                    }
                                    document
                                        .getElementById(
                                            `delete-event-button-${info.event.id}`)
                                        .addEventListener('click', () =>
                                            confirmDelete(info
                                                .event.id));
                                },
                            }).then((result) => {
                                if (result.isDenied) {
                                    window.location.href =
                                        `/planning/${info.event.id}/edit`;
                                } else if (result.isConfirmed) {
                                    Swal.fire({
                                        title: 'Valider la tâche ?',
                                        text: 'Voulez-vous valider cette tâche ?',
                                        icon: 'question',
                                        showCancelButton: true,
                                        confirmButtonText: 'Valider',
                                        cancelButtonText: 'Annuler',
                                    }).then((confirmationResult) => {
                                        if (confirmationResult.isConfirmed) {
                                            const is_validated = 1;
                                            fetch(`/planning/validateTache/${info.event.id}`, {
                                                    method: 'POST',
                                                    headers: {
                                                        'Content-Type': 'application/json',
                                                        'X-CSRF-TOKEN': csrfToken,
                                                        'Accept': 'application/json',
                                                    },
                                                    body: JSON.stringify({
                                                        _method: 'PUT',
                                                        event_id: info
                                                            .event
                                                            .id,
                                                        is_validated,
                                                        duration: (
                                                                info
                                                                .event
                                                                .end -
                                                                info
                                                                .event
                                                                .start
                                                            ) /
                                                            60000,
                                                        user_id: {!! auth()->user()->id !!},
                                                    }),
                                                })
                                                .then((response) => response
                                                    .json())
                                                .then((data) => {
                                                    if (data.success) {
                                                        Swal.fire({
                                                            title: 'Succès',
                                                            text: 'La tâche a été validée.',
                                                            icon: 'success',
                                                            timer: 2000,
                                                            showConfirmButton: false
                                                        });
                                                        updateProgress(
                                                            parseFloat(
                                                                data
                                                                .hoursThisWeek
                                                            ) ||
                                                            0);
                                                    } else {
                                                        Swal.fire('Erreur',
                                                            data
                                                            .message,
                                                            'error');
                                                    }
                                                })
                                                .catch((error) => {
                                                    console.error(
                                                        'Erreur réseau:',
                                                        error);
                                                    Swal.fire('Erreur',
                                                        'Une erreur est survenue.',
                                                        'error');
                                                });
                                        }
                                    });
                                }
                            });
                        }
                        // Si l'utilisateur est un admin ou l'événement est validé
                        else {
                            Swal.fire({
                                title: 'Détails de l\'événement',
                                html: popupHtml,
                                icon: isValidated ? 'info' : 'warning',
                                confirmButtonText: 'Retour',
                                showDenyButton: true,
                                denyButtonText: 'Modifier',
                                customClass: {
                                    denyButton: 'btn btn-warning'
                                },
                                didOpen: () => {
                                    document
                                        .getElementById(
                                            `delete-event-button-${info.event.id}`)
                                        .addEventListener('click', () =>
                                            confirmDelete(info
                                                .event.id));
                                },
                            }).then((result) => {
                                if (result.isDenied) {
                                    window.location.href =
                                        `/planning/${info.event.id}/edit`;
                                }
                            });
                        }
                    })
                    .catch((error) => {
                        console.error('Erreur lors de la récupération des données:', error);
                        Swal.fire('Erreur', 'Une erreur est survenue.', 'error');
                    });
            },


            height: 'auto',
        });

        // Ajouter les tâches passées
        @if (isset($tachesList))
            addTasksToCalendar(@json($tachesList), calendar);
        @endif


        calendar.render();
    </script>

</x-app-layout>
