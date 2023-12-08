<?php

namespace Admission\Assertion;

use Admission\Entity\Db\Admission;
use Admission\Provider\Privilege\AdmissionPrivileges;
use Admission\Service\Admission\AdmissionServiceAwareTrait;
use Application\Assertion\AbstractAssertion;
use Application\Assertion\Exception\FailedAssertionException;
use Application\Assertion\ThrowsFailedAssertionExceptionTrait;
use Application\RouteMatch;
use Application\Service\UserContextServiceAwareInterface;
use Application\Service\UserContextServiceAwareTrait;
use GuzzleHttp\Psr7\ServerRequest;
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
        if (! parent::assertController($controller, $action, $privilege)) {
            return false;
        }

        $this->admission = $this->getRequestedAdmission();

        try {
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
                    if ($this->admission !== null) {
                        $this->assertAppartenanceAdmission($this->admission);
                    }
                    break;
            }

            switch ($action) {
                case 'enregistrer-document':
                case 'supprimer' :
                case 'supprimer-document':
                case 'notifier-commentaires-ajoutes':
                case 'notifier-gestionnaire':
                    if ($this->admission !== null) {
                        $this->assertEtatAdmission($this->admission);
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
        if (! parent::assertEntity($entity, $privilege)) {
            return false;
        }
        $this->admission = $entity;

        try {

            switch ($privilege) {
                case AdmissionPrivileges::ADMISSION_MODIFIER_TOUS_DOSSIERS_ADMISSION:
                case AdmissionPrivileges::ADMISSION_MODIFIER_SON_DOSSIER_ADMISSION:
                case AdmissionPrivileges::ADMISSION_SUPPRIMER_TOUS_DOSSIERS_ADMISSION:
                case AdmissionPrivileges::ADMISSION_SUPPRIMER_SON_DOSSIER_ADMISSION:
                case AdmissionPrivileges::ADMISSION_VERIFIER:
                case AdmissionPrivileges::ADMISSION_TELEVERSER_TOUT_DOCUMENT:
                case AdmissionPrivileges::ADMISSION_TELEVERSER_SON_DOCUMENT:
                case AdmissionPrivileges::ADMISSION_SUPPRIMER_TOUT_DOCUMENT:
                case AdmissionPrivileges::ADMISSION_SUPPRIMER_SON_DOCUMENT:
                case AdmissionPrivileges::ADMISSION_NOTIFIER_GESTIONNAIRES:
                case AdmissionPrivileges::ADMISSION_NOTIFIER_COMMENTAIRES_AJOUTES:
                    $this->assertEtatAdmission($this->admission);
            }

            switch ($privilege) {
                case AdmissionPrivileges::ADMISSION_AFFICHER_SON_DOSSIER_ADMISSION:
                case AdmissionPrivileges::ADMISSION_LISTER_SON_DOSSIER_ADMISSION:
                case AdmissionPrivileges::ADMISSION_SUPPRIMER_SON_DOSSIER_ADMISSION:
                case AdmissionPrivileges::ADMISSION_MODIFIER_SON_DOSSIER_ADMISSION:
                case AdmissionPrivileges::ADMISSION_TELEVERSER_SON_DOCUMENT:
                case AdmissionPrivileges::ADMISSION_SUPPRIMER_SON_DOCUMENT:
                case AdmissionPrivileges::ADMISSION_TELECHARGER_SON_DOCUMENT:
                case AdmissionPrivileges::ADMISSION_NOTIFIER_GESTIONNAIRES:
                case AdmissionPrivileges::ADMISSION_NOTIFIER_COMMENTAIRES_AJOUTES:
                case AdmissionPrivileges::ADMISSION_VERIFIER:
                    $this->assertAppartenanceAdmission($this->admission);
            }
        } catch (FailedAssertionException $e) {
            if ($e->getMessage()) {
                $this->getServiceMessageCollector()->addMessage($e->getMessage(), __CLASS__);
            }
            return false;
        }

        return true;
    }

    private function assertAppartenanceAdmission(Admission $admission)
    {
        $role = $this->userContextService->getSelectedIdentityRole();
        if (!$role) {
            return;
        }

        if ($role->isDoctorant()) {
            $individu = $this->userContextService->getIdentityIndividu();
            $this->assertTrue(
                $admission->getIndividu()->getId() === $individu->getId(),
                "Le dossier d'admission n'appartient pas à l'individu " . $individu
            );
        }elseif ($roleEcoleDoctorale = $this->userContextService->getSelectedRoleEcoleDoctorale()) {
            $message = "Le dossier d'admission n'est pas rattachée à l'ED " . $roleEcoleDoctorale->getStructure()->getCode();
            if(!empty($admission->getInscription()->first()->getEcoleDoctorale())){
                $this->assertTrue(
                    $admission->getInscription()->first()->getEcoleDoctorale()->getStructure()->getId() === $roleEcoleDoctorale->getStructure()->getId(),
                    $message
                );
            }else{
                throw new FailedAssertionException($message);
            }
        }elseif ($roleUniteRech = $this->userContextService->getSelectedRoleUniteRecherche()) {
            $message = "Le dossier d'admission n'est pas rattaché à l'UR " . $roleUniteRech->getStructure()->getCode();
            if(!empty($admission->getInscription()->first()->getUniteRecherche())){
                $this->assertTrue(
                    $admission->getInscription()->first()->getUniteRecherche()->getStructure()->getId() === $roleUniteRech->getStructure()->getId(),
                    $message
                );
            }else{
                throw new FailedAssertionException($message);
            }
        }elseif ($this->userContextService->getSelectedRoleDirecteurThese()) {
            $individuUtilisateur = $this->userContextService->getIdentityDb()->getIndividu();
            $message = "Le dossier d'admission n'est pas dirigé par " . $individuUtilisateur;
            if(!empty($admission->getInscription()->first()->getDirecteur())){
                $this->assertTrue(
                    $admission->getInscription()->first()->getDirecteur()->getId() === $individuUtilisateur,
                    $message
                );
            }else{
                throw new FailedAssertionException($message);
            }
        }elseif ($this->userContextService->getSelectedRoleCodirecteurThese()) {
            $individuUtilisateur = $this->userContextService->getIdentityDb()->getIndividu();
            $message = "Le dossier d'admission n'est pas codirigé par " . $individuUtilisateur;
            if(!empty($admission->getInscription()->first()->getCoDirecteur())){
                $this->assertTrue(
                    $admission->getInscription()->first()->getCoDirecteur()->getId() === $individuUtilisateur,
                    $message
                );
            }else{
                throw new FailedAssertionException($message);
            }
        }
    }

    protected function assertEtatAdmission(Admission $admission)
    {
        $this->assertTrue(
            in_array($admission->getEtat()->getCode(), [Admission::ETAT_EN_COURS]),
            "Le dossier d'admission doit être en cours"
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
        if (($routeMatch = $this->getRouteMatch()) && $id = $routeMatch->getParam('individu') ) {
            $admission = $this->admissionService->getRepository()->findOneByIndividu($id);
        }

        if(empty($admission)){
            if (($routeMatch = $this->getRouteMatch()) && $id = $routeMatch->getParam('admission') ) {
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