<?php

namespace Soutenance\Assertion;

use Application\Assertion\AbstractAssertion;
use Application\Assertion\Exception\FailedAssertionException;
use Application\Assertion\ThrowsFailedAssertionExceptionTrait;
use Application\Entity\Db\Role;
use Application\RouteMatch;
use Application\Service\UserContextServiceAwareInterface;
use Application\Service\UserContextServiceAwareTrait;
use HDR\Entity\Db\HDR;
use HDR\Provider\Privileges\HDRPrivileges;
use HDR\Service\HDRServiceAwareTrait;
use Laminas\Permissions\Acl\Resource\ResourceInterface;
use Soutenance\Entity\Etat;
use Soutenance\Entity\Proposition;
use Soutenance\Entity\PropositionHDR;
use Soutenance\Provider\Privilege\PresoutenancePrivileges;
use These\Entity\Db\These;
use These\Service\These\TheseServiceAwareTrait;
use UnicaenApp\Service\MessageCollectorAwareInterface;
use UnicaenApp\Service\MessageCollectorAwareTrait;

class PresoutenanceAssertion extends AbstractAssertion
    implements UserContextServiceAwareInterface, MessageCollectorAwareInterface {

    use UserContextServiceAwareTrait;
    use MessageCollectorAwareTrait;
    use TheseServiceAwareTrait;
    use HDRServiceAwareTrait;
    use ThrowsFailedAssertionExceptionTrait;

    private These|HDR|null $entity = null;

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
     * @param These|HDR $entity
     * @param string $privilege
     * @return boolean
     */
    protected function assertEntity(ResourceInterface $entity, $privilege = null): bool
    {
        if (! parent::assertEntity($entity, $privilege)) {
            return false;
        }

        $this->entity = $entity;

        try {
            if($this->entity instanceof These){
                if(!$this->userContextService->isStructureDuRoleRespecteeForThese($this->entity)) return false;
            }elseif($this->entity instanceof HDR){
                if(!$this->userContextService->isStructureDuRoleRespecteeForHDR($this->entity)) return false;
            }

            switch ($privilege) {
                case PresoutenancePrivileges::PRESOUTENANCE_ASSOCIATION_MEMBRE_INDIVIDU:
                case PresoutenancePrivileges::PRESOUTENANCE_DATE_RETOUR_MODIFICATION:
                    $role = $this->userContextService->getSelectedIdentityRole();
                    return (($role->getCode() === Role::CODE_BDD || Role::CODE_GEST_HDR) && $role->getStructure() === $this->entity->getEtablissement()->getStructure());
                case HDRPrivileges::HDR_DONNER_RESULTAT:
                    /** @var Proposition $p */
                    $proposition = $this->entity->getCurrentProposition();
                    if($proposition instanceof PropositionHDR && $proposition->getEtat()->getCode() !== Etat::VALIDEE) return false;
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

        $this->entity = $this->getRequestedEntity();
        if ($this->entity === null) return false;

        try {
            if($this->entity instanceof These){
                if(!$this->userContextService->isStructureDuRoleRespecteeForThese($this->entity)) return false;
            }elseif($this->entity instanceof HDR){
                if(!$this->userContextService->isStructureDuRoleRespecteeForHDR($this->entity)) return false;
            }
            switch ($action) {
//                case 'presoutenance':
//                    if ($this->entity !== null) {
//                        if($this->entity instanceof These){
//                            return $this->userContextService->isStructureDuRoleRespecteeForThese($this->entity);
//                        }elseif($this->entity instanceof HDR){
//                            return $this->userContextService->isStructureDuRoleRespecteeForHDR($this->entity);
//                        }
//                    }
//                    break;
                case 'deliberation-jury':
                    /** @var Proposition $p */
                    $proposition = $this->entity->getCurrentProposition();
                    if($proposition instanceof PropositionHDR && $proposition->getEtat()->getCode() !== Etat::VALIDEE) return false;
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
    protected function getRequestedEntity(): These|HDR|null
    {
        $entity = null;
        if (($routeMatch = $this->getRouteMatch())) {
            if($routeMatch->getParam('these') !== null){
                $entity = $this->theseService->getRepository()->find($routeMatch->getParam('these'));
            }else if($routeMatch->getParam('hdr') !== null){
                $entity = $this->hdrService->getRepository()->find($routeMatch->getParam('hdr'));
            }
        }

        return $entity;
    }

    protected function getRouteMatch(): ?RouteMatch
    {
        /** @var RouteMatch $rm */
        $rm = $this->getMvcEvent()->getRouteMatch();
        return $rm;
    }
}