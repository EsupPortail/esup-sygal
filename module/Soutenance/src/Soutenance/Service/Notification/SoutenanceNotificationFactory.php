<?php

namespace Soutenance\Service\Notification;

use Application\Entity\Db\Role;
use Application\Entity\Db\Utilisateur;
use Application\Entity\Db\Validation;
use Application\Service\Email\EmailTheseServiceAwareTrait;
use Application\Service\Role\RoleServiceAwareTrait;
use Application\Service\Utilisateur\UtilisateurServiceAwareTrait;
use DateTime;
use Doctorant\Entity\Db\Doctorant;
use Individu\Entity\Db\Individu;
use Individu\Service\IndividuServiceAwareTrait;
use Notification\Exception\RuntimeException;
use Notification\Factory\NotificationFactory;
use Notification\Notification;
use Soutenance\Entity\Avis;
use Soutenance\Entity\Membre;
use Soutenance\Entity\Proposition;
use Soutenance\Provider\Template\MailTemplates;
use Soutenance\Service\Membre\MembreServiceAwareTrait;
use Soutenance\Service\Url\UrlServiceAwareTrait;
use These\Entity\Db\These;
use These\Service\Acteur\ActeurServiceAwareTrait;
use These\Service\These\TheseServiceAwareTrait;
use UnicaenAuth\Entity\Db\RoleInterface;
use UnicaenRenderer\Service\Rendu\RenduServiceAwareTrait;

/**
 * Classe de construction de notifications par mail.
 *
 * @author Unicaen
 */
class SoutenanceNotificationFactory extends NotificationFactory
{
    use ActeurServiceAwareTrait;
    use EmailTheseServiceAwareTrait;
    use IndividuServiceAwareTrait;
    use MembreServiceAwareTrait;
    use RoleServiceAwareTrait;
    use TheseServiceAwareTrait;
    use EmailTheseServiceAwareTrait;
    use UtilisateurServiceAwareTrait;
    use RenduServiceAwareTrait;
    use UrlServiceAwareTrait;

    /**
     * @return string[]
     */
    protected function getEmailAdministrateurTechnique(): array
    {
        $individus = $this->individuService->getRepository()->findByRole(Role::CODE_ADMIN_TECH);
        $emails = [];
        foreach ($individus as $individu) {
            $email = $individu->getEmailUtilisateur();
            if ($email) $emails[] = $email;
        }
        return $emails;
    }

    /**
     * @param Validation $validation
     * @return \Notification\Notification
     * @see Application/view/soutenance/notification/devalidation.phtml
     */
    public function createNotificationDevalidationProposition(Validation $validation): Notification
    {
        $mail = $validation->getIndividu()->getEmailPro();
        $these = $validation->getThese();

        if (!$mail) {
            throw new RuntimeException("Aucune adresse mail trouvée pour l'individu {$validation->getIndividu()}");
        }

        $notif = new Notification();
        $notif
            ->setSubject("Votre validation de la proposition de soutenance a été annulée")
            ->setTo($mail)
            ->setTemplatePath('soutenance/notification/devalidation')
            ->setTemplateVariables([
                'validation' => $validation,
                'these' => $these,
            ]);

        return $notif;
    }

    /**
     * @param These $these
     * @param Validation $validation
     * @return \Notification\Notification
     * @see Application/view/soutenance/notification/validation-acteur.phtml
     */
    public function createNotificationValidationProposition(These $these, Validation $validation): Notification
    {
        $emails = $this->emailTheseService->fetchEmailActeursDirects($these);

        $emails = array_filter($emails, function ($s) {
            return $s !== null;
        });

        if (empty($emails)) {
            throw new RuntimeException("Aucune adresse mail trouvée pour les acteurs directs de la thèse {$these->getId()}");
        }

        $notif = new Notification();
        $notif
            ->setSubject("Une validation de votre proposition de soutenance vient d'être faite")
            ->setTo($emails)
            ->setTemplatePath('soutenance/notification/validation-acteur')
            ->setTemplateVariables([
                'validation' => $validation,
                'these' => $these,
            ]);

        return $notif;
    }

