<?php

namespace Application\Service\Email;

use Acteur\Service\ActeurHDR\ActeurHDRServiceAwareTrait;
use Application\Entity\Db\Role;
use Application\Service\Role\ApplicationRoleServiceAwareTrait;
use Application\Service\Utilisateur\UtilisateurServiceAwareTrait;
use Application\Service\Variable\VariableServiceAwareTrait;
use HDR\Entity\Db\HDR;
use Individu\Entity\Db\IndividuRole;
use Individu\Service\IndividuServiceAwareTrait;
use InvalidArgumentException;
use Soutenance\Service\Membre\MembreServiceAwareTrait;
use Acteur\Service\ActeurThese\ActeurTheseServiceAwareTrait;
use These\Entity\Db\These;

class EmailService
{
    use ApplicationRoleServiceAwareTrait;
    use VariableServiceAwareTrait;
    use UtilisateurServiceAwareTrait;
    use ActeurTheseServiceAwareTrait;
    use ActeurHDRServiceAwareTrait;
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
    public function fetchEmailEcoleDoctorale(These|HDR $entity) : array
    {
        $individuRoles = $this->applicationRoleService->findIndividuRoleByStructure(
            $entity->getEcoleDoctorale()->getStructure(), null, $entity->getEtablissement());

        return $this->collectEmailsFromIndividuRoles($individuRoles);
    }

    /**
     * @return string[]
     */
    public function fetchEmailUniteRecherche(These|HDR $entity) : array
    {
        $individuRoles = $this->applicationRoleService->findIndividuRoleByStructure(
            $entity->getUniteRecherche()->getStructure(), null, $entity->getEtablissement());

        return $this->collectEmailsFromIndividuRoles($individuRoles);
    }

    /**
     * Retourne les éventuelles adresses mails pour les aspects "Doctorat".
     *
     * @param These|HDR $entity
     * @return string[]
     */
    public function fetchEmailAspectsDoctorat(These|HDR $entity) : array
    {
        if ($email = $entity->getEtablissement()->getEmailDoctorat()) {
            return array_map('trim', explode(',', $email));
        }
        return [];
    }

    /**
     * Retourne les éventuelles adresses mails pour les aspects "Bibliothèque".
     *
     * @param These|HDR $entity
     * @return string[]
     */
    public function fetchEmailAspectsBibliotheque(These|HDR $entity) : array
    {
        if ($email = $entity->getEtablissement()->getEmailBibliotheque()) {
            return array_map('trim', explode(',', $email));
        }
        return [];
    }

    /**
     * @param These|HDR $entity
     * @return string[]
     */
    public function fetchEmailEncadrants(These|HDR $entity) : array
    {
        $emails = [];
        $acteurService = $entity instanceof These ? $this->acteurTheseService : $this->acteurHDRService;
        $encadrants = $entity instanceof These ?
            $acteurService->getRepository()->findEncadrementThese($entity) :
            $acteurService->getRepository()->findEncadrementHDR($entity);
        foreach ($encadrants as $encadrant) {
            //tentative dans individu
            $email = $encadrant->getIndividu()->getEmailPro();
            //tentative dans membre
            if ($email === null) {
                $membre = $encadrant->getMembre();
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
                throw new InvalidArgumentException("Pas de mail pour l'encadrant de thèse et/ou HDR [".$encadrant->getIndividu()->getNomComplet()."]");
            }
            $emails[] = $email;
        }
        return $emails;
    }

    /**
     * @param These|HDR $entity
     * @return array
     */
    public function fetchEmailActeursDirects(These|HDR $entity) : array
    {
        $emails = [];
        $emails[] = $entity->getApprenant()->getIndividu()->getEmailPro();

        $encadrants = $this->fetchEmailEncadrants($entity);
        foreach ($encadrants as $encadrant) {
            $emails[] = $encadrant;
        }
        return $emails;
    }

    /**
     * @return string[]
     */
    public function fetchEmailGestionnairesHDR(HDR $entity) : array
    {
        $individuRoles = $this->applicationRoleService->findIndividuRoleByStructure(
            $entity->getEtablissement()->getStructure(), Role::CODE_GEST_HDR, $entity->getEtablissement());

        return $this->collectEmailsFromIndividuRoles($individuRoles);
    }
}