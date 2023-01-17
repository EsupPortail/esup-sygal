<?php

namespace Application\Controller;

use Application\Entity\Db\Utilisateur;
use Application\Form\CreationUtilisateurForm;
use Application\Form\InitCompteForm;
use Application\Form\InitCompteFormAwareTrait;
use Application\Search\Controller\SearchControllerInterface;
use Application\Search\Controller\SearchControllerTrait;
use Application\Search\SearchServiceAwareTrait;
use Application\Service\Notification\ApplicationNotificationFactoryAwareTrait;
use These\Service\Acteur\ActeurServiceAwareTrait;
use Doctorant\Service\DoctorantServiceAwareTrait;
use Structure\Service\EcoleDoctorale\EcoleDoctoraleServiceAwareTrait;
use Structure\Service\Etablissement\EtablissementServiceAwareTrait;
use Notification\Service\NotifierServiceAwareTrait;
use Application\Service\Role\RoleServiceAwareTrait;
use Structure\Service\Structure\StructureServiceAwareTrait;
use Structure\Service\UniteRecherche\UniteRechercheServiceAwareTrait;
use Application\Service\UserContextServiceAwareTrait;
use Application\Service\Utilisateur\UtilisateurServiceAwareTrait;
use Application\SourceCodeStringHelperAwareTrait;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\Query\Expr;
use Formation\Entity\Db\Formateur;
use Formation\Entity\Db\Session;
use Formation\Service\Session\SessionServiceAwareTrait;
use Individu\Controller\IndividuController;
use Individu\Entity\Db\Individu;
use Individu\Service\IndividuServiceAwareTrait;
use Laminas\Authentication\AuthenticationService;
use Laminas\EventManager\EventInterface;
use Laminas\Http\Request;
use Laminas\Http\Response;
use Laminas\Paginator\Paginator as LaminasPaginator;
use Laminas\View\Model\JsonModel;
use Laminas\View\Model\ViewModel;
use UnexpectedValueException;
use UnicaenApp\Exception\RuntimeException;
use UnicaenApp\Service\EntityManagerAwareTrait;
use UnicaenAuth\Entity\Shibboleth\ShibUser;
use UnicaenAuth\Options\ModuleOptions;
use UnicaenAuth\Service\ShibService;
use UnicaenAuth\Service\Traits\UserServiceAwareTrait;
use UnicaenAuthToken\Controller\TokenController;
use UnicaenAuthToken\Service\TokenServiceAwareTrait;

/**
 * @method \Application\Controller\Plugin\Forward forward()
 */
class UtilisateurController extends \UnicaenAuth\Controller\UtilisateurController implements SearchControllerInterface
{
    use SearchServiceAwareTrait;
    use SearchControllerTrait;

    use ActeurServiceAwareTrait;
    use UtilisateurServiceAwareTrait;
    use UserContextServiceAwareTrait;
    use RoleServiceAwareTrait;
    use IndividuServiceAwareTrait;
    use EntityManagerAwareTrait;
    use EcoleDoctoraleServiceAwareTrait;
    use UniteRechercheServiceAwareTrait;
    use EtablissementServiceAwareTrait;
    use NotifierServiceAwareTrait;
    use ApplicationNotificationFactoryAwareTrait;
    use StructureServiceAwareTrait;
    use SourceCodeStringHelperAwareTrait;
    use UserServiceAwareTrait;
    use TokenServiceAwareTrait;

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

    /**
     * @var ShibService
     */
    protected $shibService;

    /**
     * @var CreationUtilisateurForm
     */
    private $creationUtilisateurForm;

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

    /**
     * @param ShibService $shibService
     */
    public function setShibService(ShibService $shibService)
    {
        $this->shibService = $shibService;
    }

    /**
     * @param CreationUtilisateurForm $creationUtilisateurForm
     */
    public function setCreationUtilisateurForm(CreationUtilisateurForm $creationUtilisateurForm)
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

        $rolesData = [];
        if ($individu = $utilisateur->getIndividu()) {
            /** @var ViewModel $vm */
            $vm = $this->forward()->dispatch(IndividuController::class, ['action' => 'roles', 'individu' => $individu->getId()]);
            $rolesData = $vm->getVariables();
        }