    /**
     * @param These $these
     * @return \Notification\Notification
     * @see Application/view/soutenance/notification/validation-structure.phtml
     */
    public function createNotificationUniteRechercheProposition(These $these): Notification
    {
        $individuRoles = $this->roleService->findIndividuRoleByStructure($these->getUniteRecherche()->getStructure());
        $panic = !($this->emailTheseService->hasEmailsByEtablissement($individuRoles, $these));
        $emails = $this->emailTheseService->fetchEmailsByEtablissement($individuRoles, $these);
        //$emails = $this->emailService->fetchEmailUniteRecherche($these);

        if (!empty($emails)) {
            $notif = new Notification();
            $notif
                ->setSubject("Demande de validation d'une proposition de soutenance")
                ->setTo($emails)
                ->setTemplatePath('soutenance/notification/validation-structure')
                ->setTemplateVariables([
                    'these' => $these,
                    'type' => 'unité de recherche',
                    'panic' => $panic,
                ]);

            return $notif;
        } else {
            $emailsAdmin = $this->getEmailAdministrateurTechnique();
            $emailsMdd = $this->emailTheseService->fetchEmailMaisonDuDoctorat($these);
            $emails = array_merge($emailsAdmin, $emailsMdd);

            $notif = new Notification();
            $notif
                ->setSubject("ATTENTION MAIL NON DÉLIVRÉ : Demande de validation d'une proposition de soutenance")
                ->setTo($emails)
                ->setTemplatePath('soutenance/notification/validation-structure')
                ->setTemplateVariables([
                    'these' => $these,
                    'type' => 'unité de recherche',
                    'panic' => true,
                ]);

            return $notif;
        }
    }

    /**
     * @param These $these
     * @return \Notification\Notification
     * @see Application/view/soutenance/notification/validation-structure.phtml
     */
    public function createNotificationEcoleDoctoraleProposition(These $these): Notification
    {
        $individuRoles = $this->roleService->findIndividuRoleByStructure($these->getEcoleDoctorale()->getStructure());
        $panic = !($this->emailTheseService->hasEmailsByEtablissement($individuRoles, $these));
        $emails = $this->emailTheseService->fetchEmailsByEtablissement($individuRoles, $these);
        //$emails = $this->emailService->fetchEmailEcoleDoctorale($these);

        if (!empty($emails)) {
            $notif = new Notification();
            $notif
                ->setSubject("Demande de validation d'une proposition de soutenance")
                ->setTo($emails)
                ->setTemplatePath('soutenance/notification/validation-structure')
                ->setTemplateVariables([
                    'these' => $these,
                    'type' => 'école doctorale',
                    'panic' => $panic,
                ]);

            return $notif;
        } else {
            $emailsAdmin = $this->getEmailAdministrateurTechnique();
            $emailsMdd = $this->emailTheseService->fetchEmailMaisonDuDoctorat($these);
            $emails = array_merge($emailsAdmin, $emailsMdd);

            $notif = new Notification();
            $notif
                ->setSubject("ATTENTION MAIL NON DÉLIVRÉ : Demande de validation d'une proposition de soutenance")
                ->setTo($emails)
                ->setTemplatePath('soutenance/notification/validation-structure')
                ->setTemplateVariables([
                    'these' => $these,
                    'type' => 'école doctorale',
                    'panic' => true,
                ]);

            return $notif;
        }
    }

