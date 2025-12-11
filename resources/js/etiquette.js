import DataTable from 'datatables.net-dt';
import 'datatables.net-responsive-dt';
import { dataTableLangs } from './app'

globalThis.afficherDataTableEtiquettes = function(id) {
    try {
        const table = new DataTable('#' + id, {
            processing: true,
            serverSide: true,
            autoWidth: false,
            ajax: {
                url: location.pathname + "/data",
                data: function(d) {
                    const nameEl = document.getElementById('filter-etiquette-name');
                    const roleEl = document.getElementById('filter-role');
                    if (nameEl) d.name = nameEl.value;
                    if (roleEl) d.role = roleEl.value;
                }
            },
            columns: [
                { data: 'idEtiquette', name: 'idEtiquette' , className: 'dt-left'},
                { data: 'nom', name: 'nom' },
                { data: 'roles', name: 'roles', className: 'dt-left' },
                {
                    data: 'actions', 
                    name: 'actions', 
                    orderable: false,
                    searchable: false, 
                    className: 'all',
                    width: '1%',
                    render: function (data, type, row) {
                        return '<div style="white-space: nowrap;">' + data + '</div>';
                    }
                }
            ],
            responsive: true,
            language: dataTableLangs[currentLang] || dataTableLangs.eus
        });

        // wire filter controls
        const nameEl = document.getElementById('filter-etiquette-name');
        const roleEl = document.getElementById('filter-role');
        const resetBtn = document.getElementById('reset-etiquette-filters');
        if (nameEl) nameEl.addEventListener('input', () => table.ajax.reload());
        if (roleEl) roleEl.addEventListener('change', () => table.ajax.reload());
        if (resetBtn) resetBtn.addEventListener('click', () => {
            if (nameEl) nameEl.value = '';
            if (roleEl) roleEl.value = '';
            table.ajax.reload();
        });
    } catch (e) { 
        console.error("DataTable initialization error:", e); 
    }
}