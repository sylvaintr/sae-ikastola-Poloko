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
        new DataTable('#' + id, {
            processing: true,
            serverSide: true,
            autoWidth: false,
            ajax: location.pathname + "/data",
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


    } catch (e) { console.error("DataTable initialization error:", e); }

}