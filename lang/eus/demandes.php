<?php

return [
    'toolbar' => [
        'export' => [
            'eu' => 'Esportatu (CSV)',
            'fr' => 'Exporter (CSV)',
            'help' => [
                'eu' => 'CSV fitxategia formatu irakurgarri bihurtzeko, erabili webgune doako bat, adibidez: ConvertCSV.com edo CSVed.com',
                'fr' => 'Pour convertir le fichier CSV en format lisible, utilisez un site gratuit comme ConvertCSV.com ou CSVed.com',
            ],
        ],
        'create' => [
            'eu' => 'Sortu txartel eskaera',
            'fr' => 'Créer une demande de ticket',
        ],
    ],
    'status' => [
        'label' => [
            'eu' => 'Egoera',
            'fr' => 'Statut',
        ],
    ],
    'history_statuses' => [
        'created' => 'Eskaera sortua',
        'progress' => 'Aurrerapena',
        'done' => 'Eginda',
        'done_description' => 'Eskaera amaitutzat markatua.',
    ],
    'search' => [
        'label' => [
            'eu' => 'Sartu eskaeraren ID bat',
            'fr' => 'Entrez un request ID',
        ],
        'placeholder' => 'Entrez un request ID',
    ],
    'filters' => [
        'toggle' => [
            'eu' => 'Iragazi arabera',
            'fr' => 'Filtrer par',
        ],
        'status' => [
            'eu' => 'Egoera',
            'fr' => 'Statut',
        ],
        'type' => [
            'eu' => 'Jatorra',
            'fr' => 'Type',
        ],
        'urgency' => [
            'eu' => 'Larrialdia',
            'fr' => 'Urgence',
        ],
        'date_min' => [
            'eu' => 'Data min',
            'fr' => 'Date min',
        ],
        'date_max' => [
            'eu' => 'Data max',
            'fr' => 'Date max',
        ],
        'evenement' => [
            'eu' => 'Gertaera',
            'fr' => 'Événement',
        ],
        'reset' => 'Berrezarri',
        'submit' => 'Iragazi',
        'options' => [
            'all_status' => 'Egoera guztiak',
            'all_types' => 'Mota guztiak',
            'all_urgencies' => 'Larrialdi guztiak',
            'all_evenements' => 'Gertaera guztiak',
            'no_evenement' => 'Gertaerarik gabe',
        ],
    ],
    'table' => [
        'columns' => [
            'id' => [
                'eu' => 'Eskatu ID',
                'fr' => 'Request ID',
            ],
            'date' => [
                'eu' => 'Data',
                'fr' => 'Date',
            ],
            'title' => [
                'eu' => 'Izenburua',
                'fr' => 'Titre',
            ],
            'type' => [
                'eu' => 'Jatorra',
                'fr' => 'Type',
            ],
            'urgency' => [
                'eu' => 'Larrialdia',
                'fr' => 'Urgence',
            ],
            'status' => [
                'eu' => 'Egoera',
                'fr' => 'Statut',
            ],
            'actions' => [
                'eu' => 'Ekintzak',
                'fr' => 'Actions',
            ],
        ],
        'empty' => 'Ez dago momentuz eskaerarik.',
        'sort' => [
            'id' => 'IDaren arabera ordenatu',
            'date' => 'Dataz ordenatu',
            'type' => 'Motaz ordenatu',
            'urgency' => 'Larrialdiaz ordenatu',
            'status' => 'Egoeraz ordenatu',
        ],
        'urgency_high_hint' => 'Larrialdi maila handia hauteman da',
    ],
    'modals' => [
        'delete' => [
            'title' => 'Ezabatu eskaera',
            'message' => 'Ziur eskaera hau ezabatu nahi duzula?',
            'cancel' => 'Utzi',
            'confirm' => 'Ezabatu',
        ],
        'history_view' => [
            'title' => 'Aurrerapenaren xehetasuna',
            'fields' => [
                'title' => 'Izenburua',
                'date' => 'Data',
                'expense' => 'Gastuak',
                'description' => 'Deskribapena',
            ],
        ],
    ],
    'form' => [
        'create_title' => 'Sortu txartel eskaera',
        'create_subtitle' => 'Bete beheko eremuak eskaera berria sortzeko.',
        'edit_title' => 'Eskaera eguneratu',
        'edit_subtitle' => 'Eguneratu eskaeraren informazioa.',
        'labels' => [
            'title' => [
                'eu' => 'Izenburua',
                'fr' => 'Titre',
            ],
            'urgency' => [
                'eu' => 'Larrialdia',
                'fr' => 'Urgence',
            ],
            'description' => [
                'eu' => 'Deskribapena',
                'fr' => 'Description',
            ],
            'type' => [
                'eu' => 'Jatorra',
                'fr' => 'Type',
            ],
            'planned_expense' => [
                'eu' => 'Gastu aurreikuspena',
                'fr' => 'Dépense prévisionnelle (€)',
            ],
            'photo' => [
                'eu' => 'Argazkia',
                'fr' => 'Photo',
            ],
            'assigne' => [
                'eu' => 'Batzordea esleitu',
                'fr' => 'Assigner à une commission',
                'none' => 'Batzorderik esleitu gabe',
            ],
        ],
        'buttons' => [
            'back' => [
                'eu' => 'Itzuli',
                'fr' => 'Retour',
            ],
            'save' => [
                'eu' => 'Gorde',
                'fr' => 'Enregistrer',
            ],
            'upload' => [
                'eu' => 'Fitxategi bat hautatu',
                'fr' => 'Sélectionner un fichier',
            ],
            'disabled' => 'Eskaera amaituta',
            'disabled_sub' => 'Eskaera itxita',
        ],
        'no_file' => 'Ez da fitxategirik hautatu.',
    ],
    'history' => [
        'section' => [
            'description' => [
                'eu' => 'Izenburua',
                'fr' => 'Titre',
            ],
            'photo' => [
                'eu' => 'Argazkia',
                'fr' => 'Photo',
            ],
            'history' => [
                'eu' => 'Historikoa',
                'fr' => 'Historique',
            ],
        ],
        'columns' => [
            'status' => [
                'eu' => 'Egoera',
                'fr' => 'Statut',
            ],
            'date' => [
                'eu' => 'Data',
                'fr' => 'Date',
            ],
            'title' => [
                'eu' => 'Izenburua',
                'fr' => 'Titre',
            ],
            'assignment' => [
                'eu' => 'Esleipena',
                'fr' => 'Assignation',
            ],
            'expense' => [
                'eu' => 'Gastuak',
                'fr' => 'Dépenses',
            ],
            'actions' => [
                'eu' => 'Ekintzak',
                'fr' => 'Actions',
            ],
        ],
        'planned' => 'Gastu aurreikuspena',
        'real' => 'Benetako gastua',
        'button' => [
            'eu' => 'Gehitu aurrerapena',
            'fr' => 'Ajouter un avancement',
        ],
        'empty' => 'Ekintzen kronologia hemen agertuko da.',
    ],
    'history_form' => [
        'heading' => [
            'eu' => 'Gehitu aurrerapena',
            'fr' => 'Ajouter un avancement',
        ],
        'subtitle' => [
            'eu' => 'Bete inprimaki hau historikoan aurrerapen berria erregistratzeko.',
            'fr' => 'Complétez ce formulaire pour enregistrer un nouvel avancement dans l\'historique.',
        ],
        'fields' => [
            'title' => [
                'eu' => 'Izenburua',
                'fr' => 'Titre',
            ],
            'description' => [
                'eu' => 'Deskribapena',
                'fr' => 'Description',
            ],
            'expense' => [
                'eu' => 'Gastuak',
                'fr' => 'Dépenses',
            ],
        ],
        'button' => [
            'eu' => 'Sortu eskaera',
            'fr' => 'Créer un avancement',
        ],
        'back' => [
            'eu' => 'Itzuli',
            'fr' => 'Retour',
        ],
        'link' => '← Itzuli eskaera :id -ra',
    ],
    'actions' => [
        'view' => 'Ikusi',
        'edit' => 'Editatu',
        'delete' => 'Ezabatu',
        'validate' => 'Balioztatu',
        'close' => 'Itxi',
    ],
    'show' => [
        'type_default' => 'Gertakaria',
        'reported_by' => ':name jakinarazia • :date',
        'assigned_to' => 'Esleitutako batzordea',
        'back' => [
            'eu' => 'Itzuli eskaeretara',
            'fr' => 'Retour aux demandes',
        ],
        'photo_alt' => 'Irudia :name',
    ],
    'messages' => [
        'created' => 'Demande créée avec succès.',
        'updated' => 'Demande mise à jour.',
        'deleted' => 'Demande supprimée.',
        'history_added' => 'Nouvel avancement ajouté.',
        'validated' => 'Demande clôturée.',
        'locked' => 'La demande est déjà terminée.',
        'history_locked' => 'Eskaera hau amaituta dago, ezin da aurrerapenik gehitu.',
    ],
    'export' => [
        'demande_title' => 'Eskaera',
        'historique_title' => 'Historikoa',
        'id' => 'ID',
        'titre' => 'Izenburua',
        'description' => 'Deskribapena',
        'type' => 'Mota',
        'etat' => 'Egoera',
        'urgence' => 'Larrialdia',
        'date_creation' => 'Sortze data',
        'date_fin' => 'Amaiera data',
        'montant_previsionnel' => 'Aurreikuspen gastua',
        'montant_reel' => 'Benetako gastua',
        'realisateurs' => 'Egileak',
    ],
];

