import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import interactionPlugin from '@fullcalendar/interaction';

function getCsrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
}

document.addEventListener('DOMContentLoaded', () => {
    const el = document.getElementById('calendar-root');
    if (!el) return;

    const eventsUrl = el.dataset.eventsUrl;
    const updateUrlTemplate = el.dataset.updateUrlTemplate; // ex: /calendrier/events/__ID__

    const calendar = new Calendar(el, {
        plugins: [dayGridPlugin, timeGridPlugin, interactionPlugin],
        initialView: 'dayGridMonth',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay',
        },

        locale: 'fr',
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

            // Ajouter une classe pour les événements obligatoires
            if (info.event.extendedProps?.obligatoire) {
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

            const modalEl = document.getElementById('eventDetailModal');
            if (!modalEl) return;

            // Bootstrap doit être dispo globalement
            const modal = new bootstrap.Modal(modalEl);

            const { title } = info.event;
            const props = info.event.extendedProps || {};

            // ✅ Affichage date/heure depuis l'API (startLabel/endLabel)
            const dateText = props.endLabel ? `${props.startLabel} → ${props.endLabel}` : props.startLabel || '';

            modalEl.querySelector('#eventDetailTitle').textContent = title || 'Événement';
            modalEl.querySelector('#eventDetailDate').textContent = dateText;

            const descEl = modalEl.querySelector('#eventDetailDescription');
            descEl.textContent = props.description || 'Aucune description';

            const badgeEl = modalEl.querySelector('#eventDetailObligatoire');
            if (props.obligatoire) {
                badgeEl.textContent = 'Événement obligatoire';
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
