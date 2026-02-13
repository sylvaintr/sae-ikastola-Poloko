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
        'reset' => 'Réinitialiser',
        'submit' => 'Filtrer',
        'options' => [
            'all_status' => 'Tous les statuts',
            'all_types' => 'Tous les types',
            'all_urgencies' => 'Toutes les urgences',
            'all_evenements' => 'Tous les événements',
            'no_evenement' => 'Sans événement',
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
        'empty' => 'Aucune demande disponible pour le moment.',
        'sort' => [
            'id' => 'Trier par ID',
            'date' => 'Trier par date',
            'type' => 'Trier par type',
            'urgency' => 'Trier par urgence',
            'status' => 'Trier par statut',
        ],
        'urgency_high_hint' => 'Urgence élevée détectée',
    ],
    'modals' => [
        'delete' => [
            'title' => 'Supprimer la demande',
            'message' => 'Êtes-vous sûr de vouloir supprimer cette demande ?',
            'cancel' => 'Annuler',
            'confirm' => 'Supprimer',
        ],
        'history_view' => [
            'title' => 'Détail de l\'avancement',
            'fields' => [
                'title' => 'Titre',
                'date' => 'Date',
                'expense' => 'Dépense',
                'description' => 'Description',
            ],
        ],
    ],
    'history_statuses' => [
        'created' => 'Demande créée',
        'progress' => 'Avancement',
        'done' => 'Effectué',
        'done_description' => 'Demande marquée comme terminée.',
    ],
    'form' => [
        'create_title' => 'Sortu txartel eskaera',
        'create_subtitle' => 'Créez une nouvelle demande en complétant les champs ci-dessous.',
        'edit_title' => 'Modifier la demande',
        'edit_subtitle' => 'Mettez à jour les informations de la demande.',
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
                'none' => 'Aucune commission assignée',
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
            'disabled' => 'Demande terminée',
            'disabled_sub' => 'Demande clôturée',
        ],
        'no_file' => 'Aucun fichier sélectionné.',
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
        'planned' => 'Dépense prévisionnelle',
        'real' => 'Dépense réelle',
        'button' => [
            'eu' => 'Gehitu aurrerapena',
            'fr' => 'Ajouter un avancement',
        ],
        'empty' => 'La chronologie des actions apparaîtra ici.',
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
        'link' => '← Retour à la demande :id',
    ],
    'actions' => [
        'view' => 'Voir',
        'edit' => 'Modifier',
        'delete' => 'Supprimer',
        'validate' => 'Valider',
        'close' => 'Fermer',
    ],
    'show' => [
        'type_default' => 'Gertakaria',
        'reported_by' => ':name jakinarazia • :date',
        'assigned_to' => 'Commission assignée',
        'back' => [
            'eu' => 'Itzuli eskaeretara',
            'fr' => 'Retour aux demandes',
        ],
        'photo_alt' => 'Illustration :name',
    ],
    'messages' => [
        'created' => 'Demande créée avec succès.',
        'updated' => 'Demande mise à jour.',
        'deleted' => 'Demande supprimée.',
        'history_added' => 'Nouvel avancement ajouté.',
        'validated' => 'Demande clôturée.',
        'locked' => 'La demande est déjà terminée.',
        'history_locked' => "Cette demande est terminée, impossible d'ajouter un avancement.",
    ],
    'export' => [
        'demande_title' => 'Demande',
        'historique_title' => 'Historique',
        'id' => 'ID',
        'titre' => 'Titre',
        'description' => 'Description',
        'type' => 'Type',
        'etat' => 'État',
        'urgence' => 'Urgence',
        'date_creation' => 'Date de création',
        'date_fin' => 'Date de fin',
        'montant_previsionnel' => 'Montant prévisionnel',
        'montant_reel' => 'Montant réel',
        'realisateurs' => 'Réalisateurs',
    ],
];

