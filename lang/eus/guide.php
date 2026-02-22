<?php

return [
    // Bouton flottant
    'button_label' => 'Laguntza',
    'button_tooltip' => 'Erabiltzaile gida ireki',

    // Modal
    'modal_title' => 'Erabiltzaile gida',
    'close' => 'Itxi',
    'previous' => 'Aurrekoa',
    'next' => 'Hurrengoa',
    'finish' => 'Amaitu',
    'step_of' => '/',

    // Étapes du guide (par clé pour filtrage dynamique)
    // Pour ajouter une vidéo à une étape, ajoutez le champ 'video' avec l'URL d'embed YouTube/Vimeo
    // Exemple: 'video' => 'https://www.youtube.com/embed/VIDEO_ID',
    // ou pour Vimeo: 'video' => 'https://player.vimeo.com/video/VIDEO_ID',
    'steps' => [
        'welcome' => [
            'title' => 'Ongi etorri',
            'icon' => 'bi-house-heart',
            'description' => 'Ongi etorri Baionako Hiriondo Ikastolaren atarira! Gida honek zure eskura dauden funtzionaltasunak erabiltzen lagunduko dizu. Klikatu "Hurrengoa" hasteko.',
            'permission' => null,
            // 'video' => 'https://www.youtube.com/embed/VIDEO_ID', // Décommentez et remplacez VIDEO_ID
        ],
        'navigation' => [
            'title' => 'Nabigazioa',
            'icon' => 'bi-compass',
            'description' => 'Goiko nabigazio-barrak webgunearen atal desberdinetara sarbidea ematen dizu. Klikatu zure avatarra goiko eskuinean zure profila ikusteko, hizkuntza aldatzeko edo saioa ixteko.',
            'permission' => null,
        ],
        'actualites' => [
            'title' => 'Berriak',
            'icon' => 'bi-newspaper',
            'description' => 'Hasiera-orrialdeak Ikastolaren berriak erakusten ditu. Kategorien arabera iragazki dezakezu eta artikuluak irakur ditzakezu haien gainean klik eginez. Eranskinak deskargagarriak dira.',
            'permission' => null,
            'video' => 'https://www.youtube.com/embed/CD3d-CcQwi8',
        ],
        'demandes_create' => [
            'title' => 'Eskaera sortu',
            'icon' => 'bi-file-earmark-plus',
            'description' => '"Eskaerak" atalean, klikatu "Eskaera berria" eskaera administratibo bat sortzeko. Bete formularioa eskaera motarekin, deskribapena eta beharrezko dokumentuak erantsi.',
            'permission' => 'access-demande',
            'video' => 'https://www.youtube.com/embed/WduONgpd2h0',
        ],
        'demandes_follow' => [
            'title' => 'Zure eskaerak jarraitu',
            'icon' => 'bi-file-earmark-check',
            'description' => 'Zure eskaeren zerrendak haien egoera erakusten du: Zain, Prozesatzen, Balioztatua edo Baztertua. Klikatu eskaera baten gainean bere historia ikusteko.',
            'permission' => 'access-demande',
        ],
        'demandes_manage' => [
            'title' => 'Eskaerak kudeatu',
            'icon' => 'bi-file-earmark-medical',
            'description' => 'Kudeatzaile gisa, eskaerak balidatu, baztertu edo iruzkinak gehitu ditzakezu. Erabili ekintza botoiak eskaera bakoitza tratatzeko.',
            'permission' => 'gerer-demandes',
        ],
        'taches' => [
            'title' => 'Zure lanak',
            'icon' => 'bi-list-task',
            'description' => '"Lanak" atalak zuri esleitutako lanak erakusten ditu. Xehetasunak ikusi, aurrerapen iruzkinak gehitu eta historia kontsultatu dezakezu.',
            'permission' => 'access-tache',
        ],
        'taches_manage' => [
            'title' => 'Lanak kudeatu',
            'icon' => 'bi-kanban',
            'description' => 'Lan berriak sortu, erabiltzaileei esleitu, epeak definitu eta lanak amaituta bezala markatu ditzakezu.',
            'permission' => 'gerer-tache',
        ],
        'presence' => [
            'title' => 'Presentzia jarraipena',
            'icon' => 'bi-calendar-check',
            'description' => '"Presentzia" atalean, hautatu data eta klase bat edo gehiago. Markatu ikasle presenteak eta gorde. Hutsegiteak automatikoki zenbatzen dira.',
            'permission' => 'access-presence',
        ],
        'notifications' => [
            'title' => 'Jakinarazpenak',
            'icon' => 'bi-bell',
            'description' => 'Goiko eskuineko kanpai ikonoak irakurri gabeko jakinarazpenak erakusten ditu. Txartela gorri batek haien kopurua adierazten du. Klikatu jakinarazpen bat ikusteko.',
            'permission' => null,
        ],
        'profile' => [
            'title' => 'Zure profila',
            'icon' => 'bi-person-circle',
            'description' => 'Sartu zure profilera avatarra klikatuz. Zure informazio pertsonala aldatu, pasahitza aldatu eta dokumentu nahitaezkoak igo ditzakezu.',
            'permission' => null,
        ],
        'language' => [
            'title' => 'Hizkuntza aldatu',
            'icon' => 'bi-translate',
            'description' => 'Webgunea frantsesez eta euskaraz eskuragarri dago. Aldatzeko, klikatu zure avatarra eta gero "Frantsesera pasa" edo "Euskarara pasa".',
            'permission' => null,
        ],
        'admin_dashboard' => [
            'title' => 'Administrazioa',
            'icon' => 'bi-speedometer2',
            'description' => 'Administrazio arbela kudeatu ditzakezun atal desberdinen ikuspegi orokorra ematen dizu zure baimenen arabera.',
            'permission' => 'access-administration',
        ],
        'admin_accounts' => [
            'title' => 'Kontuen kudeaketa',
            'icon' => 'bi-people',
            'description' => 'Sortu, aldatu eta balidatu erabiltzaile kontuak. Rolak esleitu, dokumentu nahitaezkoak balidatu eta kontu inaktiboak artxibatu ditzakezu.',
            'permission' => 'gerer-utilisateurs',
        ],
        'admin_classes' => [
            'title' => 'Klaseen kudeaketa',
            'icon' => 'bi-mortarboard',
            'description' => 'Sortu eta kudeatu Ikastolako klaseak. Izena, maila definitu eta irakasleak klase bakoitzari esleitu ditzakezu.',
            'permission' => 'gerer-classes',
        ],
        'admin_enfants' => [
            'title' => 'Haurren kudeaketa',
            'icon' => 'bi-emoji-smile',
            'description' => 'Kudeatu matrikulatutako haurren fitxak. Familiekin lotu, klaseetara esleitu eta haien dosiera jarraitu dezakezu.',
            'permission' => 'gerer-enfants',
        ],
        'admin_familles' => [
            'title' => 'Familien kudeaketa',
            'icon' => 'bi-house-door',
            'description' => 'Sortu eta kudeatu familiak. Lotu gurasoak (erabiltzaileak) haurrekin eta legezko arduradunak definitu.',
            'permission' => 'gerer-familles',
        ],
        'admin_factures' => [
            'title' => 'Fakturen kudeaketa',
            'icon' => 'bi-receipt',
            'description' => 'Sortu, kontsultatu eta bidali fakturak familiei. PDF-n esportatu eta ordainketak jarraitu ditzakezu.',
            'permission' => 'gerer-factures',
        ],
        'admin_actualites' => [
            'title' => 'Berrien kudeaketa',
            'icon' => 'bi-megaphone',
            'description' => 'Sortu eta argitaratu berriak. Gehitu irudiak, dokumentuak eta kategoriak aukeratu. Dauden berriak bikoiztu edo ezabatu ditzakezu.',
            'permission' => 'gerer-actualites',
        ],
        'admin_notifications' => [
            'title' => 'Jakinarazpenak bidali',
            'icon' => 'bi-send',
            'description' => 'Sortu eta bidali jakinarazpenak erabiltzaileei. Talde espezifikoak edo erabiltzaile guztiak jo ditzakezu.',
            'permission' => 'gerer-notifications',
        ],
        'help' => [
            'title' => 'Laguntzarik behar?',
            'icon' => 'bi-question-circle',
            'description' => 'Gida hau beti eskuragarri dago beheko eskuineko "?" botoi laranjaren bidez. Galderarik baduzu, jarri harremanetan Ikastolako administrazioarekin. Eskerrik asko!',
            'permission' => null,
        ],
    ],
];
