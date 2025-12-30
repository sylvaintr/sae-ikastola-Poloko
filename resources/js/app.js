import 'bootstrap/dist/js/bootstrap.bundle';
import 'bootstrap/dist/js/bootstrap.bundle.min';
import './bootstrap';
import './children-selector';
import Alpine from 'alpinejs';
window.Alpine = Alpine;
Alpine.start();
export const dataTableLangs = {
    fr: {
        decimal: ',',
        thousands: ' ',
        info: 'Affichage de _START_ à _END_ sur _TOTAL_ entrées',
        infoEmpty: 'Aucune donnée disponible',
        lengthMenu: 'Afficher _MENU_ entrées',
        search: 'Rechercher :',
        paginate: {
            first: 'Premier',
            last: 'Dernier',
            next: 'Suivant',
            previous: 'Précédent',
        },
    },
    eus: {
        decimal: ',',
        thousands: ' ',
        info: 'Erakusten _START_ _END_ arteko _TOTAL_ sarrera',
        infoEmpty: 'Datuik ez dago eskuragarri',
        lengthMenu: 'Erakutsi _MENU_ sarrera',
        search: 'Bilatu:',
        paginate: {
            first: 'Lehenengoa',
            last: 'Azkena',
            next: 'Hurrengoa',
            previous: 'Aurrekoa',
        },
    },
};
