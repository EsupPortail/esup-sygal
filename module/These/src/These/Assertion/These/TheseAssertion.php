<?php

namespace These\Assertion\These;

use Application\Acl\WfEtapeResource;
use Application\Assertion\AbstractAssertion;
use Application\Assertion\Exception\FailedAssertionException;
use Application\Entity\Db\Role;
use Application\Entity\Db\WfEtape;
use Application\Provider\Privilege\ValidationPrivileges;
use Application\RouteMatch;
use Application\Service\UserContextService;
use Application\Service\UserContextServiceAwareTrait;
use Application\Service\Workflow\WorkflowServiceAwareInterface;
use Application\Service\Workflow\WorkflowServiceAwareTrait;
use Doctorant\Entity\Db\Doctorant;
use Laminas\Permissions\Acl\Resource\ResourceInterface;
use These\Controller\TheseController;
use These\Entity\Db\These;
use These\Provider\Privilege\ThesePrivileges;
use UnicaenApp\Exception\RuntimeException;
use UnicaenApp\Service\MessageCollectorAwareTrait;

/**
 * Class TheseAssertion
 *
 * @package Application\Assertion
 * @method UserContextService getServiceUserContext()
 */
class TheseAssertion extends AbstractAssertion implements WorkflowServiceAwareInterface
{
    use UserContextServiceAwareTrait;
    use WorkflowServiceAwareTrait;
    use MessageCollectorAwareTrait;

    const THESE_CONTROLLER = TheseController::class;
    const DOCTORANT_CONTROLLER = 'Application\Controller\Doctorant';

    private TheseEntityAssertion $theseEntityAssertion;
    private ?Doctorant $doctorant = null;
    private ?These $these = null;
    protected ?string $controller = null;
    protected ?string $action = null;

    /**
     * @param \These\Assertion\These\TheseEntityAssertion $theseEntityAssertion
     */
    public function setTheseEntityAssertion(TheseEntityAssertion $theseEntityAssertion)
    {
        $this->theseEntityAssertion = $theseEntityAssertion;
    }

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
        if ($this->getRouteMatch() === null) {
            return false;
        }

        $this->these = $this->getRouteMatch()->getThese();
        $this->doctorant = $this->getRouteMatch()->getDoctorant();

        $etape = $page['etape'] ?? null;
        if (!$etape) {
            return true;
        }

        if ($this->these && ! $this->getServiceAuthorize()->isAllowed(new WfEtapeResource($etape, $this->these))) {
            return false;
        }