    /**
     * @param These $these
     * @return \Notification\Notification
     * @see Application/view/soutenance/notification/validation-structure.phtml
     */
    public function createNotificationBureauDesDoctoratsProposition(These $these): Notification
    {
        $email = $this->emailTheseService->fetchEmailMaisonDuDoctorat($these);

        if ($email !== null) {
            $notif = new Notification();
            $notif
                ->setSubject("Demande de validation d'une proposition de soutenance")
                ->setTo($email)
                ->setTemplatePath('soutenance/notification/validation-structure')
                ->setTemplateVariables([
                    'these' => $these,
                    'type' => 'maison du doctorat',
                    'panic' => false,
                ]);

            return $notif;
        } else {
            $emailsAdmin = $this->getEmailAdministrateurTechnique();
            $emailsMdd = $this->emailTheseService->fetchEmailMaisonDuDoctorat($these);
            $emails = array_merge($emailsAdmin, $emailsMdd);

            $notif = new Notification();
            $notif
                ->setSubject("ATTENTION MAIL NON DÉLIVRÉ : Demande de validation d'une proposition de soutenance")
                ->setTo($email)
                ->setTemplatePath('soutenance/notification/validation-structure')
                ->setTemplateVariables([
                    'these' => $these,
                    'type' => 'maison du doctorat',
                    'panic' => true,
                ]);

            return $notif;
        }
    }

    /**
     * @param \These\Entity\Db\These $these
     * @return \Notification\Notification
     */
    public function createNotificationPropositionValidee(These $these): Notification
    {
        $emailsBDD = $this->emailTheseService->fetchEmailMaisonDuDoctorat($these);
        $emailsED = $this->emailTheseService->fetchEmailEcoleDoctorale($these);
        $emailsUR = $this->emailTheseService->fetchEmailUniteRecherche($these);
        $emailsActeurs = $this->emailTheseService->fetchEmailActeursDirects($these);
        $emails = array_merge($emailsBDD, $emailsED, $emailsUR, $emailsActeurs);

        $emails = array_filter($emails, function ($s) {
            return $s !== null;
        });

        if (!empty($emails)) {
            $notif = new Notification();
            $notif
                ->setSubject("Validation de proposition de soutenance de " . $these->getDoctorant()->getIndividu()->getNomComplet())
                ->setTo($emails)
                ->setTemplatePath('soutenance/notification/validation-soutenance')
                ->setTemplateVariables([
                    'these' => $these,
                ]);

            return $notif;
        } else {
            throw new RuntimeException("Aucun mail de disponible (" . __METHOD__ . "::TheseId#" . $these->getId() . ")");
        }
    }

    /**
     * @param $these
     * @return \Notification\Notification
     */
    public function createNotificationPresoutenance($these): Notification
    {
        $email = $this->emailTheseService->fetchEmailMaisonDuDoctorat($these);

        if ($email !== null) {
            $notif = new Notification();
            $notif
                ->setSubject("Vous pouvez procéder au renseignement des informations de soutenance")
                ->setTo($email)
                ->setTemplatePath('soutenance/notification/presoutenance')
                ->setTemplateVariables([
                    'these' => $these,
                ]);

            return $notif;
        } else {
            throw new RuntimeException("Aucun mail de disponible (" . __METHOD__ . "::TheseId#" . $these->getId() . ")");
        }
    }

    /**
     * @param These $these
     * @param Individu $currentUser
     * @param RoleInterface $currentRole
     * @param string $motif
     * @return \Notification\Notification
     */
    public function createNotificationRefusPropositionSoutenance($these, $currentUser, $currentRole, $motif): Notification
    {
        $emails = $this->emailTheseService->fetchEmailActeursDirects($these);

        $emails = array_filter($emails, function ($s) {
            return $s !== null;
        });

        if (!empty($emails)) {
            $notif = new Notification();
            $notif
                ->setSubject("Votre proposition de soutenance a été réfusée")
                ->setTo($emails)
                ->setTemplatePath('soutenance/notification/refus')
                ->setTemplateVariables([
                    'acteur' => $currentUser,
                    'role' => $currentRole,
                    'motif' => $motif,
                    'these' => $these,
                ]);

            return $notif;
        } else {
            throw new RuntimeException("Aucun mail de disponible (" . __METHOD__ . "::TheseId#" . $these->getId() . ")");
        }

    }


    /** ENGAGEMENT IMPARTIALITE ***************************************************************************************/

