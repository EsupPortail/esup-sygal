<?php

namespace Admission\Assertion;

use Admission\Entity\Db\Admission;
use Admission\Entity\Db\TypeValidation;
use Admission\Provider\Privilege\AdmissionPrivileges;
use Admission\Service\Admission\AdmissionServiceAwareTrait;
use Application\Assertion\AbstractAssertion;
use Application\Assertion\Exception\FailedAssertionException;
use Application\Assertion\ThrowsFailedAssertionExceptionTrait;
use Application\Entity\Db\Role;
use Application\RouteMatch;
use Application\Service\UserContextServiceAwareInterface;
use Application\Service\UserContextServiceAwareTrait;
use Doctrine\Common\Collections\Collection;
use Individu\Entity\Db\Individu;
use Individu\Service\IndividuServiceAwareTrait;
use Laminas\Permissions\Acl\Resource\ResourceInterface;
use UnicaenApp\Service\MessageCollectorAwareInterface;
use UnicaenApp\Service\MessageCollectorAwareTrait;

class AdmissionAssertion extends AbstractAssertion implements UserContextServiceAwareInterface, MessageCollectorAwareInterface
{
    private ?Admission $admission = null;
    use ThrowsFailedAssertionExceptionTrait;
    use MessageCollectorAwareTrait;
    use AdmissionServiceAwareTrait;
    use UserContextServiceAwareTrait;
    use IndividuServiceAwareTrait;

    /**
     * @param array $page
     * @return bool
     */
    public function __invoke(array $page): bool
    {
        return $this->assertPage($page);
    }

    /**
     * @param array $page
     * @return bool
     */
    private function assertPage(array $page): bool
    {
        return true;
    }

    /**
     * @param string $controller
     * @param string $action
     * @param string $privilege
     * @return boolean
     */
    protected function assertController($controller, $action = null, $privilege = null): bool
    {
        if (!parent::assertController($controller, $action, $privilege)) {
            return false;
        }

        $this->admission = $this->getRequestedAdmission();

        try {
            switch ($action) {
                case 'etudiant':
                    if ($this->admission == null) {
                        $this->assertPeutInitialiserAdmission();
                    }
                    break;
            }

            switch ($action) {
                case 'etudiant':
                case 'inscription':
                case 'financement':
                case 'document':
                case 'supprimer':
                case 'telecharger-document':
                case 'enregistrer-document':
                case 'supprimer-document':
                case 'notifier-commentaires-ajoutes':
                case 'notifier-gestionnaire':
                case 'notifier-dossier-complet':
                case 'generer-recapitulatif':
                case 'ajouter-convention-formation' :
                    if ($this->admission !== null) {
                        $this->assertAppartenanceAdmission($this->admission);
                    }
                    break;
            }

            switch ($action) {
                case 'enregistrer-document':
                case 'supprimer-document':
                case 'notifier-gestionnaire':
                    if ($this->admission !== null) {
                        $this->assertEtatAdmission($this->admission);
                    }
                    break;
            }

            switch ($action) {
                case 'generer-recapitulatif':
                    if ($this->admission !== null) {
                        $this->assertCanGenererRecapitulatif($this->admission->getAdmissionValidations());
                    }
                    break;
            }

            switch ($action) {
                case 'notifier-commentaires-ajoutes':
                case 'notifier-gestionnaire':
                case 'notifier-dossier-complet':
                case 'notifier-dossier-incomplet':
                    if ($this->admission !== null) {
                        $this->assertCanGestionnaireGererAdmission($this->admission->getAdmissionValidations());
                    }
                    break;
            }
        } catch (FailedAssertionException $e) {
            if ($e->getMessage()) {
                $this->getServiceMessageCollector()->addMessage($e->getMessage(), __CLASS__);
            }
            return false;
        }

        return true;
    }

