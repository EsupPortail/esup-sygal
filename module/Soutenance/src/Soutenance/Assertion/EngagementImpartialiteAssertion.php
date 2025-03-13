<?php

namespace Soutenance\Assertion;

use Application\Assertion\AbstractAssertion;
use Application\Service\UserContextServiceAwareTrait;
use HDR\Entity\Db\HDR;
use HDR\Service\HDRServiceAwareTrait;
use Laminas\Permissions\Acl\Assertion\AssertionInterface;
use Laminas\Permissions\Acl\Resource\ResourceInterface;
use Soutenance\Provider\Privilege\EngagementImpartialitePrivileges;
use These\Entity\Db\These;
use These\Service\These\TheseServiceAwareTrait;

class EngagementImpartialiteAssertion extends AbstractAssertion implements  AssertionInterface {
    use UserContextServiceAwareTrait;
    use TheseServiceAwareTrait;
    use HDRServiceAwareTrait;

    /**
     * !!!! Pour éviter l'erreur "Serialization of 'Closure' is not allowed"... !!!!
     *
     * @return array
     */
    public function __sleep()
    {
        return [];
    }

    public function __invoke($page)
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

        $entity = $this->getRequestedEntity();

        if($entity instanceof These){
            if(!$this->userContextService->isStructureDuRoleRespecteeForThese($entity)) return false;
        }elseif($entity instanceof HDR){
            if(!$this->userContextService->isStructureDuRoleRespecteeForHDR($entity)) return false;
        }

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

        if($entity instanceof These){
            if(!$this->userContextService->isStructureDuRoleRespecteeForThese($entity)) return false;
        }elseif($entity instanceof HDR){
            if(!$this->userContextService->isStructureDuRoleRespecteeForHDR($entity)) return false;
        }

        switch ($privilege) {
            case EngagementImpartialitePrivileges::ENGAGEMENT_IMPARTIALITE_ANNULER:
                $etatObject = $entity instanceof These ? $entity->getEtatThese() : $entity->getEtatHDR();
                if ($etatObject === These::ETAT_SOUTENUE || $etatObject === HDR::ETAT_SOUTENUE) return false;
        }

        return true;
    }

    protected function getRequestedEntity(): These|HDR|null
    {

        $entity = null;
        if (($routeMatch = $this->getRouteMatch())) {
            if ($routeMatch->getParam('these') !== null) {
                $entity = $this->theseService->getRepository()->find($routeMatch->getParam('these'));
            } else if ($routeMatch->getParam('hdr') !== null) {
                $entity = $this->hdrService->getRepository()->find($routeMatch->getParam('hdr'));
            }
        }

        return $entity;
    }
}