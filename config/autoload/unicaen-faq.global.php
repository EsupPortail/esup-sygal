<?php

use UnicaenFaq\Entity\Db\Faq;

return array(
    'unicaen-faq' => [
        /*
         * Nom du gestionnaire d'entité concerné.
         * Par défaut: 'orm_default'.
         */
        'entity_manager_name' => 'orm_default',

        /*
         * Classe de l'entité représentant un couple question-réponse.
         * Par défaut: 'UnicaenFaq\Entity\Db\Faq'.
         */
        'faq_entity_class' => Faq::class,
    ],

    /*
     * Customisation de la navigation.
     * Ex: masquer le menu, modifier sa position, changer son label, associer une ressource ACL.
     */
//    'navigation'      => [
//        'default' => [
//            'home' => [
//                'pages' => [
//                    'faq' => [
//                        'visible'  => true,
//                        'order'    => -1000,
//                        'label'    => 'FAQ',
//                        'resource' => PrivilegeController::getResourceId('UnicaenFaq\Controller\Index', 'index'),
//                    ],
//                ],
//            ],
//        ],
//    ],
);