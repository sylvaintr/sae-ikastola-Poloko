import { marked } from "marked";
import DataTable from 'datatables.net-dt';
import 'datatables.net-responsive-dt';
import { dataTableLangs } from './app'

globalThis.AfficherMarkdownfromBalise = function(idinput, idoutput) {
    const md = document.getElementById(idinput).value;
    document.getElementById(idoutput).innerHTML = marked.parse(md);
}

globalThis.AfficherMarkdownfromTexte = function(texte, idoutput) {
    document.getElementById(idoutput).innerHTML = marked.parse(texte);
}


globalThis.afficherDataTable = function(id) {
    
  

    try {
        const tableEl = document.getElementById(id);
        const ajaxUrl = (tableEl?.dataset?.ajaxUrl) ? tableEl.dataset.ajaxUrl : (location.pathname + "/data");

        const table = new DataTable('#' + id, {
            processing: true,
            serverSide: true,
            autoWidth: false,
            ajax: {
                url: ajaxUrl,
                data: function(d) {
                    // read filter controls if present
                    const typeEl = document.getElementById('filter-type');
                    const etatEl = document.getElementById('filter-etat');
                    const etiquetteEl = document.getElementById('filter-etiquette');
                    if (typeEl) d.type = typeEl.value;
                    if (etatEl) d.etat = etatEl.value;
                    if (etiquetteEl) d.etiquette = etiquetteEl.value;
                },
                error: function(xhr, status, error) {
                    try {
                        const ct =   xhr?.getResponseHeader('content-type');
                        // If server returned an HTML page (likely a login redirect), go to login.
                        if (ct?.includes('text/html')) {
                            globalThis.location = '/login';
                            return;
                        }
                        // If status indicates unauthorized/forbidden, redirect to login as well
                        if (xhr.status === 401 || xhr.status === 403) {
                            globalThis.location = '/login';
                            return;
                        }
                    } catch (e) {
                        console.error('Error handling DataTable ajax error:', e);
                    }
                    console.error('DataTable AJAX error:', status, error, xhr);
                }
            },
            columns: [
                { data: 'titre', name: 'titre' },
                { data: 'etiquettes', name: 'etiquettes', className: 'dt-left' },
                {
                    data: 'dateP',
                    name: 'dateP',
                    render: function (data) {
                        const date = new Date(data);
                        return date.toLocaleDateString();
                    }
                },
                { data: 'etat', name: 'etat' },
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

        // Wire filter controls to reload the datatable
        const typeEl = document.getElementById('filter-type');
        const etatEl = document.getElementById('filter-etat');
        const etiquetteEl = document.getElementById('filter-etiquette');
        const resetBtn = document.getElementById('reset-filters');
        if (typeEl) typeEl.addEventListener('change', () => table.ajax.reload());
        if (etatEl) etatEl.addEventListener('change', () => table.ajax.reload());
        if (etiquetteEl) etiquetteEl.addEventListener('change', () => table.ajax.reload());
        if (resetBtn) resetBtn.addEventListener('click', () => {
            if (typeEl) typeEl.value = '';
            if (etatEl) etatEl.value = '';
            if (etiquetteEl) etiquetteEl.value = '';
            table.ajax.reload();
        });


    } catch (e) { console.error("DataTable initialization error:", e); }

}