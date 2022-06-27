<?php

namespace Application\Service\Email;

use Individu\Entity\Db\IndividuRole;
use Application\Entity\Db\Role;
use Application\Entity\Db\These;
use Application\Entity\Db\Variable;
use Application\Service\Role\RoleServiceAwareTrait;
use Application\Service\Utilisateur\UtilisateurServiceAwareTrait;
use Application\Service\Variable\VariableServiceAwareTrait;

class EmailTheseService
{
    use RoleServiceAwareTrait;
    use VariableServiceAwareTrait;
    use UtilisateurServiceAwareTrait;

    /**
     * @param These $these
     * @return string
     */
    public function fetchEmailBdd(These $these) : string
    {
        $variable = $this->variableService->getRepository()->findByCodeAndThese(Variable::CODE_EMAIL_BDD, $these);
        return $variable->getValeur();

    }

    /**
     * @param IndividuRole[] $individuRoles
     * @param These $these
     * @return bool
     */
    public function hasEmailsByEtablissement(array $individuRoles, These $these) : bool
    {
        foreach ($individuRoles as $individuRole) {
            $individu = $individuRole->getIndividu();
            if ($individu->getEtablissement() === $these->getEtablissement()) {
                if ($individu->getEmail() !== null) return true;
            }
        }
        return false;
    }

    /**
     * @param IndividuRole[] $individuRoles
     * @param These $these
     * @return array
     */
    public function fetchEmailsByEtablissement(array $individuRoles, These $these) : array
    {
        $allEmails = [];
        $emails = [];
        foreach ($individuRoles as $individuRole) {
            $individu = $individuRole->getIndividu();
            if ($individu->getEtablissement() === $these->getEtablissement()) {
                if ($individu->getEmail() !== null) {
                    {
                        $emails[] = $individu->getEmail();
                        $allEmails[] = $individu->getEmail();
                    }

                } else {
                    $utilisateurs = $this->getUtilisateurService()->getRepository()->findByIndividu($individu);
                    foreach ($utilisateurs as $utilisateur) {
                        if ($utilisateur->getEmail()) {
                            $emails[] = $utilisateur->getEmail();
                            $allEmails[] = $utilisateur->getEmail();
                            break;
                        }
                    }
                }
            }
        }
        if (! empty($emails)) return $emails;
        return $allEmails;
    }

    /**
     * @param These $these
     * @return string[]
     */
    public function fetchEmailEcoleDoctorale(These $these) : array
    {
        /** @var IndividuRole[] $individuRoles */
        $individuRoles = $this->roleService->getIndividuRoleByStructure($these->getEcoleDoctorale()->getStructure());
        return $this->fetchEmailsByEtablissement($individuRoles, $these);
    }

    /**
     * @param These $these
     * @return string[]
     */
    public function fetchEmailUniteRecherche(These $these) : array
    {
        /** @var IndividuRole[] $individuRoles */
        $individuRoles = $this->roleService->getIndividuRoleByStructure($these->getUniteRecherche()->getStructure());
        return $this->fetchEmailsByEtablissement($individuRoles, $these);
    }

    /**
     * @param These $these
     * @return string[]
     */
    public function fetchEmailMaisonDuDoctorat(These $these) : array
    {
        /** @var IndividuRole[] $individuRoles */
        $individuRoles = $this->roleService->getIndividuRoleByStructure($these->getEtablissement()->getStructure());
        $individuRoles = array_filter($individuRoles, function (IndividuRole $ir) { return $ir->getRole()->getCode() === Role::CODE_BDD;});
        return $this->fetchEmailsByEtablissement($individuRoles, $these);
    }
}