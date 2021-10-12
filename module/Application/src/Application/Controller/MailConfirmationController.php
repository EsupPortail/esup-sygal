<?php

namespace Application\Controller;

use Application\Entity\Db\Individu;
use Application\Entity\Db\MailConfirmation;
use Application\Form\MailConfirmationForm;
use Application\Service\Individu\IndividuServiceAwareTrait;
use Application\Service\MailConfirmationService;
use Application\Service\Notification\NotifierServiceAwareTrait;
use Laminas\View\Model\ViewModel;

class MailConfirmationController extends AbstractController {
    use NotifierServiceAwareTrait;
    use IndividuServiceAwareTrait;

    /** @var MailConfirmationService $mailConfirmationService */
    private $mailConfirmationService;

    public function setMailConfirmationService(MailConfirmationService $service)
    {
        $this->mailConfirmationService = $service;
    }

    /**
     * @var MailConfirmationForm
     */
    private $mailConfirmationForm;

    /**
     * @param MailConfirmationForm $mailConfirmationForm
     */
    public function setMailConfirmationForm(MailConfirmationForm $mailConfirmationForm)
    {
        $this->mailConfirmationForm = $mailConfirmationForm;
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
            $form = $this->mailConfirmationForm;

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
        /** @var MailConfirmation $mailConfirmation */
        $mailConfirmation = $this->mailConfirmationService->getDemandeById($id);

        $appName = $this->appInfos()->getNom();

        $confirm = $this->url()->fromRoute('mail-confirmation-reception', ['id' => $mailConfirmation->getId(), 'code' => $mailConfirmation->getCode()], ['force_canonical' => true] , true);
        $this->notifierService->triggerMailConfirmation($mailConfirmation, $confirm);

        return $this->redirect()->toRoute("home");
        /** Branchement du mail mais peut être echanger avec une vue classique en
         * décommentant le code si dessous*/
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
