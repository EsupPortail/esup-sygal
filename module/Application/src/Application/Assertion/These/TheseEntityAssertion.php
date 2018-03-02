<?php

namespace Application\Assertion\These;

use Application\Assertion\Exception\FailedAssertionException;
use Application\Assertion\Interfaces\EntityAssertionInterface;
use Application\Assertion\ThrowsFailedAssertionExceptionTrait;
use Application\Constants;
use Application\Entity\Db\Doctorant;
use Application\Entity\Db\NatureFichier;
use Application\Entity\Db\These;
use Application\Entity\Db\TypeValidation;
use Application\Entity\Db\VersionFichier;
use Application\Entity\Db\VSitu\DepotVersionCorrigeeValidationDirecteur;
use Application\Provider\Privilege\ThesePrivileges;
use Application\Provider\Privilege\ValidationPrivileges;
use Application\Service\Fichier\FichierServiceAwareInterface;
use Application\Service\Fichier\FichierServiceAwareTrait;
use Application\Service\UserContextServiceAwareTrait;
use Application\Service\Validation\ValidationServiceAwareInterface;
use Application\Service\Validation\ValidationServiceAwareTrait;

class TheseEntityAssertion implements EntityAssertionInterface, ValidationServiceAwareInterface, FichierServiceAwareInterface
{
    use UserContextServiceAwareTrait;
    use ValidationServiceAwareTrait;
    use ThrowsFailedAssertionExceptionTrait;
    use FichierServiceAwareTrait;

    /**
     * @var These
     */
    private $these;

    /**
     * @param These $these
     * @return TheseEntityAssertion
     */
    public function setThese($these)
    {
        $this->these = $these;

        return $this;
    }

