<?php

namespace Application\Controller;

use Application\Entity\Db\Doctorant;
use Application\Entity\Db\MailConfirmation;
use Application\Form\MailConfirmationForm;
use Application\RouteMatch;
use Application\Service\Doctorant\DoctorantServiceAwareTrait;
use Application\Service\MailConfirmationService;
use Application\Service\Variable\VariableServiceAwareTrait;
use UnicaenAuth\Authentication\Adapter\Ldap as LdapAuthAdapter;
use Zend\Form\Form;
use Zend\InputFilter\Factory;
use Zend\View\Model\ViewModel;

class DoctorantController extends AbstractController
{
    use VariableServiceAwareTrait;
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
     * @param LdapAuthAdapter $ldapAuthAdapter
     */
    public function setLdapAuthAdapter(LdapAuthAdapter $ldapAuthAdapter)
    {
        $this->ldapAuthAdapter = $ldapAuthAdapter;
    }

    public function modifierPersopassAction()
    {
        $doctorant = $this->requestDoctorant();
        $mailConfirmation = $this->mailConfirmationService->getDemandeConfirmeeByIndividu($doctorant->getIndividu());
        if ($mailConfirmation !== null) {
            $viewmodel = new ViewModel([
                'email' => $mailConfirmation->getEmail(),
            ]);
            $viewmodel->setTemplate('application/doctorant/demande-ok');
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
            $viewmodel->setTemplate('application/doctorant/demande-encours');
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
     * @param string $identity
     * @param string $credential
     * @return array ['identity' => login, 'mail' => email] si la validation a réussie, [] sinon
     */
    private function validatePersopass($identity, $credential)
    {
        /** @var LdapAuthAdapter $authAdapter */
        $authAdapter = $this->ldapAuthAdapter;

        $success = $authAdapter->authenticateUsername($identity, $credential);
        if (! $success) {
            return [];
        }

        $usernames = LdapAuthAdapter::extractUsernamesUsurpation($identity);
        if (count($usernames) === 2) {
            list (, $identity) = $usernames;
        }

        $result = $authAdapter->getLdapAuthAdapter()->getLdap()->searchEntries("(supannAliasLogin=$identity)");
        $entry = current($result);

        $mail = null;
        if (!empty($entry['mail'])) {
            $mail = current($tmp = (array) $entry['mail']);
        }

        return [
            'identity' => $identity,
            'mail'     => $mail,
        ];
    }

    /**
     * @return Form
     */
    private function getModifierPersopassForm()
    {
        $form = new Form();
        $form->add([
            'type'       => 'Text',
            'name'       => 'identity',
            'options'    => [
                'label' => 'Identifiant',
            ],
            'attributes' => [
                'title' => "",
            ],
        ]);
        $form->add([
            'type'       => 'Password',
            'name'       => 'credential',
            'options'    => [
                'label' => 'Mot de passe',
            ],
            'attributes' => [
                'title' => "",
            ],
        ]);
        $form->add([
            'name'       => 'submit',
            'type'       => 'Submit',
            'attributes' => [
                'value' => 'Valider',
                'class' => 'btn btn-primary',
            ],
        ]);
        $form->setInputFilter((new Factory())->createInputFilter([
            'identity' => [
                'name' => 'identity',
                'required' => true,
            ],
            'credential' => [
                'name' => 'credential',
                'required' => true,
            ],
        ]));

        return $form;
    }

    /**
     * @return Doctorant
     */
    private function requestDoctorant()
    {
        /** @var RouteMatch $routeMatch */
        $routeMatch = $this->getEvent()->getRouteMatch();

        return $routeMatch->getDoctorant();
    }
}
