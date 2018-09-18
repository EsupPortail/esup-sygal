<?php

namespace Application\Assertion\EcoleDoctorale;

use Application\Assertion\BaseAssertion;
use Zend\Permissions\Acl\Resource\ResourceInterface;

class EcoleDoctoraleAssertion extends BaseAssertion
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
        $this->entityAssertion->setContext(['ecoleDoctorale' => $entity]);

        return $this;
    }
}