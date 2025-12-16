import DataTable from 'datatables.net-dt';
import 'datatables.net-responsive-dt';
import { dataTableLangs } from './app'




document.addEventListener('DOMContentLoaded', function () {

    try {
        new DataTable('#TableFacture', {
            processing: true,
            serverSide: true,
            autoWidth: false,
            ajax: location.pathname + "s-data",
            columns: [
                { data: 'idFacture', name: 'idFacture', className: 'dt-left' },
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
                    searchable: false, className: 'text-end',
                    render: function (data, type, row) {
                        return data;
                    }

                }
            ],
            responsive: {
                details: {
                    display: $.fn.dataTable.Responsive.display.modal( {
                        header: function ( row ) {
                            var data = row.data();
                            return 'Détails';
                        }
                    } ),
                    renderer: $.fn.dataTable.Responsive.renderer.tableAll()
                }
            },
            language: dataTableLangs[currentLang] || dataTableLangs.eus
        });
    } catch (e) { console.error("DataTable initialization error:", e); }

});

