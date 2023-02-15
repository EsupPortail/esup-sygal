<?php

namespace RapportActivite\Config;

use Application\Entity\Db\Role;
use Application\Entity\Db\TypeValidation;
use InvalidArgumentException;
use RapportActivite\Entity\Db\RapportActivite;
use RapportActivite\Entity\Db\RapportActiviteAvis;
use RapportActivite\Entity\Db\RapportActiviteValidation;
use These\Entity\Db\Acteur;

class ModuleConfig
{
    const VALIDATION_DOCTORANT = 'VALIDATION_DOCTORANT';
    const AVIS_GEST = 'AVIS_GEST';
    const AVIS_DIR_THESE = 'AVIS_DIR_THESE';
    const AVIS_CODIR_THESE = 'AVIS_CODIR_THESE';
    const AVIS_DIR_UR = 'AVIS_DIR_UR';
    const AVIS_DIR_ED = 'AVIS_DIR_ED';
    const VALIDATION_AUTO = 'VALIDATION_AUTO';

    /**
     * Config de l'enchaînement des opérations (validations ou avis) attendus.
     */
    private array $operationsConfig;

    private array $allowedRoles = [
        Role::CODE_DOCTORANT,
        Role::CODE_DIRECTEUR_THESE,
        Role::CODE_CODIRECTEUR_THESE,
        Role::CODE_RESP_UR,
        Role::CODE_RESP_ED,
        Role::CODE_GEST_ED,
    ];

