<?php

return [
    'title' => 'Administration',
    'sections' => [
        'add_message' => 'Publications',
        'accounts' => 'Comptes',
        'families' => 'Familles',
        'classes' => 'Classes',
        'invoices' => 'Factures',
        'notifications' => 'Notifications',
    ],
    'classes_page' => [
        'title' => 'Gestion des classes',
        'columns' => [
            'id' => [
                'title' => 'Identifiant',
            ],
            'name' => [
                'title' => 'Nom',
            ],
            'level' => [
                'title' => 'Niveau',
            ],
            'students_count' => [
                'title' => 'Élèves',
            ],
            'actions' => [
                'title' => 'Actions',
            ],
        ],
        'actions' => [
            'view' => 'Visualiser',
            'edit' => 'Modifier',
            'delete' => 'Supprimer',
            'confirm_delete' => 'Supprimer',
        ],
        'back' => 'Retour aux classes',
        'show' => [
            'level_label' => 'Niveau',
        ],
        'messages' => [
            'deleted' => 'La classe a été supprimée avec succès.',
            'updated' => 'La classe a été mise à jour avec succès.',
            'created' => 'La classe a été ajoutée avec succès.',
        ],
        'edit' => [
            'title' => 'Modifier la classe',
            'submit' => 'Enregistrer',
            'cancel' => 'Annuler',
        ],
        'create' => [
            'title' => 'Ajouter une classe',
            'submit' => 'Enregistrer',
            'cancel' => 'Annuler',
            'fields' => [
                'name' => 'Nom de la classe',
                'level' => 'Niveau',
            ],
        ],
        'students' => [
            'title' => 'Élèves de la classe',
            'empty' => 'Aucun élève n’est associé à cette classe pour le moment.',
            'columns' => [
                'id' => [
                    'title' => 'Identifiant',
                ],
                'last_name' => [
                    'title' => 'Nom',
                ],
                'first_name' => [
                    'title' => 'Prénom',
                ],
                'birthdate' => [
                    'title' => 'Date de naissance',
                ],
                'gender' => [
                    'title' => 'Sexe',
                ],
                'nni' => [
                    'title' => 'NNI',
                ],
            ],
        ],
        'delete_confirmation' => 'Êtes-vous sûr de vouloir supprimer la classe :name ?',
    ],
];