    /**
     * @param These $these
     * @param Membre $membre
     * @return \Notification\Notification
     */
    public function createNotificationDemandeSignatureEngagementImpartialite(These $these, Membre $membre): Notification
    {
        $vars = ['these' => $these, 'doctorant' => $these->getDoctorant(), 'rapporteur' => $membre];
        $url = $this->getUrlService()->setVariables($vars);
        $vars['Url'] = $url;

        $rendu = $this->getRenduService()->generateRenduByTemplateCode(MailTemplates::DEMANDE_ENGAGEMENT_IMPARTIALITE, $vars);
        $mail = $membre->getEmail();
        if ($mail === null) {
            throw new RuntimeException("Aucun mail trouvé pour le rapporteur");
        }

        $notif = new Notification();
        $notif
            ->setSubject($rendu->getSujet())
            ->setTo($mail)
            ->setBody($rendu->getCorps());

        return $notif;
    }

    /**
     * @param These $these
     * @param Proposition $proposition
     * @param Membre $membre
     */
    public function createNotificationSignatureEngagementImpartialite($these, $proposition, $membre): Notification
    {
        $email = $this->emailTheseService->fetchEmailMaisonDuDoctorat($these);

        if ($email !== null) {
            $notif = new Notification();
            $notif
                ->setSubject("Signature de l'engagement d'impartialité de la thèse de " . $these->getDoctorant()->getIndividu())
                ->setTo($email)
                ->setTemplatePath('soutenance/notification/engagement-impartialite-signature')
                ->setTemplateVariables([
                    'these' => $these,
                    'proposition' => $proposition,
                    'membre' => $membre,
                ]);

            return $notif;
        } else {
            throw new RuntimeException("Aucun mail de disponible (" . __METHOD__ . "::TheseId#" . $these->getId() . ")");
        }

    }

    /**
     * @param These $these
     * @param Proposition $proposition
     * @param Membre $membre
     */
    public function createNotificationRefusEngagementImpartialite($these, $proposition, $membre): Notification
    {
        $emailsAD = $this->emailTheseService->fetchEmailActeursDirects($these);
        $emailsBDD = $this->emailTheseService->fetchEmailMaisonDuDoctorat($these);
        $emails = array_merge($emailsAD, $emailsBDD);

        $emails = array_filter($emails, function ($s) {
            return $s !== null;
        });

        if (!empty($emails)) {
            $notif = new Notification();
            $notif
                ->setSubject("Refus de l'engagement d'impartialité de la thèse de " . $these->getDoctorant()->getIndividu())
                ->setTo($emails)
                ->setTemplatePath('soutenance/notification/engagement-impartialite-refus')
                ->setTemplateVariables([
                    'these' => $these,
                    'proposition' => $proposition,
                    'membre' => $membre,
                ]);

            return $notif;
        } else {
            throw new RuntimeException("Aucun mail de disponible (" . __METHOD__ . "::TheseId#" . $these->getId() . ")");
        }

    }

    /**
     * @param These $these
     * @param Proposition $proposition
     * @param Membre $membre
     */
    public function createNotificationAnnulationEngagementImpartialite($these, $proposition, $membre): Notification
    {
        $email = $membre->getEmail();

        if ($email) {
            $notif = new Notification();
            $notif
                ->setSubject("Annulation de la signature de l'engagement d'impartialité de la thèse de " . $these->getDoctorant()->getIndividu())
                ->setTo($email)
                ->setTemplatePath('soutenance/notification/engagement-impartialite-annulation')
                ->setTemplateVariables([
                    'these' => $these,
                    'proposition' => $proposition,
                    'membre' => $membre,
                ]);

            return $notif;
        } else {
            throw new RuntimeException("Aucun mail de disponible (" . __METHOD__ . "::TheseId#" . $these->getId() . ")");
        }

    }


    /**************************** avis ***************************/

