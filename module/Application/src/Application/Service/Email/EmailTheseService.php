<?php

namespace Application\Service\Email;

use Application\Service\Role\ApplicationRoleServiceAwareTrait;
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
    use ApplicationRoleServiceAwareTrait;
    use VariableServiceAwareTrait;
    use UtilisateurServiceAwareTrait;
    use ActeurServiceAwareTrait;
    use IndividuServiceAwareTrait;
    use MembreServiceAwareTrait;

    /**
     * @param IndividuRole[] $individuRoles
     */
    public function collectEmailsFromIndividuRoles(array $individuRoles) : array
    {
        $allEmails = [];
        $emails = [];
        foreach ($individuRoles as $individuRole) {
            $individu = $individuRole->getIndividu();
            if ($emailPro = $individu->getEmailPro()) {
                $emails[] = $emailPro;
                $allEmails[] = $emailPro;
            } else {
                $utilisateurs = $this->getUtilisateurService()->getRepository()->findByIndividu($individu);
                foreach ($utilisateurs as $utilisateur) {
                    if ($email = $utilisateur->getEmail()) {
                        $emails[] = $email;
                        $allEmails[] = $email;
                        break;
                    }
                }
            }
        }
        if (! empty($emails)) return $emails;
        return $allEmails;
    }

    /**
     * @return string[]
     */
    public function fetchEmailEcoleDoctorale(These $these) : array
    {
        $individuRoles = $this->applicationRoleService->findIndividuRoleByStructure(
            $these->getEcoleDoctorale()->getStructure(), null, $these->getEtablissement());

        return $this->collectEmailsFromIndividuRoles($individuRoles);
    }

    /**
     * @return string[]
     */
    public function fetchEmailUniteRecherche(These $these) : array
    {
        $individuRoles = $this->applicationRoleService->findIndividuRoleByStructure(
            $these->getUniteRecherche()->getStructure(), null, $these->getEtablissement());

        return $this->collectEmailsFromIndividuRoles($individuRoles);
    }

    /**
     * Retourne les éventuelles adresses mails pour les aspects "Doctorat".
     *
     * @param These $these
     * @return string[]
     */
    public function fetchEmailAspectsDoctorat(These $these) : array
    {
        if ($email = $these->getEtablissement()->getEmailDoctorat()) {
            return array_map('trim', explode(',', $email));
        }
        return [];
    }

    /**
     * Retourne les éventuelles adresses mails pour les aspects "Bibliothèque".
     *
     * @param These $these
     * @return string[]
     */
    public function fetchEmailAspectsBibliotheque(These $these) : array
    {
        if ($email = $these->getEtablissement()->getEmailBibliotheque()) {
            return array_map('trim', explode(',', $email));
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