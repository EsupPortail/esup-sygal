<?php

namespace Application\Assertion\These;

use Application\Assertion\Exception\FailedAssertionException;
use Application\Assertion\Interfaces\EntityAssertionInterface;
use Application\Assertion\These\GeneratedTheseEntityAssertion;
use Application\Assertion\ThrowsFailedAssertionExceptionTrait;
use Application\Entity\Db\Doctorant;
use Application\Entity\Db\NatureFichier;
use Application\Entity\Db\These;
use Application\Entity\Db\TypeValidation;
use Application\Entity\Db\VersionFichier;
use Application\Entity\Db\VSitu\DepotVersionCorrigeeValidationDirecteur;
use Application\Service\Fichier\FichierServiceAwareInterface;
use Application\Service\Fichier\FichierServiceAwareTrait;
use Application\Service\UserContextServiceAwareTrait;
use Application\Service\Validation\ValidationServiceAwareInterface;
use Application\Service\Validation\ValidationServiceAwareTrait;
use Zend\Log\LoggerAwareTrait;

class TheseEntityAssertion extends GeneratedTheseEntityAssertion
    implements EntityAssertionInterface, ValidationServiceAwareInterface, FichierServiceAwareInterface
{
    use UserContextServiceAwareTrait;
    use ValidationServiceAwareTrait;
    use ThrowsFailedAssertionExceptionTrait;
    use FichierServiceAwareTrait;
    use LoggerAwareTrait;

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
        $allowed = $this->assertAsBoolean($privilege);

//        $this->logger->debug(
//            $_SERVER['REQUEST_URI'] . PHP_EOL .
//            "assert(" . $privilege . ') : ' . ($allowed ? 'true' : 'false') . ' [' . $this->failureMessage . ']' . PHP_EOL
//        );

        $this->assertTrue($allowed, $this->failureMessage);

        return true;
    }

    protected function isExisteValidationPageDeCouverture()
    {
        $validations = $this->validationService->getRepository()->findValidationByCodeAndThese(TypeValidation::CODE_PAGE_DE_COUVERTURE, $this->these);

        return !empty($validations);
    }


    protected function isInfosBuSaisies()
    {
        return ($rdvBu = $this->these->getRdvBu()) && $rdvBu->isInfosBuSaisies();
    }

    protected function isExisteValidationRdvBu()
    {
        return $this->these && $this->these->getValidation(TypeValidation::CODE_RDV_BU);
    }

    protected function isExisteValidationBU()
    {
        return $this->these->getValidation(TypeValidation::CODE_RDV_BU) !== null;
    }

    /**
     * @return bool
     * @deprecated Utiliser isCorrectionAttendue
     */
    protected function isAucuneCorrectionAttendue()
    {
        return !$this->these->getCorrectionAutorisee();
    }

    protected function isExisteValidationCorrectionsThese()
    {
        return $this->these->getValidations(TypeValidation::CODE_CORRECTION_THESE)->count() > 0;
    }

    protected function isExisteValidationDepotVersionCorrigee()
    {
        return $this->these->getValidations(TypeValidation::CODE_DEPOT_THESE_CORRIGEE)->count() > 0;
    }

    protected function isUtilisateurExisteParmiValidateursAttendus()
    {
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

        return $found;
    }

    protected function isUtilisateurExisteParmiValidateursAyantValide()
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

    protected function isExisteValidationVersionPapierCorrigee()
    {
        $validations = $this->validationService->getRepository()->findValidationByCodeAndThese(
            TypeValidation::CODE_VERSION_PAPIER_CORRIGEE,
            $this->these
        );

        return count($validations) > 0;
    }

    protected function isCorrectionAttendue()
    {
        return $this->these->getCorrectionAutorisee();
    }

    protected function isDepotVersionCorrigeeValide()
    {
        return $this->these->getValidation(TypeValidation::CODE_DEPOT_THESE_CORRIGEE) !== null;
    }

    protected function isDateButoirDepotVersionCorrigeeDepassee()
    {
        // il y a une date butoir pour déposer la version corrigée et valider son dépôt
        $dateButoir = $this->these->getDateButoirDepotVersionCorrigee();
        if ($dateButoir !== null) {
            $now = new \DateTime('today'); // The time is set to 00:00:00

            return $now > $dateButoir;
        }

        return false;
    }

    protected function isUtilisateurEstAuteurDeLaThese()
    {
        return $this->these->getDoctorant()->getId() === $this->getIdentityDoctorant()->getId();
    }

    protected function isTheseSoutenue()
    {
        return $this->these->estSoutenue();
    }

    /**
     * @return bool
     */
    protected function isRoleDoctorantSelected()
    {
        return (bool) $this->userContextService->getSelectedRoleDoctorant();
    }

    /**
     * @return bool
     */
    protected function isExisteFichierTheseVersionOriginale()
    {
        if (null === $this->existeFichierTheseVersionOriginale) {
            $this->existeFichierTheseVersionOriginale = ! empty($this->fichierService->getRepository()->fetchFichiers(
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
    private $existeFichierTheseVersionOriginale;

    /**
     * @return bool
     */
    protected function isExisteFichierTheseVersionCorrigee()
    {
        if (null === $this->existeFichierTheseVersionCorrigee) {
            $this->existeFichierTheseVersionCorrigee = ! empty($this->fichierService->getRepository()->fetchFichiers(
                $this->these,
                NatureFichier::CODE_THESE_PDF,
                VersionFichier::CODE_ORIG_CORR,
                false));
        }

        return $this->existeFichierTheseVersionCorrigee;
    }

    /**
     * @var bool
     */
    private $existeFichierTheseVersionCorrigee;





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
}