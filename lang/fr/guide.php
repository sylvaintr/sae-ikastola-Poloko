<?php

return [
    // Bouton flottant
    'button_label' => 'Aide',
    'button_tooltip' => 'Ouvrir le guide d\'utilisation',

    // Modal
    'modal_title' => 'Guide d\'utilisation',
    'close' => 'Fermer',
    'previous' => 'Précédent',
    'next' => 'Suivant',
    'finish' => 'Terminer',
    'step_of' => 'sur',

    // Étapes du guide (par clé pour filtrage dynamique)
    // Pour ajouter une vidéo à une étape, ajoutez le champ 'video' avec l'URL d'embed YouTube/Vimeo
    // Exemple: 'video' => 'https://www.youtube.com/embed/VIDEO_ID',
    // ou pour Vimeo: 'video' => 'https://player.vimeo.com/video/VIDEO_ID',
    'steps' => [
        'welcome' => [
            'title' => 'Bienvenue',
            'icon' => 'bi-house-heart',
            'description' => 'Bienvenue sur le portail de la Baionako Hiriondo Ikastola ! Ce guide vous accompagnera dans l\'utilisation des fonctionnalités auxquelles vous avez accès. Cliquez sur "Suivant" pour commencer.',
            'permission' => null, // Toujours visible
            // 'video' => 'https://www.youtube.com/embed/VIDEO_ID', // Décommentez et remplacez VIDEO_ID
        ],
        'navigation' => [
            'title' => 'Navigation',
            'icon' => 'bi-compass',
            'description' => 'La barre de navigation en haut vous permet d\'accéder aux différentes sections du site. Cliquez sur votre avatar en haut à droite pour accéder à votre profil, changer de langue ou vous déconnecter.',
            'permission' => null,
        ],
        'actualites' => [
            'title' => 'Actualités',
            'icon' => 'bi-newspaper',
            'description' => 'La page d\'accueil affiche les actualités de l\'Ikastola. Vous pouvez filtrer par catégorie et lire les articles en cliquant dessus. Les pièces jointes (images, documents PDF) sont téléchargeables depuis chaque actualité.',
            'permission' => null,
            'video' => 'https://www.youtube.com/embed/CD3d-CcQwi8',
        ],
        'demandes_create' => [
            'title' => 'Créer une demande',
            'icon' => 'bi-file-earmark-plus',
            'description' => 'Dans la section "Demandes", cliquez sur "Nouvelle demande" pour créer une demande administrative. Remplissez le formulaire avec le type de demande, la description et joignez les documents nécessaires.',
            'permission' => 'access-demande',
            'video' => 'https://www.youtube.com/embed/WduONgpd2h0',
        ],
        'demandes_follow' => [
            'title' => 'Suivre vos demandes',
            'icon' => 'bi-file-earmark-check',
            'description' => 'La liste de vos demandes affiche leur statut : En attente, En cours, Validée ou Refusée. Cliquez sur une demande pour voir son historique et les commentaires.',
            'permission' => 'access-demande',
        ],
        'demandes_manage' => [
            'title' => 'Gérer les demandes',
            'icon' => 'bi-file-earmark-medical',
            'description' => 'En tant que gestionnaire, vous pouvez valider, refuser ou ajouter des commentaires aux demandes. Utilisez les boutons d\'action sur chaque demande pour la traiter.',
            'permission' => 'gerer-demandes',
        ],
        'taches' => [
            'title' => 'Vos tâches',
            'icon' => 'bi-list-task',
            'description' => 'La section "Tâches" affiche les tâches qui vous sont assignées. Vous pouvez voir les détails, ajouter des commentaires sur l\'avancement et consulter l\'historique.',
            'permission' => 'access-tache',
        ],
        'taches_manage' => [
            'title' => 'Gérer les tâches',
            'icon' => 'bi-kanban',
            'description' => 'Vous pouvez créer de nouvelles tâches, les assigner à des utilisateurs, définir des échéances et marquer les tâches comme terminées.',
            'permission' => 'gerer-tache',
        ],
        'presence' => [
            'title' => 'Suivi de présence',
            'icon' => 'bi-calendar-check',
            'description' => 'Dans "Présence", sélectionnez une date et une ou plusieurs classes. Cochez les élèves présents et enregistrez. Les absences sont automatiquement comptabilisées.',
            'permission' => 'access-presence',
        ],
        'notifications' => [
            'title' => 'Notifications',
            'icon' => 'bi-bell',
            'description' => 'L\'icône cloche en haut à droite affiche vos notifications non lues. Un badge rouge indique leur nombre. Cliquez sur une notification pour la consulter.',
            'permission' => null,
        ],
        'profile' => [
            'title' => 'Votre profil',
            'icon' => 'bi-person-circle',
            'description' => 'Accédez à votre profil via votre avatar. Vous pouvez modifier vos informations personnelles, changer votre mot de passe et télécharger les documents obligatoires.',
            'permission' => null,
        ],
        'language' => [
            'title' => 'Changer de langue',
            'icon' => 'bi-translate',
            'description' => 'Le site est disponible en français et en basque (euskara). Pour changer, cliquez sur votre avatar puis sur "Passer en euskara" ou "Passer en français".',
            'permission' => null,
        ],
        'admin_dashboard' => [
            'title' => 'Administration',
            'icon' => 'bi-speedometer2',
            'description' => 'Le tableau de bord Administration vous donne une vue d\'ensemble des différentes sections que vous pouvez gérer selon vos permissions.',
            'permission' => 'access-administration',
        ],
        'admin_accounts' => [
            'title' => 'Gestion des comptes',
            'icon' => 'bi-people',
            'description' => 'Créez, modifiez et validez les comptes utilisateurs. Vous pouvez assigner des rôles, valider les documents obligatoires et archiver les comptes inactifs.',
            'permission' => 'gerer-utilisateurs',
        ],
        'admin_classes' => [
            'title' => 'Gestion des classes',
            'icon' => 'bi-mortarboard',
            'description' => 'Créez et gérez les classes de l\'Ikastola. Vous pouvez définir le nom, le niveau et assigner des enseignants à chaque classe.',
            'permission' => 'gerer-classes',
        ],
        'admin_enfants' => [
            'title' => 'Gestion des enfants',
            'icon' => 'bi-emoji-smile',
            'description' => 'Gérez les fiches des enfants inscrits. Vous pouvez les associer à des familles, les affecter à des classes et suivre leur dossier.',
            'permission' => 'gerer-enfants',
        ],
        'admin_familles' => [
            'title' => 'Gestion des familles',
            'icon' => 'bi-house-door',
            'description' => 'Créez et gérez les familles. Associez les parents (utilisateurs) aux enfants et définissez les responsables légaux.',
            'permission' => 'gerer-familles',
        ],
        'admin_factures' => [
            'title' => 'Gestion des factures',
            'icon' => 'bi-receipt',
            'description' => 'Créez, consultez et envoyez les factures aux familles. Vous pouvez exporter en PDF et suivre les paiements.',
            'permission' => 'gerer-factures',
        ],
        'admin_actualites' => [
            'title' => 'Gestion des actualités',
            'icon' => 'bi-megaphone',
            'description' => 'Créez et publiez des actualités. Ajoutez des images, des documents et choisissez les catégories. Vous pouvez dupliquer ou supprimer les actualités existantes.',
            'permission' => 'gerer-actualites',
        ],
        'admin_notifications' => [
            'title' => 'Envoyer des notifications',
            'icon' => 'bi-send',
            'description' => 'Créez et envoyez des notifications aux utilisateurs. Vous pouvez cibler des groupes spécifiques ou tous les utilisateurs.',
            'permission' => 'gerer-notifications',
        ],
        'help' => [
            'title' => 'Besoin d\'aide ?',
            'icon' => 'bi-question-circle',
            'description' => 'Ce guide est toujours accessible via le bouton orange "?" en bas à droite. Pour toute question, contactez l\'administration de l\'Ikastola. Eskerrik asko !',
            'permission' => null,
        ],
    ],
];