    /**
     * @param These $these
     */
    public function createNotificationAvisRendus($these): Notification
    {
        $email = $this->emailTheseService->fetchEmailMaisonDuDoctorat($these);

        if ($email !== null) {
            $notif = new Notification();
            $notif
                ->setSubject("Tous les avis de soutenance de la thèse de " . $these->getDoctorant()->getIndividu() . " ont été rendus.")
                ->setTo($email)
                ->setTemplatePath('soutenance/notification/tous-avis-soutenance')
                ->setTemplateVariables([
                    'these' => $these,
                ]);

            return $notif;
        } else {
            throw new RuntimeException("Aucun mail de disponible (" . __METHOD__ . "::TheseId#" . $these->getId() . ")");
        }

    }

    /**
     * @param These $these
     * @param Avis $avis
     * @param string $url
     */
    public function createNotificationAvisFavorable($these, $avis, $url = null): Notification
    {
        $emailBDD = $this->emailTheseService->fetchEmailMaisonDuDoctorat($these);
        $emailsDirecteurs = $this->emailTheseService->fetchEmailEncadrants($these);
        $emailsED = $this->emailTheseService->fetchEmailEcoleDoctorale($these);
        $emailsUR = $this->emailTheseService->fetchEmailUniteRecherche($these);
        $emails = array_merge($emailBDD, $emailsDirecteurs, $emailsED, $emailsUR);

        $emails = array_filter($emails, function ($s) {
            return $s !== null;
        });

        if ($emails !== []) {
            $notif = new Notification();
            $notif
                ->setSubject("Un avis de soutenance favorable de la thèse de " . $these->getDoctorant()->getIndividu() . " a été rendue.")
                ->setTo($emails)
                ->setTemplatePath('soutenance/notification/avis-favorable')
                ->setTemplateVariables([
                    'these' => $these,
                    'avis' => $avis,
                    'url' => $url,
                ]);

            return $notif;
        } else {
            throw new RuntimeException("Aucun mail de disponible (" . __METHOD__ . "::TheseId#" . $these->getId() . ")");
        }

    }

    /**
     * @param These $these
     * @param Avis $avis
     * @param string $url
     */
    public function createNotificationAvisDefavorable($these, $avis, $url = null): Notification
    {
        $emailsDirecteurs = $this->emailTheseService->fetchEmailEncadrants($these);
        $emailsED = $this->emailTheseService->fetchEmailEcoleDoctorale($these);
        $emailsUR = $this->emailTheseService->fetchEmailUniteRecherche($these);
        $emails = array_merge($emailsDirecteurs, $emailsED, $emailsUR);

        $emails = array_filter($emails, function ($s) {
            return $s !== null;
        });

        if ($emails !== []) {
            $notif = new Notification();
            $notif
                ->setSubject("Un avis de soutenance défavorable de la thèse de " . $these->getDoctorant()->getIndividu() . " a été rendue.")
                ->setTo($emails)
                ->setTemplatePath('soutenance/notification/avis-defavorable')
                ->setTemplateVariables([
                    'these' => $these,
                    'avis' => $avis,
                    'url' => $url,
                ]);

            return $notif;
        } else {
            throw new RuntimeException("Aucun mail de disponible (" . __METHOD__ . "::TheseId#" . $these->getId() . ")");
        }

    }


    /**************************** présoutenance ***************************/

    /**
     * @param These $these
     * @param Proposition $proposition
     * @param Membre $rapporteur
     * @param string $url
     * @return \Notification\Notification
     */
    public function createNotificationDemandeAvisSoutenance(These $these, Proposition $proposition, Membre $rapporteur, string $url): Notification
    {
        $email = $rapporteur->getEmail();

        if ($email !== null) {
            $notif = new Notification();
            $notif
                ->setSubject("Demande de l'avis de soutenance de la thèse de " . $these->getDoctorant()->getIndividu())
                ->setTo($email)
                ->setTemplatePath('soutenance/notification/demande-avis-soutenance')
                ->setTemplateVariables([
                    'these' => $these,
                    'proposition' => $proposition,
                    'membre' => $rapporteur,
                    'url' => $url,
                ]);

            return $notif;
        } else {
            throw new RuntimeException("Aucun mail de disponible (" . __METHOD__ . "::TheseId#" . $these->getId() . ")");
        }
    }

