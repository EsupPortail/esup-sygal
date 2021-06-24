<?php

namespace Soutenance\Controller;

use Application\Controller\AbstractController;
use Application\Service\Acteur\ActeurServiceAwareTrait;
use Application\Service\These\TheseServiceAwareTrait;
use Soutenance\Entity\Avis;
use Soutenance\Entity\Proposition;
use Soutenance\Form\Avis\AvisForm;
use Soutenance\Form\Avis\AvisFormAwareTrait;
use Soutenance\Service\Avis\AvisServiceAwareTrait;
use Soutenance\Service\Membre\MembreServiceAwareTrait;
use Soutenance\Service\Notifier\NotifierSoutenanceServiceAwareTrait;
use Soutenance\Service\Proposition\PropositionServiceAwareTrait;
use Soutenance\Service\Validation\ValidatationServiceAwareTrait;
use Zend\Http\Request;
use Zend\View\Model\ViewModel;

class AvisController extends AbstractController
{
    use ActeurServiceAwareTrait;
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
                $fichier = $this->getAvisService()->createAvisFromUpload($files, $membre);
                $validation = $this->getValidationService()->signerAvisSoutenance($these, $membre->getIndividu());

                $avis = new Avis();
                $avis->setProposition($proposition);
                $avis->setMembre($membre);
                $avis->setFichier($fichier);
                $avis->setValidation($validation);
                $avis->setAvis($data['avis']);
                $avis->setMotif($data['motif']);
                $this->getAvisService()->create($avis);

                /**
                 * N.B. :  Après un dépôt penser à vérifier :
                 *   - peu importe l'avis il faut notifier à chaque dépot d'un avis ;
                 *   - si tous les avis sont déposés penser à notifier le bureau des doctorats.
                 */
                $allAvis = $this->getAvisService()->getAvisByThese($these);
                $allRapporteurs = $this->getMembreService()->getRapporteursByProposition($proposition);

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

                $this->redirect()->toRoute('soutenance/avis-soutenance/afficher', ['these' => $these->getId(), 'membre' => $membre->getId()], [], true);
            }
        }
        return new ViewModel([
            'form' => $form,
            'these' => $these,
            'rapporteur' => $membre->getActeur(),
        ]);
    }

    public function afficherAction()
    {
        $these = $this->requestedThese();
        $membre = $this->getMembreService()->getRequestedMembre($this, 'rapporteur');
        $rapporteur = $membre->getActeur();

        /** @var Avis $avis */
        $avis = $this->getAvisService()->getAvisByMembre($membre);

        return new ViewModel([
            'these' => $these,
            'rapporteur' => $rapporteur,
            'membre' => $membre,
            'avis' => $avis,
            'url' => $this->urlFichierThese()->telechargerFichierThese($these, $avis->getFichier()),
        ]);
    }

    public function annulerAction()
    {
        $these = $this->requestedThese();
        $membre = $this->getMembreService()->getRequestedMembre($this, 'rapporteur');
        /** @var Avis $avis */
        $avis = $this->getAvisService()->getAvisByMembre($membre);
        $this->getAvisService()->historiser($avis);

        $this->redirect()->toRoute('soutenance/index-rapporteur', ['these' => $these->getId()], [], true);
    }

}