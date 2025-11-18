import DataTable from 'datatables.net-dt';
import 'datatables.net-responsive-dt';


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
        new DataTable('#TableFacture', {
            processing: true,
            serverSide: true,
            autoWidth: false,
            ajax: location.pathname + "s-data",
            columns: [
                { data: 'idFacture', name: 'idFacture', className: 'all dt-left' },
                { data: 'titre', name: 'titre' },
                { data: 'etat', name: 'etat' },
                { data: 'idFamille', name: 'idFamille', className: 'dt-left' },
                {
                    data: 'dateC',
                    name: 'dateC',
                    render: function (data) {
                        const date = new Date(data);
                        return date.toLocaleDateString();
                    }
                },
                {
                    data: 'actions', name: 'actions', orderable: false,
                    searchable: false, className: 'all',
                    width: '1%', // Astuce pour "coller" au contenu
                    render: function (data, type, row) {
                        // ... votre HTML de boutons
                        return '<div style="white-space: nowrap;">' + data + '</div>';
                    }

                }
            ],
            responsive: true,
            language: dataTableLangs[currentLang] || dataTableLangs.eus
        });
    } catch (e) { console.error("DataTable initialization error:", e); }

});

