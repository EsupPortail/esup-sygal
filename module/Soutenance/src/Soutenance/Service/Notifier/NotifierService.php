<?php

namespace Soutenance\Service\Notifier;

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
use InvalidArgumentException;
use Laminas\View\Helper\Url as UrlHelper;
use Notification\Exception\NotificationException;
use Notification\Notification;
use Soutenance\Entity\Avis;
use Soutenance\Entity\Membre;
use Soutenance\Entity\Proposition;
use Soutenance\Service\Membre\MembreServiceAwareTrait;
use These\Entity\Db\These;
use These\Service\Acteur\ActeurServiceAwareTrait;
use These\Service\These\TheseServiceAwareTrait;
use UnicaenApp\Exception\LogicException;
use UnicaenApp\Exception\RuntimeException;
use UnicaenAuth\Entity\Db\RoleInterface;

class NotifierService extends \Notification\Service\NotifierService
{
    use ActeurServiceAwareTrait;
    use EmailTheseServiceAwareTrait;
    use IndividuServiceAwareTrait;
    use MembreServiceAwareTrait;
    use RoleServiceAwareTrait;
    use TheseServiceAwareTrait;
    use EmailTheseServiceAwareTrait;
    use UtilisateurServiceAwareTrait;

    /**
     * @var UrlHelper
     */
    protected $urlHelper;

    public function setUrlHelper($urlHelper)
    {
        $this->urlHelper = $urlHelper;
    }

    /**
     * @return string[]
     */
    protected function getEmailAdministrateurTechnique() : array
    {
        $individus = $this->getIndividuService()->getRepository()->findByRole(Role::CODE_ADMIN_TECH);
        $emails = [];
        foreach ($individus as $individu) {
            $email = $individu->getEmailUtilisateur();
            if ($email) $emails[] = $email;
        }
        return $emails;
    }

    /**
     * @param Validation $validation
     * @see Application/view/soutenance/notification/devalidation.phtml
     */
    public function triggerDevalidationProposition($validation)
    {
        $mail = $validation->getIndividu()->getEmailPro();
        $these = $validation->getThese();

        if ($mail !== null) {
            $notif = new Notification();
            $notif
                ->setSubject("Votre validation de la proposition de soutenance a été annulée")
                ->setTo($mail)
                ->setTemplatePath('soutenance/notification/devalidation')
                ->setTemplateVariables([
                    'validation' => $validation,
                    'these' => $these,
                ]);
            $this->trigger($notif);
        }
    }

    /**
     * @param These $these
     * @param Validation $validation
     * @see Application/view/soutenance/notification/validation-acteur.phtml
     */
    public function triggerValidationProposition(These $these, Validation $validation)
    {
        $emails = $this->emailTheseService->fetchEmailActeursDirects($these);

        $emails = array_filter($emails, function ($s) {
            return $s !== null;
        });

        if (!empty($emails)) {
            $notif = new Notification();
            $notif
                ->setSubject("Une validation de votre proposition de soutenance vient d'être faite")
                ->setTo($emails)
                ->setTemplatePath('soutenance/notification/validation-acteur')
                ->setTemplateVariables([
                    'validation' => $validation,
                    'these' => $these,
                ]);
            $this->trigger($notif);
        }
    }

