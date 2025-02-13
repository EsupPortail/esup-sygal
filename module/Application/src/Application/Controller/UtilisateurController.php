<?php

namespace Application\Controller;

use Application\Entity\Db\Utilisateur;
use Application\Filter\NomCompletFormatter;
use Application\Form\CreationUtilisateurForm;
use Application\Form\InitCompteFormAwareTrait;
use Application\Process\Utilisateur\UtilisateurProcessAwareTrait;
use Application\Search\Controller\SearchControllerInterface;
use Application\Search\Controller\SearchControllerTrait;
use Application\Search\SearchServiceAwareTrait;
use Application\Service\Notification\ApplicationNotificationFactoryAwareTrait;
use Application\Service\Role\ApplicationRoleServiceAwareTrait;
use Application\Service\UserContextServiceAwareTrait;
use Application\Service\Utilisateur\UtilisateurServiceAwareTrait;
use Application\SourceCodeStringHelperAwareTrait;
use Doctrine\ORM\ORMException;
use Exception;
use Formation\Service\Session\SessionServiceAwareTrait;
use Individu\Controller\IndividuController;
use Individu\Entity\Db\Individu;
use Individu\Service\IndividuServiceAwareTrait;
use JetBrains\PhpStorm\Deprecated;
use Laminas\Authentication\AuthenticationService;
use Laminas\EventManager\EventInterface;
use Laminas\Http\Request;
use Laminas\Http\Response;
use Laminas\Paginator\Paginator as LaminasPaginator;
use Laminas\View\Model\JsonModel;
use Laminas\View\Model\ViewModel;
use Notification\Service\NotifierServiceAwareTrait;
use Structure\Service\EcoleDoctorale\EcoleDoctoraleServiceAwareTrait;
use Structure\Service\Etablissement\EtablissementServiceAwareTrait;
use Structure\Service\Structure\StructureServiceAwareTrait;
use Structure\Service\UniteRecherche\UniteRechercheServiceAwareTrait;
use Acteur\Service\ActeurThese\ActeurTheseServiceAwareTrait;
use UnexpectedValueException;
use UnicaenApp\Exception\RuntimeException;
use UnicaenApp\Service\EntityManagerAwareTrait;
use UnicaenApp\Service\Mailer\MailerServiceAwareTrait;
use UnicaenAuthentification\Entity\Shibboleth\ShibUser;
use UnicaenAuthentification\Options\ModuleOptions;
use UnicaenAuthentification\Service\User as AuthentificationUserService;
use UnicaenAuthToken\Controller\TokenController;
use UnicaenAuthToken\Service\TokenServiceAwareTrait;
use UnicaenAuthToken\Service\TokenServiceException;
use Webmozart\Assert\Assert;

/**
 * @method \Application\Controller\Plugin\Forward forward()
 */
class UtilisateurController extends \UnicaenAuthentification\Controller\UtilisateurController implements SearchControllerInterface
{
    use SearchServiceAwareTrait;
    use SearchControllerTrait;

    use ActeurTheseServiceAwareTrait;
    use UtilisateurServiceAwareTrait;
    use UserContextServiceAwareTrait;
    use ApplicationRoleServiceAwareTrait;
    use IndividuServiceAwareTrait;
    use EntityManagerAwareTrait;
    use EcoleDoctoraleServiceAwareTrait;
    use UniteRechercheServiceAwareTrait;
    use EtablissementServiceAwareTrait;
    use NotifierServiceAwareTrait;
    use ApplicationNotificationFactoryAwareTrait;
    use StructureServiceAwareTrait;
    use SourceCodeStringHelperAwareTrait;
    use TokenServiceAwareTrait;
    use MailerServiceAwareTrait;

    use UtilisateurProcessAwareTrait;

    use SessionServiceAwareTrait;

    use InitCompteFormAwareTrait;

    /**
     * @var ModuleOptions
     */
    private $authModuleOptions;

    /**
     * @var AuthenticationService
     */
    protected $authenticationService;

    private CreationUtilisateurForm $creationUtilisateurForm;

    protected AuthentificationUserService $authentificationUserService;

    public function setAuthentificationUserService(AuthentificationUserService $authentificationUserService): void
    {
        $this->authentificationUserService = $authentificationUserService;
    }

