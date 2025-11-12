<?php

$common = [
    'roles' => 'Rôles',
    'roles_search' => 'Rechercher un rôle',
    'roles_search_placeholder' => 'Tapez pour rechercher...',
    'roles_selected' => 'Rôles sélectionnés',
    'roles_required' => 'Au moins un rôle doit être sélectionné.',
    'first_name' => 'Prénom',
    'last_name' => 'Nom',
    'email' => 'Email',
    'preferred_language' => 'Langue préférée',
    'fixed_expiration_date' => 'Date d\'expiration fixe',
    'submit' => 'Enregistrer',
    'cancel' => 'Annuler',
    'edit' => 'Modifier',
    'delete' => 'Supprimer',
];

return [
    'title' => 'Administration',
    'common' => $common,
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
                'title' => $common['first_name'],
            ],
            'last_name' => [
                'title' => $common['last_name'],
            ],
            'email' => [
                'title' => $common['email'],
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
            'edit' => $common['edit'],
            'delete' => $common['delete'],
            'confirm_delete' => $common['delete'],
            'validate' => 'Valider',
        ],
        'back' => 'Retour aux comptes',
        'show' => [
            'first_name_label' => $common['first_name'],
            'last_name_label' => $common['last_name'],
            'email_label' => $common['email'],
            'language_label' => $common['preferred_language'],
            'status_label' => 'Statut',
            'roles_label' => $common['roles'],
            'no_roles' => 'Aucun rôle assigné',
        ],
        'messages' => [
            'deleted' => 'Le compte a été supprimé avec succès.',
            'updated' => 'Le compte a été mis à jour avec succès.',
            'created' => 'Le compte a été ajouté avec succès.',
            'validated' => 'Le compte a été validé avec succès.',
        ],
        'edit' => [
            'title' => 'Modifier le compte',
            'submit' => $common['submit'],
            'cancel' => $common['cancel'],
            'fields' => [
                'first_name' => $common['first_name'],
                'last_name' => $common['last_name'],
                'email' => $common['email'],
                'password' => 'Mot de passe (laisser vide pour ne pas changer)',
                'password_help' => 'Laisser vide pour ne pas changer le mot de passe',
                'password_confirmation' => 'Confirmer le mot de passe',
                'language' => $common['preferred_language'],
                'status' => 'Statut de validation',
                'roles' => $common['roles'],
                'roles_search' => $common['roles_search'],
                'roles_search_placeholder' => $common['roles_search_placeholder'],
                'roles_selected' => $common['roles_selected'],
            ],
        ],
        'create' => [
            'title' => 'Ajouter un compte',
            'submit' => $common['submit'],
            'cancel' => $common['cancel'],
            'fields' => [
                'first_name' => $common['first_name'],
                'last_name' => $common['last_name'],
                'email' => $common['email'],
                'password' => 'Mot de passe',
                'password_confirmation' => 'Confirmer le mot de passe',
                'language' => $common['preferred_language'],
                'status' => 'Statut de validation',
                'roles' => $common['roles'],
                'roles_search' => $common['roles_search'],
                'roles_search_placeholder' => $common['roles_search_placeholder'],
                'roles_selected' => $common['roles_selected'],
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

