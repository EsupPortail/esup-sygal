<?php

namespace Doctorant\Controller;

use Application\Controller\AbstractController;
use Application\Entity\Db\MailConfirmation;
use Application\Form\MailConfirmationForm;
use Application\RouteMatch;
use Application\Service\MailConfirmationService;
use Doctorant\Entity\Db\Doctorant;
use Doctorant\Form\ConsentementForm;
use Doctorant\Service\DoctorantServiceAwareTrait;
use UnicaenAuth\Authentication\Adapter\Ldap as LdapAuthAdapter;
use Laminas\View\Model\ViewModel;

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
     * @var \Doctorant\Form\ConsentementForm
     */
    private $donneesPersoForm;

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

    public function setDonneesPersoForm(ConsentementForm $donneesPersoForm)
    {
        $this->donneesPersoForm = $donneesPersoForm;
    }

    /**
     * @return \Laminas\View\Model\ViewModel
     */
    public function emailContactAction(): ViewModel
    {
        $doctorant = $this->requestDoctorant();

        // Si on a une demande en attente
        $mailConfirmation = $this->mailConfirmationService->fetchMailConfirmationsForIndividu($doctorant->getIndividu());
        if ($mailConfirmation && $mailConfirmation->estEnvoye()) {
            return $this->changementEmailContactEnCours($doctorant, $mailConfirmation);
        }

        $mailConfirmation = $this->mailConfirmationService->fetchMailConfirmationsForIndividu($doctorant->getIndividu());

        return new ViewModel([
            'doctorant' => $doctorant,
            'mailConfirmation' => $mailConfirmation,
        ]);
    }

    /**
     * @return \Laminas\Http\Response|\Laminas\View\Model\ViewModel
     */
    public function modifierEmailContactAction()
    {
        $doctorant = $this->requestDoctorant();
        $force = (bool) $this->params()->fromQuery('force', false);

        // Si on a déjà une demande confirmée...
        $mailConfirmation = $this->mailConfirmationService->fetchMailConfirmationsForIndividu($doctorant->getIndividu());
        if ($mailConfirmation && $mailConfirmation->estConfirme() && !$force) {
            return $this->changementEmailContactConfirme($mailConfirmation);
        }

        // Si on a déjà une demande en attente...
        $mailConfirmation = $this->mailConfirmationService->fetchMailConfirmationsForIndividu($doctorant->getIndividu());
        if ($mailConfirmation && $mailConfirmation->estEnvoye() && !$force) {
            return $this->changementEmailContactEnCours($doctorant, $mailConfirmation);
        }

        $mailConfirmation = new MailConfirmation();
        $mailConfirmation->setIndividu($doctorant->getIndividu());
        $mailConfirmation->setEtat(MailConfirmation::ENVOYE);

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
                $this->flashMessenger()->addErrorMessage("L'adresse électronique fournie n'est pas valide : " . $email);
            }
        }

        $this->mailConfirmationForm->setAttribute('action',
            $this->url()->fromRoute('doctorant/modifier-email-contact', [], ['query' => ['force' => (int)$force]], true));
        $this->mailConfirmationForm->bind($mailConfirmation);

        return new ViewModel([
            'doctorant' => $doctorant,
            'form' => $this->mailConfirmationForm,
            'title' => "Saisie de l'adresse électronique de contact",
        ]);
    }

    /**
     * @param \Application\Entity\Db\MailConfirmation $mailConfirmation
     * @return \Laminas\View\Model\ViewModel
     */
    public function changementEmailContactConfirme(MailConfirmation $mailConfirmation): ViewModel
    {
        $viewmodel = new ViewModel([
            'email' => $mailConfirmation->getEmail(),
        ]);

        $viewmodel->setTemplate('doctorant/doctorant/demande-ok');

        return $viewmodel;
    }

    /**
     * @param \Doctorant\Entity\Db\Doctorant $doctorant
     * @param \Application\Entity\Db\MailConfirmation $mailConfirmation
     * @return \Laminas\View\Model\ViewModel
     */
    public function changementEmailContactEnCours(Doctorant $doctorant, MailConfirmation $mailConfirmation): ViewModel
    {
        $viewmodel = new ViewModel([
            'doctorant' => $doctorant,
            'email' => $mailConfirmation->getEmail(),
        ]);
        $viewmodel->setTemplate('doctorant/doctorant/demande-encours');

        return $viewmodel;
    }

    public function consentementAction()
    {
        $doctorant = $this->requestDoctorant();

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
