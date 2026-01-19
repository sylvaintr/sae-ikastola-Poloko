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
        selectable: true, // sélection plage
        eventStartEditable: false,
        eventDurationEditable: false,

        events: eventsUrl, // JSON feed URL :contentReference[oaicite:2]{index=2}

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

            const modal = new bootstrap.Modal(modalEl);

            const { title } = info.event;
            const props = info.event.extendedProps || {};
            const showUrlTemplate = el.dataset.showUrlTemplate;
            const linkEl = modalEl.querySelector('#eventDetailLink');
            if (linkEl && showUrlTemplate) {
                linkEl.href = showUrlTemplate.replace('__ID__', info.event.id);
            }

            // Remplissage
            modalEl.querySelector('#eventDetailTitle').textContent = title;
            modalEl.querySelector('#eventDetailDate').textContent = props.date || '';

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
    }

    calendar.render();
});
