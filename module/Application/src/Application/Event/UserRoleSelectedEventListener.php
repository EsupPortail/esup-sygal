<?php

namespace Application\Event;

use Application\Entity\Db\Role;
use Application\Entity\Db\Utilisateur;
use UnicaenAuth\Event\Listener\UserRoleSelectedEventAbstractListener;
use UnicaenAuth\Event\UserRoleSelectedEvent;
use UnicaenAuth\Service\UserContext;
use Zend\Permissions\Acl\Role\RoleInterface;

/**
 * Created by PhpStorm.
 * User: gauthierb
 * Date: 02/05/16
 * Time: 14:10
 */
class UserRoleSelectedEventListener extends UserRoleSelectedEventAbstractListener
{
    /**
     * @param UserRoleSelectedEvent $e
     */
    public function postSelection(UserRoleSelectedEvent $e)
    {
        $role = $e->getRole();

        if (! $role) {
            return;
        }

        if (! $role instanceof RoleInterface) {
            $role = $this->getEntityManager()->getRepository(Role::class)->findOneBy(['roleId' => $role]);
        }

        /** @var UserContext $userContext */
        $userContext = $e->getTarget();

        /** @var Utilisateur $utilisateur */
        $utilisateur = $userContext->getDbUser();
        if (! $utilisateur) {
            return;
        }

        $this->saveUserLastRole($utilisateur, $role);
    }

    private function saveUserLastRole(Utilisateur $dbUser, $role)
    {
        $dbUser->setLastRole($role);
        $this->getEntityManager()->flush($dbUser);
    }
}