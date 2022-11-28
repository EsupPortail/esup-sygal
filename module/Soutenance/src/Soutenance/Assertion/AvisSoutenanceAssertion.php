<?php

namespace Soutenance\Assertion;

use These\Entity\Db\Acteur;
use Application\Entity\Db\Role;
use These\Entity\Db\These;
use Application\Service\UserContextServiceAwareTrait;
use DateInterval;
use DateTime;
use Exception;
use Soutenance\Entity\Proposition;
use Soutenance\Provider\Privilege\AvisSoutenancePrivileges;
use Soutenance\Service\Proposition\PropositionServiceAwareTrait;
use UnicaenApp\Exception\RuntimeException;
use Laminas\Permissions\Acl\Acl;
use Laminas\Permissions\Acl\Assertion\AssertionInterface;
use Laminas\Permissions\Acl\Resource\ResourceInterface;
use Laminas\Permissions\Acl\Role\RoleInterface;

class AvisSoutenanceAssertion  implements  AssertionInterface {
    use PropositionServiceAwareTrait;
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
            case AvisSoutenancePrivileges::AVIS_VISUALISER :

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
            case AvisSoutenancePrivileges::AVIS_MODIFIER :

                if ($role->getCode() !== Role::CODE_RAPPORTEUR_JURY) return false;
                if ($utilisateur->getIndividu() !== $rapporteur->getIndividu()) return false;
                try {
                    $currentDate = new DateTime();
                } catch (Exception $e) {
                    throw new RuntimeException("Problème de récupération de la date");
                }

                /** @var Proposition $proposition */
                $proposition = $this->getPropositionService()->findOneForThese($these);
                $dateRetour = ($proposition->getRenduRapport())->add(new DateInterval('P1D'));
                if ($currentDate > $dateRetour) return false;
                return true;
                break;
            /**
             * Les personnes pouvant révoquer un avis
             * - le rapporteur
             * - le bdd de l'etablissement
             */
            case AvisSoutenancePrivileges::AVIS_ANNULER :
                if ($role->getCode() === Role::CODE_BDD && $role->getStructure() === $these->getEtablissement()->getStructure()) return true;
                if ($role->getCode() === Role::CODE_RAPPORTEUR_JURY && $utilisateur->getIndividu() === $rapporteur->getIndividu()) return true;
                return false;
                break;
        }
        return false;
    }

}