    /**
     * @param Admission $entity
     * @param string $privilege
     * @return boolean
     */
    protected function assertEntity(ResourceInterface $entity, $privilege = null): bool
    {
        if (!parent::assertEntity($entity, $privilege)) {
            return false;
        }
        $this->admission = $entity;

        try {

            switch ($privilege) {
                case AdmissionPrivileges::ADMISSION_MODIFIER_TOUS_DOSSIERS_ADMISSION:
                case AdmissionPrivileges::ADMISSION_MODIFIER_SON_DOSSIER_ADMISSION:
                case AdmissionPrivileges::ADMISSION_SUPPRIMER_TOUS_DOSSIERS_ADMISSION:
                case AdmissionPrivileges::ADMISSION_SUPPRIMER_SON_DOSSIER_ADMISSION:
                case AdmissionPrivileges::ADMISSION_TELEVERSER_TOUT_DOCUMENT:
                case AdmissionPrivileges::ADMISSION_TELEVERSER_SON_DOCUMENT:
                case AdmissionPrivileges::ADMISSION_SUPPRIMER_TOUT_DOCUMENT:
                case AdmissionPrivileges::ADMISSION_SUPPRIMER_SON_DOCUMENT:
                case AdmissionPrivileges::ADMISSION_NOTIFIER_GESTIONNAIRES:
                    $this->assertEtatAdmission($this->admission);
            }

            switch ($privilege) {
                case AdmissionPrivileges::ADMISSION_ACCEDER_COMMENTAIRES:
                case AdmissionPrivileges::ADMISSION_NOTIFIER_COMMENTAIRES_AJOUTES:
                case AdmissionPrivileges::ADMISSION_VERIFIER:
                case AdmissionPrivileges::ADMISSION_NOTIFIER_DOSSIER_COMPLET:
                case AdmissionPrivileges::ADMISSION_NOTIFIER_DOSSIER_INCOMPLET:
                    $this->assertCanGestionnaireGererAdmission($this->admission->getAdmissionValidations());
            }

            switch ($privilege) {
                case AdmissionPrivileges::ADMISSION_AFFICHER_SON_DOSSIER_ADMISSION:
                case AdmissionPrivileges::ADMISSION_SUPPRIMER_SON_DOSSIER_ADMISSION:
                case AdmissionPrivileges::ADMISSION_MODIFIER_SON_DOSSIER_ADMISSION:
                case AdmissionPrivileges::ADMISSION_TELEVERSER_SON_DOCUMENT:
                case AdmissionPrivileges::ADMISSION_SUPPRIMER_SON_DOCUMENT:
                case AdmissionPrivileges::ADMISSION_TELECHARGER_SON_DOCUMENT:
                case AdmissionPrivileges::ADMISSION_NOTIFIER_GESTIONNAIRES:
                case AdmissionPrivileges::ADMISSION_NOTIFIER_COMMENTAIRES_AJOUTES:
                case AdmissionPrivileges::ADMISSION_VERIFIER:
                case AdmissionPrivileges::ADMISSION_NOTIFIER_DOSSIER_COMPLET:
                case AdmissionPrivileges::ADMISSION_NOTIFIER_DOSSIER_INCOMPLET:
                    $this->assertAppartenanceAdmission($this->admission);
            }

            switch ($privilege) {
                case AdmissionPrivileges::ADMISSION_GENERER_RECAPITULATIF:
                    $this->assertCanGenererRecapitulatif($this->admission->getAdmissionValidations());
            }
        } catch (FailedAssertionException $e) {
            if ($e->getMessage()) {
                $this->getServiceMessageCollector()->addMessage($e->getMessage(), __CLASS__);
            }
            return false;
        }

        return true;
    }

