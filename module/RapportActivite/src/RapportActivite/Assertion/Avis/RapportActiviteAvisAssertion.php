<?php

namespace RapportActivite\Assertion\Avis;

use Application\Assertion\Exception\FailedAssertionException;
use Doctrine\ORM\NoResultException;
use InvalidArgumentException;
use Laminas\Permissions\Acl\Resource\ResourceInterface;
use RapportActivite\Assertion\RapportActiviteOperationAbstractAssertion;
use RapportActivite\Entity\Db\RapportActiviteAvis;
use RapportActivite\Provider\Privilege\RapportActivitePrivileges;
use RapportActivite\Service\Avis\RapportActiviteAvisServiceAwareTrait;
use UnicaenAvis\Service\AvisServiceAwareTrait;

class RapportActiviteAvisAssertion extends RapportActiviteOperationAbstractAssertion
{
    use RapportActiviteAvisServiceAwareTrait;
    use AvisServiceAwareTrait;

    private RapportActiviteAvis $rapportActiviteAvis;

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

//        switch ($action) {
//            case 'ajouter':
//                $rapportActivite = $this->rapportActiviteService->fetchRapportById($this->getRouteMatch()->getParam('rapport'));
//                if ($rapportActivite === null) {
//                    return false;
//                }
//                $avisType = $this->avisService->findOneAvisTypeById($this->getRouteMatch()->getParam('typeAvis'));
//                $rapportActiviteAvis = new RapportActiviteAvis($rapportActivite, $avisType);
//                $these = $rapportActivite->getThese();
//                break;
//
//            case 'modifier':
//            case 'supprimer':
//                $id = $this->getRouteMatch()->getParam('rapportAvis');
//                try {
//                    $rapportActiviteAvis = $this->rapportActiviteAvisService->findRapportAvisById($id);
//                    $these = $rapportActiviteAvis->getRapportActivite()->getThese();
//                } catch (NoResultException $e) {
//                    return false;
//                }
//                break;
//
//            default:
//                throw new InvalidArgumentException("Action inattendue : " . $action);
//        }
        if (!$this->initForControllerAction($action)) {
            return false;
        }

        $these = $this->rapportActiviteAvis->getRapportActivite()->getThese();

        try {

            switch ($action) {
                case 'ajouter':
                case 'modifier':
                case 'supprimer':
                    $this->assertEtatThese($these);
                    $this->assertAppartenanceThese($these);
            }

            switch ($action) {
                case 'ajouter':
                    $nextOperation = $this->findNextExpectedOperation($this->rapportActiviteAvis->getRapportActivite());
                    $this->assertOperationsMatch($this->rapportActiviteAvis, $nextOperation);
                    $this->assertOperationIsAllowed($nextOperation);
                    $this->assertPrecedingOperationValueCompatible($this->rapportActiviteAvis);
                    break;
            }

            switch ($action) {
                case 'modifier':
                    $this->assertFollowingOperationCompatible($this->rapportActiviteAvis);
                    $this->assertOperationIsAllowed($this->rapportActiviteAvis);
                    return true;
            }

            switch ($action) {
                case 'modifier':
                case 'supprimer':
                    $lastCompletedOperation = $this->findLastCompletedOperation($this->rapportActiviteAvis->getRapportActivite());
                    $this->assertOperationsMatch($this->rapportActiviteAvis, $lastCompletedOperation);
                    $this->assertOperationIsAllowed($this->rapportActiviteAvis);
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
            case 'ajouter':
                $rapportActivite = $this->rapportActiviteService->fetchRapportById($this->getRouteMatch()->getParam('rapport'));
                if ($rapportActivite === null) {
                    return false;
                }
                $avisType = $this->avisService->findOneAvisTypeById($this->getRouteMatch()->getParam('typeAvis'));
                $this->rapportActiviteAvis = new RapportActiviteAvis($rapportActivite, $avisType);
                break;

            case 'modifier':
            case 'supprimer':
                $id = $this->getRouteMatch()->getParam('rapportAvis');
                try {
                    $this->rapportActiviteAvis = $this->rapportActiviteAvisService->findRapportAvisById($id);
                } catch (NoResultException $e) {
                    return false;
                }
                break;

            default:
                throw new InvalidArgumentException("Action inattendue : " . $action);
        }

        return true;
    }

