<?php

namespace Formation\Controller;

use Application\Controller\AbstractController;
use Application\Search\SearchServiceAwareTrait;
use Application\Service\AnneeUniv\AnneeUnivServiceAwareTrait;
use Doctrine\ORM\PersistentCollection;
use Formation\Service\Notification\FormationNotificationFactoryAwareTrait;
use Formation\Service\Session\Search\SessionSearchServiceAwareTrait;
use Notification\Exception\ExceptionInterface;
use Notification\Exception\RuntimeException as NotificationRuntimeException;
use These\Entity\Db\These;
use Fichier\Service\Fichier\FichierStorageServiceAwareTrait;
use Fichier\Service\Storage\Adapter\Exception\StorageAdapterException;
use Formation\Entity\Db\Formation;
use Formation\Entity\Db\SessionStructureValide;
use Formation\Service\Formation\FormationServiceAwareTrait;
use Formation\Service\Presence\PresenceServiceAwareTrait;
use Formation\Service\SessionStructureValide\SessionStructureValideServiceAwareTrait;
use Laminas\Http\Response;
use Structure\Service\Etablissement\EtablissementServiceAwareTrait;
use DateTime;
use Formation\Entity\Db\Etat;
use Formation\Entity\Db\Inscription;
use Formation\Entity\Db\Seance;
use Formation\Entity\Db\Session;
use Formation\Form\Session\SessionFormAwareTrait;
use Formation\Service\Exporter\Emargement\EmargementExporter;
use Formation\Service\Inscription\InscriptionServiceAwareTrait;
use Notification\Service\NotifierServiceAwareTrait;
use Formation\Service\Session\SessionServiceAwareTrait;
use UnicaenApp\Service\EntityManagerAwareTrait;
use Laminas\View\Model\ViewModel;
use Laminas\View\Renderer\PhpRenderer;
use UnicaenApp\View\Model\CsvModel;

class SessionController extends AbstractController
{
    use EntityManagerAwareTrait;
    use EtablissementServiceAwareTrait;
    use FichierStorageServiceAwareTrait;
    use FormationServiceAwareTrait;
    use InscriptionServiceAwareTrait;
    use NotifierServiceAwareTrait;
    use FormationNotificationFactoryAwareTrait;
    use PresenceServiceAwareTrait;
    use SessionServiceAwareTrait;
    use SessionStructureValideServiceAwareTrait;
    use AnneeUnivServiceAwareTrait;
    use SessionSearchServiceAwareTrait;


    use SessionFormAwareTrait;

    private PhpRenderer $renderer;

    /**
     * @param PhpRenderer $renderer
     */
    public function setRenderer(PhpRenderer $renderer)
    {
        $this->renderer = $renderer;
    }

    public function afficherAction() : ViewModel
    {
        $session = $this->getSessionService()->getRepository()->getRequestedSession($this);

        $presences = $this->getPresenceService()->getRepository()->findPresencesBySession($session);
        $dictionnaire = [];
        foreach ($presences as $presence) {
            if($presence->getSeance()->estNonHistorise()) $dictionnaire[$presence->getSeance()->getId()][$presence->getInscription()->getId()] = $presence;
        }

        $anneeUniv = $session->getDateDebut() ? $this->anneeUnivService->fromDate($session->getDateDebut()) : $this->anneeUnivService->courante();

        return new ViewModel([
            'session' => $session,
            'presences' => $dictionnaire,
            'anneeUniv' => $anneeUniv,
        ]);
    }

    public function afficherFicheAction() : ViewModel
    {
        $session = $this->getSessionService()->getRepository()->getRequestedSession($this);
        return new ViewModel([
            'title' => "Information sur la session #".$session->getIndex(). " - ". $session->getFormation()->getLibelle(),
            'session' => $session,
        ]);
    }

