<?php

namespace Application\Assertion\Rapport;

use Application\Assertion\BaseAssertion;
use Laminas\Permissions\Acl\Resource\ResourceInterface;

/**
 * Class RapportAssertion
 *
 * @property \Application\Assertion\Rapport\RapportEntityAssertion $entityAssertion
 * @property \Application\Assertion\Rapport\RapportControllerAssertion $controllerAssertion
 * @property \Application\Assertion\Rapport\RapportPageAssertion $pageAssertion
 */
class RapportAssertion extends BaseAssertion
{
    protected function getContext(): array
    {
        return [
            'these' => $this->getRouteMatch()->getThese(),
            'rapport' => $this->getRouteMatch()->getRapport(),
        ];
    }

    /**
     * @return self
     */
    protected function initControllerAssertion(): self
    {
        $this->controllerAssertion->setContext($this->getContext());

        return $this;
    }

    /**
     * @return self
     */
    protected function initPageAssertion(): self
    {
        $this->pageAssertion->setContext($this->getContext());

        return $this;
    }

    /**
     * @param ResourceInterface $entity
     * @return self
     */
    protected function initEntityAssertion(ResourceInterface $entity): self
    {
        $this->entityAssertion->setContext(['rapport' => $entity]);

        return $this;
    }
}