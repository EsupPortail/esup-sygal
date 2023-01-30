<?php

namespace RapportActivite;

use Application\Entity\Db\Role;
use Application\Entity\Db\TypeValidation;
use RapportActivite\Entity\Db\RapportActivite;
use RapportActivite\Entity\Db\RapportActiviteAvis;
use RapportActivite\Entity\Db\RapportActiviteValidation;

const VALIDATION_DOCTORANT = 'VALIDATION_DOCTORANT';
const AVIS_GEST = 'AVIS_GEST';
const AVIS_DIR_THESE = 'AVIS_DIR_THESE';
const AVIS_CODIR_THESE = 'AVIS_CODIR_THESE';
const AVIS_DIR_UR = 'AVIS_DIR_UR';
const AVIS_DIR_ED = 'AVIS_DIR_ED';
const VALIDATION_AUTO = 'VALIDATION_AUTO';

return [
    // Options concernant les rapports d'activité
    'rapport-activite' => [
        // Opérations (validations ou avis) ordonnées attendues
        'operations' => [
            VALIDATION_DOCTORANT => [
                'type' => RapportActiviteValidation::class,
                'code' => TypeValidation::CODE_RAPPORT_ACTIVITE_DOCTORANT,
                'role' => [Role::CODE_DOCTORANT],
                'pre_condition' => null,
                'enabled' => function(RapportActivite $rapportActivite) {
                    return $rapportActivite->getFichier() === null;
                },
            ],
            AVIS_GEST => [ // NB : conservé pour compatibilité avec ancien module
                'type' => RapportActiviteAvis::class,
                'code' => RapportActiviteAvis::AVIS_TYPE__CODE__AVIS_RAPPORT_ACTIVITE_GEST, // Cf. UNICAEN_AVIS_TYPE.CODE
                'role' => [Role::CODE_GEST_ED],
                'pre_condition' => [
                    // nom de l'opération devant exister => condition sur sa valeur booléenne
                    VALIDATION_DOCTORANT => true, // la validation doctorant doit exister & sa valeur positive (tj vrai pour une validation)
                ],
                'enabled' => function(RapportActivite $rapportActivite) {
                    return !$rapportActivite->estFinContrat() &&
                        $rapportActivite->getFichier() !== null;
                },
            ],
            AVIS_DIR_THESE => [
                'type' => RapportActiviteAvis::class,
                'code' => RapportActiviteAvis::AVIS_TYPE__CODE__AVIS_RAPPORT_ACTIVITE_DIR_THESE,
                'role' => [Role::CODE_DIRECTEUR_THESE],
                'pre_condition' => [
                    VALIDATION_DOCTORANT => true,
                ],
                'enabled' => function(RapportActivite $rapportActivite) {
                    return !$rapportActivite->estFinContrat() &&
                        $rapportActivite->getFichier() === null;
                },
            ],
            AVIS_CODIR_THESE => [
                'type' => RapportActiviteAvis::class,
                'code' => RapportActiviteAvis::AVIS_TYPE__CODE__AVIS_RAPPORT_ACTIVITE_CODIR_THESE,
                'role' => [Role::CODE_CODIRECTEUR_THESE],
                'pre_condition' => [
                    VALIDATION_DOCTORANT => true,
                ],
                'enabled' => function(RapportActivite $rapportActivite) {
                    return !$rapportActivite->estFinContrat() &&
                        $rapportActivite->getFichier() === null &&
                        !$rapportActivite->getThese()->getActeursByRoleCode(Role::CODE_CODIRECTEUR_THESE)->isEmpty();
                },
            ],
            AVIS_DIR_UR => [
                'type' => RapportActiviteAvis::class,
                'code' => RapportActiviteAvis::AVIS_TYPE__CODE__AVIS_RAPPORT_ACTIVITE_DIR_UR,
                'role' => [Role::CODE_RESP_UR],
                'pre_condition' => [
                    VALIDATION_DOCTORANT => true,
                ],
                'enabled' => function(RapportActivite $rapportActivite) {
                    return !$rapportActivite->estFinContrat() &&
                        $rapportActivite->getFichier() === null;
                },
            ],
            AVIS_DIR_ED => [
                'type' => RapportActiviteAvis::class,
                'code' => RapportActiviteAvis::AVIS_TYPE__CODE__AVIS_RAPPORT_ACTIVITE_DIR_ED,
                'role' => [Role::CODE_RESP_ED],
                'pre_condition' => [
                    VALIDATION_DOCTORANT => true,
                ],
                'enabled' => function(RapportActivite $rapportActivite) {
                    return !$rapportActivite->estFinContrat();
                },
                'extra' => [
                    // Si un avis "rapport incomplet" est émis par la direction d'ED, on supprimera la validation doctorant.
                    'validation_doctorant_operation_name' => VALIDATION_DOCTORANT,
                ]
            ],
            VALIDATION_AUTO => [
                'type' => RapportActiviteValidation::class,
                'code' => TypeValidation::CODE_RAPPORT_ACTIVITE_AUTO,
                'role' => [Role::CODE_RESP_ED],
                'pre_condition' => [
                    // nom de l'opération devant exister => condition sur sa valeur booléenne
                    VALIDATION_DOCTORANT => true,
                    AVIS_GEST => null, // l'avis gestionnaire doit exister & peu importe sa valeur (null = no condition)
                    AVIS_DIR_THESE => null,
                    AVIS_CODIR_THESE => null,
                    AVIS_DIR_UR => null,
                    AVIS_DIR_ED => null,
                ],
                'enabled' => function(RapportActivite $rapportActivite) {
                    return $rapportActivite->getFichier() !== null;
                },
            ],
        ],
    ],
];