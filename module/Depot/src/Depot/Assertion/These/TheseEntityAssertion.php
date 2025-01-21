<?php

namespace Depot\Assertion\These;

use Application\Assertion\ThrowsFailedAssertionExceptionTrait;
use Application\Entity\Db\TypeValidation;
use Application\Service\UserContextServiceAwareTrait;
use Application\Service\Validation\ValidationServiceAwareInterface;
use Application\Service\Validation\ValidationServiceAwareTrait;
use Depot\Service\FichierThese\FichierTheseServiceAwareInterface;
use Depot\Service\FichierThese\FichierTheseServiceAwareTrait;
use Depot\Service\These\DepotServiceAwareTrait;
use Depot\Service\Validation\DepotValidationServiceAwareTrait;
use Doctorant\Entity\Db\Doctorant;
use Fichier\Entity\Db\NatureFichier;
use Fichier\Entity\Db\VersionFichier;
use These\Entity\Db\These;
use These\Service\These\TheseServiceAwareInterface;
use These\Service\These\TheseServiceAwareTrait;
use UnicaenApp\Exception\LogicException;

class TheseEntityAssertion extends GeneratedTheseEntityAssertion
    implements ValidationServiceAwareInterface, FichierTheseServiceAwareInterface, TheseServiceAwareInterface
{
    use UserContextServiceAwareTrait;
    use ValidationServiceAwareTrait;
    use DepotValidationServiceAwareTrait;
    use ThrowsFailedAssertionExceptionTrait;
    use FichierTheseServiceAwareTrait;
    use TheseServiceAwareTrait;
    use DepotServiceAwareTrait;

    private ?These $these = null;

    /**
     * @param array $context
     */
    public function setContext(array $context)
    {
        $this->these = $context['these'];
    }

    /**
     * @param string|null $privilege
     * @return boolean
     */
    public function assert(?string $privilege = null): bool
    {
        $allowed = $this->assertAsBoolean($privilege);

        $this->assertTrue($allowed, $this->failureMessage);

        return true;
    }

    public function isStructureDuRoleRespectee(): bool
    {
        return $this->userContextService->isStructureDuRoleRespecteeForThese($this->these);
    }

    protected function isExisteValidationPageDeCouverture(): bool
    {
        $validations = $this->validationService->getRepository()->findValidationByCodeAndThese(TypeValidation::CODE_PAGE_DE_COUVERTURE, $this->these);

        return !empty($validations);
    }

    protected function isInfosBuSaisies(): bool
    {
        return $this->depotService->isInfosBuSaisies($this->these);
    }

    protected function isExisteValidationRdvBu(): bool
    {
        return $this->these && $this->these->getValidation(TypeValidation::CODE_RDV_BU);
    }

    protected function isExisteValidationBU(): bool
    {
        return $this->these->getValidation(TypeValidation::CODE_RDV_BU) !== null;
    }

    /**
     * @return bool
     * @deprecated Utiliser isCorrectionAttendue
     */
    protected function isAucuneCorrectionAttendue(): bool
    {
        return !$this->these->getCorrectionAutorisee();
    }

    protected function isExisteValidationCorrectionsThese(): bool
    {
        return $this->these->getValidations(TypeValidation::CODE_CORRECTION_THESE)->count() > 0;
    }

    protected function isExisteValidationDepotVersionCorrigee(): bool
    {
        return $this->these->getValidations(TypeValidation::CODE_DEPOT_THESE_CORRIGEE)->count() > 0;
    }

    protected function isUtilisateurExisteParmiValidateursAttendus(): bool
    {
        // recherche de l'utilisateur parmi les validateurs attendus
        $results = $this->depotValidationService->getValidationsAttenduesPourCorrectionThese($this->these);
        $individu = $this->userContextService->getIdentityIndividu();
        $this->assertTrue($individu !== null);
        $found = false;
        foreach ($results as $result) {
            if ($result->getIndividu()->getId() === $individu->getId()) {
                $found = true;
                break;
            }
        }

        return $found;
    }

    protected function isUtilisateurExisteParmiValidateursAyantValide(): bool
    {
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

        return $found;
    }

    protected function isExisteValidationVersionPapierCorrigee(): bool
    {
        $validations = $this->validationService->getRepository()->findValidationByCodeAndThese(
            TypeValidation::CODE_VERSION_PAPIER_CORRIGEE,
            $this->these
        );

        return count($validations) > 0;
    }

    protected function isCorrectionAttendue(): bool
    {
        return $this->these->isCorrectionAutorisee();
    }

    protected function isDepotVersionCorrigeeValide(): bool
    {
        return $this->these->getValidation(TypeValidation::CODE_DEPOT_THESE_CORRIGEE) !== null;
    }

    protected function isDateButoirDepotVersionCorrigeeDepassee(): bool
    {
        return $this->these->isDateButoirDepotVersionCorrigeeDepassee($this->these->getDateSoutenance());
    }

    protected function isUtilisateurEstAuteurDeLaThese(): bool
    {
        if ($this->getIdentityDoctorant() === null) return false;
        return $this->these->getDoctorant()->getId() === $this->getIdentityDoctorant()->getId();
    }

    /**
     * @return bool
     */
    protected function isTheseEnCours(): bool
    {
        return $this->these->getEtatThese() === These::ETAT_EN_COURS;
    }

    protected function isTheseSoutenue(): bool
    {
        return $this->these->estSoutenue();
    }

    /**
     * @return bool
     */
    protected function isRoleDoctorantSelected(): bool
    {
        return (bool) $this->userContextService->getSelectedRoleDoctorant();
    }

    /**
     * @return bool
     */
    protected function isExisteFichierTheseVersionOriginale(): bool
    {
        if (null === $this->existeFichierTheseVersionOriginale) {
            $this->existeFichierTheseVersionOriginale = ! empty($this->fichierTheseService->getRepository()->fetchFichierTheses(
                $this->these,
                NatureFichier::CODE_THESE_PDF,
                VersionFichier::CODE_ORIG,
                false));
        }

        return $this->existeFichierTheseVersionOriginale;
    }

    /**
     * @var bool
     */
    private ?bool $existeFichierTheseVersionOriginale = null;

    /**
     * @return bool
     */
    protected function isExisteFichierTheseVersionCorrigee(): bool
    {
        if (null === $this->existeFichierTheseVersionCorrigee) {
            $this->existeFichierTheseVersionCorrigee = ! empty($this->fichierTheseService->getRepository()->fetchFichierTheses(
                $this->these,
                NatureFichier::CODE_THESE_PDF,
                VersionFichier::CODE_ORIG_CORR,
                false));
        }

        return $this->existeFichierTheseVersionCorrigee;
    }

    private ?bool $existeFichierTheseVersionCorrigee = null;

    protected ?Doctorant $identityDoctorant = null;

    /**
     * @return Doctorant
     */
    private function getIdentityDoctorant(): ?Doctorant
    {
        if (null === $this->identityDoctorant) {
            $this->identityDoctorant = $this->userContextService->getIdentityDoctorant();
        }

        return $this->identityDoctorant;
    }

    /**
     * @return bool
     */
    protected function isPageDeCouvertureGenerable(): bool
    {
        $informations = $this->theseService->fetchInformationsPageDeCouverture($this->these);

        foreach ($informations as $clef => $information) {
            if ($clef !== 'dateFinConfidentialite' AND $information == "") {
                return false;
            }
        }
        return true;
    }

    protected ?bool $isAttestationsVersionInitialeSaisies = null;

    protected function isAttestationsVersionInitialeSaisies(): bool
    {
        if ($this->isAttestationsVersionInitialeSaisies === null) {
            $this->isAttestationsVersionInitialeSaisies = $this->depotService->isAttestationsVersionInitialeSaisies($this->these);
        }

        return $this->isAttestationsVersionInitialeSaisies;
    }

    protected ?bool $isAttestationsVersionCorrigeeSaisies = null;

    protected function isAttestationsVersionCorrigeeSaisies(): bool
    {
        if ($this->isAttestationsVersionCorrigeeSaisies === null) {
            $this->isAttestationsVersionCorrigeeSaisies = $this->depotService->isAttestationsVersionCorrigeeSaisies($this->these);
        }

        return $this->isAttestationsVersionCorrigeeSaisies;
    }
}