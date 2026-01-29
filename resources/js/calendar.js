import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import interactionPlugin from '@fullcalendar/interaction';
import frLocale from '@fullcalendar/core/locales/fr';
import euLocale from '@fullcalendar/core/locales/eu';

function getCsrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
}

document.addEventListener('DOMContentLoaded', () => {
    const el = document.getElementById('calendar-root');
    if (!el) return;

    const eventsUrl = el.dataset.eventsUrl;
    const updateUrlTemplate = el.dataset.updateUrlTemplate; // ex: /calendrier/events/__ID__
    const eventObligatoireLabel = el.dataset.eventObligatoire || 'Événement obligatoire';
    const noDescriptionLabel = el.dataset.noDescription || 'Aucune description';

    // Labels pour les demandes
    const demandeLabel = el.dataset.demandeLabel || 'Demande';
    const demandeUrgenceLabel = el.dataset.demandeUrgence || 'Urgence';
    const demandeEtatLabel = el.dataset.demandeEtat || 'État';
    const demandeShowUrl = el.dataset.demandeShowUrl || '/demandes/__ID__';

    // Locale du calendrier (fr ou eu pour basque)
    const calendarLocale = el.dataset.locale || 'fr';

    const calendar = new Calendar(el, {
        plugins: [dayGridPlugin, timeGridPlugin, interactionPlugin],
        locales: [frLocale, euLocale],
        initialView: 'dayGridMonth',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay',
        },

        locale: calendarLocale,
        firstDay: 1,
        nowIndicator: true,

        // Affichage des événements - ne pas afficher l'heure dans le titre
        displayEventTime: false,
        eventDisplay: 'block',

        // Couleurs et styles des événements
        eventBackgroundColor: '#3788d8',
        eventBorderColor: '#2c6cb0',
        eventTextColor: '#ffffff',

        // Limiter l'affichage et ajouter "+X autres" si trop d'événements
        dayMaxEvents: 3,
        moreLinkText: 'autres',

        // Sélection (si tu veux créer plus tard via un modal)
        selectable: true,

        // ✅ Si tu veux ACTIVER le drag/drop + resize :
        // editable: true,
        // eventStartEditable: true,
        // eventDurationEditable: true,

        // ✅ Si tu veux le calendrier non modifiable (actuel) :
        editable: false,
        eventStartEditable: false,
        eventDurationEditable: false,

        events: eventsUrl,

        // Personnaliser l'affichage des événements
        eventDidMount: function(info) {
            // Ajouter un tooltip avec le titre complet
            info.el.setAttribute('title', info.event.title);

            const props = info.event.extendedProps || {};

            // Style pour les demandes
            if (props.type === 'demande') {
                info.el.classList.add('fc-event-demande');

                // Couleur selon l'urgence
                const urgence = props.urgence?.toLowerCase() || '';
                if (urgence === 'élevée' || urgence === 'elevee') {
                    info.el.style.backgroundColor = '#dc3545'; // Rouge
                    info.el.style.borderColor = '#b02a37';
                } else if (urgence === 'moyenne') {
                    info.el.style.backgroundColor = '#fd7e14'; // Orange
                    info.el.style.borderColor = '#d96a0b';
                } else {
                    info.el.style.backgroundColor = '#ffc107'; // Jaune
                    info.el.style.borderColor = '#d9a406';
                    info.el.style.color = '#212529'; // Texte sombre pour jaune
                }
            }
            // Ajouter une classe pour les événements obligatoires
            else if (props.obligatoire) {
                info.el.classList.add('fc-event-obligatoire');
                info.el.style.backgroundColor = '#dc3545';
                info.el.style.borderColor = '#b02a37';
            }
        },

        eventDrop: async (info) => {
            await persistMoveResize(info, updateUrlTemplate);
        },
        eventResize: async (info) => {
            await persistMoveResize(info, updateUrlTemplate);
        },

        eventClick: function (info) {
            info.jsEvent.preventDefault();

            const { title } = info.event;
            const props = info.event.extendedProps || {};

            // Si c'est une demande, rediriger vers la page de détail
            if (props.type === 'demande') {
                const demandeId = info.event.id.replace('demande-', '');
                const url = demandeShowUrl.replace('__ID__', demandeId);
                window.location.href = url;
                return;
            }

            // Sinon, afficher le modal pour les événements
            const modalEl = document.getElementById('eventDetailModal');
            if (!modalEl) return;

            // Bootstrap doit être dispo globalement
            const modal = new bootstrap.Modal(modalEl);

            // ✅ Affichage date/heure depuis l'API (startLabel/endLabel)
            const dateText = props.endLabel ? `${props.startLabel} → ${props.endLabel}` : props.startLabel || '';

            modalEl.querySelector('#eventDetailTitle').textContent = title || 'Événement';
            modalEl.querySelector('#eventDetailDate').textContent = dateText;

            const descEl = modalEl.querySelector('#eventDetailDescription');
            descEl.textContent = props.description || noDescriptionLabel;

            const badgeEl = modalEl.querySelector('#eventDetailObligatoire');
            if (props.obligatoire) {
                badgeEl.textContent = eventObligatoireLabel;
                badgeEl.className = 'badge rounded-pill bg-danger';
                badgeEl.classList.remove('d-none');
            } else {
                badgeEl.classList.add('d-none');
            }

            modal.show();
        },
    });

    async function persistMoveResize(info, template) {
        if (!template) return;

        const url = template.replace('__ID__', info.event.id);

        const payload = {
            start: info.event.start?.toISOString(),
            end: info.event.end?.toISOString() ?? null,
        };

        try {
            const res = await fetch(url, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                    Accept: 'application/json',
                },
                body: JSON.stringify(payload),
            });

            if (!res.ok) {
                info.revert();
                console.error('Update failed', await res.text());
            }
        } catch (e) {
            info.revert();
            console.error('Update error', e);
        }
    }

    calendar.render();
});
