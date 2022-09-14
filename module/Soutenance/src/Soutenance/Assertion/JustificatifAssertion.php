<?php

namespace Soutenance\Assertion;

use Application\Entity\Db\Role;
use These\Entity\Db\These;
use These\Service\These\TheseServiceAwareTrait;
use Application\Service\UserContextServiceAwareTrait;
use DateTime;
use Soutenance\Entity\Etat;
use Soutenance\Provider\Privilege\JustificatifPrivileges;
use Soutenance\Service\Parametre\ParametreServiceAwareTrait;
use Soutenance\Service\Proposition\PropositionServiceAwareTrait;
use Laminas\Permissions\Acl\Acl;
use Laminas\Permissions\Acl\Assertion\AssertionInterface;
use Laminas\Permissions\Acl\Resource\ResourceInterface;
use Laminas\Permissions\Acl\Role\RoleInterface;

class JustificatifAssertion implements AssertionInterface {
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
        $proposition = $this->getPropositionService()->findByThese($these);
        $date_soutenance = ($these->getDateSoutenance())?$these->getDateSoutenance():$proposition->getDate();

        $depasse = (new DateTime() > $date_soutenance);
        $encours = ($proposition->getEtat()->getCode() === Etat::EN_COURS);

        $user = $this->userContextService->getIdentityDb();
        $role = $this->userContextService->getSelectedIdentityRole();

        switch ($privilege) {
            case JustificatifPrivileges::JUSTIFICATIF_AJOUTER:
            case JustificatifPrivileges::JUSTIFICATIF_RETIRER:
                switch ($role->getCode()) {
                    case Role::CODE_ADMIN_TECH:
                        return true;
                    case Role::CODE_BDD:
                        return ($role->getStructure() === $these->getEtablissement()->getStructure());
                    case Role::CODE_DIRECTEUR_THESE :
                        return ($encours AND !$depasse and $this->getTheseService()->isDirecteur($these, $user->getIndividu()));
                    case Role::CODE_CODIRECTEUR_THESE :
                        return ($encours AND !$depasse and $this->getTheseService()->isCoDirecteur($these, $user->getIndividu()));
                    case Role::CODE_DOCTORANT :
                        return ($encours AND !$depasse and $this->getTheseService()->isDoctorant($these, $user->getIndividu()));
                }
                return false;
        }
        return false;
    }

}