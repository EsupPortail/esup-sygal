<?php

namespace ComiteSuivi\Controller;

use ComiteSuivi\Entity\DateTimeTrait;
use ComiteSuivi\Entity\Db\CompteRendu;
use ComiteSuivi\Form\CompteRendu\CompteRenduFormAwareTrait;
use ComiteSuivi\Service\ComiteSuivi\ComiteSuiviServiceAwareTrait;
use ComiteSuivi\Service\CompteRendu\CompteRenduServiceAwareTrait;
use ComiteSuivi\Service\Membre\MembreServiceAwareTrait;
use ComiteSuivi\Service\Notifier\NotifierServiceAwareTrait;
use Zend\Http\Request;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Mvc\Plugin\FlashMessenger\FlashMessenger;
use Zend\View\Model\ViewModel;

/**
 * @method FlashMessenger flashMessenger()
 */

class CompteRenduController extends AbstractActionController
{
    use ComiteSuiviServiceAwareTrait;
    use CompteRenduServiceAwareTrait;
    use DateTimeTrait;
    use MembreServiceAwareTrait;
    use NotifierServiceAwareTrait;

    use CompteRenduFormAwareTrait;

    public function afficherAction()
    {
        $compterendu = $this->getCompteRenduService()->getRequestedCompteRendu($this);
        return new ViewModel([
            'compterendu' => $compterendu,
            'comite' => $compterendu->getComite(),
            'these' => $compterendu->getComite()->getThese(),
            'urlFichierThese' => $this->urlFichierThese(),
        ]);
    }

    public function ajouterAction()
    {
        $comite = $this->getComiteSuiviService()->getRequestedComiteSuivi($this);
        $these = $comite->getThese();

        $compterendu= new CompteRendu();
        $compterendu->setComite($comite);

        $form = $this->getCompteRenduForm();
        $form->setAttribute('action', $this->url()->fromRoute('compte-rendu/ajouter', ['comite-suivi' => $comite->getId()], [], true));
        $form->bind($compterendu);

        $examinateurs = $this->getMembreService()->getExaminateurs($comite);
        $options = [];
        foreach ($examinateurs as $membre) $options[$membre->getId()] = $membre->getDenomination();
        $form->get('examinateur')->setValueOptions($options);
        $form->get('fichier')->setValue(null);

        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            $files = ['files' => $request->getFiles()->toArray()];
            if ($files['files']['compte_rendu']['size'] === 0) {
                $this->flashMessenger()->addErrorMessage("Pas de prérapport de soutenance !");
                //$this->redirect()->toRoute('comite-suivi/modifier', ['comite-suivi' => $comite->getId()]);
            } else {
                $data['fichier'] = true;
                $form->setData($data);
                if ($form->isValid()) {
                    $fichier = $this->getCompteRenduService()->createCompteRenduFromUpload($files, $compterendu->getMembre());
                    $compterendu->setFichier($fichier);
                    $this->getCompteRenduService()->create($compterendu);
                }
            }
        }

        $vm = new ViewModel([
            'compterendu' => $compterendu,
            'comite' => $comite,
            'these' => $these,
            'form' => $form,
        ]);
        $vm->setTemplate('comite-suivi/default/default-form');
        return $vm;
    }

    public function modifierAction()
    {
        $compterendu = $this->getCompteRenduService()->getRequestedCompteRendu($this);

        $form = $this->getCompteRenduForm();
        $form->setAttribute('action', $this->url()->fromRoute('compte-rendu/modifier', ['compte-rendu' => $compterendu->getId()], [], true));
        $form->bind($compterendu);

        /** @var Request $request */
        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            $form->setData($data);
            if ($form->isValid()) {
                $this->getCompteRenduService()->update($compterendu);
            }
        }

        $vm = new ViewModel([
            'compterendu' => $compterendu,
            'comite' => $compterendu->getComite(),
            'these' => $compterendu->getComite()->getThese(),
            'form' => $form,
        ]);
        $vm->setTemplate('comite-suivi/default/default-form');
        return $vm;
    }

    public function historiserAction()
    {
        $compterendu = $this->getCompteRenduService()->getRequestedCompteRendu($this);
        $this->getCompteRenduService()->historise($compterendu);
        $this->redirect()->toRoute('comite-suivi', ['these' => $compterendu->getComite()->getThese()->getId(), 'comite-suivi' => $compterendu->getComite()->getId()], [], true);
    }

    public function restaureAction()
    {
        $compterendu = $this->getCompteRenduService()->getRequestedCompteRendu($this);
        $this->getCompteRenduService()->restore($compterendu);
        $this->redirect()->toRoute('comite-suivi', ['these' => $compterendu->getComite()->getThese()->getId(), 'comite-suivi' => $compterendu->getComite()->getId()], [], true);
    }

    public function supprimerAction()
    {
        $compterendu = $this->getCompteRenduService()->getRequestedCompteRendu($this);
        $this->getCompteRenduService()->delete($compterendu);
        $this->redirect()->toRoute('comite-suivi', ['these' => $compterendu->getComite()->getThese()->getId(), 'comite-suivi' => $compterendu->getComite()->getId()], [], true);
    }

    public function finaliserAction()
    {
        $compterendu = $this->getCompteRenduService()->getRequestedCompteRendu($this);
        $compterendu->setFinaliser($this->getDateTime());
        $this->getCompteRenduService()->update($compterendu);
        $this->getNotifierService()->triggerFinaliserCompteRendu($compterendu);
        $this->redirect()->toRoute('comite-suivi', ['these' => $compterendu->getComite()->getThese()->getId(), 'comite-suivi' => $compterendu->getComite()->getId()], [], true);
    }
}