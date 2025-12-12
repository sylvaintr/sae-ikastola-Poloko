import DataTable from 'datatables.net-dt';
import 'datatables.net-responsive-dt';
import { dataTableLangs } from './app'
import $ from "jquery";



document.addEventListener("DOMContentLoaded", function () {
    // 1. Cloner la ligne d'en-tête pour les filtres
    // On utilise jQuery ici car DataTables dépend souvent de jQuery pour cette manipulation DOM spécifique


    try {
        var table = new DataTable("#TableFacture", {
            processing: true,
            serverSide: true,
            autoWidth: false,
            // Important : Permet de garder le tri sur la ligne du haut et les filtres en bas
            orderCellsTop: true,
            ajax: location.pathname + "s-data",
            columnDefs: [
                { responsivePriority: 1, targets: 0 }, // ID Facture
                { responsivePriority: 2, targets: 5 }, // Actions
                { responsivePriority: 3, targets: 1 }, // Titre
                { responsivePriority: 4, targets: 2 }, // État
                { responsivePriority: 5, targets: 3 }, // ID Famille
                { responsivePriority: 6, targets: 4 }  // Date
            ],
            responsive: {
                details: {
                    type: 'column',
                    target: 'tr'
                }
            },
            columns: [
                {
                    data: "idFacture",
                    name: "idFacture",
                    className: "all dt-left",
                },
                { data: "titre", name: "titre" },
                { data: "etat", name: "etat" },
                { data: "idFamille", name: "idFamille", className: "dt-left" },
                {
                    data: "dateC",
                    name: "dateC",
                    render: function (data) {
                        const date = new Date(data);
                        return date.toLocaleDateString(); // Format local
                    },
                },
                {
                    data: "actions",
                    name: "actions",
                    orderable: false,
                    searchable: false,
                    className: "all",
                    width: "1%",
                    render: function (data, type, row) {
                        return (
                            '<div style="white-space: nowrap;">' +
                            data +
                            "</div>"
                        );
                    },
                },
            ],
            responsive: true,
            language: dataTableLangs[currentLang] || dataTableLangs.eus,

        });
        $('#filtreEtat').on('change', function () {
        var valeurSelectionnee = $(this).val();

        // On applique le filtre sur la colonne Index 2 (Etat)
        // .search( val ) prépare la recherche
        // .draw() lance l'appel Ajax au serveur
        table.column(2).search(valeurSelectionnee).draw();
           
        });
    } catch (e) {
        console.error("DataTable initialization error:", e);
    }
});
