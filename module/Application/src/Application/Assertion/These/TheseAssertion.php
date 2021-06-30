<?php

namespace Application\Assertion\These;

use Application\Acl\WfEtapeResource;
use Application\Assertion\BaseAssertion;
use Application\Entity\Db\Role;
use Application\Entity\Db\These;
use Application\Entity\Db\WfEtape;
use Application\Provider\Privilege\DoctorantPrivileges;
use Application\Provider\Privilege\ThesePrivileges;
use Application\Provider\Privilege\ValidationPrivileges;
use Application\Service\UserContextService;
use Application\Service\Workflow\WorkflowServiceAwareInterface;
use Application\Service\Workflow\WorkflowServiceAwareTrait;
use UnicaenApp\Service\MessageCollectorAwareTrait;
use Zend\Permissions\Acl\Resource\ResourceInterface;

/**
 * Class TheseAssertion
 *
 * @package Application\Assertion
 * @method UserContextService getServiceUserContext()
 */
class TheseAssertion extends BaseAssertion implements WorkflowServiceAwareInterface
{
    use WorkflowServiceAwareTrait;
    use MessageCollectorAwareTrait;

    protected function assertEntity(ResourceInterface $these, $privilege = null)
    {
        if (! parent::assertEntity($these, $privilege)) {
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

    protected function assertController($controller, $action = null, $privilege = null)
    {
        if (! parent::assertController($controller, $action, $privilege)) {
            return false;
        }

        $these = $this->getRouteMatch()->getThese();

        switch (true) {
            case $privilege === ValidationPrivileges::THESE_VALIDATION_RDV_BU:
                return $this->isAllowed(new WfEtapeResource(WfEtape::CODE_RDV_BU_VALIDATION_BU, $these));
                break;
        }

        return true;
    }

    public function isAllowed($resource, $privilege = null)
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

    /**
     * @return static
     */
    protected function initControllerAssertion()
    {
        $this->controllerAssertion->setContext([
            'these'     => $this->getRouteMatch()->getThese(),
            'doctorant' => $this->getRouteMatch()->getDoctorant(),
        ]);

        return $this;
    }

    /**
     * @return static
     */
    protected function initPageAssertion()
    {
        $this->pageAssertion->setContext(['these' => $this->getRouteMatch()->getThese()]);

        return $this;
    }

    /**
     * @param ResourceInterface $entity
     * @return static
     */
    protected function initEntityAssertion(ResourceInterface $entity)
    {
        $this->entityAssertion->setContext(['these' => $entity]);

        return $this;
    }
}