    /**
     * @param ModuleOptions $authModuleOptions
     */
    public function setAuthModuleOptions(ModuleOptions $authModuleOptions)
    {
        $this->authModuleOptions = $authModuleOptions;
    }

    /**
     * @param AuthenticationService $authenticationService
     */
    public function setAuthenticationService(AuthenticationService $authenticationService)
    {
        $this->authenticationService = $authenticationService;
    }

    public function setCreationUtilisateurForm(CreationUtilisateurForm $creationUtilisateurForm): void
    {
        $this->creationUtilisateurForm = $creationUtilisateurForm;
    }

    /**
     * NOTA BENE : ce sont les individus et non les utilisateurs qui portent les rôles.
     *
     * @return ViewModel|Response
     */
    public function indexAction()
    {
        $result = $this->search();
        if ($result instanceof Response) {
            return $result;
        }
        /** @var LaminasPaginator $paginator */
        $paginator = $result;

        return new ViewModel([
            'paginator' => $paginator,
            'filters' => $this->filters(),
        ]);
    }

    /**
     * @return ViewModel
     */
    public function voirAction(): ViewModel
    {
        $id = $this->params()->fromRoute('utilisateur');

        /** @var Utilisateur $utilisateur */
        $utilisateur = $this->utilisateurService->getRepository()->find($id);

        $userTokens = $this->tokenService->findUserTokensByUserId($utilisateur->getId());
        $this->tokenService->injectLogInUriInUserTokens($userTokens);

//        $rolesData = [];
//        if ($individu = $utilisateur->getIndividu()) {
//            /** @var ViewModel $vm */
//            /** @see IndividuController::rolesAction() */
//            $vm = $this->forward()->dispatch(IndividuController::class, ['action' => 'roles', 'individu' => $individu->getId()]);
//            $rolesData = $vm->getVariables();
//        }

        return new ViewModel([
            'utilisateur' => $utilisateur,
            'tokens' => $userTokens,
//            'rolesData' => $rolesData,
            'redirect' => $this->url()->fromRoute(null, [], [], true),
        ]);

    }