    public function ajouterAction() : ViewModel
    {
        $formation = $this->getFormationService()->getRepository()->getRequestedFormation($this);

        $session = new Session();
        $session->setFormation($formation);
        $session = $this->getSessionService()->setValeurParDefaut($session);

        $form = $this->getSessionForm();
        $form->setUrlResponsable($this->url()->fromRoute('individu/rechercher', [], [], true));
        $form->setAttribute('action', $this->url()->fromRoute('formation/session/ajouter', ['module' => $formation->getId()], [], true));
        $form->bind($session);

        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            $form->setData($data);
            if ($form->isValid()) {
                $this->getSessionService()->create($session);
                /** @var Etat $enPrepration */
                $enPrepration = $this->getEntityManager()->getRepository(Etat::class)->findOneBy(["code" => Etat::CODE_PREPARATION]);
                $session->setEtat($enPrepration);
                $this->sessionService->addHeurodatage($session, $enPrepration);

                $this->getSessionService()->update($session);

                $ns = new SessionStructureValide();
                $ns->setSession($session);

                switch ($session->getType()) {
                    case Formation::TYPE_CODE_TRAVERSAL :
                        $ns->setStructure($session->getSite()->getStructure());
                        break;
                    case Formation::TYPE_CODE_SPECIFIQUE :
                        $ns->setStructure($session->getTypeStructure());
                }
                if ($ns->getStructure()) $this->getSessionStructureValideService()->create($ns);
            }
        }

