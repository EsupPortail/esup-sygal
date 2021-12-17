<?php

namespace Doctorant\Controller;

use Application\Controller\AbstractController;
use Application\Entity\Db\MailConfirmation;
use Application\Form\MailConfirmationForm;
use Application\RouteMatch;
use Application\Service\MailConfirmationService;
use Doctorant\Entity\Db\Doctorant;
use Doctorant\Service\DoctorantServiceAwareTrait;
use UnicaenAuth\Authentication\Adapter\Ldap as LdapAuthAdapter;
use Zend\View\Model\ViewModel;

class DoctorantController extends AbstractController
{
    use DoctorantServiceAwareTrait;

    /** @var MailConfirmationService $mailConfirmationService */
    private $mailConfirmationService;

    /**
     * @var MailConfirmationForm
     */
    private $mailConfirmationForm;

    /**
     * @var LdapAuthAdapter
     */
    private $ldapAuthAdapter;

    /**
     * @param MailConfirmationService $mailConfirmationService
     */
    public function setMailConfirmationService(MailConfirmationService $mailConfirmationService)
    {
        $this->mailConfirmationService = $mailConfirmationService;
    }

    /**
     * @param MailConfirmationForm $mailConfirmationForm
     */
    public function setMailConfirmationForm(MailConfirmationForm $mailConfirmationForm)
    {
        $this->mailConfirmationForm = $mailConfirmationForm;
    }

    /**
     * @return \Zend\Http\Response|\Zend\View\Model\ViewModel
     */
    public function modifierPersopassAction()
    {
        $doctorant = $this->requestDoctorant();
        $mailConfirmation = $this->mailConfirmationService->getDemandeConfirmeeByIndividu($doctorant->getIndividu());
        if ($mailConfirmation !== null) {
            $viewmodel = new ViewModel([
                'email' => $mailConfirmation->getEmail(),
            ]);
            $viewmodel->setTemplate('doctorant/doctorant/demande-ok');
            return $viewmodel;
        }

        $mailConfirmation = $this->mailConfirmationService->getDemandeEnCoursByIndividu($doctorant->getIndividu());

        //Si on a déjà une demande en attente
        $back = $this->params()->fromRoute('back');

        if ($mailConfirmation !== null && ($back == 0 || $back === null)) {
            $viewmodel = new ViewModel([
                'doctorant' => $doctorant,
                'email' => $mailConfirmation->getEmail(),
            ]);
            $viewmodel->setTemplate('doctorant/doctorant/demande-encours');
            return $viewmodel;
        }

        if ($mailConfirmation === null) {
            $mailConfirmation = new MailConfirmation();
            $mailConfirmation->setIndividu($doctorant->getIndividu());
            $mailConfirmation->setEtat(MailConfirmation::ENVOYER);
        }
        $request = $this->getRequest();
        if ($request->isPost()) {

            $data = $request->getPost();
            $email = $data['email'];
            if (filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                $mailConfirmation->setEmail($email);
                $id = $this->mailConfirmationService->save($mailConfirmation);
                $this->mailConfirmationService->generateCode($id);
                return $this->redirect()->toRoute('mail-confirmation-envoie', ['id' => $id], [], true);
            } else {
                $this->flashMessenger()->addErrorMessage("L'adresse électronique fournie <strong>".$email."</strong> est non valide.");
            }
        }

        $form = $this->mailConfirmationForm;
        $form->setAttribute('action', $this->url()->fromRoute('doctorant/modifier-persopass', [], [], true));

        $form->bind($mailConfirmation);

        return new ViewModel([
            'doctorant' => $doctorant,
            'form' => $form,
            'title' => "Saisie de l'adresse électronique de contact",
            //'detournement' => (bool) $this->params('detournement'),
            //'emailBdD' => $variable->getValeur(),
        ]);
    }

    /**
     * @return Doctorant|null
     */
    private function requestDoctorant(): ?Doctorant
    {
        /** @var RouteMatch $routeMatch */
        $routeMatch = $this->getEvent()->getRouteMatch();

        return $routeMatch->getDoctorant();
    }
}
