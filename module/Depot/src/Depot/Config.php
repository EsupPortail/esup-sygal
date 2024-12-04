<?php

namespace Depot;

use Fichier\Entity\Db\NatureFichier;

class Config
{
    static public function generateDepotFichierDiversRoutesConfig(): array
    {
        $config = [];
        foreach (NatureFichier::CODES_FICHIERS_DIVERS as $code) {
            $key = (new NatureFichier())->setCode($code)->getCodeToLowerAndDash();
            $config[$key] = [
                'type' => 'Literal',
                'options' => [
                    'route' => '/' . $key,
                    'defaults' => [
                        'action' => 'depot-' . $key,
                    ],
                ],
            ];
        }

        return $config;
    }
}