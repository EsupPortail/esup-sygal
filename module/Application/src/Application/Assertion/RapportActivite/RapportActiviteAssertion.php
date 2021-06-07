<?php

namespace Application\Assertion\RapportActivite;

use Application\Assertion\BaseAssertion;
use Application\Entity\Db\Rapport;
use Zend\Permissions\Acl\Resource\ResourceInterface;

class RapportActiviteAssertion extends BaseAssertion
{
    /**
     * @return self
     */
    protected function initControllerAssertion(): self
    {
        $this->controllerAssertion->setContext([
            'rapport' => $this->getRapportActivite(),
        ]);

        return $this;
    }

    /**
     * @return self
     */
    protected function initPageAssertion(): self
    {
        $this->pageAssertion->setContext([
            'rapport' => $this->getRapportActivite(),
        ]);

        return $this;
    }

    /**
     * @param ResourceInterface $entity
     * @return self
     */
    protected function initEntityAssertion(ResourceInterface $entity): self
    {
        $this->entityAssertion->setContext([
            'rapport' => $entity,
        ]);

        return $this;
    }

    /**
     * @return Rapport|null
     */
    protected function getRapportActivite(): ?Rapport
    {
        $these = $this->getRouteMatch()->getThese();
        $rapport = $this->getRouteMatch()->getRapport();

//        if ($rapport === null) {
//            $rapport = new Rapport();
//            if ($these !== null) {
//                $rapport->setThese($these);
//            }
//        }

        return $rapport;
    }
}