    /**
     * @param These $these
     * @see Application/view/soutenance/notification/validation-structure.phtml
     * @throws NotificationException
     */
    public function triggerNotificationUniteRechercheProposition(These $these)
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
            $this->trigger($notif);
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
            $this->trigger($notif);
        }
    }

    /**
     * @param These $these
     * @see Application/view/soutenance/notification/validation-structure.phtml
     */
    public function triggerNotificationEcoleDoctoraleProposition(These $these)
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
            $this->trigger($notif);
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
            $this->trigger($notif);
        }
    }

    /**
     * @param These $these
     * @see Application/view/soutenance/notification/validation-structure.phtml
     */
    public function triggerNotificationBureauDesDoctoratsProposition(These $these)
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
            $this->trigger($notif);
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
            $this->trigger($notif);
        }
    }

    /** @param These $these */
    public function triggerNotificationPropositionValidee(These $these)
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
                ->setSubject("Validation de proposition de soutenance de ".$these->getDoctorant()->getIndividu()->getNomComplet())
                ->setTo($emails)
                ->setTemplatePath('soutenance/notification/validation-soutenance')
                ->setTemplateVariables([
                    'these' => $these,
                ]);
            $this->trigger($notif);
        } else {
            throw new InvalidArgumentException("Aucun mail de disponible (".__METHOD__."::TheseId#".$these->getId().")");
        }
    }

    /** @param These $these */
    public function triggerNotificationPresoutenance($these)
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
            $this->trigger($notif);
        } else {
            throw new InvalidArgumentException("Aucun mail de disponible (".__METHOD__."::TheseId#".$these->getId().")");
        }
    }

    /**
     * @param These $these
     * @param Individu $currentUser
     * @param RoleInterface $currentRole
     * @param string $motif
     */
    public function triggerRefusPropositionSoutenance($these, $currentUser, $currentRole, $motif)
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
            $this->trigger($notif);
        } else {
            throw new InvalidArgumentException("Aucun mail de disponible (".__METHOD__."::TheseId#".$these->getId().")");
        }

    }

    /** ENGAGEMENT IMPARTIALITE ***************************************************************************************/

    /**
     * @param These $these
     * @param Proposition $proposition
     * @param Membre $membre
     * @param string $url
     */
    public function triggerDemandeSignatureEngagementImpartialite(These $these, Proposition $proposition, Membre $membre, string $url)
    {
        $email = $membre->getEmail();

        if ($email !== null) {
            $notif = new Notification();
            $notif
                ->setSubject("Demande de signature de l'engagement d'impartialité de la thèse de " . $these->getDoctorant()->getIndividu())
                ->setTo($email)
                ->setTemplatePath('soutenance/notification/engagement-impartialite-demande')
                ->setTemplateVariables([
                    'these' => $these,
                    'proposition' => $proposition,
                    'membre' => $membre,
                    'url' => $url,
                ]);
            $this->trigger($notif);
        } else {
            throw new InvalidArgumentException("Aucun mail de disponible (".__METHOD__."::TheseId#".$these->getId().")");
        }

    }

    /**
     * @param These $these
     * @param Proposition $proposition
     * @param Membre $membre
     */
    public function triggerSignatureEngagementImpartialite($these, $proposition, $membre)
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
            $this->trigger($notif);
        } else {
            throw new InvalidArgumentException("Aucun mail de disponible (".__METHOD__."::TheseId#".$these->getId().")");
        }

    }

    /**
     * @param These $these
     * @param Proposition $proposition
     * @param Membre $membre
     */
    public function triggerRefusEngagementImpartialite($these, $proposition, $membre)
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
            $this->trigger($notif);
        } else {
            throw new InvalidArgumentException("Aucun mail de disponible (".__METHOD__."::TheseId#".$these->getId().")");
        }

    }

    /**
     * @param These $these
     * @param Proposition $proposition
     * @param Membre $membre
     */
    public function triggerAnnulationEngagementImpartialite($these, $proposition, $membre)
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
            $this->trigger($notif);
        } else {
            throw new InvalidArgumentException("Aucun mail de disponible (".__METHOD__."::TheseId#".$these->getId().")");
        }

    }

    /**
     * @param These $these
     * @param Proposition $proposition
     * @param Membre $rapporteur
     * @param string $url
     */
    public function triggerDemandeAvisSoutenance(These $these, Proposition $proposition, Membre $rapporteur, string $url)
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
            $this->trigger($notif);
        } else {
            throw new InvalidArgumentException("Aucun mail de disponible (".__METHOD__."::TheseId#".$these->getId().")");
        }

    }

    /**
     * @param These $these
     */
    public function triggerAvisRendus($these)
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
            $this->trigger($notif);
        } else {
            throw new InvalidArgumentException("Aucun mail de disponible (".__METHOD__."::TheseId#".$these->getId().")");
        }

    }

    /**
     * @param These $these
     * @param Avis $avis
     * @param string $url
     */
    public function triggerAvisFavorable($these, $avis, $url = null)
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
            $this->trigger($notif);
        } else {
            throw new InvalidArgumentException("Aucun mail de disponible (".__METHOD__."::TheseId#".$these->getId().")");
        }

    }

    /**
     * @param These $these
     * @param Avis $avis
     * @param string $url
     */
    public function triggerAvisDefavorable($these, $avis, $url = null)
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
            $this->trigger($notif);
        } else {
            throw new InvalidArgumentException("Aucun mail de disponible (".__METHOD__."::TheseId#".$these->getId().")");
        }

    }


    /**
     * @param These $these
     * @param Proposition $proposition
     * @param Avis[] $avis
     */
    public function triggerFeuVertSoutenance($these, $proposition, $avis)
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
            $this->trigger($notif);
        } else {
            throw new InvalidArgumentException("Aucun mail de disponible (".__METHOD__."::TheseId#".$these->getId().")");
        }

    }

    /**
     * @param These $these
     * @param Proposition $proposition
     */
    public function triggerStopperDemarcheSoutenance($these, $proposition)
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
            $this->trigger($notif);
        } else {
            throw new InvalidArgumentException("Aucun mail de disponible (".__METHOD__."::TheseId#".$these->getId().")");
        }

    }

    /**
     * @param These $these
     * @param Utilisateur $utilisateur
     * @param string $url
     *
     * @deprecated Pas utilisée !
     */
    public function triggerInitialisationCompte($these, $utilisateur, $url)
    {

        $email = $utilisateur->getEmail();
        if ($email === null) throw new LogicException("Aucun email de fourni !");

        $token = $utilisateur->getPasswordResetToken();
        if ($token === null) throw new LogicException("Aucun token de fourni !");

        if (!empty($email)) {
            $notif = new Notification();
            $notif
                ->setSubject("Initialisation de votre compte pour la these de " . $these->getDoctorant()->getIndividu()->getNomComplet())
                ->setTo($email)
                ->setTemplatePath('soutenance/notification/init-compte')
                ->setTemplateVariables([
                    'these' => $these,
                    'username' => $utilisateur->getUsername(),
                    'url' => $url,
                ]);
            $this->trigger($notif);
        } else {
            throw new InvalidArgumentException("Aucun mail de disponible (".__METHOD__."::TheseId#".$these->getId().")");
        }

    }

    /**
     * @param Proposition $proposition
     * @param Utilisateur $user
     * @param string $url
     */
    public function triggerConnexionRapporteur(Proposition $proposition, Utilisateur $user, string $url)
    {
        $email = $user->getEmail();
        if ($email === null) throw new LogicException("Aucun email de fourni !");

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
            $this->trigger($notif);
        } else {
            throw new InvalidArgumentException("Aucun mail de disponible (".__METHOD__."::TheseId#".$proposition->getThese()->getId().")");
        }

    }

    /**
     * @param Membre $membre
     * @param string $url
     */
    public function triggerNotificationRapporteurRetard($membre, $url)
    {
        if ($membre->getActeur() === null) throw new RuntimeException("Notification vers rapporteur [MembreId = " . $membre->getId() . "] impossible car aucun acteur n'est lié.");

        $email = $membre->getEmail();
        if ($email === null) throw new RuntimeException("Notification vers rapporteur [MembreId = " . $membre->getId() . "] impossible car aucun email est donné pour l'individu associé [IndividuId = " . $membre->getIndividu()->getId() . "].");


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
            $this->trigger($notif);
        } else {
            throw new InvalidArgumentException("Aucun mail de disponible (".__METHOD__."::TheseId#".$these->getId().")");
        }

    }

    /**
     * @param Doctorant $doctorant
     * @param Proposition $proposition
     * @param DateTime $date
     * @param string $email
     * @param string $url
     * @param array $avisArray
     */
    public function triggerEnvoiConvocationDoctorant(Doctorant $doctorant, Proposition $proposition, DateTime $date, string $email, string $url, array $avisArray)
    {
        if ($email === null) throw new LogicException("Aucun mail n'est fourni pour l'envoi de la convocation.", 0);

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
        $this->trigger($notif);
    }

    /**
     * @param Membre $membre
     * @param Proposition $proposition
     * @param DateTime $date
     * @param string $email
     * @param string $url
     * @param array $avisArray
     */
    public function triggerEnvoiConvocationMembre(Membre $membre, Proposition $proposition, DateTime $date, string $email, string $url, array $avisArray)
    {
        if ($email === null) throw new LogicException("Aucun mail n'est fourni pour l'envoi de la convocation.", 0);

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
        $this->trigger($notif);
    }

}