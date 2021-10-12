<?php

namespace Soutenance\Assertion;

use Application\Assertion\AbstractAssertion;
use Application\Entity\Db\Role;
use Application\Entity\Db\These;
use Application\Service\These\TheseServiceAwareTrait;
use Application\Service\UserContextServiceAwareTrait;
use DateInterval;
use DateTime;
use Soutenance\Provider\Privilege\JustificatifPrivileges;
use Soutenance\Service\Parametre\ParametreServiceAwareTrait;
use Soutenance\Service\Proposition\PropositionServiceAwareTrait;
use Laminas\Permissions\Acl\Acl;
use Laminas\Permissions\Acl\Assertion\AssertionInterface;
use Laminas\Permissions\Acl\Resource\ResourceInterface;
use Laminas\Permissions\Acl\Role\RoleInterface;

class JustificatifAssertion
    implements AssertionInterface
    //extends AbstractAssertion
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
//    public function assertEntity(ResourceInterface $resource = null, $privilege = null)
    {
        /** @var These $these */
        $these = $resource;
        $proposition = $this->getPropositionService()->findByThese($these);

        $date_current = new DateTime();
        $date_soutenance = ($these->getDateSoutenance())?$these->getDateSoutenance():$proposition->getDate();
        $interval = $this->getParametreService()->getParametreByCode('PERIODE_INTERVENTION_DIRECTEUR')->getValeur();
        $maxi = (DateTime::createFromFormat('d/m/Y', $date_soutenance->format('d/m/Y')))->add(new DateInterval('P'.$interval.'D'));

        $user = $this->userContextService->getIdentityDb();
        $role = $this->userContextService->getSelectedIdentityRole();
        if ($role->getCode() === Role::CODE_ADMIN_TECH) return true;

        switch ($privilege) {
            case JustificatifPrivileges::JUSTIFICATIF_AJOUTER:
                if ($role->getCode() === Role::CODE_BDD && $role->getStructure() === $these->getEtablissement()->getStructure()) return true;
                if ($date_current > $maxi) return false;
                if ($role->getCode() === Role::CODE_DIRECTEUR_THESE)return $this->getTheseService()->isDirecteur($these, $user->getIndividu());
                if ($role->getCode() === Role::CODE_DIRECTEUR_THESE)return $this->getTheseService()->isCoDirecteur($these, $user->getIndividu());
                if ($role->getCode() === Role::CODE_DOCTORANT)return $this->getTheseService()->isDoctorant($these, $user->getIndividu());
                return false;
            case JustificatifPrivileges::JUSTIFICATIF_RETIRER:
                if ($role->getCode() === Role::CODE_BDD && $role->getStructure() === $these->getEtablissement()->getStructure()) return true;
                if ($date_current > $maxi) return false;
                if ($role->getCode() === Role::CODE_DIRECTEUR_THESE)return $this->getTheseService()->isDirecteur($these, $user->getIndividu());
                if ($role->getCode() === Role::CODE_DIRECTEUR_THESE)return $this->getTheseService()->isCoDirecteur($these, $user->getIndividu());
                return false;
        }

        return false;
    }


//    public function assertController($controller, $action = null, $privilege = null)
//    {
//        return false;
//    }

}