import jQuery from 'jquery';
globalThis.$ = globalThis.jQuery = jQuery;

import * as bootstrap from 'bootstrap';
globalThis.bootstrap = bootstrap;

import './bootstrap';
import './children-selector';

// Register jQuery DataTables plugin globally so legacy inline scripts using
// `$('.datatable-taches').DataTable()` work correctly.
import 'datatables.net';
import 'datatables.net-dt';
import 'datatables.net-responsive';
import 'datatables.net-responsive-dt';

// Importation de Bootstrap Icons
import 'bootstrap-icons/font/bootstrap-icons.css';

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
