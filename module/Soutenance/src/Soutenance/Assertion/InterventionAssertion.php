<?php

namespace Soutenance\Assertion;

use Application\Entity\Db\Role;
use These\Entity\Db\These;
use These\Service\These\TheseServiceAwareTrait;
use Application\Service\UserContextServiceAwareTrait;
use DateInterval;
use DateTime;
use Soutenance\Provider\Privilege\InterventionPrivileges;
use Soutenance\Service\Parametre\ParametreServiceAwareTrait;
use Soutenance\Service\Proposition\PropositionServiceAwareTrait;
use Laminas\Permissions\Acl\Acl;
use Laminas\Permissions\Acl\Assertion\AssertionInterface;
use Laminas\Permissions\Acl\Resource\ResourceInterface;
use Laminas\Permissions\Acl\Role\RoleInterface;

class InterventionAssertion implements  AssertionInterface
{
    use UserContextServiceAwareTrait;
    use ParametreServiceAwareTrait;
    use PropositionServiceAwareTrait;
    use TheseServiceAwareTrait;

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
        $proposition = $this->getPropositionService()->findOneForThese($these);
        $date_soutenance = ($these->getDateSoutenance())?$these->getDateSoutenance():$proposition->getDate();

        $interval = $this->getParametreService()->getParametreByCode('PERIODE_INTERVENTION_DIRECTEUR')->getValeur();
        $mini = (new DateTime())->sub(new DateInterval('P'.$interval.'D'));
        $maxi = (new DateTime())->add(new DateInterval('P'.$interval.'D'));

        $user = $this->userContextService->getIdentityDb();
        $role = $this->userContextService->getSelectedIdentityRole();
        if ($role->getCode() === Role::CODE_ADMIN_TECH) return true;

        switch ($privilege) {
            case InterventionPrivileges::INTERVENTION_AFFICHER:
                if ($role->getCode() === Role::CODE_BDD && $role->getStructure() === $these->getEtablissement()->getStructure()) return true;
                if ($role->getCode() === Role::CODE_DIRECTEUR_THESE)return $this->getTheseService()->isDirecteur($these, $user->getIndividu());
                if ($role->getCode() === Role::CODE_DIRECTEUR_THESE)return $this->getTheseService()->isCoDirecteur($these, $user->getIndividu());
                if ($role->getCode() === Role::CODE_DOCTORANT)return $this->getTheseService()->isDoctorant($these, $user->getIndividu());
                return false;
            case InterventionPrivileges::INTERVENTION_MODIFIER:
                if ($date_soutenance < $mini OR $date_soutenance > $maxi) return false;
                if ($role->getCode() === Role::CODE_BDD && $role->getStructure() === $these->getEtablissement()->getStructure()) return true;
                if ($role->getCode() === Role::CODE_DIRECTEUR_THESE)return $this->getTheseService()->isDirecteur($these, $user->getIndividu());
                if ($role->getCode() === Role::CODE_DIRECTEUR_THESE)return $this->getTheseService()->isCoDirecteur($these, $user->getIndividu());
                return false;
        }

        return false;
    }


}