<?php

namespace Application\Controller;

use Application\Entity\Db\Individu;
use Application\Entity\Db\MailConfirmation;
use Application\Form\MailConfirmationForm;
use Application\Service\Individu\IndividuServiceAwareTrait;
use Application\Service\MailConfirmationService;
use Application\Service\Notification\NotifierServiceAwareTrait;
use Zend\View\Model\ViewModel;

class MailConfirmationController extends AbstractController {
    use NotifierServiceAwareTrait;
    use IndividuServiceAwareTrait;

    /** @var MailConfirmationService $mailConfirmationService */
    public $mailConfirmationService;

    public function setMailConfirmationService(MailConfirmationService $service)
    {
        $this->mailConfirmationService = $service;
    }


    public function indexAction()
    {
        $request = $this->getRequest();
        $id = $this->params()->fromRoute('id');

        $form = null;
        $individu = null;
        $mailConfirmation = null;

        if ($request->isPost()) {
            //dès lors que l'on revient d'un post on affiche le formalaire de demande
            /** @var MailConfirmationForm $form */
            $form = $this->getServiceLocator()->get('formElementManager')->get('MailConfirmationForm');

            if ($id !== null) {
                //edition d'une demande existante
                $mailConfirmation = $this->mailConfirmationService->getDemandeById($id);
            } else {
                //nouvelle demande
                $mailConfirmation = new MailConfirmation();
                $mailConfirmation->setEtat(MailConfirmation::ENVOYER);
                $data = $request->getPost();

                if (isset($data['idIndividu'])) {
                    /** @var Individu $individu */
                    $individu = $this->individuService->getRepository()->find($data['idIndividu']);
                    $mailConfirmation->setIndividu($individu);
                    if (isset($data['email']) && $data['email'] !== "") {
                        $mailConfirmation->setEmail($data['email']);

                        if (filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {

                            //enresgistrement de la demande
                            $id = $this->mailConfirmationService->save($mailConfirmation);
                            $this->mailConfirmationService->generateCode($id);

                            $this->redirect()->toRoute('mail-confirmation-envoie', ['id' => $id], [], true);
                            $form = null;
                        } else {
                            $this->flashMessenger()->addErrorMessage("MailConfirmation: Votre email n'est pas valide");
                        }
                    } else {
                        //pas de mail on reste sur le formulaire
                    }
                } else {
                    /** @var Individu $individu */
                    $individu = $this->individuService->getRepository()->find($data['individu']['id']);
                    $mailConfirmation->setIndividu($individu);
                }


            }
            if ($form !== null) $form->bind($mailConfirmation);
        }

        return new ViewModel([
              'form' => $form,
              'encours' => $this->mailConfirmationService->getDemandeEnCours(),
              'confirmees' => $this->mailConfirmationService->getDemandeConfirmees(),
        ]);
    }

    public function envoieAction()
    {
        $id = $this->params()->fromRoute('id');
        //$this->mailConfirmationService->generateCode($id);
        $mailConfirmation = $this->mailConfirmationService->getDemandeById($id);


        $confirm = $this->url()->fromRoute('mail-confirmation-reception', ['id' => $mailConfirmation->getId(), 'code' => $mailConfirmation->getCode()], ['force_canonical' => true] , true);
        $destinataire = $mailConfirmation->getIndividu()->getPrenom1() ." ". $mailConfirmation->getIndividu()->getNomUsuel() . " " . "&lt;<tt>".$mailConfirmation->getEmail()."</tt>&gt;";
        $titre = "[SyGAL] Confirmation de votre email";
        $corps = "<br/>"
            ."&nbsp;&nbsp;&nbsp;&nbsp;Bonjour,<br/><br/>"
            ."Pour finaliser l'enregistrement de votre email de contact veuillez confirmer celui-ci en cliquant sur le liens suivant :<br/>"
            ."<a href='".$confirm."'>"
            .$confirm
            ."</a><br/><br/>"
            ."Une fois confirmé, cet email sera utilisé pour recevoir les notifications de SyGAL et vous permettera de vous connecter à SyGAL.";


        $this->notifierService->triggerMailConfirmation($mailConfirmation, $titre, $corps);

        return $this->redirect()->toRoute("home");
        /** Branchement du mail mais peut être echanger avec une vue classique en
         * décommentant le code si dessous*/

//        return new ViewModel([
//            'destinataire' => $destinataire,
//            'titre' => $titre,
//            'corps' => $corps,
//        ]);
    }

    /**
     * @return ViewModel
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function receptionAction()
    {
        /**
         * @var int $id
         * @var MailConfirmation $mailConfirmation
         */
        $id   = $this->params()->fromRoute('id');
        $code = $this->params()->fromRoute('code');
        $mailConfirmation = $this->mailConfirmationService->getDemandeById($id);

        if (! $mailConfirmation->isConfirmer() && $mailConfirmation->getCode() === $code) {
            $this->mailConfirmationService->confirmEmail($id);
        }

        return new ViewModel([
            'mailConfirmation' => $mailConfirmation,
        ]);
    }

    /**
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function swapAction()
    {
        $id = $this->params()->fromRoute('id');
        $this->mailConfirmationService->swapEtat($id);

        $this->redirect()->toRoute('mail-confirmation-acceuil');
    }

    public function removeAction() {
        $id = $this->params()->fromRoute('id');
        $this->mailConfirmationService->remove($id);

        $this->redirect()->toRoute('mail-confirmation-acceuil');
    }
}
