import DataTable from 'datatables.net-dt';
import 'datatables.net-responsive-dt';


// Langues similaires à facture.js
const dataTableLangs = {
    fr: {
        decimal: ",",
        thousands: " ",
        info: "Affichage de _START_ à _END_ sur _TOTAL_ entrées",
        infoEmpty: "Aucune donnée disponible",
        lengthMenu: "Afficher _MENU_ entrées",
        search: "Rechercher :",
        paginate: {
            first: "Premier",
            last: "Dernier",
            next: "Suivant",
            previous: "Précédent"
        }
    },
    eus: {
        decimal: ",",
        thousands: " ",
        info: "Erakusten _START_ _END_ arteko _TOTAL_ sarrera",
        infoEmpty: "Datuik ez dago eskuragarri",
        lengthMenu: "Erakutsi _MENU_ sarrera",
        search: "Bilatu:",
        paginate: {
            first: "Lehenengoa",
            last: "Azkena",
            next: "Hurrengoa",
            previous: "Aurrekoa"
        }
    }
};


document.addEventListener('DOMContentLoaded', function () {
    try {
        new DataTable('#TableClasses', {
            processing: true,
            serverSide: true,
            autoWidth: false,

            searching: false, // 🔥 Désactive la barre de recherche

            ajax: '/admin/classes/data',

            columns: [
                { data: 'nom', name: 'nom', className: 'dt-left' },
                { data: 'niveau', name: 'niveau', className: 'dt-left' },
                {
                    data: 'actions',
                    name: 'actions',
                    orderable: false,
                    searchable: false,
                    className: 'all',
                    width: '1%',
                    render: function (data) {
                        return '<div style="white-space: nowrap;">' + data + '</div>';
                    }
                }
            ],

            responsive: true,
            language: dataTableLangs[currentLang] || dataTableLangs.eus
        });

    } catch (e) {
        console.error("DataTable initialization error:", e);
    }
});