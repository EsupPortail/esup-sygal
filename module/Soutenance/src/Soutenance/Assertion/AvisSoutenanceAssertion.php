<?php

namespace Soutenance\Assertion;

use Application\Entity\Db\Acteur;
use Application\Entity\Db\Role;
use Application\Entity\Db\These;
use Application\Service\UserContextServiceAwareTrait;
use Soutenance\Provider\Privilege\AvisSoutenancePrivileges;
use Zend\Permissions\Acl\Acl;
use Zend\Permissions\Acl\Assertion\AssertionInterface;
use Zend\Permissions\Acl\Resource\ResourceInterface;
use Zend\Permissions\Acl\Role\RoleInterface;

class AvisSoutenanceAssertion  implements  AssertionInterface {
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

    public function assert(Acl $acl, RoleInterface $role = null, ResourceInterface $resource = null, $privilege = null) {

        /**
         * @var Acteur $rapporteur
         * @var These $these
         */
        $rapporteur = $resource;
        $these = $rapporteur->getThese();

        $utilisateur = $this->userContextService->getIdentityDb();
        $role = $this->userContextService->getSelectedIdentityRole();

        switch ($privilege) {
            /**
             * Les personnes pouvant visualiser l'avis de soutenance sont :
             * - l'administrateur technique ou observateur COMUE
             * - le BdD de l'établissement de la thèse
             * - les directeurs/co-directeurs de la thèses
             * - le rapporteur émettant l'avis
             */
            case AvisSoutenancePrivileges::SOUTENANCE_AVIS_VISUALISER :

                if ($role->getCode() === Role::CODE_ADMIN_TECH || $role->getCode() === Role::CODE_OBSERVATEUR) return true;
                if ($role->getCode() === Role::CODE_BDD && $role->getStructure() === $these->getEtablissement()->getStructure()) return true;
                if ($these->hasActeurWithRole($utilisateur->getIndividu(),Role::CODE_DIRECTEUR_THESE) || $these->hasActeurWithRole($utilisateur->getIndividu(),Role::CODE_CODIRECTEUR_THESE)) return true;
                if ($role->getCode() === Role::CODE_RAPPORTEUR_JURY && $utilisateur->getIndividu() === $rapporteur->getIndividu()) return true;
                return false;
                break;
            /**
             * Les personnes pouvant éditer l'avis de soutenance sont :
             * - le rapporteur émettant l'avis
             */
            case AvisSoutenancePrivileges::SOUTENANCE_AVIS_MODIFIER :

                if ($role->getCode() !== Role::CODE_RAPPORTEUR_JURY) return false;
                if ($utilisateur->getIndividu() !== $rapporteur->getIndividu()) return false;
                return true;
                break;
        }
    }

}