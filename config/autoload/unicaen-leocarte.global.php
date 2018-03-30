<?php

namespace UnicaenLeocarte;

return [
    'unicaen-leocarte' => [
        'photo_config' => [
            // Témoin indiquant si le droit d'utilisation de la photo doit être pris en considération ou non.
            'check_droit_utilisation_photo' => true,

            // Options de génération d'une image informative lorsque le droit d'utilisation de la photo est refusé.
            'generation_photo_non_autorisee_config' => [
                'width'   => 100,
                'height'  => 120,
            ],
        ],
    ]
];