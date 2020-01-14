<?php

namespace Soutenance\Controller\Presoutenance;


use Application\Controller\AbstractController;
use Application\Entity\Db\Acteur;
use Application\Entity\Db\Profil;
use Application\Entity\Db\TypeValidation;
use Application\Entity\Db\Utilisateur;
use Application\Entity\Db\Validation;
use Application\Service\Acteur\ActeurServiceAwareTrait;
use Application\Service\FichierThese\PdcData;
use Application\Service\Individu\IndividuServiceAwareTrait;
use Application\Service\Role\RoleServiceAwareTrait;
use Application\Service\These\TheseServiceAwareTrait;
use Application\Service\Utilisateur\UtilisateurServiceAwareTrait;
use DateInterval;
use Exception;
use Soutenance\Entity\Avis;
use Soutenance\Entity\Etat;
use Soutenance\Entity\Membre;
use Soutenance\Form\AdresseSoutenance\AdresseSoutenanceFormAwareTrait;
use Soutenance\Form\DateRenduRapport\DateRenduRapportForm;
use Soutenance\Form\DateRenduRapport\DateRenduRapportFormAwareTrait;
use Soutenance\Service\Avis\AvisServiceAwareTrait;
use Soutenance\Service\EngagementImpartialite\EngagementImpartialiteServiceAwareTrait;
use Soutenance\Service\Exporter\AvisSoutenance\AvisSoutenancePdfExporter;
use Soutenance\Service\Exporter\Convocation\ConvocationPdfExporter;
use Soutenance\Service\Membre\MembreServiceAwareTrait;
use Soutenance\Service\Notifier\NotifierSoutenanceServiceAwareTrait;
use Soutenance\Service\Parametre\ParametreServiceAwareTrait;
use Soutenance\Service\Exporter\ProcesVerbal\ProcesVerbalSoutenancePdfExporter;
use Soutenance\Service\Proposition\PropositionServiceAwareTrait;
use Soutenance\Service\Validation\ValidatationServiceAwareTrait;
use UnicaenApp\Exception\LogicException;
use UnicaenApp\Exception\RuntimeException;
use UnicaenAuth\Service\Traits\UserServiceAwareTrait;
use Zend\Http\Request;
use Zend\View\Model\ViewModel;
use Zend\View\Renderer\PhpRenderer;

class PresoutenanceController extends AbstractController
{
    use TheseServiceAwareTrait;
    use MembreServiceAwareTrait;
    use IndividuServiceAwareTrait;
    use NotifierSoutenanceServiceAwareTrait;
    use PropositionServiceAwareTrait;
    use ActeurServiceAwareTrait;
    use ValidatationServiceAwareTrait;
    use RoleServiceAwareTrait;
    use AvisServiceAwareTrait;
    use UtilisateurServiceAwareTrait;
    use UserServiceAwareTrait;
    use ParametreServiceAwareTrait;
    use EngagementImpartialiteServiceAwareTrait;

    use DateRenduRapportFormAwareTrait;
    use AdresseSoutenanceFormAwareTrait;

    public function presoutenanceAction()
    {
        $these = $this->requestedThese();
        $proposition = $this->getPropositionService()->findByThese($these);
        $rapporteurs = $this->getPropositionService()->getRapporteurs($proposition);

        /** Si la proposition ne possède pas encore de date de rendu de rapport alors la valeur par défaut est donnée */
        $renduRapport = $proposition->getRenduRapport();
        if (!$renduRapport) {
            if ($proposition->getDate() === null) throw new RuntimeException("Aucune date de soutenance de renseignée !");
            try {
                $renduRapport = $proposition->getDate();
                $deadline = $this->getParametreService()->getParametreByCode('AVIS_DEADLINE')->getValeur();
                $renduRapport = $renduRapport->sub(new DateInterval('P'. $deadline.'D'));
            } catch (Exception $e) {
                throw new RuntimeException("Un problème a été rencontré lors du calcul de la date de rendu des rapport.");
            }
            $proposition->setRenduRapport($renduRapport);
            $this->getPropositionService()->update($proposition);
        }

        /** Recupération des engagements d'impartialité  et des avis de soutenance */
        /** ==> clef: Membre->getActeur()->getIndividu()->getId() <== */
        $engagements = $this->getEngagementImpartialiteService()->getEngagmentsImpartialiteByThese($these);
        $avis = $this->getAvisService()->getAvisByThese($these);

        $validationBDD = $this->getValidationService()->getRepository()->findValidationByCodeAndThese(TypeValidation::CODE_VALIDATION_PROPOSITION_BDD, $these) ;
        $validationPDC = $this->getValidationService()->getRepository()->findValidationByCodeAndThese(TypeValidation::CODE_PAGE_DE_COUVERTURE, $these) ;

        return new ViewModel([
            'these'                 => $these,
            'proposition'           => $proposition,
            'rapporteurs'           => $rapporteurs,
            'engagements'           => $engagements,
            'avis'                  => $avis,
            'tousLesEngagements'    => count($engagements) === count($rapporteurs),
            'tousLesAvis'           => count($avis) === count($rapporteurs),
            'urlFichierThese'       => $this->urlFichierThese(),
            'validationBDD'         => $validationBDD,
            'validationPDC'         => $validationPDC,

            'deadline' => $this->getParametreService()->getParametreByCode('AVIS_DEADLINE')->getValeur(),
        ]);
    }

