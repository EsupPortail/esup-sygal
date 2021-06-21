<?php

use UnicaenFaq\Entity\Db\Faq;

return [
    'unicaen-faq' => [
        // Nom du gestionnaire d'entité concerné.
        // Par défaut: 'orm_default'.
        'entity_manager_name' => 'orm_default',

        // Classe de l'entité représentant un couple question-réponse.
        // Par défaut: 'UnicaenFaq\Entity\Db\Faq'.
        'faq_entity_class' => Faq::class,
    ],

    // Customisation de la navigation.
    // Ex: masquer le menu, modifier sa position, changer son label, associer une ressource ACL.
    'navigation' => [
        'default' => [
            'home' => [
                'pages' => [
                    'faq' => [
                        'order' => 200,
                        'label' => "Aide",
                        'pages' => [
                            'faq' => [
                                'label' => 'Questions fréquentes',
                                'route' => 'faq',
                            ],
                            'contact' => [
                                'label' => 'Contact / assistance',
                                'route' => 'contact',
                            ],
                            'apropos' => [
                                'label' => 'À propos...',
                                'route' => 'apropos',
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
];