    /**
     * @param string $privilege
     * @return boolean
     * @throws FailedAssertionException
     */
    public function assert($privilege = null)
    {
        switch ($privilege) {
            /**
             * THESE_DEPOT_VERSION_INITIALE
             */
            case ThesePrivileges::THESE_DEPOT_VERSION_INITIALE:
                $this->assertTrue(
                    $this->these && !$this->these->getCorrectionAutorisee(),
                    "Le dépôt d'une version initiale n'est plus possible dès lors qu'une version corrigée est attendue."
                );
                break;

            /**
             * THESE_SAISIE_CONFORMITE_ARCHIVAGE
             */
            case ThesePrivileges::THESE_SAISIE_CONFORMITE_ARCHIVAGE:
                if ($this->existeFichierTheseVersionCorrigee()) {
                    $this->assertDepotVersionCorrigeeNonEncoreValide();
                } else {
                    $this->assertAucuneValidationBU();
                }
                break;

            /**
             * THESE_DEPOT_VERSION_CORRIGEE
             */
            case ThesePrivileges::THESE_DEPOT_VERSION_CORRIGEE:
                $this->assertCorrectionAttendue();
                $this->assertCorrectionNonEncoreValideNiveauDirecteur();
                $this->assertDateButoirDepotVersionCorrigeeNonDepasse();
                break;

            /**
             * THESE_SAISIE_RDV_BU
             */
            case ThesePrivileges::THESE_SAISIE_RDV_BU:
                $this->assertAucuneValidationBU("La validation par la BU a été faite.");
                break;

            /**
             * THESE_VALIDATION_RDV_BU
             */
            case ValidationPrivileges::THESE_VALIDATION_RDV_BU:
                $this->assertAucuneValidationBU("La validation par la BU a été faite.");
                // ce qui suit a été ajouté lors de l'absorption de l'étape RDV_BU_SAISIE_BU par l'étape RDV_BU_VALIDATION_BU
                $this->assertTrue(
                    ($rdvBu = $this->these->getRdvBu()) && $rdvBu->isInfosBuSaisies(),
                    "La BU n'a pas renseigné toutes informations requises."
                );
                break;

            /**
             * THESE_VALIDATION_RDV_BU_SUPPR
             */
            case ValidationPrivileges::THESE_VALIDATION_RDV_BU_SUPPR:
                $this->assertTrue($this->these && $this->these->getValidation(TypeValidation::CODE_RDV_BU));
                $this->assertFalse($this->existeFichierTheseVersionCorrigee());
                break;

            /**
             * VALIDATION_DEPOT_THESE_CORRIGEE
             */
            case ValidationPrivileges::VALIDATION_DEPOT_THESE_CORRIGEE:
                $this->assertCorrectionAttendue();
                $this->assertDepotVersionCorrigeeNonEncoreValide();
                $this->assertDateButoirDepotVersionCorrigeeNonDepasse();
                break;

            /**
             * VALIDATION_DEPOT_THESE_CORRIGEE_SUPPR
             */
            case ValidationPrivileges::VALIDATION_DEPOT_THESE_CORRIGEE_SUPPR:
                $this->assertTrue(
                    $this->these->getValidations(TypeValidation::CODE_CORRECTION_THESE)->count() === 0
                );
                break;

            /**
             * VALIDATION_CORRECTION_THESE
             */
            case ValidationPrivileges::VALIDATION_CORRECTION_THESE:
                // le dépôt de la version corrigée doit être validé
                $this->assertTrue(
                    $this->these->getValidations(TypeValidation::CODE_DEPOT_THESE_CORRIGEE)->count() > 0,
                    "Le dépôt de la version corrigée n'a pas encore été validé par le doctorant."
                );

                // recherche de l'utilisateur parmi les validateurs attendus
                $results = $this->validationService->getValidationsAttenduesPourCorrectionThese($this->these);
                $individu = $this->userContextService->getIdentityIndividu();
                $this->assertTrue($individu !== null);
                $found = false;
                /** @var DepotVersionCorrigeeValidationDirecteur $result */
                foreach ($results as $result) {
                    if ($result->getIndividu()->getId() === $individu->getId()) {
                        $found = true;
                        break;
                    }
                }
                $this->assertTrue($found);
                break;

            /**
             * VALIDATION_CORRECTION_THESE_SUPPR
             */
            case ValidationPrivileges::VALIDATION_CORRECTION_THESE_SUPPR:
                // recherche de l'utilisateur parmi les personnes ayant validé
                $individu = $this->userContextService->getIdentityIndividu();
                $this->assertTrue($individu !== null);
                $found = false;
                $validations = $this->these->getValidations(TypeValidation::CODE_CORRECTION_THESE);
                foreach ($validations as $validation) {
                    if ($validation->getIndividu() && $validation->getIndividu()->getId() === $individu->getId()) {
                        $found = true;
                        break;
                    }
                }
                $this->assertTrue($found);
                break;

            /**
             * Validation de la remise de la version papier corrigée.
             */
            case ValidationPrivileges::VALIDATION_VERSION_PAPIER_CORRIGEE:
                $res = $this->validationService->getRepository()->findValidationByCodeAndThese(
                    TypeValidation::CODE_VERSION_PAPIER_CORRIGEE,
                    $this->these
                );
                $pasEncoreValidee = empty($res);
                $this->assertTrue($pasEncoreValidee);
                break;
        }

        /**
         * Spécificités du rôle Doctorant.
         */
        if ($this->selectedRoleIsDoctorant()) {
            $this->assertEntityAsDoctorant($privilege);
        }

        return true;
    }

