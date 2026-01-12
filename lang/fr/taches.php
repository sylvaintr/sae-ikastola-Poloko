<?php

return [
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
        'reset' => 'Réinitialiser',
        'submit' => 'Filtrer',
        'options' => [
            'all_status' => 'Tous les statuts',
            'all_types' => 'Tous les types',
            'all_urgencies' => 'Toutes les urgences',
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
        'empty' => 'Aucune tâche disponible pour le moment.',
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
            'title' => 'Supprimer la tâche',
            'message' => 'Êtes-vous sûr de vouloir supprimer cette tâche ?',
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
        'created' => 'Tâche créée',
        'progress' => 'Avancement',
        'done' => 'Effectué',
        'done_description' => 'Tâche marquée comme terminée.',
    ],
    'form' => [
        'create_title' => 'Sortu txartel eskaera',
        'create_subtitle' => 'Créez une nouvelle tâche en complétant les champs ci-dessous.',
        'edit_title' => 'Modifier la tâche',
        'edit_subtitle' => 'Mettez à jour les informations de la tâche.',
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
            'disabled' => 'Tâche terminée',
            'disabled_sub' => 'Tâche clôturée',
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
        'link' => '← Retour à la tâche :id',
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
        'back' => [
            'eu' => 'Itzuli eskaeretara',
            'fr' => 'Retour aux tâches',
        ],
        'photo_alt' => 'Illustration :name',
    ],
    'messages' => [
        'created' => 'Tâche créée avec succès.',
        'updated' => 'Tâche mise à jour.',
        'deleted' => 'Tâche supprimée.',
        'history_added' => 'Nouvel avancement ajouté.',
        'validated' => 'Tâche clôturée.',
        'locked' => 'La Tâche est déjà terminée.',
        'history_locked' => 'Cette Tâche est terminée, impossible d’ajouter un avancement.',
        'history_not_allowed' => 'Vous n’avez pas la permission d’ajouter un avancement.',
    ],
];

