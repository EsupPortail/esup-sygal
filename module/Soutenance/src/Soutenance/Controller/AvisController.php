<?php

namespace Soutenance\Controller;

use Application\Controller\AbstractController;
use Application\Entity\Db\NatureFichier;
use Application\Entity\Db\VersionFichier;
use Application\Service\Acteur\ActeurServiceAwareTrait;
use Application\Service\Fichier\FichierServiceAwareTrait;
use Application\Service\FichierThese\FichierTheseServiceAwareTrait;
use Application\Service\These\TheseServiceAwareTrait;
use Soutenance\Entity\Avis;
use Soutenance\Entity\Proposition;
use Soutenance\Filter\NomAvisFormatter;
use Soutenance\Form\Avis\AvisForm;
use Soutenance\Form\Avis\AvisFormAwareTrait;
use Soutenance\Service\Avis\AvisServiceAwareTrait;
use Soutenance\Service\Membre\MembreServiceAwareTrait;
use Soutenance\Service\Notifier\NotifierSoutenanceServiceAwareTrait;
use Soutenance\Service\Proposition\PropositionServiceAwareTrait;
use Soutenance\Service\Validation\ValidatationServiceAwareTrait;
use Zend\Http\Request;
use Zend\Mvc\Plugin\FlashMessenger\FlashMessenger;
use Zend\View\Model\ViewModel;

/** @method FlashMessenger flashMessenger() */

class AvisController extends AbstractController {
    use ActeurServiceAwareTrait;
    use FichierServiceAwareTrait;
    use FichierTheseServiceAwareTrait;
    use AvisServiceAwareTrait;
    use MembreServiceAwareTrait;
    use NotifierSoutenanceServiceAwareTrait;
    use PropositionServiceAwareTrait;
    use TheseServiceAwareTrait;
    use ValidatationServiceAwareTrait;

    use AvisFormAwareTrait;

    public function indexAction()
    {
        $these = $this->requestedThese();
        $membre = $this->getMembreService()->getRequestedMembre($this, 'rapporteur');

        /** @var Proposition $proposition */
        $proposition = $this->getPropositionService()->findByThese($these);
        $avis = $this->getAvisService()->getAvisByMembre($membre);

        if ($avis !== null) {
            $this->redirect()->toRoute('soutenance/avis-soutenance/afficher', ['these' => $these->getId(), 'rapporteur' => $membre->getId()]);
        }

        /** @var AvisForm $form */
        $form = $this->getAvisForm();

        /** @var Request $request */
        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            $files = ['files' => $request->getFiles()->toArray()];

            if ($files['files']['rapport']['size'] === 0) {
                $this->flashMessenger()->addErrorMessage("Pas de prérapport de soutenance !");
                $this->redirect()->toRoute('soutenance/avis-soutenance', ['these' => $these->getId(), 'rapporteur' => $membre->getId()]);
            }
            if ($data['avis'] === "Défavorable" && trim($data['motif']) == '') {
                $this->flashMessenger()->addErrorMessage("Vous devez motivez votre avis défavorable en quelques mots.");
                $this->redirect()->toRoute('soutenance/avis-soutenance', ['these' => $these->getId(), 'rapporteur' => $membre->getId()]);
            }

            $form->setData($data);
            if ($form->isValid()) {

                //todo faire une fonction dans AvisService ...
                $nature = $this->fichierTheseService->fetchNatureFichier(NatureFichier::CODE_PRE_RAPPORT_SOUTENANCE);
                $version = $this->fichierTheseService->fetchVersionFichier(VersionFichier::CODE_ORIG);
                $fichiers = $this->fichierService->createFichiersFromUpload($files, $nature, $version, new NomAvisFormatter($membre->getIndividu()));
                $fichier = current($fichiers);

                $validation = $this->getValidationService()->signerAvisSoutenance($these, $membre->getIndividu());

                $avis = new Avis();
                $avis->setProposition($proposition);
                $avis->setMembre($membre);
                $avis->setFichier($fichier);
                $avis->setValidation($validation);
                $avis->setAvis($data['avis']);
                $avis->setMotif($data['motif']);
                $this->getAvisService()->create($avis);

                //test du rendu de tous les avis
                $allAvis        = $this->getAvisService()->getAvisByThese($these);
                $allRapporteurs = $this->getMembreService()->getRapporteursByProposition($proposition);



                $url = null; //$this->urlFichierThese()->telechargerFichierThese($these, $avis->getFichier());
                if ($avis->getAvis() === Avis::FAVORABLE) {
                    $this->getNotifierSoutenanceService()->triggerAvisFavorable($these, $avis, $url);
                }
                if ($avis->getAvis() === Avis::DEFAVORABLE) {
                    $this->getNotifierSoutenanceService()->triggerAvisDefavorable($these, $avis, $url);
                }

                if (count($allAvis) === count($allRapporteurs)) {
                    $this->getNotifierSoutenanceService()->triggerAvisRendus($these);
                }

                $this->redirect()->toRoute('soutenance/avis-soutenance/afficher', ['these' => $these->getId(), 'membre' => $membre->getId()], [], true);
            }
        }
        return new ViewModel([
            'form' => $form,
            'these' => $these,
            'rapporteur' => $membre->getActeur(),
        ]);
    }

    public function afficherAction() {
        $these = $this->requestedThese();
        $membre = $this->getMembreService()->getRequestedMembre($this, 'rapporteur');
        $rapporteur = $membre->getActeur();
        /** @var Avis $avis */
        $avis = $this->getAvisService()->getAvisByMembre($membre);

        return new ViewModel([
            'these' => $these,
            'rapporteur' => $rapporteur,
            'avis' => $avis,
            'url' => $this->urlFichierThese()->telechargerFichierThese($these, $avis->getFichier()),
        ]);
    }

    public function annulerAction() {
        $these = $this->requestedThese();
        $membre = $this->getMembreService()->getRequestedMembre($this, 'rapporteur');
        /** @var Avis $avis */
        $avis = $this->getAvisService()->getAvisByMembre($membre);
        $this->getAvisService()->historiser($avis);

        $this->redirect()->toRoute('soutenance/avis-soutenance', ['these' => $these->getId(), 'rapporteur' => $membre->getId()], [], true);
    }

}