<?php

return [
    /* Configuration d'UnicaenImport */
    'unicaen-import' => [

        // Liste des aides de vues facilitant la lecture du différentiel (écarts de données entre l'appli et sa source)
        'differentiel_view_helpers' => [
            /* nom de la table (attention à la CASSE) => Nom de l'aide de vue (qui doit hériter de UnicaenImport\View\Helper\DifferentielLigne\DifferentielLigne) */
        ],

        /**
         * Injection automatique d'une Source dans les entités créées par l'appli.
         */
        'entity_source_injector' => [
            // Code unique de la source à injecter (null pour désactiver le mécanisme).
            'source_code' => 'App',
        ],
    ],
];