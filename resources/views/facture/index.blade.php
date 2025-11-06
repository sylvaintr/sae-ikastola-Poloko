<x-app-layout>
    <div class="container">
        <div>
            <h1 class="text-capitalize mb-0">{{ Lang::get('nav.factures', [], 'eus') }}</h1>
            @if (Lang::getLocale() == 'fr')
                <p class="text-capitalize">{{ Lang::get('nav.factures') }}</p>
            @endif
        </div>



        <table id="myTable" class="table table-bordered table-striped nowrap">
            <thead>
                <tr>
                    <th>ID facture</th>
                    <th>titre</th>
                    <th>état</th>
                    <th>parrent id</th>
                    <th>date creation</th>
                    <th>actions</th>
                </tr>
            </thead>

        </table>






        <script>
            const currentLang = "{{ app()->getLocale() }}";
        </script>

        @vite('resources/js/facture.js')

</x-app-layout>