    /**
     * @param These $these
     * @param Proposition $proposition
     * @param Avis[] $avis
     */
    public function createNotificationFeuVertSoutenance($these, $proposition, $avis): Notification
    {
        $emailsActeurs = $this->emailTheseService->fetchEmailActeursDirects($these);
        $emailsED = $this->emailTheseService->fetchEmailEcoleDoctorale($these);
        $emailsUR = $this->emailTheseService->fetchEmailUniteRecherche($these);
        $emails = array_merge($emailsActeurs, $emailsED, $emailsUR);

        $emails = array_filter($emails, function ($s) {
            return $s !== null;
        });

        if (!empty($emails)) {
            $notif = new Notification();
            $notif
                ->setSubject("La soutenance de " . $these->getDoctorant()->getIndividu() . " a été acceptée par votre établissement.")
                ->setTo($emails)
                ->setTemplatePath('soutenance/notification/feu-vert-soutenance')
                ->setTemplateVariables([
                    'these' => $these,
                    'proposition' => $proposition,
                    'avis' => $avis,
                ]);

            return $notif;
        } else {
            throw new RuntimeException("Aucun mail de disponible (" . __METHOD__ . "::TheseId#" . $these->getId() . ")");
        }
    }

    /**
     * @param These $these
     * @param Proposition $proposition
     */
    public function createNotificationStopperDemarcheSoutenance($these, $proposition): Notification
    {
        $emailsActeurs = $this->emailTheseService->fetchEmailActeursDirects($these);
        $emailsED = $this->emailTheseService->fetchEmailEcoleDoctorale($these);
        $emailsUR = $this->emailTheseService->fetchEmailUniteRecherche($these);
        $emails = array_merge($emailsActeurs, $emailsED, $emailsUR);

        $emails = array_filter($emails, function ($s) {
            return $s !== null;
        });

        if (!empty($emails)) {
            $notif = new Notification();
            $notif
                ->setSubject("Les démarches de soutenance de " . $these->getDoctorant()->getIndividu() . " ont été stoppées par la maison du doctorats de votre établissement.")
                ->setTo($emails)
                ->setTemplatePath('soutenance/notification/stopper-demarche-soutenance')
                ->setTemplateVariables([
                    'these' => $these,
                    'proposition' => $proposition,
                ]);

            return $notif;
        } else {
            throw new RuntimeException("Aucun mail de disponible (" . __METHOD__ . "::TheseId#" . $these->getId() . ")");
        }
    }

    /**
     * @param Proposition $proposition
     * @param Utilisateur $user
     * @param string $url
     * @return \Notification\Notification
     */
    public function createNotificationConnexionRapporteur(Proposition $proposition, Utilisateur $user, string $url): Notification
    {
        $email = $user->getEmail();
        if ($email === null) {
            throw new RuntimeException("Aucun email de fourni !");
        }

        if (!empty($email)) {
            $notif = new Notification();
            $notif
                ->setSubject("Connexion en tant que rapporteur de la thèse de " . $proposition->getThese()->getDoctorant()->getIndividu()->getNomComplet())
                ->setTo($email)
                ->setTemplatePath('soutenance/notification/connexion-rapporteur')
                ->setTemplateVariables([
                    'proposition' => $proposition,
                    'these' => $proposition->getThese(),
                    'username' => $user->getUsername(),
                    'url' => $url,
                ]);

            return $notif;
        } else {
            throw new RuntimeException("Aucun mail de disponible (" . __METHOD__ . "::TheseId#" . $proposition->getThese()->getId() . ")");
        }
    }

