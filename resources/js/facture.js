import DataTable from 'datatables.net-dt';
import 'datatables.net-dt/css/dataTables.dataTables.css';


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
    new DataTable('#myTable', {
        processing: true,    // montre un spinner pendant le chargement
        serverSide: true,    // active le mode serveur
        ajax: '/sae-ikastola-Poloko/public/factures-data', // l’URL définie dans Laravel
        columns: [
            { data: 'idFacture', name: 'idFacture' },
            { data: 'titre', name: 'titre' },
            { data: 'etat', name: 'etat' },
            { data: 'idFamille', name: 'idFamille' },
            {
                data: 'dateC', name: 'dateC', render: function (data) {
                    const date = new Date(data);
                    return date.toLocaleDateString();
                }
            },
            { data: 'actions', name: 'actions', orderable: false, searchable: false }
        ],
        language: dataTableLangs[currentLang] || dataTableLangs.fr,
        responsive: true
    });
});

