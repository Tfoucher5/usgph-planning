<x-app-layout>
    <x-slot:title>Planning - Type</x-slot:title>
    <div class="container mt-4">
        <div class="text-center mb-4">
            <h1 class="fw-bold text-primary display-5">Planning Type des Salariés</h1>
        </div>

        <script>
            function changeSalarie(salarieId) {
                window.location.href = `/tache/salarie/${salarie_id}`;
            }
        </script>

        @isset($isEmpty)
            @if ($isEmpty == true)
                <div class="alert alert-warning " role="alert">
                    Aucune donnée trouvée pour cet utilisateur.
                </div>
            @endif
        @endisset

        <div class="row mb-4">
            <div class="col-12 col-md-6 d-flex justify-content-center align-items-center">
                @if (isset($salaries) && $salaries->isNotEmpty())
                    <div class="d-flex justify-content-center align-items-center w-100">
                        <select name="salarie_id" id="salarie" class="form-select" onchange="changeSalarie(this.value)"
                            style="max-width: 250px; flex: 1;">
                            <option value="" disabled
                                {{ !isset($user) && !isset($userSelected) ? 'selected' : '' }}>-- Sélectionner un
                                salarié --</option>
                            @foreach ($salaries as $salarie)
                                <option value="{{ $salarie->id }}"
                                    {{ (isset($user) && $salarie->id == $user->id) || (isset($userSelected) && $salarie->id == $userSelected->id) ? 'selected' : '' }}>
                                    {{ $salarie->identity }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @else
                    <p class="text-muted text-center">Aucun salarié disponible.</p>
                @endif
            </div>
            <div class="col-12 col-md-6 d-flex justify-content-center align-items-center">
                <div class="d-flex justify-content-center mt-4">
                    <a href="{{ route('tache.create') }}" class="btn btn-primary btn-sm text-white">
                        <i class="fas fa-plus text-white"></i> Ajouter une tâche dans le planning prévisionnel
                    </a>
                </div>
            </div>
        </div>

        <div id="calendar" class="card shadow-sm h-auto mt-4"></div>
    </div>

    <script type="module">
        let calendar;
        const calendarEl = document.getElementById('calendar');
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

                    fetch(`/tache/${eventId}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': csrfToken,
                                'Content-Type': 'application/json',
                                'Accept': 'application/json'
                            },
                        })
                        .then((response) => response.json())
                        .then((data) => {
                            if (data.success) {
                                Swal.fire('Succès', 'L\'événement a été supprimé.', 'success').then(() => {
                                    const event = calendar.getEventById(eventId);
                                    event.remove();
                                    calendar.refetchEvents()
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
        document.addEventListener('DOMContentLoaded', function() {
            calendar = new Calendar(calendarEl, {
                plugins: [window.timeGridPlugin, window.interactionPlugin],
                headerToolbar: false,
                allDaySlot: false,
                editable: true,
                eventOverlap: false,
                initialView: 'timeGridWeek',
                locale: 'fr',
                weekNumbers: false,
                nowIndicator: true,
                dayHeaderFormat: {
                    weekday: 'long'
                },
                slotDuration: '00:15:00',
                slotLabelInterval: '01:00:00',
                slotMinTime: '00:00:00',
                slotMaxTime: '24:00:00',
                scrollTime: '08:00:00',
                height: 'auto',
                navLinks: false,
                dateClick: false,

                datesSet: function(info) {

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
                                            if (el.textContent.includes(event
                                                    .title)) {
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

                                            const newUrl = window.location.href.split(
                                                '?')[0];
                                            history.replaceState(null, '', newUrl);

                                            setTimeout(() => {
                                                eventEl.style.backgroundColor =
                                                    '';
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

                                    const timeGridContainer = document.querySelector(
                                        '.fc-timegrid-slots');
                                    if (timeGridContainer) {
                                        const slotHeight = timeGridContainer.scrollHeight / 24;
                                        const scrollPosition = (currentHour + currentMinutes /
                                                60) *
                                            slotHeight;

                                        const containerHeight = document.querySelector(
                                                '.fc-timegrid-body')
                                            .clientHeight;
                                        const finalPosition = Math.max(0, scrollPosition - (
                                            containerHeight / 2));

                                        timeGridContainer.closest('.fc-timegrid-body')
                                            .scrollTop =
                                            finalPosition;
                                    }
                                }
                            }, 300);
                        }
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

                            const jourDeLaSemaine = event.start.getDay() === 0 ? 7 : event.start
                                .getDay();
                            const userId = @json(auth()->user()->id);

                            fetch(`/tache/${event.id}`, {
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
                                        jour: jourDeLaSemaine,
                                        user_id: info.event.userId,
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
                                                'Une erreur est survenue lors de la mise à jour déplacement 1',
                                            icon: 'error'
                                        });
                                    }
                                })
                                .catch(error => {
                                    console.error('Erreur lors de la mise à jour :', error);
                                    info.revert();
                                    Swal.fire({
                                        title: 'Erreur',
                                        text: 'Une erreur est survenue lors de la mise à jour déplacement 2',
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

                            const userId = @json(auth()->user()->id);

                            fetch(`/tache/${event.id}`, {
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
                                        user_id: info.event.userId,
                                        overlapFalse: true,
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
                        (heure_fin_minute < 10 ? '0' : '') + heure_fin_minute;

                    var dayOfWeek = clickedDate.getDay();

                    var daysOfWeek = ["Dimanche", "Lundi", "Mardi", "Mercredi", "Jeudi", "Vendredi",
                        "Samedi"
                    ];
                    var dayName = daysOfWeek[dayOfWeek];

                    var userId = document.getElementById('salarie').value;

                    Swal.fire({
                        title: 'Créer une nouvelle tâche',
                        text: `Souhaitez-vous créer une tâche pour ${heure_debut} (${dayName}) ?`,
                        showCancelButton: true,
                        confirmButtonText: 'Oui, créer',
                        cancelButtonText: 'Annuler',
                        preConfirm: () => {
                            window.location.href = '/tache/create?heure_debut=' +
                                encodeURIComponent(heure_debut) +
                                '&heure_fin=' + encodeURIComponent(heure_fin_formatee) +
                                '&jour=' + encodeURIComponent(dayOfWeek) + '&user_id=' +
                                encodeURIComponent(userId);
                        }
                    });
                },
                eventClick: function(info) {
                    const eventId = info.event.id;

                    if (!eventId) {
                        Swal.fire('Erreur', 'Cet événement n\'a pas d\'ID valide.', 'error');
                        return;
                    }

                    const isAdmin = @json(auth()->user()->isA('admin'));
                    var userId = document.getElementById('salarie').value;

                    const popupHtml = `
                      <p><strong>Titre :</strong> ${info.event.title}</p>
                      <p><strong>Lieu :</strong> ${info.event.extendedProps.location || 'Non spécifié'}</p>

                      <div class="mt-4 d-flex justify-content-between">
                          <a href="/tache/${eventId}/edit?user=${userId}" class="btn btn-warning text-dark">
                              <i class="fas fa-edit"></i> Modifier
                          </a>

                          <button type="button" class="btn btn-danger" onclick="confirmDelete(${eventId})">
                              <i class="fas fa-trash-alt"></i> Supprimer
                          </button>
                      </div>
                    `;

                    Swal.fire({
                        title: 'Détails de l\'événement',
                        html: popupHtml,
                        showConfirmButton: false,
                        showCloseButton: true
                    });
                }
            });

            // Ajout des événements
            @isset($tachesThisWeek)
                @foreach ($tachesThisWeek as $tache)
                    calendar.addEvent({
                        id: "{{ $tache['id'] }}",
                        title: "{{ $tache['title'] }}",
                        start: "{{ $tache['start'] }}",
                        end: "{{ $tache['end'] }}",
                        location: "{{ $tache['location'] }}",
                        description: "{{ $tache['description'] }}"
                    });
                @endforeach
            @endisset

            calendar.render();
        });
    </script>

</x-app-layout>