    public function __construct()
    {
        $this->setOperationsConfig([
            self::VALIDATION_DOCTORANT => [
                'type' => RapportActiviteValidation::class,
                'code' => TypeValidation::CODE_RAPPORT_ACTIVITE_DOCTORANT,
                'role' => [Role::CODE_DOCTORANT],
                'pre_condition' => null,
                'enabled' => function(RapportActivite $rapportActivite) {
                    return $rapportActivite->getFichier() === null;
                },
                'enabled_as_dql' => function(string $rapportAlias) {
                    return "$rapportAlias.fichier is null";
                },
            ],
            self::AVIS_GEST => [ // NB : conservé pour compatibilité avec ancien module
                'type' => RapportActiviteAvis::class,
                'code' => RapportActiviteAvis::AVIS_TYPE__CODE__AVIS_RAPPORT_ACTIVITE_GEST, // Cf. UNICAEN_AVIS_TYPE.CODE
                'role' => [Role::CODE_GEST_ED],
                'pre_condition' => [
                    // nom de l'opération devant exister => condition sur sa valeur booléenne
                    self::VALIDATION_DOCTORANT => true, // la validation doctorant doit exister & sa valeur positive (tj vrai pour une validation)
                ],
                'enabled' => function(RapportActivite $rapportActivite) {
                    return $rapportActivite->getFichier() !== null;
                },
                'enabled_as_dql' => function(string $rapportAlias) {
                    return "$rapportAlias.fichier is not null";
                },
            ],
            self::AVIS_DIR_THESE => [
                'type' => RapportActiviteAvis::class,
                'code' => RapportActiviteAvis::AVIS_TYPE__CODE__AVIS_RAPPORT_ACTIVITE_DIR_THESE,
                'role' => [Role::CODE_DIRECTEUR_THESE],
                'pre_condition' => [
                    self::VALIDATION_DOCTORANT => true,
                ],
                'enabled' => function(RapportActivite $rapportActivite) {
                    return
                        !$rapportActivite->estFinContrat() &&
                        $rapportActivite->getFichier() === null;
                },
                'enabled_as_dql' => function(string $rapportAlias) {
                    return "$rapportAlias.estFinContrat = false AND $rapportAlias.fichier is null";
                },
            ],
            self::AVIS_CODIR_THESE => [
                'type' => RapportActiviteAvis::class,
                'code' => RapportActiviteAvis::AVIS_TYPE__CODE__AVIS_RAPPORT_ACTIVITE_CODIR_THESE,
                'role' => [Role::CODE_CODIRECTEUR_THESE],
                'pre_condition' => [
                    self::VALIDATION_DOCTORANT => true,
                ],
                'enabled' => function(RapportActivite $rapportActivite) {
                    return
                        !$rapportActivite->estFinContrat() &&
                        $rapportActivite->getFichier() === null &&
                        $rapportActivite->getThese()->getActeursByRoleCode(Role::CODE_CODIRECTEUR_THESE)->count();
                },
                'enabled_as_dql' => function(string $rapportAlias) {
                    return
                        "$rapportAlias.estFinContrat = false AND $rapportAlias.fichier is null " .
                        "AND EXISTS (
                            SELECT a_filter FROM " . Acteur::class . " a_filter 
                            JOIN a_filter.role r WITH r.code = '" . Role::CODE_CODIRECTEUR_THESE . "'
                            WHERE a_filter.histoDestruction is null AND a_filter.these = $rapportAlias.these
                        )";
                },
            ],
            self::AVIS_DIR_UR => [
                'type' => RapportActiviteAvis::class,
                'code' => RapportActiviteAvis::AVIS_TYPE__CODE__AVIS_RAPPORT_ACTIVITE_DIR_UR,
                'role' => [Role::CODE_RESP_UR],
                'pre_condition' => [
                    self::VALIDATION_DOCTORANT => true,
                ],
                'enabled' => function(RapportActivite $rapportActivite) {
                    return
                        !$rapportActivite->estFinContrat() &&
                        $rapportActivite->getFichier() === null;
                },
                'enabled_as_dql' => function(string $rapportAlias) {
                    return "$rapportAlias.estFinContrat = false AND $rapportAlias.fichier is null";
                },
            ],
            self::AVIS_DIR_ED => [
                'type' => RapportActiviteAvis::class,
                'code' => RapportActiviteAvis::AVIS_TYPE__CODE__AVIS_RAPPORT_ACTIVITE_DIR_ED,
                'role' => [Role::CODE_RESP_ED],
                'pre_condition' => [
                    self::VALIDATION_DOCTORANT => true,
                ],
                'enabled' => function(RapportActivite $rapportActivite) {
                    return
                        (!$rapportActivite->estFinContrat() && $rapportActivite->getFichier() === null) ||
                        $rapportActivite->getFichier() !== null;
                },
                'enabled_as_dql' => function(string $rapportAlias) {
                    return
                        "$rapportAlias.estFinContrat = false AND $rapportAlias.fichier is null 
                        OR $rapportAlias.fichier is not null";
                },
                'extra' => [
                    // Si un avis "rapport incomplet" est émis par la direction d'ED, on supprimera la validation doctorant.
                    'validation_doctorant_operation_name' => self::VALIDATION_DOCTORANT,
                ],
            ],
            self::VALIDATION_AUTO => [
                'type' => RapportActiviteValidation::class,
                'code' => TypeValidation::CODE_RAPPORT_ACTIVITE_AUTO,
                'role' => [Role::CODE_RESP_ED],
                'pre_condition' => [
                    // nom de l'opération devant exister => condition sur sa valeur booléenne
                    self::VALIDATION_DOCTORANT => true,
                    self::AVIS_GEST => null, // l'avis gestionnaire doit exister & peu importe sa valeur (null = no condition)
                    self::AVIS_DIR_THESE => null,
                    self::AVIS_CODIR_THESE => null,
                    self::AVIS_DIR_UR => null,
                    self::AVIS_DIR_ED => null,
                ],
                'enabled' => function(RapportActivite $rapportActivite) {
                    return $rapportActivite->getFichier() !== null;
                },
                'enabled_as_dql' => function(string $rapportAlias) {
                    return "$rapportAlias.fichier is not null";
                },
            ],
        ]);
    }

    public function getOperationsConfig(): array
    {
        return $this->operationsConfig;
    }

    public function setOperationsConfig(array $operationsConfig)
    {
        $this->validateOperationsConfig($operationsConfig);

        $this->operationsConfig = $operationsConfig;
    }

    private function validateOperationsConfig(array $operationsConfig)
    {
        if (empty($operationsConfig)) {
            throw new InvalidArgumentException("Config vide !");
        }

        $distinctsCodes = [];
        $distinctsCodesForTypes = [];

        foreach ($operationsConfig as $config) {
            if (!array_key_exists($k = 'type', $config)) {
                throw new InvalidArgumentException("Config invalide, clé '$k' introuvable !");
            }
            if (!array_key_exists($k = 'code', $config)) {
                throw new InvalidArgumentException("Config invalide, clé '$k' introuvable !");
            }

            if (!array_key_exists($k = 'role', $config)) {
                throw new InvalidArgumentException("Config invalide, clé '$k' introuvable !");
            }
            $this->validateRoles($config['role']);

            if (in_array($config['code'], $distinctsCodes)) {
                throw new InvalidArgumentException("Config invalide, doublons de codes interdits !");
            }
            $distinctsCodes[] = $config['code'];

            if (($distinctsCodesForTypes[$config['type']] ?? null) === $config['code']) {
                throw new InvalidArgumentException("Config invalide, doublons {type, code} interdits !");
            }
            $distinctsCodesForTypes[$config['type']] = $config['code'];

        }
    }

    private function validateRoles(array $roles)
    {
        if ($diff = array_diff(array_unique($roles), $this->allowedRoles)) {
            throw new InvalidArgumentException("Les rôles suivants ne sont pas supportés : " . implode(', ', $diff));
        }
    }
}