    public function dateRenduRapportAction()
    {
        $these = $this->requestedThese();
        $proposition = $this->getPropositionService()->findByThese($these);

        /** @var DateRenduRapportForm $form */
        $form = $this->getDateRenduRapportForm();
        $form->setAttribute('action', $this->url()->fromRoute('soutenance/presoutenance/date-rendu-rapport', ['these' => $these->getId()], [], true));
        $form->bind($proposition);

        /** @var Request $request */
        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            $form->setData($data);
            if ($form->isValid()) {
                $this->getPropositionService()->update($proposition);
            }
        }

        $vm = new ViewModel();
        $vm->setTemplate('soutenance/default/default-form');
        $vm->setVariables([
                'form' => $form,
                'title' => 'Modification de la date de rendu des rapports',
        ]);
        return $vm;
    }

    /**
     * Ici on affecte au membre des acteurs qui remonte des SIs des établissements
     * Puis on affecte les rôles rapporteurs et membres
     * QUID :: Président ...
     */
    public function associerJuryAction()
    {
        $these = $this->requestedThese();
        $proposition = $this->getPropositionService()->findByThese($these);

        /** @var Membre[] $membres */
        $membres = $proposition->getMembres();
        $membre = $this->getMembreService()->getRequestedMembre($this);

        /** Ici on prépare la liste des acteurs correspondant aux différents rôles pour le Select du formulaire
         *  d'association. On part du principe :
         *  - qu'un Rapporteur du jury est Rapporteur et Membre du jury,
         *  - qu'un Rapporteur absent  est Rapporteur,
         *  - qu'un Membre du jury     est Membre du jury.
         */
        $acteurs = $this->getActeurService()->getRepository()->findActeurByThese($these);
        switch($membre->getRole()) {
            case Membre::RAPPORTEUR_JURY :
            case Membre::RAPPORTEUR_VISIO :
                $acteurs = array_filter($acteurs, function(Acteur $a) {
                    /** @var Profil  $profil */
                    $profil = ($a->getRole()->getProfils()->first());
                    return $profil->getRoleCode() === 'R';});
                break;
            case Membre::RAPPORTEUR_ABSENT :
                $acteurs = array_filter($acteurs, function(Acteur $a) {
                    /** @var Profil  $profil */
                    $profil = ($a->getRole()->getProfils()->first());
                    return $profil->getRoleCode() === 'R';});
                break;
            case Membre::MEMBRE_JURY :
                $acteurs = array_filter($acteurs, function(Acteur $a) {
                    /** @var Profil  $profil */
                    $profil = ($a->getRole()->getProfils()->first());
                    return $profil->getRoleCode() === 'M';});
                break;
        }

        $acteurs_libres = [];
        foreach ($acteurs as $acteur) {
            $libre = true;
            foreach ($membres as $membre_) {
                if ($membre_->getActeur() && $membre_->getActeur()->getId() === $acteur->getId()) {
                    $libre = false;
                    break;
                }
            }
            if ($libre) $acteurs_libres[] = $acteur;
        }

        /** @var Request $request */
        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            $acteurId = $data['acteur'];
            /** @var Acteur $acteur */
            $acteur = $this->getActeurService()->getRepository()->find($acteurId);
            $individu = $acteur->getIndividu();

            if (!$acteur) throw new RuntimeException("Aucun acteur à associer !");

            //mise à jour du membre de soutenance
            $membre->setActeur($acteur);
            $this->getMembreService()->update($membre);
            //affectation du rôle
            $this->getRoleService()->addIndividuRole($individu,$acteur->getRole());
            //creation de l'utilisateur
            $utilisateurs = $this->utilisateurService->getRepository()->findByIndividu($individu);
            if (empty($utilisateurs)) {
                $user = $this->utilisateurService->createFromIndividu($individu, $this->generateUsername($membre), 'none');
                $this->userService->updateUserPasswordResetToken($user);
                $url = $this->url()->fromRoute('utilisateur/init-compte', ['token' => $user->getPasswordResetToken()], ['force_canonical' => true], true);
                $this->getNotifierSoutenanceService()->triggerInitialisationCompte($these, $user, $url);
            }
        }

        return new ViewModel([
            'title' => "Association de ".$membre->getDenomination()." à un acteur SyGAL",
            'acteurs' => $acteurs_libres,
            'membre' => $membre,
            'these' => $these,
        ]);
    }

    public function deassocierJuryAction()
    {
        $these = $this->requestedThese();
        $membre = $this->getMembreService()->getRequestedMembre($this);

        /** @var Acteur[] $acteurs */
        $acteurs = $this->getActeurService()->getRepository()->findActeurByThese($these);
        $acteur = null;
        foreach ($acteurs as $acteur_) {
            if ($acteur_ === $membre->getActeur()) $acteur = $acteur_;
        }
        if (!$acteur) throw new RuntimeException("Aucun acteur à deassocier !");

        //retrait dans membre de soutenance
        $username = $this->generateUsername($membre);
        $membre->setActeur(null);
        $this->getMembreService()->update($membre);
        $this->getRoleService()->removeIndividuRole($acteur->getIndividu(), $acteur->getRole());


        $validations = $this->getValidationService()->getRepository()->findValidationByCodeAndIndividu(TypeValidation::CODE_ENGAGEMENT_IMPARTIALITE, $acteur->getIndividu());
        if (!empty($validations)) {
            $this->getValidationService()->unsignEngagementImpartialite(current($validations));
        }

        /** @var Utilisateur $utilisateur */
        $utilisateur = $this->utilisateurService->getRepository()->findByUsername($username);
        if ($utilisateur) $this->utilisateurService->supprimerUtilisateur($utilisateur);

        return $this->redirect()->toRoute('soutenance/presoutenance', ['these' => $these->getId()], [], true);
    }

    /**
     * Envoi des demandes d'avis de soutenance
     * /!\ si un membre est fourni alors seulement envoyé à celui-ci sinon à tous les rapporteurs
     */
    public function notifierDemandeAvisSoutenanceAction()
    {
        $these = $this->requestedThese();
        $proposition = $this->getPropositionService()->findByThese($these);
        $membre = $this->getMembreService()->getRequestedMembre($this);

        /** @var Membre[] $rapporteurs */
        $rapporteurs = [];
        if ($membre) {
            $rapporteurs[] = $membre;
        } else {
            $rapporteurs = $this->getPropositionService()->getRapporteurs($proposition);
        }

        /** @var Membre $rapporteur */
        foreach ($rapporteurs as $rapporteur) {
            $this->getNotifierSoutenanceService()->triggerDemandeAvisSoutenance($these, $proposition, $rapporteur);
        }

        return $this->redirect()->toRoute('soutenance/presoutenance', ['these' => $these->getId()], [], true);
    }

    public function revoquerAvisSoutenanceAction()
    {
        $idAvis = $this->params()->fromRoute('avis');
        $avis = $this->getAvisService()->getAvis($idAvis);

        $this->getAvisService()->historiser($avis);

        $this->redirect()->toRoute('soutenance/presoutenance', ['these' => $avis->getThese()->getId()], [], true);
    }

    public function feuVertAction() {
        $these = $this->requestedThese();
        $proposition = $this->getPropositionService()->findByThese($these);

        $etat = $this->getPropositionService()->getPropositionEtatByCode(Etat::VALIDEE);
        $proposition->setEtat($etat);
        $this->getPropositionService()->update($proposition);

        /** @var Avis[] $avis*/
        $avis = $this->getAvisService()->getAvisByThese($these);

        $this->getNotifierSoutenanceService()->triggerFeuVertSoutenance($these, $proposition, $avis);
        $this->flashMessenger()
            //->setNamespace('presoutenance')
            ->addSuccessMessage("Notifications d'accord de soutenance envoyées");

        $this->redirect()->toRoute('soutenance/presoutenance', ['these' => $these->getId()], [], true);
    }

    public function stopperDemarcheAction() {
        $these = $this->requestedThese();
        $proposition = $this->getPropositionService()->findByThese($these);

        $etat = $this->getPropositionService()->getPropositionEtatByCode(Etat::REJETEE);
        $proposition->setEtat($etat);
        $this->getPropositionService()->update($proposition);

        $this->getNotifierSoutenanceService()->triggerStopperDemarcheSoutenance($these, $proposition);
        $this->flashMessenger()
            //->setNamespace('presoutenance')
            ->addSuccessMessage("Notifications d'arrêt des démarches de soutenance soutenance envoyées");

        $this->redirect()->toRoute('soutenance/presoutenance', ['these' => $these->getId()], [], true);
    }

    public function modifierAdresseAction() {
        $these = $this->requestedThese();
        $proposition = $this->getPropositionService()->findByThese($these);

        $form = $this->getAdresseSoutenanceForm();
        $form->setAttribute('action', $this->url()->fromRoute('soutenance/presoutenance/modifier-adresse', ['these' => $these->getId()], [], true));
        $form->bind($proposition);

        /** @var Request $request */
        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            $form->setData($data);
            if ($form->isValid()) {
                $this->getPropositionService()->update($proposition);
            }
        }

        return new ViewModel([
            'title' => "Modifier l'adresse exacte de la soutenance",
            'these' => $these,
            'form' => $form,
        ]);

    }

    /** Document pour la signature en présidence */
    public function procesVerbalSoutenanceAction()
    {
        $these = $this->requestedThese();
        $proposition = $this->getPropositionService()->findByThese($these);

        /** @var PdcData $pdcData */
        $pdcData = $this->getTheseService()->fetchInformationsPageDeCouverture($these);

        /* @var $renderer PhpRenderer */
        $renderer = $this->getServiceLocator()->get('view_renderer');

        $exporter = new ProcesVerbalSoutenancePdfExporter($renderer, 'A4');
        $exporter->setVars([
            'proposition' => $proposition,
            'these' => $these,
            'informations' => $pdcData,
        ]);
        $exporter->export('export.pdf');
        exit;
    }

    /** Document pour la signature en présidence */
    public function avisSoutenanceAction()
    {
        $these = $this->requestedThese();
        $proposition = $this->getPropositionService()->findByThese($these);

        /** @var PdcData $pdcData */
        $pdcData = $this->getTheseService()->fetchInformationsPageDeCouverture($these);

        /* @var $renderer PhpRenderer */
        $renderer = $this->getServiceLocator()->get('view_renderer');

        $exporter = new AvisSoutenancePdfExporter($renderer, 'A4');
        $exporter->setVars([
            'proposition' => $proposition,
            'these' => $these,
            'informations' => $pdcData,
        ]);
        $exporter->export('export.pdf');
        exit;
    }

    /** Document pour la signature en présidence */
    public function convocationAction()
    {
        $these = $this->requestedThese();
        $proposition = $this->getPropositionService()->findByThese($these);

        /** @var PdcData $pdcData */
        $pdcData = $this->getTheseService()->fetchInformationsPageDeCouverture($these);

        /* @var $renderer PhpRenderer */
        $renderer = $this->getServiceLocator()->get('view_renderer');

        /** @var Validation[] $validationMDD */
        $validationMDD = $this->getValidationService()->getRepository()->findValidationByCodeAndThese(TypeValidation::CODE_VALIDATION_PROPOSITION_BDD, $these);
        $dateValidation = (! empty($validationMDD))?current($validationMDD)->getHistoModification():null;

        /** @var string $ville */
        switch($these->getEtablissement()->getSigle()) {
            case "UCN" : $ville = "Caen"; break;
            case "URN" :
            case "INSA" :
                $ville = "Rouen"; break;
            case "ULHN" : $ville = "Le Havre"; break;
            default:
                $ville = "Manquant";
        }

        $exporter = new ConvocationPdfExporter($renderer, 'A4');
        $exporter->setVars([
            'proposition' => $proposition,
            'these' => $these,
            'informations' => $pdcData,
            'date' => $dateValidation,
            'ville' => $ville,
        ]);
        $exporter->export('export.pdf');
        exit;
    }

    public function notifierRetardRapportPresoutenanceAction() {

        $delai = new DateInterval('P15D');
        $membres = $this->getMembreService()->getRapporteursEnRetard($delai);
        $url = $this->url()->fromRoute('soutenance/index-rapporteur', [], ['force_canonical' => true], true);

        foreach ($membres as $membre) {
            $this->getNotifierSoutenanceService()->triggerNotificationRapporteurRetard($membre, $url);
        }
        exit();
    }

    //TODO recuperer les partie commune pour nettoyer dessous
    /**
     * Fonction calculant le nom du rapporteur : NOMUSUEL_MEMBREID
     * @param Membre $membre
     * @return string
     */
    private function generateUsername($membre) {
        $acteur = $membre->getActeur();
        if ($acteur === null) throw new LogicException("La génération du username est basée sur l'Individu qui est mamquant.");
        $nomusuel = strtolower($acteur->getIndividu()->getNomUsuel());
        return ($nomusuel . "_" . $membre->getId());
    }
}