<?php

namespace Application\Assertion\RapportAnnuel;

use Application\Assertion\BaseAssertion;
use Application\Entity\Db\RapportAnnuel;
use Zend\Permissions\Acl\Resource\ResourceInterface;

class RapportAnnuelAssertion extends BaseAssertion
{
    /**
     * @return static
     */
    protected function initControllerAssertion()
    {
        $this->controllerAssertion->setContext([
            'rapportAnnuel' => $this->getRapportAnnuel(),
        ]);

        return $this;
    }

    /**
     * @return static
     */
    protected function initPageAssertion()
    {
        $this->pageAssertion->setContext([
            'rapportAnnuel' => $this->getRapportAnnuel(),
        ]);

        return $this;
    }

    /**
     * @param ResourceInterface $entity
     * @return static
     */
    protected function initEntityAssertion(ResourceInterface $entity)
    {
        $this->entityAssertion->setContext([
            'rapportAnnuel' => $entity,
        ]);

        return $this;
    }

    /**
     * @return RapportAnnuel
     */
    protected function getRapportAnnuel()
    {
        $these = $this->getRouteMatch()->getThese();
        $rapportAnnuel = $this->getRouteMatch()->getRapportAnnuel();

        if ($rapportAnnuel === null) {
            $rapportAnnuel = new RapportAnnuel();
            if ($these !== null) {
                $rapportAnnuel->setThese($these);
            }
        }

        return $rapportAnnuel;
    }
}