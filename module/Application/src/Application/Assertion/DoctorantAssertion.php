<?php

namespace Application\Assertion\These;

use Application\Assertion\BaseAssertion;
use Application\Entity\Db\Role;
use Application\Entity\Db\These;
use Application\Provider\Privilege\DoctorantPrivileges;
use Application\Service\UserContextService;
use Zend\Permissions\Acl\Resource\ResourceInterface;

/**
 * Class TheseAssertion
 *
 * @package Application\Assertion
 * @method UserContextService getServiceUserContext()
 */
class DoctorantAsserction extends BaseAssertion {

    protected function assertEntity(ResourceInterface $these, $privilege = null)
    {
        if (!parent::assertEntity($these, $privilege)) {
            return false;
        }

        /** @var These $these */
        $role = $this->userContextService->getSelectedIdentityRole();
        $individu = $this->userContextService->getIdentityIndividu();
        switch ($privilege) {
            case $privilege === DoctorantPrivileges::DOCTORANT_AFFICHER_EMAIL_CONTACT :
                // doctorant
                if ($role->getCode() === Role::CODE_ADMIN_TECH) return true;
                if ($role->getCode() === Role::CODE_DOCTORANT) return $these->getDoctorant()->getIndividu() === $individu;
                return false;
                break;
        }

        return true;
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