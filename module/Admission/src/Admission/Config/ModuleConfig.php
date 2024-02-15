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
    const VALIDATION_CONVENTION_FORMATION_DOCTORALE_DIR_THESE = 'VALIDATION_CONVENTION_FORMATION_DOCT_DIR_THESE';
    const AVIS_DIR_THESE = 'AVIS_DIR_THESE';
    const VALIDATION_CONVENTION_FORMATION_DOCTORALE_CODIR_THESE = 'VALIDATION_CONVENTION_FORMATION_DOCT_CODIR_THESE';
    const AVIS_CODIR_THESE = 'AVIS_CODIR_THESE';
    const VALIDATION_CONVENTION_FORMATION_DOCTORALE_DIR_UR = 'VALIDATION_CONVENTION_FORMATION_DOCT_DIR_UR';
    const AVIS_DIR_UR = 'AVIS_DIR_UR';
    const VALIDATION_CONVENTION_FORMATION_DOCTORALE_DIR_ED = 'VALIDATION_CONVENTION_FORMATION_DOCT_DIR_ED';
    const AVIS_DIR_ED = 'AVIS_DIR_ED';
    const SIGNATURE_PRESIDENT = 'SIGNATURE_PRESIDENT';

    /**
     * Config de l'enchaînement des opérations (validations/avis) attendus.
     */
    private array $operationsConfig;

    private array $allowedRoles = [
        Role::CODE_ADMISSION_CANDIDAT,
        Role::CODE_ADMISSION_DIRECTEUR_THESE,
        Role::CODE_DIRECTEUR_THESE,
        Role::CODE_ADMISSION_CODIRECTEUR_THESE,
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
                'role' => [Role::CODE_ADMIN_TECH, Role::CODE_ADMISSION_CANDIDAT],
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
                'role' => [Role::CODE_ADMIN_TECH, Role::CODE_ADMISSION_CANDIDAT],
                'pre_condition' => self::ATTESTATION_HONNEUR_CHARTE_DOCTORALE,
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
                'pre_condition' => function(Admission $admission) {
                    return $admission->isDossierComplet();
                },
                'enabled' => null,
                'enabled_as_dql' => null,
            ],
            /**
             * Attestation sur l'honneur par la direction de thèse de la bonne lecture de la convention de formation doctorale.
             */
            self::VALIDATION_CONVENTION_FORMATION_DOCTORALE_DIR_THESE => [
                'type' => AdmissionValidation::class,
                'code' => TypeValidation::CODE_VALIDATION_CONVENTION_FORMATION_DOCTORALE_DIR_THESE,
                'role' => [Role::CODE_ADMIN_TECH, Role::CODE_DIRECTEUR_THESE, Role::CODE_ADMISSION_DIRECTEUR_THESE],
                'categorie' => [
                    "name" => "conventionFormationDoctorale",
                    //si l'on veut afficher cette validation avec les autres opérations
                    "showWithOthers" => false
                ],
                'pre_condition' => [
                    self::ATTESTATION_HONNEUR => true,
                    self::VALIDATION_GESTIONNAIRE => true
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
                'role' => [Role::CODE_ADMIN_TECH, Role::CODE_DIRECTEUR_THESE, Role::CODE_ADMISSION_DIRECTEUR_THESE],
                'pre_condition' => [
                    self::ATTESTATION_HONNEUR => true,
                    self::VALIDATION_GESTIONNAIRE => true,
                    self::VALIDATION_CONVENTION_FORMATION_DOCTORALE_DIR_THESE => true
                ],
                //pas de notif, peut-être à enlever si changement d'ordre des validations
                'readonly' => true,
                'enabled' => null,
                'enabled_as_dql' => null,
                'extra' => [
                    'validation_convention_formation_doctorale_dir_these_operation_name' => self::VALIDATION_CONVENTION_FORMATION_DOCTORALE_DIR_THESE,
                    'validation_convention_formation_doctorale_codir_these_operation_name' => self::VALIDATION_CONVENTION_FORMATION_DOCTORALE_CODIR_THESE,
                    'validation_convention_formation_doctorale_dir_ur_operation_name' => self::VALIDATION_CONVENTION_FORMATION_DOCTORALE_DIR_UR,
                    'validation_convention_formation_doctorale_dir_ed_operation_name' => self::VALIDATION_CONVENTION_FORMATION_DOCTORALE_DIR_ED,
                    // Si un avis "dossier d'admssion incomplet" est émis, on supprimera toutes les validations précédentes.
                    'validation_etudiant_operation_name' => self::ATTESTATION_HONNEUR,
                    'validation_gestionnaire_operation_name' => self::VALIDATION_GESTIONNAIRE,
                    'avis_direction_these_operation_name' => self::AVIS_DIR_THESE,
                    'avis_codirection_these_operation_name' => self::AVIS_CODIR_THESE,
                    'avis_direction_ur_operation_name' => self::AVIS_DIR_UR,
                    'avis_direction_ed_operation_name' => self::AVIS_DIR_ED,
                    'avis_presidence_operation_name' => self::SIGNATURE_PRESIDENT,
                ],
            ],
            /**
             * Attestation sur l'honneur par la co-direction de thèse de la bonne lecture de la convention de formation doctorale.
             */
            self::VALIDATION_CONVENTION_FORMATION_DOCTORALE_CODIR_THESE => [
                'type' => AdmissionValidation::class,
                'code' => TypeValidation::CODE_VALIDATION_CONVENTION_FORMATION_DOCTORALE_CODIR_THESE,
                'role' => [Role::CODE_ADMIN_TECH, Role::CODE_CODIRECTEUR_THESE, Role::CODE_ADMISSION_CODIRECTEUR_THESE],
                'categorie' => [
                    "name" => "conventionFormationDoctorale",
                    //si l'on veut afficher cette validation avec les autres opérations
                    "showWithOthers" => false
                ],
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
             * Avis par la co-direction de thèse
             */
            self::AVIS_CODIR_THESE => [
                'type' => AdmissionAvis::class,
                'code' => AdmissionAvis::AVIS_TYPE__CODE__AVIS_ADMISSION_CODIR_THESE,
                'role' => [Role::CODE_ADMIN_TECH, Role::CODE_CODIRECTEUR_THESE, Role::CODE_ADMISSION_CODIRECTEUR_THESE],
                'pre_condition' => [
                    self::ATTESTATION_HONNEUR => true,
                    self::VALIDATION_GESTIONNAIRE => true,
                    self::AVIS_DIR_THESE => true,
                    self::VALIDATION_CONVENTION_FORMATION_DOCTORALE_CODIR_THESE => true
                ],
                'enabled' => function(Admission $admission) {
                    if(!empty($admission->getInscription()->first())){
                        return
                            $admission->getInscription()->first()->getCoDirection() !== null && $admission->getInscription()->first()->getCoDirection() == true;
                    }
                    return false;
                },
                //pas de notif, peut-être à enlever si changement d'ordre des validations
                'readonly' => true,
                'extra' => [
                    'validation_convention_formation_doctorale_dir_these_operation_name' => self::VALIDATION_CONVENTION_FORMATION_DOCTORALE_DIR_THESE,
                    'validation_convention_formation_doctorale_codir_these_operation_name' => self::VALIDATION_CONVENTION_FORMATION_DOCTORALE_CODIR_THESE,
                    'validation_convention_formation_doctorale_dir_ur_operation_name' => self::VALIDATION_CONVENTION_FORMATION_DOCTORALE_DIR_UR,
                    'validation_convention_formation_doctorale_dir_ed_operation_name' => self::VALIDATION_CONVENTION_FORMATION_DOCTORALE_DIR_ED,
                    // Si un avis "dossier d'admssion incomplet" est émis, on supprimera toutes les validations précédentes.
                    'validation_etudiant_operation_name' => self::ATTESTATION_HONNEUR,
                    'validation_gestionnaire_operation_name' => self::VALIDATION_GESTIONNAIRE,
                    'avis_direction_these_operation_name' => self::AVIS_DIR_THESE,
                    'avis_codirection_these_operation_name' => self::AVIS_CODIR_THESE,
                    'avis_direction_ur_operation_name' => self::AVIS_DIR_UR,
                    'avis_direction_ed_operation_name' => self::AVIS_DIR_ED,
                    'avis_presidence_operation_name' => self::SIGNATURE_PRESIDENT,
                ],
                'enabled_as_dql' => null,
            ],
            /**
             * Attestation sur l'honneur par la direction de l'UR de la bonne lecture de la convention de formation doctorale.
             */
            self::VALIDATION_CONVENTION_FORMATION_DOCTORALE_DIR_UR => [
                'type' => AdmissionValidation::class,
                'code' => TypeValidation::CODE_VALIDATION_CONVENTION_FORMATION_DOCTORALE_DIR_UR,
                'role' => [Role::CODE_ADMIN_TECH, Role::CODE_RESP_UR],
                'categorie' => [
                    "name" => "conventionFormationDoctorale",
                    //si l'on veut afficher cette validation avec les autres opérations
                    "showWithOthers" => false
                ],
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
                    self::AVIS_CODIR_THESE => true,
                    self::VALIDATION_CONVENTION_FORMATION_DOCTORALE_DIR_UR => true
                ],
                'extra' => [
                    'validation_convention_formation_doctorale_dir_these_operation_name' => self::VALIDATION_CONVENTION_FORMATION_DOCTORALE_DIR_THESE,
                    'validation_convention_formation_doctorale_codir_these_operation_name' => self::VALIDATION_CONVENTION_FORMATION_DOCTORALE_CODIR_THESE,
                    'validation_convention_formation_doctorale_dir_ur_operation_name' => self::VALIDATION_CONVENTION_FORMATION_DOCTORALE_DIR_UR,
                    'validation_convention_formation_doctorale_dir_ed_operation_name' => self::VALIDATION_CONVENTION_FORMATION_DOCTORALE_DIR_ED,
                    // Si un avis "dossier d'admssion incomplet" est émis, on supprimera toutes les validations précédentes.
                    'validation_etudiant_operation_name' => self::ATTESTATION_HONNEUR,
                    'validation_gestionnaire_operation_name' => self::VALIDATION_GESTIONNAIRE,
                    'avis_direction_these_operation_name' => self::AVIS_DIR_THESE,
                    'avis_codirection_these_operation_name' => self::AVIS_CODIR_THESE,
                    'avis_direction_ur_operation_name' => self::AVIS_DIR_UR,
                    'avis_direction_ed_operation_name' => self::AVIS_DIR_ED,
                    'avis_presidence_operation_name' => self::SIGNATURE_PRESIDENT,
                ],
                //pas de notif, peut-être à enlever si changement d'ordre des validations
                'readonly' => true,
                'enabled' => null,
                'enabled_as_dql' => null,
            ],
            /**
             * Attestation sur l'honneur par la direction de l'UR de la bonne lecture de la convention de formation doctorale.
             */
            self::VALIDATION_CONVENTION_FORMATION_DOCTORALE_DIR_ED=> [
                'type' => AdmissionValidation::class,
                'code' => TypeValidation::CODE_VALIDATION_CONVENTION_FORMATION_DOCTORALE_DIR_ED,
                'role' => [Role::CODE_ADMIN_TECH, Role::CODE_RESP_ED],
                'categorie' => [
                    "name" => "conventionFormationDoctorale",
                    //si l'on veut afficher cette validation avec les autres opérations
                    "showWithOthers" => false
                ],
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
                    self::AVIS_DIR_UR => true,
                    self::VALIDATION_CONVENTION_FORMATION_DOCTORALE_DIR_ED => true
                ],
                'extra' => [
                    'validation_convention_formation_doctorale_dir_these_operation_name' => self::VALIDATION_CONVENTION_FORMATION_DOCTORALE_DIR_THESE,
                    'validation_convention_formation_doctorale_codir_these_operation_name' => self::VALIDATION_CONVENTION_FORMATION_DOCTORALE_CODIR_THESE,
                    'validation_convention_formation_doctorale_dir_ur_operation_name' => self::VALIDATION_CONVENTION_FORMATION_DOCTORALE_DIR_UR,
                    'validation_convention_formation_doctorale_dir_ed_operation_name' => self::VALIDATION_CONVENTION_FORMATION_DOCTORALE_DIR_ED,
                    // Si un avis "dossier d'admssion incomplet" est émis, on supprimera toutes les validations précédentes.
                    'validation_etudiant_operation_name' => self::ATTESTATION_HONNEUR,
                    'validation_gestionnaire_operation_name' => self::VALIDATION_GESTIONNAIRE,
                    'avis_direction_these_operation_name' => self::AVIS_DIR_THESE,
                    'avis_codirection_these_operation_name' => self::AVIS_CODIR_THESE,
                    'avis_direction_ur_operation_name' => self::AVIS_DIR_UR,
                    'avis_direction_ed_operation_name' => self::AVIS_DIR_ED,
                    'avis_presidence_operation_name' => self::SIGNATURE_PRESIDENT,
                ],
                //pas de notif, peut-être à enlever si changement d'ordre des validations
                'readonly' => true,
                'enabled' => null,
                'enabled_as_dql' => null,
            ],
            /**
             * Validation par la présidence de l'université
             */
            self::SIGNATURE_PRESIDENT => [
                'type' => AdmissionAvis::class,
                'code' => AdmissionAvis::AVIS_TYPE__CODE__AVIS_ADMISSION_PRESIDENCE,
                'role' => [Role::CODE_ADMIN_TECH, Role::CODE_GEST_ED],
                'pre_condition' => [
                    self::ATTESTATION_HONNEUR => true,
                    self::VALIDATION_GESTIONNAIRE => true,
                    self::AVIS_DIR_THESE => true,
                    self::AVIS_CODIR_THESE => true,
                    self::AVIS_DIR_UR => true,
                    self::AVIS_DIR_ED => true
                ],
                'extra' => [
                    'validation_convention_formation_doctorale_dir_these_operation_name' => self::VALIDATION_CONVENTION_FORMATION_DOCTORALE_DIR_THESE,
                    'validation_convention_formation_doctorale_codir_these_operation_name' => self::VALIDATION_CONVENTION_FORMATION_DOCTORALE_CODIR_THESE,
                    'validation_convention_formation_doctorale_dir_ur_operation_name' => self::VALIDATION_CONVENTION_FORMATION_DOCTORALE_DIR_UR,
                    'validation_convention_formation_doctorale_dir_ed_operation_name' => self::VALIDATION_CONVENTION_FORMATION_DOCTORALE_DIR_ED,
                    // Si un avis "dossier d'admssion incomplet" est émis, on supprimera toutes les validations précédentes.
                    'validation_etudiant_operation_name' => self::ATTESTATION_HONNEUR,
                    'validation_gestionnaire_operation_name' => self::VALIDATION_GESTIONNAIRE,
                    'avis_direction_these_operation_name' => self::AVIS_DIR_THESE,
                    'avis_codirection_these_operation_name' => self::AVIS_CODIR_THESE,
                    'avis_direction_ur_operation_name' => self::AVIS_DIR_UR,
                    'avis_direction_ed_operation_name' => self::AVIS_DIR_ED,
                    'avis_presidence_operation_name' => self::SIGNATURE_PRESIDENT,
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