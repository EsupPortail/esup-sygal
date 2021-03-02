<?php

namespace Soutenance\Assertion;

use Application\Entity\Db\Acteur;
use Application\Entity\Db\Role;
use Application\Entity\Db\These;
use Application\Service\UserContextServiceAwareTrait;
use Doctrine\Common\Collections\Collection;
use Soutenance\Provider\Privilege\EngagementImpartialitePrivileges;
use Zend\Permissions\Acl\Acl;
use Zend\Permissions\Acl\Assertion\AssertionInterface;
use Zend\Permissions\Acl\Resource\ResourceInterface;
use Zend\Permissions\Acl\Role\RoleInterface;

class EngagementImpartialiteAssertion implements  AssertionInterface {
    use UserContextServiceAwareTrait;

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

    public function assert(Acl $acl, RoleInterface $role = null, ResourceInterface $resource = null, $privilege = null)
    {
        /** @var These $these */
        $these = $resource;


        switch ($privilege) {
            case EngagementImpartialitePrivileges::ENGAGEMENT_IMPARTIALITE_SIGNER:
                $utilisateur = $this->userContextService->getIdentityDb();
                /** @var Collection $rapporteurs */
                $rapporteurs = $these->getActeursByRoleCode(Role::CODE_RAPPORTEUR_JURY);
                return $rapporteurs->map(function(Acteur $acteur) { return $acteur->getIndividu(); })->contains($utilisateur->getIndividu());
                break;
            case EngagementImpartialitePrivileges::ENGAGEMENT_IMPARTIALITE_ANNULER:
            case EngagementImpartialitePrivileges::ENGAGEMENT_IMPARTIALITE_NOTIFIER:
                $role = $this->userContextService->getSelectedIdentityRole();
                return ($role->getCode() === Role::CODE_BDD && $role->getStructure() === $these->getEtablissement()->getStructure());
                break;
            case EngagementImpartialitePrivileges::ENGAGEMENT_IMPARTIALITE_VISUALISER:
                $role = $this->userContextService->getSelectedIdentityRole();
                return ($role->getStructure() === $these->getEtablissement()->getStructure() || $role->getCode() === Role::CODE_OBSERVATEUR || $role->getCode() === Role::CODE_ADMIN_TECH);
                break;
        }
        return false;
    }
}