    #[Deprecated(
        reason: 'Utiliser IndividuController::rechercherAction à la place.',
        replacement: 'IndividuController::rechercherAction')
    ]
    public function rechercherIndividuAction(?string $type = null) : JsonModel
    {
        $type = $this->params()->fromQuery('type');
        if (($term = $this->params()->fromQuery('term'))) {
            $rows = $this->individuService->getRepository()->findByText($term, $type);
            $f = new NomCompletFormatter();
            $result = [];
            foreach ($rows as $row) {
                $prenoms23 = implode(' ', array_filter([$row['prenom2'], $row['prenom3']]));
                // mise en forme attendue par l'aide de vue FormSearchAndSelect
                $label = trim($f->filter($row) . ' ' . $prenoms23);
                $extra = $row['email'] ?: $row['source_code'];
                $result[] = array(
                    'id' => $row['id'], // identifiant unique de l'item
                    'label' => $label, // libellé de l'item
                    'text' => $label, // pour Select2.js
                    'extra' => $extra, // infos complémentaires (facultatives) sur l'item
                );
            }
            usort($result, fn($a, $b) => $a['label'] <=> $b['label']);

            return new JsonModel($result);
        }
        exit;
    }

    /**
     * @return \Laminas\Http\Response|\Laminas\View\Model\ViewModel
     */
    public function ajouterAction()
    {
        /** @var Request $request */
        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            $this->creationUtilisateurForm->setData($data);
            if ($this->creationUtilisateurForm->isValid()) {
                if (!trim($data['nomPatronymique'])) {
                    $data['nomPatronymique'] = $data['nomUsuel'];
                }
                if ($data['individu'] == '1') {
                    $individu = $this->individuService->newIndividuFromData($data->toArray());
                    $this->individuService->saveIndividu($individu);

                    $utilisateur = $this->utilisateurService->createUtilisateurFromIndividuAndFormData($individu, $data->toArray());
                } else {
                    $utilisateur = $this->utilisateurService->createFromFormData($data->toArray());
                }
                $this->authentificationUserService->updateUserPasswordResetToken($utilisateur);

                try {
                    $notif = $this->applicationNotificationFactory->createNotificationInitialisationCompte($utilisateur, $utilisateur->getPasswordResetToken());
                    $this->notifierService->trigger($notif);
                } catch (\Notification\Exception\RuntimeException $e) {
                    // aucun destinataire, todo : gerer le cas !
                }

                $this->flashMessenger()->addSuccessMessage("Utilisateur <strong>{$utilisateur->getUsername()}</strong> créé avec succès.");

                return $this->redirect()->toRoute('unicaen-utilisateur/voir', ['utilisateur' => $utilisateur->getId()], [], true);
            }
        }

        return new ViewModel([
            'form' => $this->creationUtilisateurForm,
        ]);
    }

    public function supprimerAction(): Response|ViewModel
    {
        if ($post = $this->params()->fromPost()) {
            Assert::keyExists($post, 'utilisateur');
            Assert::eq($post['utilisateur'], $this->params()->fromRoute('utilisateur'));
            $id = $post['utilisateur'];
            $utilisateur = $this->utilisateurService->getRepository()->find($id); /** @var Utilisateur $utilisateur */
            try {
                $this->utilisateurService->supprimerUtilisateur($utilisateur);
                $this->flashMessenger()->addSuccessMessage("Compte utilisateur suivant supprimé avec succès : " . $utilisateur->getUsername());
            } catch (Exception $e) {
                $this->flashMessenger()->addErrorMessage("Le compte utilisateur '{$utilisateur->getUsername()}' n'a pas pu être supprimé. " . $e->getMessage());
                return $this->redirect()->toRoute('unicaen-utilisateur/voir', ['utilisateur' => $utilisateur->getId()]);
            }
        }

        return $this->redirect()->toRoute('utilisateur');
    }

    /**
     * Usurpe l'identité d'un **individu**.
     *
     * *Pour pouvoir usurper l'identité d'un individu, il faut que celui-ci se soit connecté au moins
     * une fois à l'application, de manière à ce que son compte utilisateur ait été créé avec
     * des données complètes.*
     *
     * @return Response
     */
    public function usurperIndividuAction(): Response
    {
        $request = $this->getRequest();
        if (!$request instanceof Request) {
            exit(1);
        }

        $individuId = $request->getPost('individu', $request->getQuery('individu'));
        if (!$individuId) {
            return $this->redirect()->toRoute('home');
        }
        $individu = $this->individuService->getRepository()->find($individuId);

        /** @var Utilisateur $utilisateur */
        $utilisateur = $this->utilisateurService->getRepository()->findOneBy(['individu' => $individuId]);
        if ($utilisateur === null) {
            $message = sprintf(
                "La demande d'usurpation de l'individu '%s' (%d) a échoué car aucun compte utilisateur correspondant " .
                "n'a été trouvé. " .
                "Pour pouvoir usurper l'identité d'un individu, il faut que celui-ci se soit connecté au moins " .
                "une fois à l'application, de manière à ce que son compte utilisateur ait été créé avec " .
                "des données complètes.",
                $individu, $individu->getId()
            );
            $this->flashMessenger()->addErrorMessage($message);
            return $this->redirect()->toRoute('home');
        }

        $usernameUsurpe = $utilisateur->getUsername();
        $sessionIdentity = $this->userContextService->usurperIdentite($usernameUsurpe);
        if ($sessionIdentity !== null) {
            // cuisine spéciale si l'utilisateur courant s'est authentifié via Shibboleth
            $this->usurperIdentiteShib($utilisateur);
        }

        return $this->redirect()->toRoute('home');
    }

    /**
     * Instancie un ShibUser à partir des attibuts de l'utilisateur spécifié.
     *
     * @param Utilisateur $utilisateur
     * @return ShibUser
     */
    public function createShibUserFromUtilisateur(Utilisateur $utilisateur)
    {
        $individu = $utilisateur->getIndividu();
        if ($individu === null) {
            throw new RuntimeException("L'utilisateur '$utilisateur' n'a aucun individu lié");
        }

        $supannId = $this->sourceCodeStringHelper->removePrefixFrom($individu->getSourceCode());

        $toShibUser = new ShibUser();
        $toShibUser->setEppn($utilisateur->getUsername());
        $toShibUser->setId($supannId);
        $toShibUser->setDisplayName($individu->getNomComplet());
        $toShibUser->setEmail($utilisateur->getEmail());
        $toShibUser->setNom($individu->getNomUsuel());
        $toShibUser->setPrenom($individu->getPrenom());

        return $toShibUser;
    }

    public function retirerRoleAction()
    {
        /**
         * @var Request $request
         */
        $request = $this->getRequest();
        if ($request->isPost()) {
            /** @var Individu $individu */
            $individuId = $this->params()->fromRoute('individu');
            $individu = $this->getIndividuService()->getRepository()->find($individuId);
            $roleId = $this->params()->fromRoute('role');
            /** @var \Application\Entity\Db\Role $role */
            $role = $this->getApplicationRoleService()->getRepository()->find($roleId);

            $this->applicationRoleService->removeRole($individuId, $roleId);

            try {
                $notif = $this->applicationNotificationFactory->createNotificationChangementRole("retrait", $role, $individu);
                $this->notifierService->trigger($notif);
            } catch (\Notification\Exception\RuntimeException $e) {
                // aucun destinataire : étonnant mais pas très grave.
            }

            return new JsonModel(['status' => 'success', 'role' => (string) $role]);
        }

        return new JsonModel();
    }

    public function ajouterRoleAction()
    {
        /**
         * @var Request $request
         */
        $request = $this->getRequest();
        if ($request->isPost()) {
            /** @var Individu $individu */
            $individuId = $this->params()->fromRoute('individu');
            $individu = $this->getIndividuService()->getRepository()->find($individuId);
            $roleId = $this->params()->fromRoute('role');
            $role = $this->getApplicationRoleService()->getRepository()->find($roleId);

            $this->applicationRoleService->addRole($individuId, $roleId);

            try {
                $notif = $this->applicationNotificationFactory->createNotificationChangementRole("ajout", $role, $individu);
                $this->notifierService->trigger($notif);
            } catch (\Notification\Exception\RuntimeException $e) {
                // aucun destinataire, todo : gerer le cas !
            }
        }
        return false;
    }

    public function gererUtilisateurAction()
    {
        $individu = $this->getIndividuService()->getRequestedIndividu($this);
        $acteurs = $this->acteurTheseService->getRepository()->findActeursForIndividu($individu);
        $utilisateurs = $this->utilisateurService->getRepository()->findByIndividu($individu, $isLocal = true); // done
        // NB: findByIndividu() avec $isLocal = true renverra 1 utilisateur au maximum
        $utilisateur = $utilisateurs ? current($utilisateurs) : null;
        if ($utilisateur === null and $individu->getEmailPro() !== null) {
            $utilisateur = $this->utilisateurService->getRepository()->findByUsername($individu->getEmailPro());
        }

        return new ViewModel([
            'individu' => $individu,
            'acteurs' => $acteurs,
            'utilisateur' => $utilisateur,
        ]);
    }

    public function registerAction(): ViewModel
    {
        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            $this->creationUtilisateurForm->setData($data);
            if ($this->creationUtilisateurForm->isValid()) {
                try {
                    $utilisateur = $this->utilisateurProcess->createUtilisateurAndIndividuFromFormData($data->toArray());

                    return new ViewModel([
                        'utilisateur' => $utilisateur,
                    ]);
                } catch (Exception $e) {
                    $this->flashMessenger()->addErrorMessage(
                        "Une erreur est survenue, la création a été annulée ! " . $e->getMessage()
                    );
                }
            }
        }

        return new ViewModel([
            'utilisateur' => null,
            'form' => $this->creationUtilisateurForm,
        ]);
    }

    public function creerCompteLocalIndividuAction()
    {
        $individu = $this->getIndividuService()->getRequestedIndividu($this);
        $utilisateurs = $this->utilisateurService->getRepository()->findByIndividu($individu); // done

        if (empty($utilisateurs)) {
            $user = $this->utilisateurService->createFromIndividu($individu, $individu->getEmailPro(), 'none');
            $this->authentificationUserService->updateUserPasswordResetToken($user);

            $url = $this->url()->fromRoute(
                'utilisateur/init-compte',
                ['token' => $user->getPasswordResetToken()],
                ['query' => ['username' => $user->getUsername()], 'force_canonical' => true],
                true
            );
            try {
                $notif = $this->applicationNotificationFactory->createNotificationInitialisationCompte($user, $url);
                $this->notifierService->trigger($notif);
            } catch (\Notification\Exception\RuntimeException $e) {
                // aucun destinataire, todo : gerer le cas !
            }
        } else {
            $this->flashMessenger()->addErrorMessage('Impossible de créer le compte local car un utilisateur est déjà lié à cet individu.');
        }

        return $this->redirect()->toRoute('utilisateur/gerer-utilisateur', ['individu' => $individu->getId()], [], true);
    }

    public function resetPasswordAction()
    {
        $individu = $this->getIndividuService()->getRequestedIndividu($this);
        $utilisateurs = $this->utilisateurService->getRepository()->findByIndividu($individu, $isLocal = true); // done
        // NB: findByIndividu() avec $isLocal = true renverra 1 utilisateur au maximum
        $utilisateur = $utilisateurs ? current($utilisateurs) : null;

        if ($utilisateur !== null) {
            $this->authentificationUserService->updateUserPasswordResetToken($utilisateur);
            $url = $this->url()->fromRoute(
                'utilisateur/init-compte',
                ['token' => $utilisateur->getPasswordResetToken()],
                ['query' => ['username' => $utilisateur->getUsername()], 'force_canonical' => true],
                true
            );

            try {
                $notif = $this->applicationNotificationFactory->createNotificationResetCompte($utilisateur, $url);
                $this->notifierService->trigger($notif);
            } catch (\Notification\Exception\RuntimeException $e) {
                // aucun destinataire, todo : gerer le cas !
            }
        } else {
            $this->flashMessenger()->addErrorMessage('Impossible de réinitiliser la mot de passe car aucun utilisateur est lié');
        }

        return $this->redirect()->toRoute('utilisateur/gerer-utilisateur', ['individu' => $individu->getId()], [], true);
    }

    public function initCompteAction(): Response|ViewModel
    {
        $token = $this->params()->fromRoute('token');
        $username = $this->params()->fromQuery('username');

        $utilisateur = $this->utilisateurService->getRepository()->findByToken($token);
        if ($utilisateur === null) {
            return new ViewModel([
                'utilisateur' => null,
                'initialized' => false,
            ]);
        }

        $this->initCompteForm->setUsername($utilisateur->getUsername());
        $this->initCompteForm->setAttribute('action', $this->url()->fromRoute('utilisateur/init-compte', ['token' => $token], true));
        $this->initCompteForm->bind((new Utilisateur())->setUsername($username));

        /** @var Request $request */
        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            $this->initCompteForm->setData($data);
            if ($this->initCompteForm->isValid()) {
                $this->utilisateurService->changePassword($utilisateur, $data['password1']);

                return new ViewModel([
                    'utilisateur' => $utilisateur,
                    'initialized' => true,
                ]);
            }
        }

        return new ViewModel([
            'form' => $this->initCompteForm,
            'utilisateur' => $utilisateur,
            'initialized' => false,
        ]);
    }

    public function lierNouvelIndividuAction()
    {
        $utilisateur = $this->getUtilisateurService()->getRequestedUtilisateur($this);
        $title = "Lier l'utilisateur &laquo; {$utilisateur->getUsername()} &raquo; à un individu";
        $vars = [
            'title' => $title,
            'utilisateur' => $utilisateur,
        ];

        $vm = new ViewModel($vars);
        $vm->setTemplate('application/utilisateur/lier-individu');

        /** Délégation à {@see IndividuController::ajouterAction()} */
        $ajouterIndividuResponse = $this->forward()->dispatch(IndividuController::class, [
            'action' => $action = 'ajouter',
            'utilisateur' => $utilisateur->getId(),
            'return' => true,
        ]);
        if ($ajouterIndividuResponse instanceof Response) {
            // si une Response est retournée, on est dans la cas où l'individu a été créé et son id est dans le Header
            if (!($field = $ajouterIndividuResponse->getHeaders()->get('individu'))) {
                throw new UnexpectedValueException(sprintf(
                    "L'action %s::%s est sensée retourner une Response contenant un champ 'individu' dans le Header",
                    IndividuController::class, $action
                ));
            }
            $individuId = $field->getFieldValue();

            return $this->redirect()->toRoute('utilisateur/lier-individu',
                ['utilisateur' => $utilisateur->getId(), 'individu' => $individuId],
                ['query' => ['modal' => 1]],
                true
            );
        }
        else {
            /** @var \Individu\Form\IndividuForm $form */
            $form = $ajouterIndividuResponse->getVariable('form');
            $form->setAttribute('action', $this->url()->fromRoute(null, [], [], true));

            $vm->addChild($ajouterIndividuResponse, 'ajouterIndividuChildView');
        }

        return $vm;
    }

    public function lierIndividuAction()
    {
        $utilisateur = $this->getUtilisateurService()->getRequestedUtilisateur($this);
        $title = "Lier l'utilisateur &laquo; {$utilisateur->getUsername()} &raquo; à un individu";
        $vars = [
            'title' => $title,
            'utilisateur' => $utilisateur,
        ];

        /**
         * Si on a un utilisateur et un individu alors on doit réaliser le lien
         */
        $individu = $this->getIndividuService()->getRequestedIndividu($this);
        if ($individu !== null) {
            $utilisateur->setIndividu($individu);
            try {
                $this->getUtilisateurService()->getEntityManager()->flush($utilisateur);
            } catch(ORMException $e) {
                throw new RuntimeException("Un problème est survenu lors de l'enregistrement en base de donnée", 0, $e);
            }
            $vars['individu'] = $individu;

            return new ViewModel($vars);
        }

        $request = $this->getRequest();
        if ($request->isPost()) {

            /**
             * Si on revient du post un individu à été selectionné et on examine pour validation
             */
            $data = $request->getPost();
            $individuId = $data['individu']['id'];
            /** @var Individu $individu */
            $individu = $this->getIndividuService()->getRepository()->find($individuId);
            if ($individu !== null) {
                $acteurs = $this->getActeurTheseService()->getRepository()->findActeursForIndividu($individu);
                $roles = $individu->getRoles();

                $vars['individu'] = $individu;
                $vars['acteurs'] = $acteurs;
                $vars['roles'] = $roles;

                return new ViewModel($vars);
            }
        }

        return new ViewModel($vars);
    }

    public function delierIndividuAction()
    {
        $utilisateur = $this->getUtilisateurService()->getRequestedUtilisateur($this);
        $utilisateur->setIndividu(null);
        try {
            $this->getUtilisateurService()->getEntityManager()->flush($utilisateur);
        } catch (ORMException $e) {
            throw new RuntimeException("Un problème est survenu lors de l'enregistrement en base de donnée", 0, $e);
        }
        return $this->redirect()->toRoute('unicaen-utilisateur/voir', ['utilisateur' => $utilisateur->getId()], [], true);
    }

    /**
     * Demande de création d'un jeton d'authentification.
     *
     * NB : à l'issue de la création, l'utilisateur reçoit automatiquement son jeton, cf {@see envoyerToken()}.
     */
    public function ajouterTokenAction(): Response|ViewModel
    {
        $utilisateur = $this->getUtilisateurService()->getRequestedUtilisateur($this, 'user');

        // on délègue au module unicaen/auth-token
        try {
            return $this->forward()->dispatch(TokenController::class, [
                'action' => 'creer',
                'user' => $utilisateur->getId(),
            ]);
        } catch (Exception $e) {
            $this->flashMessenger()->addErrorMessage("Opération impossible : " . $e->getMessage());
            return $this->redirect()->toRoute('unicaen-utilisateur/voir', ['utilisateur' => $utilisateur->getId()], [], true);
        }
    }

    public function listenEventsOf(TokenController $tokenController): void
    {
        // écoute pour envoyer automatiquement tout jeton nouvellement créé à l'utlisateur
        $tokenController->getEventManager()->attach(
            $tokenController::EVENT_TOKEN_CREATE_AFTER_SAVE,
            [$this, 'envoyerToken']
        );
    }

    public function envoyerToken(EventInterface $event): void
    {
        /** @var \Application\Entity\Db\UtilisateurToken $utilisateurToken */
        $utilisateurToken = $event->getParam('userToken');
        /** @var \Laminas\Mail\Message $mailMessage */
        $mailMessage = $event->getParam('mailMessage');

        try {
            try {
                $message = $this->mailerService->send($mailMessage);
            } catch (Exception $e) {
                throw new \RuntimeException("L'envoi du token par mail a échoué", null, $e);
            }

            $utilisateurToken->setSentOn(new \DateTime('now'));
            $this->tokenService->saveUserToken($utilisateurToken);

            $this->flashMessenger()->addSuccessMessage(sprintf(
                "Le jeton utilisateur a été envoyé avec succès à %s.",
                $message->getTo()->rewind()->getEmail()
            ));
        } catch (Exception $e) {
            throw new RuntimeException("Erreur rencontrée lors de l'envoi du jeton utilisateur.", null, $e);
        }
    }
}