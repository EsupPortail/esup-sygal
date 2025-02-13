<?php

namespace Soutenance\Assertion;

use Application\Entity\Db\Role;
use HDR\Entity\Db\HDR;
use HDR\Service\HDRServiceAwareTrait;
use Soutenance\Entity\PropositionHDR;
use Soutenance\Entity\PropositionThese;
use These\Entity\Db\These;
use These\Service\These\TheseServiceAwareTrait;
use Application\Service\UserContextServiceAwareTrait;
use DateTime;
use Soutenance\Entity\Etat;
use Soutenance\Provider\Privilege\JustificatifPrivileges;
use Soutenance\Service\Proposition\PropositionServiceAwareTrait;
use Laminas\Permissions\Acl\Acl;
use Laminas\Permissions\Acl\Assertion\AssertionInterface;
use Laminas\Permissions\Acl\Resource\ResourceInterface;
use Laminas\Permissions\Acl\Role\RoleInterface;
use UnicaenParametre\Service\Parametre\ParametreServiceAwareTrait;

class JustificatifAssertion implements AssertionInterface {
    use UserContextServiceAwareTrait;
    use ParametreServiceAwareTrait;
    use PropositionServiceAwareTrait;
    use TheseServiceAwareTrait;
    use HDRServiceAwareTrait;

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
        /** @var These|HDR $entity */
        $entity = $resource;
        $proposition = $this->getPropositionService()->findOneForObject($entity);
        if($proposition instanceof PropositionThese){
            $date_soutenance = ($entity->getDateSoutenance())?$entity->getDateSoutenance():$proposition->getDate();
        }else if($proposition instanceof PropositionHDR){
            $date_soutenance = $proposition->getDate();
        }

        $depasse = $date_soutenance && new DateTime() > $date_soutenance;
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
                    case Role::CODE_GEST_HDR:
                        return ($role->getStructure() === $entity->getEtablissement()->getStructure());
                    case Role::CODE_DIRECTEUR_THESE :
                        return ($encours AND !$depasse and $this->getTheseService()->isDirecteur($entity, $user->getIndividu()));
                    case Role::CODE_CODIRECTEUR_THESE :
                        return ($encours AND !$depasse and $this->getTheseService()->isCoDirecteur($entity, $user->getIndividu()));
                    case Role::CODE_DOCTORANT :
                        return ($encours AND !$depasse and $this->getTheseService()->isDoctorant($entity, $user->getIndividu()));
                    case Role::CODE_HDR_CANDIDAT :
                        return ($encours AND !$depasse and $this->hdrService->isCandidat($entity, $user->getIndividu()));
                    case Role::CODE_HDR_GARANT :
                        return ($encours AND !$depasse and $this->hdrService->isGarant($entity, $user->getIndividu()));
                }
                return false;
        }
        return false;
    }
}