    /**
     * @param Membre $membre
     * @param string $url
     */
    public function createNotificationNotificationRapporteurRetard($membre, $url): Notification
    {
        if ($membre->getActeur() === null) {
            throw new RuntimeException("Notification vers rapporteur [MembreId = " . $membre->getId() . "] impossible car aucun acteur n'est lié.");
        }

        $email = $membre->getEmail();
        if ($email === null) {
            throw new RuntimeException("Notification vers rapporteur [MembreId = " . $membre->getId() . "] impossible car aucun email est donné pour l'individu associé [IndividuId = " . $membre->getIndividu()->getId() . "].");
        }

        $these = $membre->getProposition()->getThese();
        $doctorant = $these->getDoctorant()->getIndividu();

        if (!empty($email)) {
            $notif = new Notification();
            $notif
                ->setSubject("Demande de rapport de présoutenance pour la thèse de " . $doctorant->getNomComplet())
                ->setTo($email)
                ->setTemplatePath('soutenance/notification/retard-rapporteur')
                ->setTemplateVariables([
                    'these' => $these,
                    'doctorant' => $doctorant,
                    'proposition' => $membre->getProposition(),
                    'url' => $url,
                ]);

            return $notif;
        } else {
            throw new RuntimeException("Aucun mail de disponible (" . __METHOD__ . "::TheseId#" . $these->getId() . ")");
        }
    }

    /**
     * @param Doctorant $doctorant
     * @param Proposition $proposition
     * @param DateTime $date
     * @param string $email
     * @param string $url
     * @param array $avisArray
     * @return \Notification\Notification
     */
    public function createNotificationEnvoiConvocationDoctorant(Doctorant $doctorant, Proposition $proposition, DateTime $date, string $email, string $url, array $avisArray): Notification
    {
        $these = $proposition->getThese();
        $pdcData = $this->getTheseService()->fetchInformationsPageDeCouverture($these);

        $notif = new Notification();
        $notif
            ->setSubject("Convocation pour la soutenance de thèse de  " . $doctorant->getNomComplet())
            ->setTo($email)
            ->setTemplatePath('soutenance/notification/convocation-doctorant')
            ->setTemplateVariables([
                'these' => $proposition->getThese(),
                'proposition' => $proposition,
                'doctorant' => $doctorant,
                'date' => $date,
                'url' => $url,
                'informations' => $pdcData,

                'avisArray' => $avisArray,

            ]);

        return $notif;
    }

    /**
     * @param Membre $membre
     * @param Proposition $proposition
     * @param DateTime $date
     * @param string $email
     * @param string $url
     * @param array $avisArray
     * @return \Notification\Notification
     */
    public function createNotificationEnvoiConvocationMembre(Membre $membre, Proposition $proposition, DateTime $date, string $email, string $url, array $avisArray): Notification
    {
        $doctorant = $proposition->getThese()->getDoctorant();
        $these = $proposition->getThese();
        $pdcData = $this->getTheseService()->fetchInformationsPageDeCouverture($these);

        $notif = new Notification();
        $notif
            ->setSubject("Convocation pour la soutenance de thèse de  " . $doctorant->getNomComplet())
            ->setTo($email)
            ->setTemplatePath('soutenance/notification/convocation-membre')
            ->setTemplateVariables([
                'these' => $these,
                'proposition' => $proposition,
                'informations' => $pdcData,
                'date' => $date,
                'membre' => $membre,
                'url' => $url,

                'avisArray' => $avisArray,
            ]);

        return $notif;
    }

    public function createNotificationTransmettreDocumentsDirectionThese(These $these, Proposition $proposition) : Notification
    {
        $vars = ['these' => $these, 'proposition' => $proposition, 'doctorant' => $these->getDoctorant()];
        $url = $this->getUrlService()->setVariables($vars);
        $vars['Url'] = $url;

        $rendu = $this->getRenduService()->generateRenduByTemplateCode(MailTemplates::TRANSMETTRE_DOCUMENTS_DIRECTION, $vars);
        $mail = $these->getDirecteursTheseEmails();
        if (count($mail) === 0) {
            throw new RuntimeException("Aucun mail trouvés pour les directeurs de thèse");
        }

        $notif = new Notification();
        $notif
            ->setSubject($rendu->getSujet())
            ->setTo($mail)
            ->setBody($rendu->getCorps());

        return $notif;
    }
}