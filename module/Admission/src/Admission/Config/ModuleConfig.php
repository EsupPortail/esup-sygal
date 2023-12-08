<?php

namespace Admission\Config;

use Admission\Entity\Db\Admission;
use Admission\Entity\Db\AdmissionValidation;
use Admission\Entity\Db\TypeValidation;
use Application\Entity\Db\Role;
use InvalidArgumentException;

class ModuleConfig
{
    const ATTESTATION_HONNEUR = 'ATTESTATION_HONNEUR';
    const VALIDATION_GESTIONNAIRE = 'VALIDATION_GESTIONNAIRE';
    const VALIDATION_DIRECTION_THESE = 'VALIDATION_DIRECTION_THESE';
    const VALIDATION_CO_DIRECTION_THESE = 'VALIDATION_CO_DIRECTION_THESE';
    const VALIDATION_UR = 'VALIDATION_UR';
    const VALIDATION_ED = 'VALIDATION_ED';
    const SIGNATURE_PRESIDENT = 'SIGNATURE_PRESIDENT';

    /**
     * Config de l'enchaînement des opérations (validations) attendus.
     */
    private array $operationsConfig;

    private array $allowedRoles = [
        Role::CODE_DOCTORANT,
        Role::CODE_DIRECTEUR_THESE,
        Role::CODE_CODIRECTEUR_THESE,
        Role::CODE_RESP_UR,
        Role::CODE_RESP_ED,
        Role::CODE_GEST_ED,
        Role::CODE_ADMIN_TECH,
    ];

    public function __construct()
    {
        $this->setOperationsConfig([
            /**
             * Attestation sur l'honneur par l'étudiant.
             */
            self::ATTESTATION_HONNEUR => [
                'type' => AdmissionValidation::class,
                'code' => TypeValidation::CODE_ATTESTATION_HONNEUR,
                'role' => [Role::CODE_ADMIN_TECH, Role::CODE_DOCTORANT],
                'pre_condition' => null,
                'enabled' => null,
                'enabled_as_dql' => null,
            ],
            /**
             * Validation par les gestionnaires de scolarité
             */
            self::VALIDATION_GESTIONNAIRE => [
                'type' => AdmissionValidation::class,
                'code' => TypeValidation::CODE_VALIDATION_GESTIONNAIRE,
                'role' => [Role::CODE_ADMIN_TECH, Role::CODE_GEST_ED],
                'pre_condition' => [
                    self::ATTESTATION_HONNEUR => true, // l'attestation sur l'honneur doit exister & sa valeur positive (tj vrai pour une validation)
                ],
                'enabled' => null,
                'enabled_as_dql' => null,
            ],
            /**
             * Validation par la direction de thèse
             */
            self::VALIDATION_DIRECTION_THESE => [
                'type' => AdmissionValidation::class,
                'code' => TypeValidation::CODE_VALIDATION_DIRECTION_THESE,
                'role' => [Role::CODE_ADMIN_TECH, Role::CODE_DIRECTEUR_THESE],
                'pre_condition' => [
                    self::ATTESTATION_HONNEUR => true,
                    self::VALIDATION_GESTIONNAIRE => true
                ],
                'enabled' => null,
                'enabled_as_dql' => null,
            ],
            /**
             * Validation par la co-direction de thèse
             */
            self::VALIDATION_CO_DIRECTION_THESE => [
                'type' => AdmissionValidation::class,
                'code' => TypeValidation::CODE_VALIDATION_CO_DIRECTION_THESE,
                'role' => [Role::CODE_ADMIN_TECH, Role::CODE_CODIRECTEUR_THESE],
                'pre_condition' => [
                    self::ATTESTATION_HONNEUR => true,
                    self::VALIDATION_GESTIONNAIRE => true,
                    self::VALIDATION_DIRECTION_THESE => true
                ],
                'enabled' => function(Admission $admission) {
                    if(!empty($admission->getInscription()->first())){
                        return
                            $admission->getInscription()->first()->getCoDirection() !== null && $admission->getInscription()->first()->getCoDirection() == true;
                    }
                    return false;
                },
                'enabled_as_dql' => null,
            ],
            /**
             * Validation par l'unité de recherche
             */
            self::VALIDATION_UR => [
                'type' => AdmissionValidation::class,
                'code' => TypeValidation::CODE_VALIDATION_UR,
                'role' => [Role::CODE_ADMIN_TECH, Role::CODE_RESP_UR],
                'pre_condition' => [
                    self::ATTESTATION_HONNEUR => true,
                    self::VALIDATION_GESTIONNAIRE => true,
                    self::VALIDATION_DIRECTION_THESE => true,
                    self::VALIDATION_CO_DIRECTION_THESE => true
                ],
                'enabled' => null,
                'enabled_as_dql' => null,
            ],
            /**
             * Validation par l'école doctorale
             */
            self::VALIDATION_ED => [
                'type' => AdmissionValidation::class,
                'code' => TypeValidation::CODE_VALIDATION_ED,
                'role' => [Role::CODE_ADMIN_TECH, Role::CODE_RESP_ED],
                'pre_condition' => [
                    self::ATTESTATION_HONNEUR => true,
                    self::VALIDATION_GESTIONNAIRE => true,
                    self::VALIDATION_DIRECTION_THESE => true,
                    self::VALIDATION_CO_DIRECTION_THESE => true,
                    self::VALIDATION_UR => true
                ],
                'enabled' => null,
                'enabled_as_dql' => null,
            ],
            /**
             * Validation par la présidence de l'université
             */
            self::SIGNATURE_PRESIDENT => [
                'type' => AdmissionValidation::class,
                'code' => TypeValidation::CODE_SIGNATURE_PRESIDENT,
                'role' => [Role::CODE_ADMIN_TECH, Role::CODE_ADMIN_TECH],
                'pre_condition' => [
                    self::ATTESTATION_HONNEUR => true,
                    self::VALIDATION_GESTIONNAIRE => true,
                    self::VALIDATION_DIRECTION_THESE => true,
                    self::VALIDATION_CO_DIRECTION_THESE => true,
                    self::VALIDATION_UR => true
                ],
                'enabled' => null,
                'enabled_as_dql' => null,
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