        $vm = new ViewModel([
            'title' => "Ajout d'une session de formation",
            'form' => $form,
        ]);
        $vm->setTemplate('formation/session/modifier');
        return $vm;
    }

    public function modifierAction() : ViewModel
    {
        $session = $this->getSessionService()->getRepository()->getRequestedSession($this);

        $form = $this->getSessionForm();
        $form->setUrlResponsable($this->url()->fromRoute('individu/rechercher', [], [], true));
        $form->setAttribute('action', $this->url()->fromRoute('formation/session/modifier', ['session' => $session->getId()], [], true));
        $form->bind($session);

        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            $form->setData($data);
            if ($form->isValid()) {
                $this->getSessionService()->update($session);
            }
        }

        $vm = new ViewModel([
            'title' => "Modification d'une session de formation",
            'form' => $form,
        ]);
        return $vm;
    }

    public function historiserAction() : Response
    {
        $session = $this->getSessionService()->getRepository()->getRequestedSession($this);
        $this->getSessionService()->historise($session);

        $retour = $this->params()->fromQuery('retour');
        if ($retour) return $this->redirect()->toUrl($retour);
        return $this->redirect()->toRoute('formation/session');
    }

    public function restaurerAction() : Response
    {
        $session = $this->getSessionService()->getRepository()->getRequestedSession($this);
        $this->getSessionService()->restore($session);

        $retour = $this->params()->fromQuery('retour');
        if ($retour) return $this->redirect()->toUrl($retour);
        return $this->redirect()->toRoute('formation/session');
    }

    public function supprimerAction() : ViewModel
    {
        $session = $this->getSessionService()->getRepository()->getRequestedSession($this);

        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            if ($data["reponse"] === "oui") $this->getSessionService()->delete($session);
            exit();
        }

        $vm = new ViewModel();
        if ($session !== null) {
            $vm->setTemplate('formation/default/confirmation');
            $vm->setVariables([
                'title' => "Suppression de la session #" . $session->getIndex(),
                'text' => "La suppression est définitive êtes-vous sûr&middot;e de vouloir continuer ?",
                'action' => $this->url()->fromRoute('formation/session/supprimer', ["module" => $session->getId()], [], true),
            ]);
        }
        return $vm;
    }

    public function changerEtatAction()
    {
        $session = $this->getSessionService()->getRepository()->getRequestedSession($this);
        /** @var Etat $etat */
        $etat = $this->getEntityManager()->getRepository(Etat::class)->getRequestedEtat($this);

        if ($etat) {
            $retour = $this->params()->fromQuery('retour');
            $session->setEtat($etat);
            $this->getSessionService()->update($session);

            if ($retour) return $this->redirect()->toUrl($retour);
            return $this->redirect()->toRoute('formation/session/afficher', ['session' => $session->getId()], [], true);
        }

        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            /** @var Etat $etat */
            $etat = $this->getEntityManager()->getRepository(Etat::class)->find($data["etat"]);

            if ($etat !== null) {
                $session->setEtat($etat);
                $this->sessionService->addHeurodatage($session, $etat);
                $this->getSessionService()->update($session);

                switch ($session->getEtat()->getCode()) {
                    case Etat::CODE_FERME :
                        $this->triggerNotificationInscriptionsListePrincipale($session);
                        $this->triggerNotificationInscriptionsListeComplementaire($session);
                        $this->triggerNotificationInscriptionClose($session);
                        break;
                    case Etat::CODE_IMMINENT :
                        $this->triggerNotificationSessionImminente($session);
                        $this->triggerNotificationInscriptionEchec($session);
                        break;
                    case Etat::CODE_CLOTURER :
                        $this->triggerNotificationSessionTerminee($session);
                        break;
                    case Etat::CODE_ANNULEE :
                        $this->triggerNotificationSessionAnnulee($session);
                        break;
                    case Etat::CODE_OUVERTE :
                        //Envoi d'un mail lors de la création d'une formation spécifique à la liste de diffusion de l'ED déclarée
                        $formation = $session->getFormation();
                        $formationSpecifique = $formation->getType() === Formation::TYPE_CODE_SPECIFIQUE || $session->getType() === Formation::TYPE_CODE_SPECIFIQUE;
                        $structuresValides = $session->getStructuresValides();

                        if ($formationSpecifique) {
                            if (!$structuresValides->isEmpty()) {
                                $this->triggerNotificationFormationSpecifiqueAjoutee($session);
                                break;
                            } else {
                                $this->flashMessenger()->addWarningMessage("Si vous voulez la notification des doctorants de l'ouverture des inscriptions de cette session (<b>{$formation->getLibelle()}</b>), il faut renseigner une ou des structures valides.");
                            }
                        }

                }
            }
        }

        return new ViewModel([
           "title" => "Changement de l'état de la session",
           "etats" => $this->getEntityManager()->getRepository(Etat::class)->findAll(),
           "session" => $session,
        ]);
    }

    private function triggerNotificationInscriptionsListePrincipale(Session $session)
    {
        $inscriptions = $session->getInscriptionsByListe(Inscription::LISTE_PRINCIPALE);
        foreach ($inscriptions as $inscription) {
            try {
                $notif = $this->formationNotificationFactory->createNotificationInscriptionListePrincipale($inscription);
                $this->notifierService->trigger($notif);
            } catch (NotificationRuntimeException $e) {
                // aucun destinataire trouvé lors de la construction de la notif : cas à gérer !
            }
        }
    }

    private function triggerNotificationInscriptionsListeComplementaire(Session $session)
    {
        $inscriptions = $session->getInscriptionsByListe(Inscription::LISTE_COMPLEMENTAIRE);
        foreach ($inscriptions as $inscription) {
            try {
                $notif = $this->formationNotificationFactory->createNotificationInscriptionListeComplementaire($inscription);
                $this->notifierService->trigger($notif);
            } catch (NotificationRuntimeException $e) {
                // aucun destinataire trouvé lors de la construction de la notif : cas à gérer !
            }
        }
    }

    private function triggerNotificationFormationSpecifiqueAjoutee(Session $session): void
    {
        $notifs = [];
        $errorMessages = [];
        /** @var SessionStructureValide $structureValide */
        foreach ($session->getStructuresValides() as $structureValide) {
            try {
                $notifs[] = $this->formationNotificationFactory->createNotificationFormationSpecifiqueAjoutee($session->getFormation(), $structureValide);
            } catch (NotificationRuntimeException $e) {
                $errorMessages[] = $e->getMessage();
            }
        }

        // NB : aucun envoi si un pb a été rencontré à la création/init de la notif (ex : liste de diff non active)
        $destinataires = [];
        if (count($errorMessages) === 0) {
            foreach ($notifs as $notif) {
                $result = $this->notifierService->trigger($notif);
                if (!empty($result->getErrorMessages())) {
                    $this->flashMessenger()->addErrorMessage($result->getErrorMessages()[0] . " (" . $structureValide->getStructure()->getEcoleDoctorale() . ").");
                } else {
                    //Si il n'y a pas d'erreurs, on informe l'utilisateur des destinataires notifiés
                    $destinataires[] = $notif->getTo();
                }
            }
        } else {
            $errorMessage = implode("<br>", $errorMessages);
            $this->flashMessenger()->addErrorMessage(
                "La notification par mail (de l'ouverture des inscriptions) des doctorants n'a pas pu être effectuée. " . $errorMessage
            );
        }

        // Si il y a au moins un destinataire qui a pu recevoir un mail
        if (!empty($destinataires)) {
            $destinataires = array_reduce($destinataires, function ($carry, $item) {
                $carry[] = implode(', ', $item);
                return $carry;
            }, []);
            $destinataires = implode(', ', $destinataires);

            $this->flashMessenger()->addInfoMessage(
                "Une notification par mail (de l'ouverture des inscriptions) a été envoyée aux doctorants appartenant aux listes de diffusion suivantes : " . $destinataires
            );
        }
    }

    private function triggerNotificationInscriptionClose(Session $session)
    {
        $nonClasses = $session->getNonClasses();

        foreach ($nonClasses as $inscription) {
            try {
                $notif = $this->formationNotificationFactory->createNotificationInscriptionClose($inscription);
                $this->notifierService->trigger($notif);
            } catch (NotificationRuntimeException $e) {
                // aucun destinataire trouvé lors de la construction de la notif : cas à gérer !
            }
        }
    }

    private function triggerNotificationInscriptionEchec(Session $session)
    {
        $complementaire = $session->getListeComplementaire();
        $nonClasses = $session->getNonClasses();

        $inscriptions = array_merge($nonClasses, $complementaire);

        foreach ($inscriptions as $inscription) {
            try {
                $notif = $this->formationNotificationFactory->createNotificationInscriptionEchec($inscription);
                $this->notifierService->trigger($notif);
            } catch (NotificationRuntimeException $e) {
                // aucun destinataire trouvé lors de la construction de la notif : cas à gérer !
            }
        }
    }

    private function triggerNotificationSessionImminente(Session $session)
    {
        $inscriptions = $session->getListePrincipale();

        try {
            $notif = $this->formationNotificationFactory->createNotificationSessionImminenteFormateur($session);
            $this->notifierService->trigger($notif);
        } catch (NotificationRuntimeException $e) {
            // aucun destinataire trouvé lors de la construction de la notif : cas à gérer !
        }

        foreach ($inscriptions as $inscription) {
            try {
                $notif = $this->formationNotificationFactory->createNotificationSessionImminente($inscription);
                $this->notifierService->trigger($notif);
            } catch (NotificationRuntimeException $e) {
                // aucun destinataire trouvé lors de la construction de la notif : cas à gérer !
            }
        }

    }

    private function triggerNotificationSessionTerminee(Session $session)
    {
        $inscriptions = $session->getListePrincipale();

        foreach ($inscriptions as $inscription) {
            try {
                $notif = $this->formationNotificationFactory->createNotificationSessionTerminee($inscription);
                $this->notifierService->trigger($notif);
            } catch (NotificationRuntimeException $e) {
                // aucun destinataire trouvé lors de la construction de la notif : cas à gérer !
            }
        }
    }

    private function triggerNotificationSessionAnnulee(Session $session)
    {
        $inscriptions = array_merge($session->getListePrincipale(), $session->getListeComplementaire());

        foreach ($inscriptions as $inscription) {
            try {
                $notif = $this->formationNotificationFactory->createNotificationSessionAnnulee($inscription);
                $this->notifierService->trigger($notif);
            } catch (NotificationRuntimeException $e) {
                // aucun destinataire trouvé lors de la construction de la notif : cas à gérer !
            }
        }
    }

    public function classerInscriptionsAction() : Response
    {
        $session = $this->getSessionService()->getRepository()->getRequestedSession($this);

        $inscriptions = $session->getInscriptions();
        $classements = [Inscription::LISTE_PRINCIPALE => [],  Inscription::LISTE_COMPLEMENTAIRE => [], 'N' => []];
        /** @var Inscription $inscription */
        foreach ($inscriptions as $inscription) {
            if ($inscription->estNonHistorise()) {
                $liste = $inscription->getListe() ?: 'N';
                $classements[$liste][] = $inscription;
            }
        }
        $positionPrincipale = count($classements[Inscription::LISTE_PRINCIPALE]);
        $positionComplementaire = count($classements[Inscription::LISTE_COMPLEMENTAIRE]);
        usort($classements['N'], function(Inscription $a,Inscription $b) { return $a->getHistoCreation() > $b->getHistoCreation(); });
        foreach ($classements['N'] as $inscription) {

            if ($positionPrincipale < $session->getTailleListePrincipale()) {
                $inscription->setListe(Inscription::LISTE_PRINCIPALE);
                $this->getInscriptionService()->update($inscription);
                if ($session->isFinInscription()) {
                    try {
                        $notif = $this->formationNotificationFactory->createNotificationInscriptionListePrincipale($inscription);
                        $this->notifierService->trigger($notif);
                    } catch (NotificationRuntimeException $e) {
                        // aucun destinataire trouvé lors de la construction de la notif : cas à gérer !
                    }
                }
                $positionPrincipale++;
            } else {
                if ($positionComplementaire < $session->getTailleListeComplementaire()) {
                    $inscription->setListe(Inscription::LISTE_COMPLEMENTAIRE);
                    $this->getInscriptionService()->update($inscription);
                    if ($session->isFinInscription()) {
                        try {
                            $notif = $this->formationNotificationFactory->createNotificationInscriptionListeComplementaire($inscription);
                            $this->notifierService->trigger($notif);
                        } catch (NotificationRuntimeException $e) {
                            // aucun destinataire trouvé lors de la construction de la notif : cas à gérer !
                        }
                    }
                    $positionComplementaire++;
                }
                else {
                    break;
                }
            }
        }

        $retour = $this->params()->fromQuery('retour');
        if ($retour) return $this->redirect()->toUrl($retour);
        return $this->redirect()->toRoute('formation/session/afficher', ['session' => $session->getId()], [], true);
    }

    public function declasserInscriptionsAction() : Response
    {
        $session = $this->getSessionService()->getRepository()->getRequestedSession($this);

        /** @var Inscription $inscription */
        foreach ($session->getInscriptions() as $inscription) {
            $inscription->setListe(null);
            $this->getInscriptionService()->update($inscription);
        }

        $retour = $this->params()->fromQuery('retour');
        if ($retour) return $this->redirect()->toUrl($retour);
        return $this->redirect()->toRoute('formation/session/afficher', ['session' => $session->getId()], [], true);
    }

    public function genererExportAction() : CsvModel
    {
        $session = $this->getSessionService()->getRepository()->getRequestedSession($this);
        $annee = $session->getDateDebut() ? $this->anneeUnivService->fromDate($session->getDateDebut())->getPremiereAnnee() :
            $this->anneeUnivService->courante()->getPremiereAnnee();


        $headers = ['Liste', 'Dénomination étudiant', 'Adresse électronique', 'Année de thèse', 'Établissement', 'École doctorale', 'Unité de recherche', 'Desinscription', 'Motif de desinscription'];

        $inscriptions = $session->getInscriptions()->toArray();
        $records = [];
        /** @var Inscription $inscription */
        foreach ($inscriptions as $inscription) {
            $doctorant = $inscription->getDoctorant();
            $theses = array_filter($doctorant->getTheses(), function (These $t) { return ($t->getEtatThese() === These::ETAT_EN_COURS AND $t->estNonHistorise());});
            /** @var These $these */
            $these = (!empty($theses))?current($theses):null;
            $etablissements = array_map(function (These $t) { return ($t->getEtablissement())?$t->getEtablissement()->getStructure()->getLibelle():"Établissement non renseigné";}, $theses);
            $ecoles = array_map(function (These $t) { return ($t->getEcoleDoctorale())?$t->getEcoleDoctorale()->getStructure()->getLibelle():"École doctorale non renseignée";}, $theses);
            $unites = array_map(function (These $t) { return ($t->getUniteRecherche())?$t->getUniteRecherche()->getStructure()->getLibelle():"Unité de recherche non renseignée";}, $theses);
            $nbInscription = ($these)?$these->getNbInscription():"---";
            $entry = [
                'Liste' => $inscription->getListe(),
                'Dénomination étudiant' => $doctorant->getIndividu()->getNomComplet(),
                'Adresse électronique' => $doctorant->getIndividu()->getEmailUtilisateur(),
                'Année de thèse' => $nbInscription,
                'Établissement' => implode("/",$etablissements),
                'École doctorale' => implode("/",$ecoles),
                'Unité de recherche' => implode("/",$unites),
                'Desinscription' => ($inscription->getHistoDestruction())?$inscription->getHistoDestruction()->format('d/m/Y'): null,
                'Motif de desinscription' => $inscription->getDescription(),
            ];
            $records[] = $entry;
        }

        $filename = (new DateTime())->format('Ymd-His') . '-session-' . str_replace(' ','_',$session->getFormation()->getLibelle()) . '-'. $session->getDateDebut()->format('d_m_Y') .'.csv';
        $CSV = new CsvModel();
        $CSV->setDelimiter(';');
        $CSV->setEnclosure('"');
        $CSV->setHeader($headers);
        $CSV->setData($records);
        $CSV->setFilename($filename);
        return $CSV;
    }

    public function transmettreListeInscritsAction() : Response
    {
        $session = $this->getSessionService()->getRepository()->getRequestedSession($this);

        try {
            $notif = $this->formationNotificationFactory->createNotificationTransmettreInscritsSession($session);
            $this->notifierService->trigger($notif);
        } catch (NotificationRuntimeException) {
            // aucun destinataire, todo : cas à gérer !
        }

        return $this->redirect()->toRoute('formation/session/afficher', ['session' => $session->getId()], [], true);
    }

    public function genererEmargementsAction()
    {
        $session = $this->getSessionService()->getRepository()->getRequestedSession($this);
        $annee = $session->getDateDebut() ? $this->anneeUnivService->fromDate($session->getDateDebut())->getPremiereAnnee() :
            $this->anneeUnivService->courante()->getPremiereAnnee();
        $seances = $session->getSeances()->toArray();
        $seances = array_filter($seances, function ($a) { return $a->estNonHistorise();});
        usort($seances, function (Seance $a, Seance $b) { return $a->getDebut() > $b->getDebut();});

        $logos = [];
        try {
            $logos['site'] = $this->fichierStorageService->getFileForLogoStructure($session->getSite()->getStructure());
        } catch (StorageAdapterException $e) {
            $logos['site'] = null;
        }

        if ($comue = $this->etablissementService->fetchEtablissementComue()) {
            try {
                $logos['comue'] = $this->fichierStorageService->getFileForLogoStructure($comue->getStructure());
            } catch (StorageAdapterException $e) {
                $logos['comue'] = null;
            }
        }

        //exporter
        $export = new EmargementExporter($this->renderer, 'A4');
        $export->setVars([
            'seance' => $seances[0],
            'logos' => $logos,
            'annee' => $annee
        ]);
        $export->exportAll($seances, 'SYGAL_emargement_' . $session->getId() . ".pdf");
    }

    public function genererExportCsvAction(): Response|CsvModel
    {
        $queryParams = array_filter($this->params()->fromQuery(), function($value) {
            return !is_null($value) && $value !== '';
        });

        $this->sessionSearchService->init();
        $this->sessionSearchService->processQueryParams($queryParams);
        $qb = $this->sessionSearchService->getQueryBuilder();
        $listing = $qb->getQuery()->getResult();
        //export
        $headers = ['Module', 'Formation', 'Type de formation', 'Établissement organisateur',
            'Responsable de la formation', 'Formateur(s)', 'État de la session',
            'Structures valides pour l\'inscription', 'Modalité', 'Nombre d\'heures',
            'Séance(s)', 'Effectif Liste principale', 'Effectif Liste complémentaire', 'Nombre d\'inscrits',
            'Nombre d\'inscrits sur liste principale', 'Effectif présents d\'après la liste d\'émargement', 'Inscrits sur liste principale'];
        $records = [];
        /** @var Session $session */
        foreach ($listing as $session) {
            $formation = $session->getFormation();

            $structuresValides = $session->getStructuresValides();
            $structuresValidesStrings = array_map(function($structureValide) {
                return $structureValide->getStructure()->getLibelle();
            }, $structuresValides->toArray());

            $formateurs = $session->getFormateurs();
            $formateursStrings = array_map(function($formateur) {
                return $formateur->getIndividu();
            }, $formateurs->toArray());

            $inscrits = $session->getInscriptions()->toArray();
            $nbInscrits = count($inscrits);
            /** @var Seance[] $seances */
            $seances = $session->getSeances()->toArray();
            $seances = array_filter($seances, function (Seance $a) { return $a->estNonHistorise();});
            usort($seances, function(Seance $a, Seance $b) { return $a->getDebut() > $b->getDebut();});
            $seanceStrings = array_map(function($seance) {
                return $seance->getDebut()->format('d/m/Y');
            }, $seances);

            $inscritsPrincipale = $session->getInscriptionsByListe(Inscription::LISTE_PRINCIPALE);
            $nbInscritsListePrincipale = count($inscritsPrincipale);
            $inscritsPrincipaleStrings = array_map(function($inscritPrincipale) {
                return $inscritPrincipale->getDoctorant()->getIndividu();
            }, $inscritsPrincipale);

            if ($session->getEtat()->getCode() !== Etat::CODE_IMMINENT and $session->getEtat()->getCode() !== Etat::CODE_FERME and $session->getEtat()->getCode() !== Etat::CODE_CLOTURER) {
                $presences = "Les présences ne peuvent pas être affichées.";
            } else {
                $presences = $this->getPresenceService()->getRepository()->findPresencesBySession($session);
                $dictionnaire = [];
                foreach ($presences as $presence) {
                    if($presence->isPresent()) $dictionnaire[$presence->getSeance()->getId()][] = $presence;
                }
                $presences = $dictionnaire;

                $presencesStrings = array_map(function ($seance) use ($presences, $session) {
                    $nbPresencesParSeances = "";
                    $seanceDate = $seance->getDebut()->format('d/m/Y H:i');
                    $seanceId = $seance->getId();

                    if (isset($presences[$seanceId])) {
                        $nbPresencesParSeances = count($presences[$seanceId]);
                    }
                    return $nbPresencesParSeances ? $seanceDate . ' : ' . $nbPresencesParSeances : null;
                }, $seances);
                $presences = $presencesStrings ? implode(' ; ', $presencesStrings) : null;
            }

            $entry = [];
            $entry['Module'] = $formation && $formation->getModule() ? $formation->getModule()->getLibelle() : null;
            $entry['Formation'] = $formation ? $formation->getLibelle() : null;
            $entry['Type de formation'] = $formation ? $formation->getType() : null;
            $entry['Établissement organisateur'] = $session->getSite() ? $session->getSite()->getStructure()->getLibelle() : null;
            $entry['Responsable de la formation'] = $session->getResponsable() ? $session->getResponsable() : null;
            $entry['Formateur(s)'] = implode("/", $formateursStrings);;
            $entry['État de la session'] = $session->getEtat();
            $entry["Structures valides pour l'inscription"] = implode("/", $structuresValidesStrings);
            $entry['Modalité'] = $session->getModalite();
            $entry['Nombre d\'heures'] = (string) $session->getDuree();
            $entry['Séance(s)'] = implode(' / ', $seanceStrings);
            $entry['Effectif Liste principale'] = $session->getTailleListePrincipale();
            $entry['Effectif Liste complémentaire'] = $session->getTailleListeComplementaire();
            $entry['Nombre d\'inscrits'] = $nbInscrits;
            $entry['Nombre d\'inscrits sur liste principale'] = $nbInscritsListePrincipale;
            $entry['Effectif présents d\'après la liste d\'émargement'] = $presences;
            $entry['Inscrits sur liste principale'] = implode(' / ', $inscritsPrincipaleStrings);;

            $records[] = $entry;
        }
        $filename = (new DateTime())->format('Ymd') . '_sessions.csv';
        $CSV = new CsvModel();
        $CSV->setDelimiter(';');
        $CSV->setEnclosure('"');
        $CSV->setHeader($headers);
        $CSV->setData($records);
        $CSV->setFilename($filename);

        return $CSV;
    }
}