        return true;
    }

    protected function assertEntity(ResourceInterface $these, $privilege = null): bool
    {
        if (! parent::assertEntity($these, $privilege)) {
            return false;
        }

        $this->theseEntityAssertion->setContext(['these' => $these]);
        try {
            $this->theseEntityAssertion->assert($privilege);
        } catch (FailedAssertionException $e) {
            if ($e->getMessage()) {
                $this->getServiceMessageCollector()->addMessage($e->getMessage(), __CLASS__);
            }
            return false;
        }

        /** @var These $these */

        $role = $this->userContextService->getSelectedIdentityRole();
        $individu = $this->userContextService->getIdentityIndividu();
        switch (true) {
            case $privilege === ThesePrivileges::THESE_SAISIE_DESCRIPTION_VERSION_INITIALE:
                return ! $this->isAllowed(new WfEtapeResource(WfEtape::CODE_DEPOT_VERSION_ORIGINALE_CORRIGEE, $these));
                break;
            case $privilege === ValidationPrivileges::THESE_VALIDATION_RDV_BU:
                return $this->isAllowed(new WfEtapeResource(WfEtape::CODE_RDV_BU_VALIDATION_BU, $these));
                break;
            case $privilege === ThesePrivileges::THESE_CONSULTATION_SES_THESES:
            case $privilege === ThesePrivileges::THESE_MODIFICATION_SES_THESES:
                // doctorant
                if ($role->getCode() === Role::CODE_DOCTORANT) return $these->getDoctorant()->getIndividu() === $individu;
                // directeur
                if ($role->getCode() === Role::CODE_DIRECTEUR_THESE) {
                    $directeurs = $these->getActeursByRoleCode(Role::CODE_DIRECTEUR_THESE);
                    $individus = [];
                    foreach ($directeurs as $directeur) $individus[] = $directeur->getIndividu();
                    return (array_search($individu, $individus) !== false);
                }
                if ($role->getCode() === Role::CODE_CODIRECTEUR_THESE) {
                    $directeurs = $these->getActeursByRoleCode(Role::CODE_CODIRECTEUR_THESE);
                    $individus = [];
                    foreach ($directeurs as $directeur) $individus[] = $directeur->getIndividu();
                    return (array_search($individu, $individus) !== false);
                }
                // structure
                // todo : remplacer par $role->isStructureDependant() && $role->getTypeStructureDependant()->isEcoleDoctorale() :
                if (in_array($role->getCode(), [Role::CODE_RESP_ED, Role::CODE_GEST_ED])) {
                    return $these->getEcoleDoctorale()->getStructure() === $role->getStructure();
                }
                // todo : remplacer par $role->isStructureDependant() && $role->getTypeStructureDependant()->isUniteRecherche() :
                if (in_array($role->getCode(), [Role::CODE_RESP_UR, Role::CODE_GEST_UR])) {
                    return $these->getUniteRecherche()->getStructure() === $role->getStructure();
                }
                // todo : remplacer par $role->isStructureDependant() && $role->getTypeStructureDependant()->isEtablissement() :
                if ($role->getCode() === Role::CODE_ADMIN || $role->getCode() === Role::CODE_BDD || $role->getCode() === Role::CODE_BU)
                    return $these->getEtablissement()->getStructure() === $role->getStructure();
                break;
        }

        return true;
    }

    protected function assertController($controller, $action = null, $privilege = null): bool
    {
        if (! parent::assertController($controller, $action, $privilege)) {
            return false;
        }

        $this->controller = $controller;
        $this->action = $action;

        $this->these = $this->getRouteMatch()->getThese();
        $this->doctorant = $this->getRouteMatch()->getDoctorant();

        switch (true) {
            case $this->selectedRoleIsDoctorant():
                if (! $this->assertControllerAsDoctorant()) {
                    return false;
                }
        }

        if ($this->these === null) {
            return false;
        }

//        if (! $this->userContextService->isStructureDuRoleRespecteeForThese($this->these)) {
//            return false;
//        }

        switch (true) {
            case $privilege === ValidationPrivileges::THESE_VALIDATION_RDV_BU:
                return $this->isAllowed(new WfEtapeResource(WfEtape::CODE_RDV_BU_VALIDATION_BU, $this->these));
                break;
        }

        return true;
    }

    protected function assertControllerAsDoctorant(): bool
    {
        $identityDoctorant = $this->getIdentityDoctorant();

        if ($identityDoctorant === null) {
            throw new RuntimeException("Anomalie: le role doctorant est sélectionné mais aucune donnée d'identité doctorant n'est disponible");
        }

        switch (true) {
            case $this->actionIs(self::DOCTORANT_CONTROLLER, 'modifier-email-contact'):
                return $this->doctorant && $this->doctorant->getId() === $identityDoctorant->getId();
                break;
        }

        if ($this->these === null) {
            return true;
        }

        return $this->these->getDoctorant()->getId() === $identityDoctorant->getId();
    }

    /**
     * @param string $expectedController
     * @param string $expectedAction
     * @return bool
     */
    private function actionIs(string $expectedController, string $expectedAction): bool
    {
        return $this->controller === $expectedController && $this->action === $expectedAction;
    }

    /**
     * @return bool
     */
    private function selectedRoleIsDoctorant(): bool
    {
        return (bool) $this->userContextService->getSelectedRoleDoctorant();
    }

    protected ?Doctorant $identityDoctorant = null;

    private function getIdentityDoctorant(): ?Doctorant
    {
        if (null === $this->identityDoctorant) {
            $this->identityDoctorant = $this->userContextService->getIdentityDoctorant();
        }

        return $this->identityDoctorant;
    }

    public function isAllowed($resource, $privilege = null): bool
    {
        $allowed = parent::isAllowed($resource, $privilege);

        if (! $allowed) {
            switch (true) {
                case $resource instanceof WfEtapeResource:
                    $etape = $this->workflowService->getEtapeRepository()->findOneBy(['code' => $resource->getEtape()]);
                    $this->getServiceMessageCollector()->addMessage(
                        sprintf("L'étape &laquo; %s &raquo; n'est pas encore accessible.", $etape->getLibelleAutres()),
                        $etape->getCode());
                    break;
                default:
                    break;
            }
        }

        return $allowed;
    }

    protected function getRouteMatch(): ?RouteMatch
    {
        /** @var \Application\RouteMatch $rm */
        return $this->getMvcEvent()->getRouteMatch();
    }
}