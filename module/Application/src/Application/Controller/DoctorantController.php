<?php

namespace Application\Controller;

use Application\Entity\Db\Doctorant;
use Application\Filter\DbExceptionFormatter;
use Application\RouteMatch;
use Application\Service\Doctorant\DoctorantServiceAwareInterface;
use Application\Service\Doctorant\DoctorantServiceAwareTrait;
use Application\Service\Env\EnvServiceAwareInterface;
use Application\Service\Env\EnvServiceAwareTrait;
use Doctrine\DBAL\DBALException;
use UnicaenAuth\Authentication\Adapter\Ldap as LdapAuthAdapter;
use Zend\Form\Form;
use Zend\InputFilter\Factory;
use Zend\Stdlib\ParametersInterface;
use Zend\View\Model\ViewModel;

class DoctorantController extends AbstractController implements
    EnvServiceAwareInterface, DoctorantServiceAwareInterface
{
    use EnvServiceAwareTrait;
    use DoctorantServiceAwareTrait;

    public function modifierPersopassAction()
    {
        $doctorant = $this->requestDoctorant();
        $form = $this->getModifierPersopassForm();

        $form->setData([
            'identity' => $doctorant->getPersopass(),
            'credential' => '',
        ]);

        $request = $this->getRequest();
        if ($request->isPost()) {
            /** @var ParametersInterface $post */
            $post = $this->getRequest()->getPost();
            $form->setData($post);
            if ($form->isValid()) {
                if ($result = $this->validatePersopass($post['identity'], $post['credential'])) {
                    try {
                        $this->doctorantService->updateDoctorant($doctorant, $result);

                        // prise en compte du paramètre GET 'return' positionné par SaisiePersopassRouteDeflector :
                        if ($redirectUri = $this->params()->fromQuery('return')) {
                            return $this->redirect()->toUrl($redirectUri);
                        }

                        if (!$request->isXmlHttpRequest()) {
                            return $this->redirect()->toRoute('home', [], [], true);
                        }
                    } catch (DBALException $e) {
                        $f = new DbExceptionFormatter();
                        $message = $f->filter($e);
                        $form->setMessages(['credential' => [$message]]);
                    }
                }
                else {
                    $form->setMessages(['credential' => ["Identifiant ou mot de passe incorrect"]]);
                }
            }
        }

        $form->setAttribute('action', $request->getRequestUri());

        return new ViewModel([
            'doctorant' => $doctorant,
            'form' => $form,
            'title' => "Saisie du Persopass",
            'detournement' => (bool) $this->params('detournement'),
            'emailBdD' => $this->envService->findOneByAnnee()->getEmailBdD(),
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
        $authAdapter = $this->getServiceLocator()->get('UnicaenAuth\Authentication\Adapter\Ldap');

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
