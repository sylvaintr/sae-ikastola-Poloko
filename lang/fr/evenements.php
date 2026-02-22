<?php

return [
    // Titres
    'title' => 'Événements',
    'subtitle' => 'Liste des événements enregistrés',
    'create_title' => 'Créer un nouvel événement',
    'edit_title' => 'Modifier l\'événement',
    'show_title' => 'Détails de l\'événement',
    'calendar_title' => 'Calendrier',

    // Boutons
    'add' => 'Ajouter un événement',
    'add_subtitle' => 'Créer un nouvel événement',
    'save' => 'Enregistrer',
    'create' => 'Créer',
    'cancel' => 'Annuler',
    'back_to_list' => 'Retour aux événements',
    'export' => 'Export',
    'export_subtitle' => 'Exporter le calendrier',

    // Champs
    'titre' => 'Titre',
    'description' => 'Description',
    'date' => 'Date',
    'start_date' => 'Date de début',
    'start_time' => 'Heure de début',
    'end_date' => 'Date de fin',
    'end_time' => 'Heure de fin',
    'end_date_hint' => 'Laissez vide si l\'événement se termine le même jour',
    'all_day' => 'Journée entière',
    'obligatoire' => 'Obligatoire',
    'obligatoire_label' => 'Oui, cet événement est obligatoire',
    'statut' => 'Statut',
    'actions' => 'Actions',
    'cibles' => 'Cibles',
    'roles' => 'Rôles associés',

    // Statuts
    'status_obligatoire' => 'Obligatoire',
    'status_optionnel' => 'Optionnel',

    // Recherche/Tri
    'search' => 'Rechercher',
    'search_placeholder' => 'Rechercher un événement...',
    'search_hint' => 'Recherche par titre ou ID',
    'sort' => 'Trier',
    'sort_id_desc' => 'ID - plus récents en premier',
    'sort_id_asc' => 'ID - plus anciens en premier',
    'sort_date_desc' => 'Date - plus récentes en premier',
    'sort_date_asc' => 'Date - plus anciennes en premier',

    // Cibles/Rôles
    'search_cible' => 'Rechercher une cible',
    'search_cible_placeholder' => 'Tapez pour rechercher...',
    'cibles_selected' => 'Cibles sélectionnés',
    'no_cible_selected' => 'Aucune cible n\'a été sélectionnée',
    'cible_error' => 'Au moins une cible doit être sélectionnée.',

    // Messages
    'no_events' => 'Aucun événement disponible pour le moment.',
    'created_success' => 'Événement créé avec succès',
    'updated_success' => 'Événement mis à jour avec succès',
    'deleted_success' => 'Événement supprimé avec succès',

    // Modal suppression
    'delete_title' => 'Supprimer l\'événement',
    'delete_confirm' => 'Voulez-vous vraiment supprimer l\'événement',
    'delete' => 'Supprimer',

    // Actions
    'action_view' => 'Voir les détails',
    'action_edit' => 'Modifier',
    'action_delete' => 'Supprimer',

    // Calendrier
    'calendar_close' => 'Fermer',
    'event_obligatoire' => 'Événement obligatoire',
    'no_description' => 'Aucune description',

    // Page détail (show)
    'cible' => 'Cible',
    'cible_restricted' => 'Restreint',
    'cible_all' => 'Tous',
    'recurrence' => 'Récurrence',
    'recurrence_annual' => 'Annuelle',
    'no_description_provided' => 'Aucune description fournie.',
    'edit_event' => 'Modifier l\'événement',
    'add_recette' => 'Ajouter une opération comptable',
    'view_demandes' => 'Voir les demandes',

    // Comptabilité
    'accounting' => 'Comptabilité',
    'type' => 'Type',
    'amount' => 'Montant',
    'status' => 'Statut',
    'status_pending' => 'En attente',
    'no_recettes' => 'Aucune recette pour le moment.',
    'total_depenses_prev' => 'Total des dépenses prévisionnelles',
    'total_depenses' => 'Total des dépenses',
    'total_recettes' => 'Total des recettes',

    // Types de recette
    'type_recette' => 'Recette',
    'type_depense_prev' => 'Dépense prévisionnelle',
    'type_depense' => 'Dépense',

    // Modal recette
    'quantity' => 'Quantité',
    'confirm_delete_recette' => 'Supprimer cette recette ?',

    // Légende calendrier
    'legend' => 'Légende',
    'legend_event' => 'Événement',
    'legend_event_obligatoire' => 'Événement obligatoire',
    'legend_demande_high' => 'Demande urgente',
    'legend_demande_medium' => 'Demande moyenne',
    'legend_demande_low' => 'Demande faible',

    // Export CSV
    'export_btn' => 'Exporter CSV',
    'export_btn_help' => 'Pour convertir le fichier CSV en format lisible, utilisez un site gratuit comme ConvertCSV.com ou CSVed.com',
    'export' => [
        'evenement_title' => 'Événement',
        'comptabilite_title' => 'Comptabilité',
        'id' => 'ID',
        'titre' => 'Titre',
        'description' => 'Description',
        'start_at' => 'Date de début',
        'end_at' => 'Date de fin',
        'obligatoire' => 'Obligatoire',
        'roles' => 'Rôles',
        'type_col' => 'Type',
        'description_col' => 'Description',
        'prix' => 'Prix',
        'quantite' => 'Quantité',
        'total' => 'Total',
        'total_recettes' => 'Total des recettes',
        'total_depenses_prev' => 'Total des dépenses prévisionnelles',
        'total_depenses' => 'Total des dépenses',
    ],

    // Synchronisation calendrier
    'sync_button' => 'Synchroniser',
    'sync_title' => 'Synchroniser le calendrier',
    'sync_description' => 'Ajoutez ce calendrier à votre application préférée (Google Agenda, Outlook, Apple Calendar) pour recevoir automatiquement les mises à jour.',
    'sync_url' => 'URL d\'abonnement',
    'sync_instructions' => 'Instructions d\'ajout',
    'copy_url' => 'Copier l\'URL',
    'url_copied' => 'URL copiée !',
    'regenerate_token' => 'Régénérer le lien',
    'regenerate_hint' => 'Si votre lien a été compromis',
    'confirm_regenerate' => 'Régénérer le lien ? L\'ancien lien ne fonctionnera plus.',
    'token_regenerated' => 'Nouveau lien généré avec succès.',
    'google_step_1' => 'Ouvrez Google Agenda',
    'google_step_2' => 'Cliquez sur "+" à côté de "Autres agendas" puis "À partir de l\'URL"',
    'google_step_3' => 'Collez l\'URL ci-dessus et cliquez sur "Ajouter l\'agenda"',
    'outlook_step_1' => 'Ouvrez Outlook',
    'outlook_step_2' => 'Allez dans Calendrier > Ajouter un calendrier > S\'abonner à partir du web',
    'outlook_step_3' => 'Collez l\'URL et validez',
    'apple_step_1' => 'Ouvrez l\'app Calendrier',
    'apple_step_2' => 'Menu Fichier > Nouvel abonnement à un calendrier',
    'apple_step_3' => 'Collez l\'URL et cliquez sur "S\'abonner"',
];
