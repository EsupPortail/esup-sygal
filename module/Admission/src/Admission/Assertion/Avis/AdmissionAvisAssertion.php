<?php

namespace Admission\Assertion\Avis;

use Admission\Entity\Db\Admission;
use Application\Assertion\Exception\FailedAssertionException;
use Application\RouteMatch;
use Doctrine\ORM\NoResultException;
use InvalidArgumentException;
use Laminas\Permissions\Acl\Resource\ResourceInterface;
use Admission\Assertion\AdmissionOperationAbstractAssertion;
use Admission\Entity\Db\AdmissionAvis;
use Admission\Provider\Privilege\AdmissionPrivileges;
use Admission\Service\Avis\AdmissionAvisServiceAwareTrait;
use UnicaenApp\Exception\RuntimeException;
use UnicaenAvis\Service\AvisServiceAwareTrait;

class AdmissionAvisAssertion extends AdmissionOperationAbstractAssertion
{
    use AdmissionAvisServiceAwareTrait;
    use AvisServiceAwareTrait;

    private AdmissionAvis $admissionAvis;

    /**
     * @param string $controller
     * @param string $action
     * @param string $privilege
     *
     * @return boolean
     */
    protected function assertController($controller, $action = null, $privilege = null): bool
    {
        if (! parent::assertController($controller, $action, $privilege)) {
            return false;
        }

        if (!$this->initForControllerAction($action)) {
            return false;
        }

        $admission = $this->getRequestedAdmissionForAvis();
        
        try {

            switch ($action) {
                case 'aviser':
                case 'modifier':
                case 'supprimer':
                    $this->assertEtatAdmission($admission);
                    $this->assertAppartenanceAdmission($admission);
            }

            switch ($action) {
                case 'aviser':
                    $nextOperation = $this->findNextExpectedOperation($this->admissionAvis->getAdmission());
                    $this->assertOperationsMatch($this->admissionAvis, $nextOperation);
                    $this->assertOperationIsAllowed($nextOperation);
                    $this->assertPrecedingOperationValueCompatible($this->admissionAvis);
                    break;
            }

            switch ($action) {
                case 'modifier':
                    $this->assertFollowingOperationCompatible($this->admissionAvis);
                    $this->assertOperationIsAllowed($this->admissionAvis);
                    return true;
            }

            switch ($action) {
                case 'modifier':
                case 'desaviser':
                    $lastCompletedOperation = $this->findLastCompletedOperation($this->admissionAvis->getAdmission());
                    $this->assertOperationsMatch($this->admissionAvis, $lastCompletedOperation);
                    $this->assertOperationIsAllowed($this->admissionAvis);
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
     * @param AdmissionAvis $entity
     * @param string $privilege
     *
     * @return boolean
     */
    protected function assertEntity(ResourceInterface $entity, $privilege = null): bool
    {
        if (! parent::assertEntity($entity, $privilege)) {
            return false;
        }

        $admissionAvis = $entity;
        $admission = $admissionAvis->getAdmission();

        try {

            switch ($privilege) {
                case AdmissionPrivileges::ADMISSION_AJOUTER_AVIS_TOUT:
                case AdmissionPrivileges::ADMISSION_AJOUTER_AVIS_SIEN:
                case AdmissionPrivileges::ADMISSION_MODIFIER_AVIS_TOUT:
                case AdmissionPrivileges::ADMISSION_MODIFIER_AVIS_SIEN:
                case AdmissionPrivileges::ADMISSION_SUPPRIMER_AVIS_TOUT:
                case AdmissionPrivileges::ADMISSION_SUPPRIMER_AVIS_SIEN:
                    $this->assertEtatAdmission($admission);
                    $this->assertAppartenanceAdmission($admission);
                    break;
            }

            switch ($privilege) {
                case AdmissionPrivileges::ADMISSION_AJOUTER_AVIS_TOUT:
                case AdmissionPrivileges::ADMISSION_AJOUTER_AVIS_SIEN:
                case AdmissionPrivileges::ADMISSION_MODIFIER_AVIS_TOUT:
                case AdmissionPrivileges::ADMISSION_MODIFIER_AVIS_SIEN:
                case AdmissionPrivileges::ADMISSION_SUPPRIMER_AVIS_TOUT:
                case AdmissionPrivileges::ADMISSION_SUPPRIMER_AVIS_SIEN:
//                    $this->assertAucuneValidation($admission);
//                    break;
            }

            switch ($privilege) {
                case AdmissionPrivileges::ADMISSION_AJOUTER_AVIS_TOUT:
                case AdmissionPrivileges::ADMISSION_AJOUTER_AVIS_SIEN:
                    $nextOperation = $this->findNextExpectedOperation($admissionAvis->getAdmission());
                    $this->assertOperationsMatch($admissionAvis, $nextOperation);
                    $this->assertOperationIsAllowed($nextOperation);
                    $this->assertPrecedingOperationValueCompatible($admissionAvis);
                    // IMPORTANT : pour une création, pas de vérification portant sur l'opération suivante.
                    break;
            }

            switch ($privilege) {
                case AdmissionPrivileges::ADMISSION_MODIFIER_AVIS_TOUT:
                case AdmissionPrivileges::ADMISSION_MODIFIER_AVIS_SIEN:
                    $this->assertOperationIsAllowed($admissionAvis);
                    $this->assertFollowingOperationCompatible($admissionAvis);
                    // IMPORTANT : pour une modification, vérification portant sur l'opération suivante.
                    break;
            }

            switch ($privilege) {
                case AdmissionPrivileges::ADMISSION_MODIFIER_AVIS_TOUT:
                case AdmissionPrivileges::ADMISSION_MODIFIER_AVIS_SIEN:
                case AdmissionPrivileges::ADMISSION_SUPPRIMER_AVIS_TOUT:
                case AdmissionPrivileges::ADMISSION_SUPPRIMER_AVIS_SIEN:
                    $lastCompletedOperation = $this->findLastCompletedOperation($admissionAvis->getAdmission());
                    $this->assertOperationsMatch($admissionAvis, $lastCompletedOperation);
                    $this->assertOperationIsAllowed($admissionAvis);
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

    private function initForControllerAction(string $action): bool
    {
        switch ($action) {
            case 'aviser':
                $admission = $this->admissionService->getRepository()->findOneById($this->getRouteMatch()->getParam('admission'));
                if ($admission === null) {
                    return false;
                }
                $avisType = $this->avisService->findOneAvisTypeById($this->getRouteMatch()->getParam('typeAvis'));
                $this->admissionAvis = new AdmissionAvis($admission, $avisType);
                break;

            case 'modifier':
            case 'desaviser':
                $id = $this->getRouteMatch()->getParam('admissionAvis');
                try {
                    $this->admissionAvis = $this->admissionAvisService->getRepository()->findAdmissionAvisById($id);
                } catch (NoResultException $e) {
                    return false;
                }
                break;

            default:
                throw new InvalidArgumentException("Action inattendue : " . $action);
        }

        return true;
    }

    private function getRequestedAdmissionForAvis(): ?Admission
    {
        $admission = null;
        if (($routeMatch = $this->getRouteMatch()) && $id = $routeMatch->getParam('admissionAvis') ) {
            try {
                $admissionAvis = $this->admissionAvisService->getRepository()->findAdmissionAvisById($id);
                $admission = $admissionAvis->getAdmission();
            } catch (NoResultException $e) {
                throw new RuntimeException("Aucun avis trouvé avec cet id : $id", 0, $e);
            }
        }

        if (($routeMatch = $this->getRouteMatch()) && $id = $routeMatch->getParam('admission') ) {
            try {
                $admission = $this->admissionService->getRepository()->findOneById($id);
            } catch (NoResultException $e) {
                throw new RuntimeException("Aucun avis trouvé avec cet id : $id", 0, $e);
            }
        }

        return $admission;
    }
}