    /**
     * @param string $privilege
     * @throws FailedAssertionException
     */
    private function assertEntityAsDoctorant($privilege = null)
    {
        $this->assertTrue(
            $this->these->getDoctorant()->getId() === $this->getIdentityDoctorant()->getId(),
            "Cette thèse n'est pas la vôtre.");

        switch ($privilege) {
            case ThesePrivileges::THESE_SAISIE_DESCRIPTION:
            case ThesePrivileges::THESE_SAISIE_ATTESTATIONS:
            case ThesePrivileges::THESE_SAISIE_AUTORISATION_DIFFUSION:
            case ThesePrivileges::THESE_DEPOT_VERSION_INITIALE:
                if ($this->existeFichierTheseVersionCorrigee()) {
                    $this->assertDepotVersionCorrigeeNonEncoreValide();
                } else {
                    $this->assertAucuneValidationBU();
                }
                break;
        }
        //Une correction doit être apportée par le doctorant, celui-ci ne peut plus modifier son autorisation de diffusion
        if ($privilege === ThesePrivileges::THESE_SAISIE_AUTORISATION_DIFFUSION) {
            $this->assertFalse(
                $this->existeFichierTheseVersionCorrigee(),
            "Aucune version corrigée n'a été fournie.");
        }

        if (ThesePrivileges::THESE_DEPOT_VERSION_INITIALE) {
            $this->assertFalse(
                $this->these->estSoutenue(),
                "Dépot initial bloqué car soutenance effectuée"
            );
        }
    }

    private function assertAucuneValidationBU($message = null)
    {
        // la BU ne doit pas avoir validé
        $rdvBuPasValide = ! $this->these->getValidation(TypeValidation::CODE_RDV_BU);
        $this->assertTrue(
            $rdvBuPasValide,
            $message ?: "Opération impossible dès lors que la BU a validé.");
    }


    private function assertCorrectionAttendue()
    {
        // des corrections doivent être attendues
        $this->assertTrue(
            $this->these->getCorrectionAutorisee(),
            "Aucune correction n'est attendue pour cette thèse");
    }

    private function assertDepotVersionCorrigeeNonEncoreValide()
    {
        // le dépôt de la version corrigée ne doit pas avoir été validé
        $depotTheseCorrigeePasValide = ! $this->these->getValidation(TypeValidation::CODE_DEPOT_THESE_CORRIGEE);
        $this->assertTrue(
            $depotTheseCorrigeePasValide,
            "Opération impossible dès lors que le dépôt de la version corrigée a été validé.");
    }

    private function assertCorrectionNonEncoreValideNiveauDirecteur()
    {
        // le dépôt de la version corrigée ne doit pas avoir été validé
        $collection = $this->these->getValidations(TypeValidation::CODE_CORRECTION_THESE);
        $nbValidation = $collection->count();
        $correctionPasValideeParDirecteur =  ($nbValidation === 0);
        $this->assertTrue(
            $correctionPasValideeParDirecteur,
            "Opération impossible dès lors que le dépôt de la version corrigée a été validé par au moins un directeur.");
    }

    private function assertDateButoirDepotVersionCorrigeeNonDepasse()
    {
        // il y a une date butoir pour déposer la version corrigée et valider son dépôt
        $dateButoir = $this->these->getDateButoirDepotVersionCorrigee();
        if ($dateButoir !== null) {
            $now = new \DateTime('today'); // The time is set to 00:00:00
            $this->assertTrue(
                $now <= $dateButoir,
                sprintf("La date butoir pour le dépôt de la version corrigée est dépassée (%s).",
                    $dateButoir->format(Constants::DATE_FORMAT)));
        }
    }

    /**
     * @return bool
     */
    private function selectedRoleIsDoctorant()
    {
        return (bool) $this->userContextService->getSelectedRoleDoctorant();
    }
    /**
     * @var Doctorant
     */
    protected $identityDoctorant;

    /**
     * @return Doctorant
     */
    private function getIdentityDoctorant()
    {
        if (null === $this->identityDoctorant) {
            $this->identityDoctorant = $this->userContextService->getIdentityDoctorant();
        }

        return $this->identityDoctorant;
    }

    private function existeFichierTheseVersionCorrigee()
    {
//        if (! $this->getFichiersByNatureEtVersion(NatureFichier::CODE_THESE_PDF, VersionFichier::CODE_ORIG_CORR)->isEmpty()) {
        if (! empty($this->fichierService->getRepository()->fetchFichiers($this->these, NatureFichier::CODE_THESE_PDF, VersionFichier::CODE_ORIG_CORR, false))) {
            return true;
        }
        return false;
    }
}