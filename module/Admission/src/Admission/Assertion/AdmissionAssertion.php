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
            if ($action == 'etudiant') {
                if ($this->admission == null) {
                    $this->assertPeutInitialiserAdmission();
                }
            }

            if ($action == 'index') {
                $this->assertCanAccessModuleAdmission();
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

            if ($action == 'generer-recapitulatif') {
                if ($this->admission !== null) {
                    $this->assertCanGenererRecapitulatif($this->admission->getAdmissionValidations());
                }
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
                case AdmissionPrivileges::ADMISSION_TELEVERSER_TOUT_DOCUMENT:
                case AdmissionPrivileges::ADMISSION_TELEVERSER_SON_DOCUMENT:
                case AdmissionPrivileges::ADMISSION_SUPPRIMER_TOUT_DOCUMENT:
                case AdmissionPrivileges::ADMISSION_SUPPRIMER_SON_DOCUMENT:
                    $this->assertEtatAdmission($this->admission);
            }

            switch ($privilege) {
                case AdmissionPrivileges::ADMISSION_ACCEDER_COMMENTAIRES:
                case AdmissionPrivileges::ADMISSION_NOTIFIER_COMMENTAIRES_AJOUTES:
                case AdmissionPrivileges::ADMISSION_VERIFIER:
                case AdmissionPrivileges::ADMISSION_NOTIFIER_DOSSIER_INCOMPLET:
                    $this->assertCanGestionnaireGererAdmission($this->admission->getAdmissionValidations());
            }

            switch ($privilege) {
                case AdmissionPrivileges::ADMISSION_AFFICHER_SON_DOSSIER_ADMISSION:
                case AdmissionPrivileges::ADMISSION_AFFICHER_SON_DOSSIER_ADMISSION_DANS_LISTE:
                case AdmissionPrivileges::ADMISSION_SUPPRIMER_SON_DOSSIER_ADMISSION:
                case AdmissionPrivileges::ADMISSION_MODIFIER_SON_DOSSIER_ADMISSION:
                case AdmissionPrivileges::ADMISSION_TELEVERSER_SON_DOCUMENT:
                case AdmissionPrivileges::ADMISSION_SUPPRIMER_SON_DOCUMENT:
                case AdmissionPrivileges::ADMISSION_TELECHARGER_SON_DOCUMENT:
                case AdmissionPrivileges::ADMISSION_NOTIFIER_COMMENTAIRES_AJOUTES:
                case AdmissionPrivileges::ADMISSION_VERIFIER:
                case AdmissionPrivileges::ADMISSION_NOTIFIER_DOSSIER_INCOMPLET:
                    $this->assertAppartenanceAdmission($this->admission);
            }

            if ($privilege == AdmissionPrivileges::ADMISSION_GENERER_RECAPITULATIF) {
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

    private function assertPeutInitialiserAdmission(): void
    {
        $role = $this->userContextService->getSelectedIdentityRole();
        if (!$role) {
            return;
        }

        $routeMatch = $this->getRouteMatch();
        $id = $routeMatch->getParam('individu');

        $roleDirecteurThese = $this->userContextService->getSelectedRoleDirecteurThese();
        //Si l'individu connecté a le rôle user, il ne peut qu'initialiser son dossier d'admission
        if ($role->getRoleId() === Role::ROLE_ID_USER) {
            $individu = $this->userContextService->getIdentityIndividu();
            $this->assertTrue(
                (int)$id === $individu->getId(),
                "Le dossier d'admission n'appartient pas à l'individu " . $individu
            );
        }else if($role->getRoleId() === Role::ROLE_ID_ADMISSION_DIRECTEUR_THESE || $roleDirecteurThese || $role->getCode() === Role::CODE_ADMIN_TECH){
            return;
        }else{
            throw new FailedAssertionException("Vous n'avez pas les droits pour initialiser un dossier d'admission");
        }
    }

    private function assertCanAccessModuleAdmission(): void
    {
        $role = $this->userContextService->getSelectedIdentityRole();
        if (!$role) {
            return;
        }

        $roleEcoleDoctorale = $this->userContextService->getSelectedRoleEcoleDoctorale();
        $roleUniteRecherche = $this->userContextService->getSelectedRoleUniteRecherche();
        $roleDirecteurThese = $this->userContextService->getSelectedRoleDirecteurThese();
        $roleCoDirecteurThese = $this->userContextService->getSelectedRoleCodirecteurThese();
        $roleMaisonDoctorat = $this->userContextService->getSelectedRoleBDD();

        if ($role->getRoleId() === Role::ROLE_ID_USER ||
            $role->getRoleId() === Role::ROLE_ID_ADMISSION_DIRECTEUR_THESE ||
            $role->getRoleId() === Role::ROLE_ID_ADMISSION_CODIRECTEUR_THESE ||
            $role->getRoleId() === Role::ROLE_ID_ADMISSION_CANDIDAT ||
            $role->getCode() === Role::CODE_ADMIN_TECH ||
            $roleEcoleDoctorale ||
            $roleUniteRecherche ||
            $roleDirecteurThese ||
            $roleCoDirecteurThese ||
            $roleMaisonDoctorat) {
            return;
        }else{
            throw new FailedAssertionException("Vous ne pouvez pas accéder au module admission");
        }
    }

    private function assertAppartenanceAdmission(Admission $admission)
    {
        $role = $this->userContextService->getSelectedIdentityRole();
        if (!$role) {
            return;
        }

        $individu = $this->userContextService->getIdentityIndividu();
        //Si le rôle connecté est Candidat
        if ($role->getRoleId() == Role::ROLE_ID_ADMISSION_CANDIDAT) {
            //Si l'étudiant attaché au dossier n'est pas celui de l'individu connecté
            if($admission->getIndividu()->getId() !== $individu->getId()){
                throw new FailedAssertionException("Le dossier d'admission n'appartient pas à l'individu " . $individu);
            }
        } elseif($role->getRoleId() == Role::ROLE_ID_ADMISSION_DIRECTEUR_THESE){
            $message = "Le dossier d'admission n'est pas dirigé par " . $individu;
            if ($admission->getInscription()->first() && $admission->getInscription()->first()->getDirecteur()) {
                $this->assertTrue(
                    $admission->getInscription()->first()->getDirecteur()->getId() === $individu->getId(),
                    $message
                );
            } else if (empty($admission->getInscription()->first()) || ($admission->getInscription()->first() && empty($admission->getInscription()->first()->getDirecteur()))) {
                return true;
            } else {
                throw new FailedAssertionException($message);
            }
        } elseif($role->getRoleId() == Role::ROLE_ID_ADMISSION_CODIRECTEUR_THESE){
            if($admission->getInscription()->first()){
                //Si le co-directeur attaché au dossier n'est pas celui de l'individu connecté
                if (!$admission->getInscription()->first()->getCoDirecteur() || $admission->getInscription()->first()->getCoDirecteur() && $individu->getId() !== $admission->getInscription()->first()->getCoDirecteur()->getId()) {
                    throw new FailedAssertionException("Le dossier d'admission n'est pas co-dirigé par " . $individu);
                }
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
        }else if($role->getCode() !== Role::CODE_ADMIN_TECH && $role->getCode() !== Role::CODE_BDD){
            throw new FailedAssertionException("Vous ne pouvez pas accéder au module admission");
        }
    }

    protected function assertEtatAdmission(Admission $admission): void
    {
        $this->assertTrue(
            $admission->getEtat()->getCode() == Admission::ETAT_EN_COURS_SAISIE,
            "Le dossier d'admission doit être en cours"
        );
    }

    protected function assertCanNotifierCommentairesAjoutes(Collection $admissionValidations): void
    {
        foreach ($admissionValidations as $admissionValidation) {
            $this->assertTrue(
                TypeValidation::CODE_VALIDATION_GESTIONNAIRE !== $admissionValidation->getTypeValidation()->getCode(),
                "L'envoi de commentaires n'est possible que lorsque la validation des gestionnaires
                                 n'a pas encore été effectuée"
            );
        }
    }

    protected function assertCanGestionnaireGererAdmission(Collection $admissionValidations): void
    {
        foreach ($admissionValidations as $admissionValidation) {
            $this->assertTrue(
                TypeValidation::CODE_VALIDATION_GESTIONNAIRE !== $admissionValidation->getTypeValidation()->getCode(),
                "Le gestionnaire peut gérer le dossier (ajouter des commentaires, notifier l'étudiant) seulement si leur validation
                                 n'a pas encore été effectuée"
            );
        }
    }

    protected function assertCanGenererRecapitulatif(Collection $admissionValidations): void
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
        /** @var RouteMatch $rm */
        $rm = $this->getMvcEvent()->getRouteMatch();
        return $rm;
    }
}