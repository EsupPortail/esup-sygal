<?php

namespace Soutenance\Controller;

use Application\Controller\AbstractController;
use Application\Entity\Db\NatureFichier;
use Application\Entity\Db\VersionFichier;
use Application\Service\Acteur\ActeurServiceAwareTrait;
use Application\Service\FichierThese\FichierTheseServiceAwareTrait;
use Application\Service\These\TheseServiceAwareTrait;
use Application\Service\Utilisateur\UtilisateurServiceAwareTrait;
use Soutenance\Entity\Avis;
use Soutenance\Form\Avis\AvisFormAwareTrait;
use Soutenance\Service\Avis\AvisServiceAwareTrait;
use Soutenance\Service\EngagementImpartialite\EngagementImpartialiteServiceAwareTrait;
use Soutenance\Service\Membre\MembreServiceAwareTrait;
use Soutenance\Service\Notifier\NotifierSoutenanceServiceAwareTrait;
use Soutenance\Service\Validation\ValidatationServiceAwareTrait;
use Zend\View\Model\ViewModel;

class RapporteurController extends AbstractController
{
    use ActeurServiceAwareTrait;
    use AvisServiceAwareTrait;
    use EngagementImpartialiteServiceAwareTrait;
    use FichierTheseServiceAwareTrait;
    use MembreServiceAwareTrait;
    use TheseServiceAwareTrait;
    use ValidatationServiceAwareTrait;
    use NotifierSoutenanceServiceAwareTrait;
    use UtilisateurServiceAwareTrait;

    use AvisFormAwareTrait;



    public function indexAction()
    {
        $these = $this->getTheseService()->getRequestedThese($this);
        $membre = $this->getMembreService()->getRequestedMembre($this);
        $clef = $this->params()->fromQuery('clef');

        $valide = ($this->getMembreService()->verifierClef($membre, $clef) and $membre->getActeur() !== null);

        $fichier = null;
        if ($this->fichierTheseService->getRepository()->hasVersion($these, VersionFichier::CODE_ARCHI)) {
            $fichier = $this->fichierTheseService->getRepository()->fetchFichierTheses($these, NatureFichier::CODE_THESE_PDF, VersionFichier::CODE_ARCHI);
        } else {
            $fichier = $this->fichierTheseService->getRepository()->fetchFichierTheses($these, NatureFichier::CODE_THESE_PDF, VersionFichier::CODE_ORIG);
        }
        $encadrants = $this->getActeurService()->getRepository()->findEncadrementThese($these);
        $engagement = $this->getEngagementImpartialiteService()->getEngagementImpartialiteByMembre($these, $membre);
        if ($engagement === null) $engagement = $this->getEngagementImpartialiteService()->getRefusEngagementImpartialiteByMembre($these, $membre);


        $avis = $this->getAvisService()->getAvisByMembre($membre);
        $form = $this->getAvisForm();
        $form->setAttribute('action', $this->url()->fromRoute('soutenance/rapporteur', ['these' => $these->getId(), 'membre' => $membre->getId()], ['query' => ['clef' => $clef]], true));
        $form->bind($avis ?: new Avis());

        $request = $this->getRequest();
        if ($request->isPost()) {
            $this->gererAvis($request, $form, $these, $membre, $clef);
        }

        return new ViewModel([
            'these' => $these,
            'membre' => $membre,
            'clef' => $clef,

            'valide' => $valide,

            'encadrants' => $encadrants,
            'engagement' => $engagement,
            'manuscrit' => $fichier,
            'avis' => $avis,
            'avisForm' => $form,
            'urlAvis' => ($avis)?$this->urlFichierThese()->telechargerFichierThese($these, $avis->getFichier()):null,
        ]);
    }

    private function gererAvis($request, $form, $these, $membre, $clef)
    {
        $data = $request->getPost();
        $files = ['files' => $request->getFiles()->toArray()];

        if ($files['files']['rapport']['size'] === 0) {
            $this->flashMessenger()->addErrorMessage("Pas de prérapport de soutenance !");
            return $this->redirect()->toRoute('soutenance/rapporteur', ['these' => $these->getId(), 'membre' => $membre->getId()], ['query' => ['clef' => $clef]]);
        }
        if ($data['avis'] === "Défavorable" && trim($data['motif']) == '') {
            $this->flashMessenger()->addErrorMessage("Vous devez motivez votre avis défavorable en quelques mots.");
            return $this->redirect()->toRoute('soutenance/rapporteur', ['these' => $these->getId(), 'membre' => $membre->getId()], ['query' => ['clef' => $clef]]);
        }

        $form->setData($data);
        if ($form->isValid()) {
            $fichierAvis = $this->getAvisService()->createAvisFromUpload($files, $membre);
            $validation = $this->getValidationService()->signerAvisSoutenance($these, $membre->getIndividu(), true);

            $avis = new Avis();
            $avis->setProposition($membre->getProposition());
            $avis->setMembre($membre);
            $avis->setFichier($fichierAvis);
            $avis->setValidation($validation);
            $avis->setAvis($data['avis']);
            $avis->setMotif($data['motif']);
            $sygal = $this->getUtilisateurService()->getRepository()->findByUsername('sygal-app');
            $avis->setHistoCreateur($sygal);
            $avis->setHistoModificateur($sygal);
            $this->getAvisService()->create($avis, $sygal);

            /**
             * N.B. :  Après un dépôt penser à vérifier :
             *   - peu importe l'avis il faut notifier à chaque dépot d'un avis ;
             *   - si tous les avis sont déposés penser à notifier le bureau des doctorats.
             */
            $allAvis = $this->getAvisService()->getAvisByThese($these);
            $allRapporteurs = $this->getMembreService()->getRapporteursByProposition($membre->getProposition());

            if ($avis->getAvis() === Avis::FAVORABLE) {
                $this->getNotifierSoutenanceService()->triggerAvisFavorable($these, $avis);
            }
            if ($avis->getAvis() === Avis::DEFAVORABLE) {
                $this->getNotifierSoutenanceService()->triggerAvisDefavorable($these, $avis);
            }

            /** TODO ajouter un prédicat dans thèse ou soutenance ??? */
            if (count($allAvis) === count($allRapporteurs)) {
                $this->getNotifierSoutenanceService()->triggerAvisRendus($these);
            }
        }
    }
}