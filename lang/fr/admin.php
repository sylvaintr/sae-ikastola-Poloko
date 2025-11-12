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
    'accounts_page' => [
        'title' => 'Kontuak',
        'title_subtitle' => 'Comptes',
        'search_placeholder' => 'Erabiltzaile baten bilaketa...',
        'search_label' => 'Rechercher un utilisateur',
        'add_button' => 'Gehitu kontu bat',
        'add_button_subtitle' => 'Ajouter un compte',
        'columns' => [
            'id' => [
                'title' => 'Identifiant',
            ],
            'first_name' => [
                'title' => 'Prénom',
            ],
            'last_name' => [
                'title' => 'Nom',
            ],
            'email' => [
                'title' => 'Email',
            ],
            'status' => [
                'title' => 'Statut',
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
        'back' => 'Retour aux comptes',
        'show' => [
            'first_name_label' => 'Prénom',
            'last_name_label' => 'Nom',
            'email_label' => 'Email',
            'language_label' => 'Langue préférée',
            'status_label' => 'Statut',
            'roles_label' => 'Rôles',
            'no_roles' => 'Aucun rôle assigné',
        ],
        'messages' => [
            'deleted' => 'Le compte a été supprimé avec succès.',
            'updated' => 'Le compte a été mis à jour avec succès.',
            'created' => 'Le compte a été ajouté avec succès.',
        ],
        'edit' => [
            'title' => 'Modifier le compte',
            'submit' => 'Enregistrer',
            'cancel' => 'Annuler',
            'fields' => [
                'first_name' => 'Prénom',
                'last_name' => 'Nom',
                'email' => 'Email',
                'password' => 'Mot de passe (laisser vide pour ne pas changer)',
                'password_help' => 'Laisser vide pour ne pas changer le mot de passe',
                'password_confirmation' => 'Confirmer le mot de passe',
                'language' => 'Langue préférée',
                'status' => 'Statut de validation',
                'roles' => 'Rôles',
                'roles_search' => 'Rechercher un rôle',
                'roles_search_placeholder' => 'Tapez pour rechercher...',
                'roles_selected' => 'Rôles sélectionnés',
            ],
        ],
        'create' => [
            'title' => 'Ajouter un compte',
            'submit' => 'Enregistrer',
            'cancel' => 'Annuler',
            'fields' => [
                'first_name' => 'Prénom',
                'last_name' => 'Nom',
                'email' => 'Email',
                'password' => 'Mot de passe',
                'password_confirmation' => 'Confirmer le mot de passe',
                'language' => 'Langue préférée',
                'status' => 'Statut de validation',
                'roles' => 'Rôles',
                'roles_search' => 'Rechercher un rôle',
                'roles_search_placeholder' => 'Tapez pour rechercher...',
                'roles_selected' => 'Rôles sélectionnés',
            ],
        ],
        'password_strength' => [
            'weak' => 'Faible',
            'medium' => 'Moyen',
            'strong' => 'Fort',
            'very_strong' => 'Très fort',
            'match' => 'Les mots de passe correspondent',
            'no_match' => 'Les mots de passe ne correspondent pas',
        ],
        'delete_confirmation' => 'Êtes-vous sûr de vouloir supprimer le compte :name ?',
    ],
];

