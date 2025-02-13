<?php

namespace Soutenance\Assertion;

use Application\Assertion\AbstractAssertion;
use Application\Assertion\Exception\FailedAssertionException;
use Application\Assertion\ThrowsFailedAssertionExceptionTrait;
use Application\RouteMatch;
use Application\Service\UserContextServiceAwareInterface;
use Application\Service\UserContextServiceAwareTrait;
use HDR\Entity\Db\HDR;
use HDR\Service\HDRServiceAwareTrait;
use These\Entity\Db\These;
use These\Service\These\TheseServiceAwareTrait;
use UnicaenApp\Service\MessageCollectorAwareInterface;
use UnicaenApp\Service\MessageCollectorAwareTrait;

class IndexAssertion extends AbstractAssertion
    implements UserContextServiceAwareInterface, MessageCollectorAwareInterface
{

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

        try {
            switch ($action) {
                case 'index-rapporteur':
                case 'index-acteur':
                    if ($this->entity !== null) {
                        if($this->entity instanceof These){
                            return $this->userContextService->isStructureDuRoleRespecteeForThese($this->entity);
                        }elseif($this->entity instanceof HDR){
                            return $this->userContextService->isStructureDuRoleRespecteeForHDR($this->entity);
                        }
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

    protected function getRouteMatch(): ?RouteMatch
    {
        /** @var RouteMatch $rm */
        $rm = $this->getMvcEvent()->getRouteMatch();
        return $rm;
    }
}