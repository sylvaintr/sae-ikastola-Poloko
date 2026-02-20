<?php

return [
    'status' => [
        'label' => [
            'eu' => 'Egoera',
            'fr' => 'Statut',
        ],
    ],
    'history_statuses' => [
        'created' => 'Orbana sortua',
        'progress' => 'Aurrerapena',
        'done' => 'Eginda',
        'done_description' => 'Orbana amaitutzat markatua.',
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
        'reset' => 'Berrezarri',
        'submit' => 'Iragazi',
        'options' => [
            'all_status' => 'Egoera guztiak',
            'all_types' => 'Mota guztiak',
            'all_urgencies' => 'Larrialdi guztiak',
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
            'title' => 'Ezabatu orbana',
            'message' => 'Ziur orbana hau ezabatu nahi duzula?',
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
        'create_title' => 'Sortu txartel orbana',
        'create_subtitle' => 'Bete beheko eremuak orbana berria sortzeko.',
        'edit_title' => 'Orbana eguneratu',
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
            'disabled' => 'Orbana amaituta',
            'disabled_sub' => 'Orbana itxita',
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
            'eu' => 'Sortu orbana',
            'fr' => 'Créer un avancement',
        ],
        'back' => [
            'eu' => 'Itzuli',
            'fr' => 'Retour',
        ],
        'link' => '← Itzuli orbana :id -ra',
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
        'back' => [
            'eu' => 'Itzuli eskaeretara',
            'fr' => 'Retour aux demandes',
        ],
        'photo_alt' => 'Irudia :name',
    ],
    'messages' => [
        'created' => [
            'eu' => 'Orbana ondo sortu da.',
            'fr' => 'Tâche créée avec succès.',
        ],
        'updated' => [
            'eu' => 'Orbana eguneratua.',
            'fr' => 'Tâche mise à jour.',
        ],
        'deleted' => [
            'eu' => 'Orbana ezabatua.',
            'fr' => 'Tâche supprimée.',
        ],
        'history_added' => [
            'eu' => 'Aurrerapen berria gehitua.',
            'fr' => 'Nouvel avancement ajouté.',
        ],
        'validated' => [
            'eu' => 'Orbana itxita.',
            'fr' => 'Tâche clôturée.',
        ],
        'locked' => [
            'eu' => 'Orbana dagoeneko amaituta dago.',
            'fr' => 'La tâche est déjà terminée.',
        ],
        'history_locked' => [
            'eu' => 'Orbana hau amaituta dago, ezin da aurrerapenik gehitu.',
            'fr' => 'Cette tâche est terminée, impossible d\'ajouter un avancement.',
        ],
        'history_not_allowed' => [
            'eu' => 'Ez duzu aurrerapenik gehitzeko baimenik.',
            'fr' => 'Vous n\'avez pas la permission d\'ajouter un avancement.',
        ],
        'delete_error' => [
            'eu' => 'Errorea ezabatzean.',
            'fr' => 'Erreur lors de la suppression.',
        ],
    ],
    'validation' => [
        'titre_required' => [
            'eu' => 'Izenburua eremua beharrezkoa da.',
            'fr' => 'Le champ titre est requis.',
        ],
        'description_required' => [
            'eu' => 'Deskribapena eremua beharrezkoa da.',
            'fr' => 'Le champ description est requis.',
        ],
        'type_required' => [
            'eu' => 'Larrialdi mota beharrezkoa da.',
            'fr' => 'Le type d\'urgence est requis.',
        ],
        'dateD_required' => [
            'eu' => 'Data eremua beharrezkoa da.',
            'fr' => 'Le champ date est requis.',
        ],
        'dateD_date' => [
            'eu' => 'Data formatu baliozkoa izan behar da.',
            'fr' => 'La date doit être au format valide.',
        ],
        'realisateurs_required' => [
            'eu' => 'Gutxienez erabiltzaile bat hautatu behar da.',
            'fr' => 'Au moins un utilisateur doit être sélectionné.',
        ],
        'realisateurs_min' => [
            'eu' => 'Gutxienez erabiltzaile bat hautatu behar da.',
            'fr' => 'Au moins un utilisateur doit être sélectionné.',
        ],
    ],
];

