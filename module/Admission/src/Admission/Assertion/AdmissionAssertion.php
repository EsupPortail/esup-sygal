<?php

namespace Admission\Assertion;

use Admission\Entity\Db\Admission;
use Admission\Entity\Db\TypeValidation;
use Admission\Provider\Privilege\AdmissionPrivileges;
use Admission\Service\Admission\AdmissionServiceAwareTrait;
use Application\Assertion\Exception\FailedAssertionException;
use Application\Assertion\ThrowsFailedAssertionExceptionTrait;
use Application\Entity\Db\Role;
use Application\Service\UserContextServiceAwareInterface;
use Application\Service\UserContextServiceAwareTrait;
use Doctrine\Common\Collections\Collection;
use Individu\Service\IndividuServiceAwareTrait;
use Laminas\Permissions\Acl\Resource\ResourceInterface;
use UnicaenApp\Service\MessageCollectorAwareInterface;
use UnicaenApp\Service\MessageCollectorAwareTrait;

class AdmissionAssertion extends AdmissionAbstractAssertion implements UserContextServiceAwareInterface, MessageCollectorAwareInterface
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
        $codeNatureFichier = $this->getCodeNatureFichier();

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
                    if ($this->admission !== null) {
                        $this->assertCanGererDocument($this->admission, $codeNatureFichier);
                    }
                    break;
            }

            switch ($action) {
                case 'notifier-gestionnaire':
                    if ($this->admission !== null) {
                        $this->assertEtatAdmission($this->admission);
                    }
                    break;
            }

            if ($action == 'generer-recapitulatif') {
                if ($this->admission !== null) {
                    $this->assertCanGenererAndAccederRecapitulatif($this->admission->getAdmissionValidations());
                }
            }

            switch ($action) {
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
                case AdmissionPrivileges::ADMISSION_VERIFIER:
                case AdmissionPrivileges::ADMISSION_NOTIFIER_DOSSIER_INCOMPLET:
                    $this->assertCanGestionnaireGererAdmission($this->admission->getAdmissionValidations());
            }

            if ($privilege == AdmissionPrivileges::ADMISSION_NOTIFIER_DOSSIER_INCOMPLET) {
                $this->assertCanNotifierDossierIncomplet($this->admission->getAdmissionValidations());
            }

            switch ($privilege) {
                case AdmissionPrivileges::ADMISSION_AFFICHER_SON_DOSSIER_ADMISSION:
                case AdmissionPrivileges::ADMISSION_AFFICHER_SON_DOSSIER_ADMISSION_DANS_LISTE:
                case AdmissionPrivileges::ADMISSION_SUPPRIMER_SON_DOSSIER_ADMISSION:
                case AdmissionPrivileges::ADMISSION_MODIFIER_SON_DOSSIER_ADMISSION:
                case AdmissionPrivileges::ADMISSION_TELEVERSER_SON_DOCUMENT:
                case AdmissionPrivileges::ADMISSION_SUPPRIMER_SON_DOCUMENT:
                case AdmissionPrivileges::ADMISSION_TELECHARGER_SON_DOCUMENT:
                case AdmissionPrivileges::ADMISSION_VERIFIER:
                case AdmissionPrivileges::ADMISSION_NOTIFIER_DOSSIER_INCOMPLET:
                    $this->assertAppartenanceAdmission($this->admission);
            }

            switch ($privilege) {
                case AdmissionPrivileges::ADMISSION_GENERER_RECAPITULATIF:
                case AdmissionPrivileges::ADMISSION_ACCEDER_RECAPITULATIF_DOSSIER:
                $this->assertCanGenererAndAccederRecapitulatif($this->admission->getAdmissionValidations());
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

    protected function assertEtatAdmission(Admission $admission): void
    {
        $this->assertTrue(
            $admission->getEtat()->getCode() == Admission::ETAT_EN_COURS_SAISIE,
            "Le dossier d'admission doit être en cours"
        );
    }

    protected function assertCanGererDocument(Admission $admission, string $codeNatureFichier): void
    {
        //Le récapitulatif du dossier d'admission signé est géré seulement par la/le gestionnaire du dossier
        if($codeNatureFichier === "ADMISSION_RECAPITULATIF_DOSSIER_SIGNE"){
            if(!$this->userContextService->getSelectedRoleEcoleDoctorale()){
                throw new FailedAssertionException("Seule la/le gestionnaire du dossier peut gérer ce document");
            }
            $this->assertTrue(
                $admission->getEtat()->getCode() == (Admission::ETAT_EN_COURS_VALIDATION || Admission::ETAT_VALIDE || Admission::ETAT_REJETE),
                "Le dossier d'admission doit être en cours de validation / être validé / être rejeté"
            );
        }else{
            $this->assertTrue(
                $admission->getEtat()->getCode() == Admission::ETAT_EN_COURS_SAISIE,
                "Le dossier d'admission doit être en cours"
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

    protected function assertCanNotifierDossierIncomplet(Collection $admissionValidations): void
    {
        $attestationHonneurEffectuee = false;
        $validationGestionnairePasEffectuee = true;
        foreach ($admissionValidations as $admissionValidation) {
            if (TypeValidation::CODE_ATTESTATION_HONNEUR === $admissionValidation->getTypeValidation()->getCode()) {
                $attestationHonneurEffectuee = true;
            }

            if (TypeValidation::CODE_VALIDATION_GESTIONNAIRE === $admissionValidation->getTypeValidation()->getCode()) {
                $validationGestionnairePasEffectuee = false;
            }
        }

        $this->assertTrue(
            $attestationHonneurEffectuee && $validationGestionnairePasEffectuee,
            "La génération du récapitulatif n'est possible que lorsque la validation des gestionnaires
                     n'a pas encore été effectuée et que l'attestation sur l'honneur a été faîte de la part de l'étudiant"
        );
    }

    protected function assertCanGenererAndAccederRecapitulatif(Collection $admissionValidations): void
    {
        $canGenerate = false;
        foreach ($admissionValidations as $admissionValidation) {
            $canGenerate = TypeValidation::CODE_VALIDATION_GESTIONNAIRE === $admissionValidation->getTypeValidation()->getCode() ? true : $canGenerate;
        }
        $this->assertTrue(
            $canGenerate,
            "La génération du récapitulatif n'est possible que lorsque la validation des gestionnaires
                                 n'a pas encore été effectuée"
        );
    }

    protected function getCodeNatureFichier(): ?string
    {
        $codeNatureFichier = null;
        if (($routeMatch = $this->getRouteMatch()) && $id = $routeMatch->getParam('codeNatureFichier')) {
            $codeNatureFichier = $id;
        }

        return $codeNatureFichier;
    }
}