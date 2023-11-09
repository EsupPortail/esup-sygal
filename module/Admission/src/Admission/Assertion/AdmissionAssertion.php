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
use Laminas\Permissions\Acl\Resource\ResourceInterface;
use RapportActivite\Entity\Db\RapportActivite;
use These\Entity\Db\These;
use UnicaenApp\Service\MessageCollectorAwareInterface;
use UnicaenApp\Service\MessageCollectorAwareTrait;

class AdmissionAssertion extends AbstractAssertion implements UserContextServiceAwareInterface, MessageCollectorAwareInterface
{
    private ?Admission $admission = null;
    private ?RapportActivite $rapportActivite = null;
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

//        $these = $this->getRequestedThese();
//        if ($these === null) {
//            return true;
//        }

//        if (!$this->initForControllerAction($action)) {
//            return false;
//        }

//        try {
//            switch ($action) {
//                case 'lister':
//                    // je ne comprends pas pourquoi : on peut arriver ici sans thèse spécifiée dans l'URL !
//                    if ($this->these !== null) {
//                        $this->assertAppartenanceThese($this->these);
//                    }
//                    break;
//
//                case 'ajouter':
//                case 'financement':
//                    if ($this->admission !== null) {
//                        $this->assertAppartenanceAdmission($this->admission);
//                    }
//                break;
//                case 'modifier':
//                case 'supprimer':
//                case 'consulter':
//                case 'telecharger':
//                case 'generer':
//                    $this->assertAppartenanceThese($this->rapportActivite->getThese());
//                    break;
//            }
//        } catch (FailedAssertionException $e) {
//            if ($e->getMessage()) {
//                $this->getServiceMessageCollector()->addMessage($e->getMessage(), __CLASS__);
//            }
//            return false;
//        }

        return true;
    }

    private function initForControllerAction(string $action): bool
    {
        switch ($action) {
            case 'lister':
//                $this->admission = $this->getRequestedThese();
//                break;
            case 'financement':
                $this->admission = $this->getRequestedAdmission();
                break;
//            case 'consulter':
//            case 'modifier':
//            case 'supprimer':
//            case 'ajouter':
//                $this->rapportActivite = $this->rapportActiviteService->newRapportActivite($this->getRequestedThese());
//                break;

            default:
                throw new \InvalidArgumentException(__METHOD__ . " : Action inattendue : " . $action);
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

//            switch ($privilege) {
//                case RapportActivitePrivileges::RAPPORT_ACTIVITE_AJOUTER_TOUT:
//                case RapportActivitePrivileges::RAPPORT_ACTIVITE_AJOUTER_SIEN:
//                case RapportActivitePrivileges::RAPPORT_ACTIVITE_MODIFIER_TOUT:
//                case RapportActivitePrivileges::RAPPORT_ACTIVITE_MODIFIER_SIEN:
//                case RapportActivitePrivileges::RAPPORT_ACTIVITE_TELEVERSER_TOUT:
//                case RapportActivitePrivileges::RAPPORT_ACTIVITE_TELEVERSER_SIEN:
//                case RapportActivitePrivileges::RAPPORT_ACTIVITE_SUPPRIMER_TOUT:
//                case RapportActivitePrivileges::RAPPORT_ACTIVITE_SUPPRIMER_SIEN:
//                    $this->assertEtatThese($this->rapportActivite->getThese());
//            }

            switch ($privilege) {
                case AdmissionPrivileges::ADMISSION_AFFICHER_SON_DOSSIER_ADMISSION:
                case AdmissionPrivileges::ADMISSION_LISTER_SON_DOSSIER_ADMISSION:
                case AdmissionPrivileges::ADMISSION_SUPPRIMER_SON_DOSSIER_ADMISSION:
                case AdmissionPrivileges::ADMISSION_MODIFIER_SON_DOSSIER_ADMISSION:
                    $this->assertAppartenanceAdmission($this->admission->getIndividu());
            }
        } catch (FailedAssertionException $e) {
            if ($e->getMessage()) {
                $this->getServiceMessageCollector()->addMessage($e->getMessage(), __CLASS__);
            }
            return false;
        }

        return true;
    }

//    private function assertEtatThese(These $these)
//    {
//        $this->assertTrue(
//            in_array($these->getEtatThese(), [These::ETAT_EN_COURS, These::ETAT_SOUTENUE]),
//            "La thèse doit être en cours ou soutenue"
//        );
//    }

    private function assertAppartenanceAdmission(Admission $admission)
    {
        $role = $this->userContextService->getSelectedIdentityRole();
        if (!$role) {
            return;
        }

        if ($role->isDoctorant()) {
            $doctorant = $this->userContextService->getIdentityDoctorant();
            $this->assertTrue(
                $admission->getIndividu()->getId() === $doctorant->getId(),
                "Le dossier d'admission n'appartient pas à l'individu " . $doctorant
            );
        }
    }

    private function getRequestedThese(): ?These
    {
        if ($rapportActivite = $this->getRequestedRapport()) {
            return $rapportActivite->getThese();
        } elseif ($routeMatch = $this->getRouteMatch()) {
            return $routeMatch->getThese();
        } else {
            return null;
        }
    }

    private function getRequestedAdmission(): ?Admission
    {
        $admission = null;
        if (($routeMatch = $this->getRouteMatch()) && $id = $routeMatch->getParam('individu')) {
            $admission = $this->admissionService->getRepository()->findOneByIndividu($id);
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