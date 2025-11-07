import DataTable from 'datatables.net-bs5';


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
    let dataTable = new DataTable('#myTable');
    dataTable.processing(true);
    dataTable.serverSide(true);
    dataTable.ajax('/sae-ikastola-Poloko/public/factures-data');
    dataTable.columns([
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
    ]);
    dataTable.responsive(true);
    dataTable.language(dataTableLangs[currentLang] || dataTableLangs.eus);
});

