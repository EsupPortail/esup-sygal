<?php

namespace Admission\Config;

use Admission\Entity\Db\Admission;
use Admission\Entity\Db\AdmissionAvis;
use Admission\Entity\Db\AdmissionValidation;
use Admission\Entity\Db\TypeValidation;
use Application\Entity\Db\Role;
use InvalidArgumentException;

class ModuleConfig
{
    const ATTESTATION_HONNEUR_CHARTE_DOCTORALE = 'ATTESTATION_HONNEUR_CHARTE_DOCTORALE';
    const ATTESTATION_HONNEUR = 'ATTESTATION_HONNEUR';
    const VALIDATION_GESTIONNAIRE = 'VALIDATION_GESTIONNAIRE';
    const AVIS_DIR_THESE = 'AVIS_DIR_THESE';
    const AVIS_CODIR_THESE = 'AVIS_CODIR_THESE';
    const AVIS_DIR_UR = 'AVIS_DIR_UR';
    const AVIS_DIR_ED = 'AVIS_DIR_ED';
    const SIGNATURE_PRESIDENT = 'SIGNATURE_PRESIDENT';

    /**
     * Config de l'enchaînement des opérations (validations/avis) attendus.
     */
    private array $operationsConfig;

    private array $allowedRoles = [
        Role::ROLE_ID_USER,
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
             * Attestation sur l'honneur par l'étudiant de la bonne lecture de sa charte doctorale.
             */
            self::ATTESTATION_HONNEUR_CHARTE_DOCTORALE => [
                'type' => AdmissionValidation::class,
                'code' => TypeValidation::CODE_ATTESTATION_HONNEUR_CHARTE_DOCTORALE,
                'role' => [Role::CODE_ADMIN_TECH, Role::ROLE_ID_USER],
                'pre_condition' => null,
                'enabled' => null,
                'enabled_as_dql' => null,
            ],
            /**
             * Attestation sur l'honneur par l'étudiant.
             */
            self::ATTESTATION_HONNEUR => [
                'type' => AdmissionValidation::class,
                'code' => TypeValidation::CODE_ATTESTATION_HONNEUR,
                'role' => [Role::CODE_ADMIN_TECH, Role::ROLE_ID_USER],
                'pre_condition' => function(Admission $admission) {
                    $condition_verified = [];
                    if($admission->isDossierComplet()){
                        $condition_verified[] = $admission->isDossierComplet();
                    }

                    if(self::ATTESTATION_HONNEUR_CHARTE_DOCTORALE){
                        $condition_verified[] = self::ATTESTATION_HONNEUR_CHARTE_DOCTORALE;
                    }

                    return count($condition_verified) == 2;
                },
                //pas de notif, peut-être à enlever si changement d'ordre des validations
                'readonly' => true,
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
             * Avis par la direction de thèse
             */
            self::AVIS_DIR_THESE => [
                'type' => AdmissionAvis::class,
                'code' => AdmissionAvis::AVIS_TYPE__CODE__AVIS_ADMISSION_DIR_THESE,
                'role' => [Role::CODE_ADMIN_TECH, Role::CODE_DIRECTEUR_THESE],
                'pre_condition' => [
                    self::ATTESTATION_HONNEUR => true,
                    self::VALIDATION_GESTIONNAIRE => true
                ],
                'enabled' => null,
                'enabled_as_dql' => null,
                'extra' => [
                    // Si un avis "dossier d'admssion incomplet" est émis, on supprimera toutes les validations précédentes.
                    'validation_etudiant_operation_name' => self::ATTESTATION_HONNEUR,
                    'validation_gestionnaire_operation_name' => self::VALIDATION_GESTIONNAIRE,
                ],
            ],
            /**
             * Avis par la co-direction de thèse
             */
            self::AVIS_CODIR_THESE => [
                'type' => AdmissionAvis::class,
                'code' => AdmissionAvis::AVIS_TYPE__CODE__AVIS_ADMISSION_CODIR_THESE,
                'role' => [Role::CODE_ADMIN_TECH, Role::CODE_CODIRECTEUR_THESE],
                'pre_condition' => [
                    self::ATTESTATION_HONNEUR => true,
                    self::VALIDATION_GESTIONNAIRE => true,
                    self::AVIS_DIR_THESE => true
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
             * Avis par l'unité de recherche
             */
            self::AVIS_DIR_UR => [
                'type' => AdmissionAvis::class,
                'code' => AdmissionAvis::AVIS_TYPE__CODE__AVIS_ADMISSION_DIR_UR,
                'role' => [Role::CODE_ADMIN_TECH, Role::CODE_RESP_UR],
                'pre_condition' => [
                    self::ATTESTATION_HONNEUR => true,
                    self::VALIDATION_GESTIONNAIRE => true,
                    self::AVIS_DIR_THESE => true,
                    self::AVIS_CODIR_THESE => true
                ],
                'enabled' => null,
                'enabled_as_dql' => null,
            ],
            /**
             * Avis par l'école doctorale
             */
            self::AVIS_DIR_ED => [
                'type' => AdmissionAvis::class,
                'code' => AdmissionAvis::AVIS_TYPE__CODE__AVIS_ADMISSION_DIR_ED,
                'role' => [Role::CODE_ADMIN_TECH, Role::CODE_RESP_ED],
                'pre_condition' => [
                    self::ATTESTATION_HONNEUR => true,
                    self::VALIDATION_GESTIONNAIRE => true,
                    self::AVIS_DIR_THESE => true,
                    self::AVIS_CODIR_THESE => true,
                    self::AVIS_DIR_UR => true
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
                'role' => [Role::CODE_ADMIN_TECH, Role::CODE_GEST_ED],
                'pre_condition' => [
                    self::ATTESTATION_HONNEUR => true,
                    self::VALIDATION_GESTIONNAIRE => true,
                    self::AVIS_DIR_THESE => true,
                    self::AVIS_CODIR_THESE => true,
                    self::AVIS_DIR_UR => true,
                    self::AVIS_DIR_ED => true
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