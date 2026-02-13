<?php

return [
    // Titres
    'title' => 'Gertaerak',
    'subtitle' => 'Erregistratutako gertaeren zerrenda',
    'create_title' => 'Gertaera berria sortu',
    'edit_title' => 'Gertaera aldatu',
    'show_title' => 'Gertaeraren xehetasunak',
    'calendar_title' => 'Egutegia',

    // Boutons
    'add' => 'Gertaera gehitu',
    'add_subtitle' => 'Gertaera berria sortu',
    'save' => 'Gorde',
    'create' => 'Sortu',
    'cancel' => 'Utzi',
    'back_to_list' => 'Itzuli gertaeren zerrendara',
    'export' => 'Esportatu',
    'export_subtitle' => 'Egutegia esportatu',

    // Champs
    'titre' => 'Izenburua',
    'description' => 'Deskribapena',
    'date' => 'Data',
    'start_date' => 'Hasiera data',
    'start_time' => 'Hasiera ordua',
    'end_date' => 'Amaiera data',
    'end_time' => 'Amaiera ordua',
    'end_date_hint' => 'Utzi hutsik gertaera egun berean amaitzen bada',
    'all_day' => 'Egun osoa',
    'obligatoire' => 'Derrigorrezkoa',
    'obligatoire_label' => 'Bai, gertaera hau derrigorrezkoa da',
    'statut' => 'Egoera',
    'actions' => 'Ekintzak',
    'cibles' => 'Helburuak',
    'roles' => 'Erlazionatutako rolak',

    // Statuts
    'status_obligatoire' => 'Derrigorrezkoa',
    'status_optionnel' => 'Hautazkoa',

    // Recherche/Tri
    'search' => 'Bilatu',
    'search_placeholder' => 'Gertaera bat bilatu...',
    'search_hint' => 'Izenburuaren edo IDaren arabera bilatu',
    'sort' => 'Ordenatu',
    'sort_id_desc' => 'ID - berrienak lehenik',
    'sort_id_asc' => 'ID - zaharrenak lehenik',
    'sort_date_desc' => 'Data - berrienak lehenik',
    'sort_date_asc' => 'Data - zaharrenak lehenik',

    // Cibles/Rôles
    'search_cible' => 'Helburu bat bilatu',
    'search_cible_placeholder' => 'Idatzi bilatzeko...',
    'cibles_selected' => 'Hautatutako helburuak',
    'no_cible_selected' => 'Ez da helbururik hautatu',
    'cible_error' => 'Gutxienez helburu bat hautatu behar da.',

    // Messages
    'no_events' => 'Une honetan ez dago gertaerarik erabilgarri.',
    'created_success' => 'Gertaera ongi sortu da',
    'updated_success' => 'Gertaera ongi eguneratu da',
    'deleted_success' => 'Gertaera ongi ezabatu da',

    // Modal suppression
    'delete_title' => 'Gertaera ezabatu',
    'delete_confirm' => 'Ziur zaude gertaera hau ezabatu nahi duzula',
    'delete' => 'Ezabatu',

    // Actions
    'action_view' => 'Xehetasunak ikusi',
    'action_edit' => 'Aldatu',
    'action_delete' => 'Ezabatu',

    // Calendrier
    'calendar_close' => 'Itxi',
    'event_obligatoire' => 'Gertaera derrigorrezkoa',
    'no_description' => 'Deskribapenik gabe',

    // Page détail (show)
    'cible' => 'Helburua',
    'cible_restricted' => 'Mugatua',
    'cible_all' => 'Denak',
    'recurrence' => 'Errepikatzea',
    'recurrence_annual' => 'Urtekoa',
    'no_description_provided' => 'Ez da deskribapenik eman.',
    'edit_event' => 'Gertaera aldatu',
    'add_recette' => 'Diru-sarrera gehitu',

    // Comptabilité
    'accounting' => 'Kontabilitatea',
    'type' => 'Mota',
    'amount' => 'Zenbatekoa',
    'status' => 'Egoera',
    'status_pending' => 'Zain',
    'no_recettes' => 'Une honetan ez dago diru-sarrerarik.',
    'total_depenses_prev' => 'Aurreikusitako gastuen guztira',
    'total_depenses' => 'Gastuen guztira',
    'total_recettes' => 'Diru-sarreren guztira',

    // Types de recette
    'type_recette' => 'Diru-sarrera',
    'type_depense_prev' => 'Aurreikusitako gastua',
    'type_depense' => 'Gastua',

    // Modal recette
    'quantity' => 'Kantitatea',
    'confirm_delete_recette' => 'Diru-sarrera hau ezabatu?',

    // Légende calendrier
    'legend' => 'Legenda',
    'legend_event' => 'Gertaera',
    'legend_event_obligatoire' => 'Gertaera derrigorrezkoa',
    'legend_demande_high' => 'Eskaera larria',
    'legend_demande_medium' => 'Eskaera ertaina',
    'legend_demande_low' => 'Eskaera txikia',

    // Export CSV
    'export_btn' => 'CSV esportatu',
    'export' => [
        'evenement_title' => 'Gertaera',
        'comptabilite_title' => 'Kontabilitatea',
        'id' => 'ID',
        'titre' => 'Izenburua',
        'description' => 'Deskribapena',
        'start_at' => 'Hasiera data',
        'end_at' => 'Amaiera data',
        'obligatoire' => 'Derrigorrezkoa',
        'roles' => 'Rolak',
        'type_col' => 'Mota',
        'description_col' => 'Deskribapena',
        'prix' => 'Prezioa',
        'quantite' => 'Kantitatea',
        'total' => 'Guztira',
        'total_recettes' => 'Diru-sarreren guztira',
        'total_depenses_prev' => 'Aurreikusitako gastuen guztira',
        'total_depenses' => 'Gastuen guztira',
    ],

    // Synchronisation calendrier
    'sync_button' => 'Sinkronizatu',
    'sync_title' => 'Egutegia sinkronizatu',
    'sync_description' => 'Gehitu egutegi hau zure aplikazio gogokoenean (Google Agenda, Outlook, Apple Calendar) eguneraketak automatikoki jasotzeko.',
    'sync_url' => 'Harpidetza URLa',
    'sync_instructions' => 'Gehitzeko argibideak',
    'copy_url' => 'URLa kopiatu',
    'url_copied' => 'URLa kopiatuta!',
    'regenerate_token' => 'Esteka birsortu',
    'regenerate_hint' => 'Zure esteka konprometituta badago',
    'confirm_regenerate' => 'Esteka birsortu? Aurreko estekak ez du gehiago funtzionatuko.',
    'token_regenerated' => 'Esteka berria ongi sortua.',
    'google_step_1' => 'Ireki Google Agenda',
    'google_step_2' => 'Klikatu "+" "Beste egutegiak" ondoan eta gero "URLetik"',
    'google_step_3' => 'Itsatsi goiko URLa eta klikatu "Egutegia gehitu"',
    'outlook_step_1' => 'Ireki Outlook',
    'outlook_step_2' => 'Joan Egutegia > Egutegia gehitu > Webetik harpidetu',
    'outlook_step_3' => 'Itsatsi URLa eta baieztatu',
    'apple_step_1' => 'Ireki Egutegia aplikazioa',
    'apple_step_2' => 'Fitxategia menua > Egutegi harpidetza berria',
    'apple_step_3' => 'Itsatsi URLa eta klikatu "Harpidetu"',
];