    /**
     * @param RapportActiviteAvis $entity
     * @param string $privilege
     *
     * @return boolean
     */
    protected function assertEntity(ResourceInterface $entity, $privilege = null): bool
    {
        if (! parent::assertEntity($entity, $privilege)) {
            return false;
        }

        $rapportActiviteAvis = $entity;
        $rapport = $rapportActiviteAvis->getRapportActivite();

        try {

            switch ($privilege) {
                case RapportActivitePrivileges::RAPPORT_ACTIVITE_AJOUTER_AVIS_TOUT:
                case RapportActivitePrivileges::RAPPORT_ACTIVITE_AJOUTER_AVIS_SIEN:
                case RapportActivitePrivileges::RAPPORT_ACTIVITE_MODIFIER_AVIS_TOUT:
                case RapportActivitePrivileges::RAPPORT_ACTIVITE_MODIFIER_AVIS_SIEN:
                case RapportActivitePrivileges::RAPPORT_ACTIVITE_SUPPRIMER_AVIS_TOUT:
                case RapportActivitePrivileges::RAPPORT_ACTIVITE_SUPPRIMER_AVIS_SIEN:
                    $this->assertEtatThese($rapport->getThese());
                    $this->assertAppartenanceThese($rapport->getThese());
                    break;
            }

            switch ($privilege) {
                case RapportActivitePrivileges::RAPPORT_ACTIVITE_AJOUTER_AVIS_TOUT:
                case RapportActivitePrivileges::RAPPORT_ACTIVITE_AJOUTER_AVIS_SIEN:
                case RapportActivitePrivileges::RAPPORT_ACTIVITE_MODIFIER_AVIS_TOUT:
                case RapportActivitePrivileges::RAPPORT_ACTIVITE_MODIFIER_AVIS_SIEN:
                case RapportActivitePrivileges::RAPPORT_ACTIVITE_SUPPRIMER_AVIS_TOUT:
                case RapportActivitePrivileges::RAPPORT_ACTIVITE_SUPPRIMER_AVIS_SIEN:
//                    $this->assertAucuneValidation($rapport);
//                    break;
            }

            switch ($privilege) {
                case RapportActivitePrivileges::RAPPORT_ACTIVITE_AJOUTER_AVIS_TOUT:
                case RapportActivitePrivileges::RAPPORT_ACTIVITE_AJOUTER_AVIS_SIEN:
                    $nextOperation = $this->findNextExpectedOperation($rapportActiviteAvis->getRapportActivite());
                    $this->assertOperationsMatch($rapportActiviteAvis, $nextOperation);
                    $this->assertOperationIsAllowed($nextOperation);
                    $this->assertPrecedingOperationValueCompatible($rapportActiviteAvis);
                    // IMPORTANT : pour une création, pas de vérification portant sur l'opération suivante.
                    break;
            }

            switch ($privilege) {
                case RapportActivitePrivileges::RAPPORT_ACTIVITE_MODIFIER_AVIS_TOUT:
                case RapportActivitePrivileges::RAPPORT_ACTIVITE_MODIFIER_AVIS_SIEN:
                    $this->assertOperationIsAllowed($rapportActiviteAvis);
                    $this->assertFollowingOperationCompatible($rapportActiviteAvis);
                    // IMPORTANT : pour une modification, vérification portant sur l'opération suivante.
                    break;
            }

            switch ($privilege) {
                case RapportActivitePrivileges::RAPPORT_ACTIVITE_MODIFIER_AVIS_TOUT:
                case RapportActivitePrivileges::RAPPORT_ACTIVITE_MODIFIER_AVIS_SIEN:
                case RapportActivitePrivileges::RAPPORT_ACTIVITE_SUPPRIMER_AVIS_TOUT:
                case RapportActivitePrivileges::RAPPORT_ACTIVITE_SUPPRIMER_AVIS_SIEN:
                    $lastCompletedOperation = $this->findLastCompletedOperation($rapportActiviteAvis->getRapportActivite());
                    $this->assertOperationsMatch($rapportActiviteAvis, $lastCompletedOperation);
                    $this->assertOperationIsAllowed($rapportActiviteAvis);
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
}