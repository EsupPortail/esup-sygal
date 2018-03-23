<?php

namespace Application\Controller;

use Application\Entity\Db\MailConfirmation;
use Application\Form\MailConfirmationForm;
use Application\Service\Individu\IndividuService;
use Application\Service\Individu\IndividuServiceAwareTrait;
use Application\Service\MailConfirmationService;
use Application\Service\Notification\NotificationServiceAwareTrait;
use Zend\View\Model\ViewModel;

class MailConfirmationController extends AbstractController {
    use NotificationServiceAwareTrait;
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
                    $individu = $this->individuService->getIndviduById($data['idIndividu']);
                    $mailConfirmation->setIndividu($individu);
                    if (isset($data['email']) && $data['email'] !== "") {
                        $mailConfirmation->setEmail($data['email']);

                        //enresgistrement de la demande
                        $id = $this->mailConfirmationService->save($mailConfirmation);
                        $this->mailConfirmationService->generateCode($id);
                        $this->redirect()->toRoute('mail-confirmation-envoie', ['id' => $id], [] , true);
                        $form = null;
                    } else {
                        //pas de mail on reste sur le formulaire
                    }
                } else {
                    $individu = $this->individuService->getIndviduById($data['individu']['id']);
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
        $mailConfirmation = $this->mailConfirmationService->getDemandeById($id);
        //if ($mailConfirmation->getCode() === null) {
            $this->mailConfirmationService->generateCode($id);
            $mailConfirmation = $this->mailConfirmationService->getDemandeById($id);
       // }

        return new ViewModel([
            'mailConfirmation' => $mailConfirmation,
            'notifier' => $this->notificationService,
        ]);
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