        return new ViewModel([
            'utilisateur' => $utilisateur,
            'tokens' => $userTokens,
            'rolesData' => $rolesData,
            'redirect' => $this->url()->fromRoute(null, [], [], true),
        ]);

    }

//    /**
//     * @param \Individu\Entity\Db\Individu $individu
//     * @return \Application\Entity\Db\Role[]
//     */
//    private function collectRolesDynamiquesForIndividu(Individu $individu): array
//    {
//        $roles = [];
//
//        // rôles d'acteur
//        $acteurs = $this->acteurService->getRepository()->findActeursByIndividu($individu);
//        if ($acteurs) {
//            $acteursDirecteurThese = $this->acteurService->filterActeursDirecteurThese($acteurs);
//            $acteursCoDirecteurThese = $this->acteurService->filterActeursCoDirecteurThese($acteurs);
//            $acteursPresidentJury = $this->acteurService->filterActeursPresidentJury($acteurs);
//            $acteursRapporteurJury = $this->acteurService->filterActeursRapporteurJury($acteurs);
//            $roles = array_merge($roles, array_map(
//                function (Acteur $a) {
//                    return $a->getRole();
//                },
//                array_merge($acteursDirecteurThese, $acteursCoDirecteurThese, $acteursPresidentJury, $acteursRapporteurJury)
//            ));
//        }
//
//        $doctorant = $this->doctorantService->getRepository()->findOneByIndividu($individu);
//        if ($doctorant) {
//            $roles[] = $this->roleService->getRepository()->findRoleDoctorantForEtab($doctorant->getEtablissement());
//        }
//
//        $sessions = $this->getSessionService()->getEntityManager()->getRepository(Session::class)->findSessionsByFormateur($individu);
//        if (!empty($sessions)) {
//            $formateur = $this->getRoleService()->getRepository()->findByCode(Formateur::ROLE);
//            $roles[] = $formateur;
//        }
//
//        return array_unique($roles);
//    }

    /**
     * AJAX.
     *
     * Recherche d'un Individu.
     *
     * @param string|null $type => permet de spécifier un type d'acteur ...
     * @return JsonModel
     */
    public function rechercherIndividuAction(?string $type = null) : JsonModel
    {
        $type = $this->params()->fromQuery('type');
        if (($term = $this->params()->fromQuery('term'))) {
            $rows = $this->individuService->getRepository()->findByText($term, $type);
            $result = [];
            foreach ($rows as $row) {
                $prenoms = implode(' ', array_filter([$row['prenom1'], $row['prenom2'], $row['prenom3']]));
                // mise en forme attendue par l'aide de vue FormSearchAndSelect
                $label = $row['nom_usuel'] . ' ' . $prenoms;
                $extra = $row['email'] ?: $row['source_code'];
                $result[] = array(
                    'id' => $row['id'], // identifiant unique de l'item
                    'label' => $label,     // libellé de l'item
                    'extra' => $extra,     // infos complémentaires (facultatives) sur l'item
                );
            }
            usort($result, function ($a, $b) {
                return strcmp($a['label'], $b['label']);
            });

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

                    $utilisateur = $this->utilisateurService->createFromIndividuAndFormData($individu, $data->toArray());
                } else {
                    $utilisateur = $this->utilisateurService->createFromFormData($data->toArray());
                }
                $this->userService->updateUserPasswordResetToken($utilisateur);

                try {
                    $notif = $this->applicationNotificationFactory->createNotificationInitialisationCompte($utilisateur, $utilisateur->getPasswordResetToken());
                    $this->notifierService->trigger($notif);
                } catch (\Notification\Exception\RuntimeException $e) {
                    // aucun destinataire, todo : gerer le cas !
                }

                $this->flashMessenger()->addSuccessMessage("Utilisateur <strong>{$utilisateur->getUsername()}</strong> créé avec succès.");

                return $this->redirect()->toRoute('utilisateur');
            }
        }

        return new ViewModel([
            'form' => $this->creationUtilisateurForm,
        ]);
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
        $sessionIdentity = $this->serviceUserContext->usurperIdentite($usernameUsurpe);
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
            $role = $this->getRoleService()->getRepository()->find($roleId);

            $this->roleService->removeRole($individuId, $roleId);

            try {
                $notif = $this->applicationNotificationFactory->createNotificationChangementRole("retrait", $role, $individu);
                $this->notifierService->trigger($notif);
            } catch (\Notification\Exception\RuntimeException $e) {
                // aucun destinataire, todo : gerer le cas !
            }
        }

        return new ViewModel([]);
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
            $role = $this->getRoleService()->getRepository()->find($roleId);

            $this->roleService->addRole($individuId, $roleId);

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
        $acteurs = $this->acteurService->getRepository()->findActeursForIndividu($individu);
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

    public function creerCompteLocalIndividuAction()
    {
        $individu = $this->getIndividuService()->getRequestedIndividu($this);
        $utilisateurs = $this->utilisateurService->getRepository()->findByIndividu($individu); // done

        if (empty($utilisateurs)) {
            $user = $this->utilisateurService->createFromIndividu($individu, $individu->getEmailPro(), 'none');
            $this->userService->updateUserPasswordResetToken($user);

            $url = $this->url()->fromRoute('utilisateur/init-compte', ['token' => $user->getPasswordResetToken()], ['force_canonical' => true], true);
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
            $this->userService->updateUserPasswordResetToken($utilisateur);
            $url = $this->url()->fromRoute('utilisateur/init-compte', ['token' => $utilisateur->getPasswordResetToken()], ['force_canonical' => true], true);

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

    public function initCompteAction()
    {
        $token = $this->params()->fromRoute('token');
        $utilisateur = $this->utilisateurService->getRepository()->findByToken($token);
        if ($utilisateur === null) {
            return new ViewModel([
            ]);
        }

        /** @var InitCompteForm $form */
        $form = $this->getInitCompteForm();
        $form->setUsername($utilisateur->getUsername());
        $form->setAttribute('action', $this->url()->fromRoute('utilisateur/init-compte', ['token' => $token], [], true));
        $form->bind(new Utilisateur());

        /** @var Request $request */
        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            $form->setData($data);
            if ($form->isValid()) {
                $this->utilisateurService->changePassword($utilisateur, $data['password1']);
                $this->flashMessenger()->addSuccessMessage('Mot de passe initialisé avec succés.');
                return $this->redirect()->toRoute('home');
            }
        }

        return new ViewModel([
            'form' => $form,
            'utilisateur' => $utilisateur,
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
                $acteurs = $this->getActeurService()->getRepository()->findActeursForIndividu($individu);
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
        return $this->redirect()->toRoute('utilisateur/voir', ['utilisateur' => $utilisateur->getId()], [], true);
    }

    /**
     * Demande de création d'un jeton d'authentification.
     *
     * NB : à l'issue de la création, l'utilisateur reçoit automatiquement son jeton, cf {@see envoyerToken()}.
     *
     * @return ViewModel|Response
     */
    public function ajouterTokenAction()
    {
        $utilisateur = $this->getUtilisateurService()->getRequestedUtilisateur($this);

        // on délègue au module unicaen/auth-token
        return $this->forward()->dispatch(TokenController::class, [
            'action' => 'creer',
            'user' => $utilisateur->getId(),
        ]);
    }

    public function listenEventsOf(TokenController $tokenController)
    {
        // écoute pour envoyer automatiquement tout jeton nouvellement créé à l'utlisateur
        $tokenController->getEventManager()->attach(
            $tokenController::EVENT_TOKEN_CREATE_AFTER_SAVE,
            [$this, 'envoyerToken']
        );
    }

    public function envoyerToken(EventInterface $event)
    {
        /** @var \Application\Entity\Db\UtilisateurToken $utilisateurToken */
        $utilisateurToken = $event->getParam('userToken');

        // on délègue au module unicaen/auth-token
        $this->forward()->dispatch(TokenController::class, [
            'action' => 'envoyer',
            'userToken' => $utilisateurToken->getId(),
        ]);
    }
}