    private function assertPeutInitialiserAdmission()
    {
        $role = $this->userContextService->getSelectedIdentityRole();
        if (!$role) {
            return;
        }

        $routeMatch = $this->getRouteMatch();
        $id = $routeMatch->getParam('individu');
        //Si l'individu connecté a le rôle user, il ne peut qu'initialiser son dossier d'admission
        if ($role->getRoleId() == Role::ROLE_ID_USER) {
            $individu = $this->userContextService->getIdentityIndividu();
            $this->assertTrue(
                (int)$id === $individu->getId(),
                "Le dossier d'admission n'appartient pas à l'individu " . $individu
            );
        }else{ // à faire évoluer lorsque il y aura les nouveaux rôles pour le directeur de thèse
//            throw new FailedAssertionException("Vous n'avez pas les droits pour initialiser un dossier d'admission");
        }
    }

    private function assertAppartenanceAdmission(Admission $admission)
    {
        $role = $this->userContextService->getSelectedIdentityRole();
        if (!$role) {
            return;
        }

        $isNotDirecteurOfAdmission = false;
        $isNotEtudiantOfAdmission = false;
        $isNotCoDirecteurOfAdmission = false;

        //Si le rôle connecté est Authentifié
        if ($role->getRoleId() == Role::ROLE_ID_USER) {
            $individu = $this->userContextService->getIdentityIndividu();
            //Si l'étudiant attaché au dossier est bien celui de l'individu connecté
            if($admission->getIndividu()->getId() !== $individu->getId()){
                $isNotEtudiantOfAdmission = true;
            }

            $individuUtilisateur = $this->userContextService->getIdentityDb()->getIndividu();
            if($admission->getInscription()->first()){
                //Si le directeur attaché au dossier n'est pas celui de l'individu connecté
                if(!$admission->getInscription()->first()->getDirecteur() || $admission->getInscription()->first()->getDirecteur() && $individuUtilisateur->getId() !== $admission->getInscription()->first()->getDirecteur()->getId()){
                    $isNotDirecteurOfAdmission = true;
                }

                //Si le co-directeur attaché au dossier n'est pas celui de l'individu connecté
                if (!$admission->getInscription()->first()->getCoDirecteur() || $admission->getInscription()->first()->getCoDirecteur() && $individuUtilisateur->getId() !== $admission->getInscription()->first()->getCoDirecteur()->getId()) {
                    $isNotCoDirecteurOfAdmission = true;
                }
            }

            if($isNotEtudiantOfAdmission && $isNotDirecteurOfAdmission && $isNotCoDirecteurOfAdmission){
                throw new FailedAssertionException("Vous ne pouvez pas à accéder à cette page");
            }
        } elseif ($roleEcoleDoctorale = $this->userContextService->getSelectedRoleEcoleDoctorale()) {
            $message = "Le dossier d'admission n'est pas rattachée à l'ED " . $roleEcoleDoctorale->getStructure()->getCode();
            if ($admission->getInscription()->first() && $admission->getInscription()->first()->getEcoleDoctorale()) {
                $this->assertTrue(
                    $admission->getInscription()->first()->getEcoleDoctorale()->getStructure()->getId() === $roleEcoleDoctorale->getStructure()->getId(),
                    $message
                );
            } else {
                throw new FailedAssertionException($message);
            }
        } elseif ($roleUniteRech = $this->userContextService->getSelectedRoleUniteRecherche()) {
            $message = "Le dossier d'admission n'est pas rattaché à l'UR " . $roleUniteRech->getStructure()->getCode();
            if ($admission->getInscription()->first() && $admission->getInscription()->first()->getUniteRecherche()) {
                $this->assertTrue(
                    $admission->getInscription()->first()->getUniteRecherche()->getStructure()->getId() === $roleUniteRech->getStructure()->getId(),
                    $message
                );
            } else {
                throw new FailedAssertionException($message);
            }
            // Si les informations d'inscription n'ont pas encore été saisies, ou
            // que les informations ont été saisies mais que le directeur n'a pas encore été renseigné
            // on laisse le droit à un n'importe quel directeur (qui à ce rôle pour l'instant) de n'importe quel établissement
            // de pouvoir rentrer les informations du dossier à la place de l'étudiant
        } elseif ($this->userContextService->getSelectedRoleDirecteurThese()) {
            $individuUtilisateur = $this->userContextService->getIdentityDb()->getIndividu();
            $message = "Le dossier d'admission n'est pas dirigé par " . $individuUtilisateur;
            if ($admission->getInscription()->first() && $admission->getInscription()->first()->getDirecteur()) {
                $this->assertTrue(
                    $admission->getInscription()->first()->getDirecteur()->getId() === $individuUtilisateur->getId(),
                    $message
                );
            } else if (empty($admission->getInscription()->first()) || ($admission->getInscription()->first() && empty($admission->getInscription()->first()->getDirecteur()))) {
                return true;
            } else {
                throw new FailedAssertionException($message);
            }
        } elseif ($this->userContextService->getSelectedRoleCodirecteurThese()) {
            $individuUtilisateur = $this->userContextService->getIdentityDb()->getIndividu();
            $message = "Le dossier d'admission n'est pas codirigé par " . $individuUtilisateur;
            if ($admission->getInscription()->first() && $admission->getInscription()->first()->getCoDirecteur()) {
                $this->assertTrue(
                    $admission->getInscription()->first()->getCoDirecteur()->getId() === $individuUtilisateur->getId(),
                    $message
                );
            } else {
                throw new FailedAssertionException($message);
            }
        }
    }

