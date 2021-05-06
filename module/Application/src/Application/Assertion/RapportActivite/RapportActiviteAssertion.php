<?php

namespace Application\Assertion\RapportActivite;

use Application\Assertion\BaseAssertion;
use Application\Entity\Db\Rapport;
use Zend\Permissions\Acl\Resource\ResourceInterface;

class RapportActiviteAssertion extends BaseAssertion
{
    /**
     * @return static
     */
    protected function initControllerAssertion()
    {
        $this->controllerAssertion->setContext([
            'rapport' => $this->getRapportActivite(),
        ]);

        return $this;
    }

    /**
     * @return static
     */
    protected function initPageAssertion()
    {
        $this->pageAssertion->setContext([
            'rapport' => $this->getRapportActivite(),
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
            'rapport' => $entity,
        ]);

        return $this;
    }

    /**
     * @return Rapport
     */
    protected function getRapportActivite()
    {
        $these = $this->getRouteMatch()->getThese();
        $rapport = $this->getRouteMatch()->getRapport();

        if ($rapport === null) {
            $rapport = new Rapport();
            if ($these !== null) {
                $rapport->setThese($these);
            }
        }

        return $rapport;
    }
}