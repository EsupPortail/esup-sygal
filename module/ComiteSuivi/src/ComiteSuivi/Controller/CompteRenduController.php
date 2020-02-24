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
use Zend\View\Model\ViewModel;

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
        ]);
    }

    public function ajouterAction()
    {
        $comite = $this->getComiteSuiviService()->getRequestedComiteSuivi($this);
        $membre = $this->getMembreService()->getRequestedMembre($this);

        if ($membre === null) {

            /** @var Request $request */
            $request = $this->getRequest();
            if ($request->isPost()) {
                $data = $request->getPost();
                $examinateurId = $data['examinateur'];
                $membre = $this->getMembreService()->getMembre($examinateurId);
            }

            if ($membre === null) {
                $examinateurs = $this->getMembreService()->getExaminateurs($comite);
                return new ViewModel([
                    'title' => "Sélection d'un examinateur",
                    'comite' => $comite,
                    'examinateurs' => $examinateurs,
                ]);
            }
        }

        $compterendu = $this->getCompteRenduService()->getCompteRenduByComiteAndExaminateur($comite, $membre);
        if ($compterendu === null) {
            $compterendu = new CompteRendu();
            $compterendu->setComite($comite);
            $compterendu->setMembre($membre);
            $this->getCompteRenduService()->create($compterendu);
        }

        return $this->redirect()->toRoute('compte-rendu/modifier', ['comite-suivi' => $comite->getId(), 'compte-rendu' => $compterendu->getId()], [], true);
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

        return new ViewModel([
            'compterendu' => $compterendu,
            'comite' => $compterendu->getComite(),
            'these' => $compterendu->getComite()->getThese(),
            'form' => $form,
        ]);
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