    protected function assertEtatAdmission(Admission $admission)
    {
        $this->assertTrue(
            in_array($admission->getEtat()->getCode(), [Admission::ETAT_EN_COURS_SAISIE]),
            "Le dossier d'admission doit être en cours"
        );
    }

    protected function assertCanNotifierCommentairesAjoutes(Collection $admissionValidations)
    {
        foreach ($admissionValidations as $admissionValidation) {
            $this->assertTrue(
                TypeValidation::CODE_VALIDATION_GESTIONNAIRE !== $admissionValidation->getTypeValidation()->getCode(),
                "L'envoi de commentaires n'est possible que lorsque la validation des gestionnaires
                                 n'a pas encore été effectuée"
            );
        }
    }

    protected function assertCanGestionnaireGererAdmission(Collection $admissionValidations)
    {
        foreach ($admissionValidations as $admissionValidation) {
            $this->assertTrue(
                TypeValidation::CODE_VALIDATION_GESTIONNAIRE !== $admissionValidation->getTypeValidation()->getCode(),
                "Le gestionnaire peut gérer le dossier (ajouter des commentaires, notifier l'étudiant) seulement si leur validation
                                 n'a pas encore été effectuée"
            );
        }
    }

    protected function assertCanGenererRecapitulatif(Collection $admissionValidations)
    {
        $canGenerate = false;
        foreach ($admissionValidations as $admissionValidation) {
            $canGenerate = TypeValidation::CODE_VALIDATION_GESTIONNAIRE === $admissionValidation->getTypeValidation()->getCode() ? true : $canGenerate;
        }
        $this->assertTrue(
            $canGenerate,
            "L'envoi de commentaires n'est possible que lorsque la validation des gestionnaires
                                 n'a pas encore été effectuée"
        );
    }

    private function assertModificationPossible(Admission $admission)
    {
        if ($admission->getAdmissionValidations()->count()) {
            // modif impossible si une validation existe
            throw new FailedAssertionException("La modification n'est plus possible car le dossier d'admission a été validé.");
        }
    }

    private function getRequestedAdmission(): ?Admission
    {
        $admission = null;
        if (($routeMatch = $this->getRouteMatch()) && $id = $routeMatch->getParam('individu')) {
            $admission = $this->admissionService->getRepository()->findOneByIndividu($id);
        }

        if (empty($admission)) {
            if (($routeMatch = $this->getRouteMatch()) && $id = $routeMatch->getParam('admission')) {
                $admission = $this->admissionService->getRepository()->findOneById($id);
            }
        }

        return $admission;
    }

    protected function getRouteMatch(): ?RouteMatch
    {
        /** @var \Application\RouteMatch $rm */
        $rm = $this->getMvcEvent()->getRouteMatch();
        return $rm;
    }
}