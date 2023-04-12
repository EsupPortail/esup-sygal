<?php

namespace Application\Service\Email;

use Application\Service\Role\RoleServiceAwareTrait;
use Application\Service\Utilisateur\UtilisateurServiceAwareTrait;
use Application\Service\Variable\VariableServiceAwareTrait;
use Individu\Entity\Db\IndividuRole;
use Individu\Service\IndividuServiceAwareTrait;
use InvalidArgumentException;
use Soutenance\Service\Membre\MembreServiceAwareTrait;
use These\Entity\Db\These;
use These\Service\Acteur\ActeurServiceAwareTrait;

class EmailTheseService
{
    use RoleServiceAwareTrait;
    use VariableServiceAwareTrait;
    use UtilisateurServiceAwareTrait;
    use ActeurServiceAwareTrait;
    use IndividuServiceAwareTrait;
    use MembreServiceAwareTrait;

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
                if ($individu->getEmailPro() !== null) return true;
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
                if ($individu->getEmailPro() !== null) {
                    {
                        $emails[] = $individu->getEmailPro();
                        $allEmails[] = $individu->getEmailPro();
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
        $individuRoles = $this->roleService->findIndividuRoleByStructure($these->getEcoleDoctorale()->getStructure());
        return $this->fetchEmailsByEtablissement($individuRoles, $these);
    }

    /**
     * @param These $these
     * @return string[]
     */
    public function fetchEmailUniteRecherche(These $these) : array
    {
        /** @var IndividuRole[] $individuRoles */
        $individuRoles = $this->roleService->findIndividuRoleByStructure($these->getUniteRecherche()->getStructure());
        return $this->fetchEmailsByEtablissement($individuRoles, $these);
    }

    /**
     * Retourne l'éventuelle adresse mail pour les aspects "Doctorat".
     *
     * @param These $these
     * @return string[]
     */
    public function fetchEmailAspectsDoctorat(These $these) : array
    {
        if ($email = $these->getEtablissement()->getEmailDoctorat()) {
            return [$email];
        }
        return [];
    }

    /**
     * Retourne l'éventuelle adresse mail pour les aspects "Bibliothèque".
     *
     * @param These $these
     * @return string[]
     */
    public function fetchEmailAspectsBibliotheque(These $these) : array
    {
        if ($email = $these->getEtablissement()->getEmailBibliotheque()) {
            return [$email];
        }
        return [];
    }

    /**
     * @param These $these
     * @return string[]
     */
    public function fetchEmailEncadrants(These $these) : array
    {
        $emails = [];
        $encadrants = $this->acteurService->getRepository()->findEncadrementThese($these);
        foreach ($encadrants as $encadrant) {
            //tentative dans individu
            $email = $encadrant->getIndividu()->getEmailPro();
            //tentative dans membre
            if ($email === null) {
                $membre = $this->membreService->getMembreByActeur($encadrant);
                if ($membre) $email = $membre->getEmail();
            }
            //tentative dans utilisateur
            if ($email === null) {
                $utilisateurs = $this->utilisateurService->getRepository()->findByIndividu($encadrant->getIndividu());
                foreach ($utilisateurs as $utilisateur) {
                    $email = $utilisateur->getEmail();
                    if ($email !== null) break;
                }
            }
            // echec ...
            if ($email === null) {
                throw new InvalidArgumentException("Pas de mail pour l'encadrant de thèse [".$encadrant->getIndividu()->getNomComplet()."]");
            }
            $emails[] = $email;
        }
        return $emails;
    }

    /**
     * @param These $these
     * @return array
     */
    public function fetchEmailActeursDirects(These $these) : array
    {
        $emails = [];
        $emails[] = $these->getDoctorant()->getIndividu()->getEmailPro();

        $encadrants = $this->fetchEmailEncadrants($these);
        foreach ($encadrants as $encadrant) {
            $emails[] = $encadrant;
        }
        return $emails;
    }
}