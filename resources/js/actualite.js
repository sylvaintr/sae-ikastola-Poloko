import { marked } from "marked";
import DataTable from 'datatables.net-dt';
import 'datatables.net-responsive-dt';
import { dataTableLangs } from './app'

window.AfficherMarkdownfromBalise = function(idinput, idoutput) {
    const md = document.getElementById(idinput).value;
    document.getElementById(idoutput).innerHTML = marked.parse(md);
}

window.AfficherMarkdownfromTexte = function(texte, idoutput) {
    document.getElementById(idoutput).innerHTML = marked.parse(texte);
}


window.afficherDataTable = function(id) {
    
  

    try {
        const table = new DataTable('#' + id, {
            processing: true,
            serverSide: true,
            autoWidth: false,
            ajax: {
                url: location.pathname + "/data",
                data: function(d) {
                    // read filter controls if present
                    const typeEl = document.getElementById('filter-type');
                    const etatEl = document.getElementById('filter-etat');
                    const etiquetteEl = document.getElementById('filter-etiquette');
                    if (typeEl) d.type = typeEl.value;
                    if (etatEl) d.etat = etatEl.value;
                    if (etiquetteEl) d.etiquette = etiquetteEl.value;
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