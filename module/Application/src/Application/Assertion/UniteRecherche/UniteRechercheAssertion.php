<?php

namespace Application\Assertion\UniteRecherche;

use Application\Assertion\BaseAssertion;
use Zend\Permissions\Acl\Resource\ResourceInterface;

class UniteRechercheAssertion extends BaseAssertion
{
    /**
     * @return static
     */
    protected function initControllerAssertion()
    {
        return $this;
    }

    /**
     * @return static
     */
    protected function initPageAssertion()
    {
        return $this;
    }

    /**
     * @param ResourceInterface $entity
     * @return static
     */
    protected function initEntityAssertion(ResourceInterface $entity)
    {
        $this->entityAssertion->setContext(['uniteRecherche' => $entity]);

        return $this;
    }
}