<?php

namespace Application\Controller;

use Application\Entity\Db\Individu;
use Application\Entity\Db\TypeStructure;
use Application\Entity\Db\Utilisateur;
use Application\Form\CreationUtilisateurForm;
use Application\Form\InitCompteForm;
use Application\Form\InitCompteFormAwareTrait;
use Application\Search\Controller\SearchControllerInterface;
use Application\Search\Controller\SearchControllerTrait;
use Application\Search\SearchServiceAwareTrait;
use Application\Service\Acteur\ActeurServiceAwareTrait;
use Application\Service\EcoleDoctorale\EcoleDoctoraleServiceAwareTrait;
use Application\Service\Etablissement\EtablissementServiceAwareTrait;
use Application\Service\Individu\IndividuServiceAwareTrait;
use Application\Service\Notification\NotifierServiceAwareTrait;
use Application\Service\Role\RoleServiceAwareTrait;
use Application\Service\Structure\StructureServiceAwareTrait;
use Application\Service\UniteRecherche\UniteRechercheServiceAwareTrait;
use Application\Service\UserContextServiceAwareTrait;
use Application\Service\Utilisateur\UtilisateurServiceAwareTrait;
use Application\SourceCodeStringHelperAwareTrait;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\Query\Expr;
use UnicaenApp\Exception\RuntimeException;
use UnicaenApp\Service\EntityManagerAwareTrait;
use UnicaenAuth\Entity\Shibboleth\ShibUser;
use UnicaenAuth\Options\ModuleOptions;
use UnicaenAuth\Service\ShibService;
use UnicaenAuth\Service\Traits\UserServiceAwareTrait;
use UnicaenAuthToken\Controller\TokenController;
use UnicaenAuthToken\Service\TokenServiceAwareTrait;
use Zend\Authentication\AuthenticationService;
use Zend\EventManager\EventInterface;
use Zend\Http\Request;
use Zend\Http\Response;
use Zend\Paginator\Paginator as ZendPaginator;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;

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
    use StructureServiceAwareTrait;
    use SourceCodeStringHelperAwareTrait;
    use UserServiceAwareTrait;
    use TokenServiceAwareTrait;

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
        /** @var ZendPaginator $paginator */
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

        $rolesAffectes = [];
        if ($individu = $utilisateur->getIndividu()) {
            $rolesAffectes = $individu->getRoles();
        }

        $roles = $this->roleService->findAllRoles();

        // établissements : pour l'instant les rôles ne concernent que des établissements d'inscription donc on flitre
        $etablissementsQb = $this->structureService->getAllStructuresAffichablesByTypeQb(TypeStructure::CODE_ETABLISSEMENT, 'libelle', true);
        $etablissementsQb->join('structure.etablissement', 'etab', Expr\Join::WITH, 'etab.estInscription = true');
        $etablissements = $etablissementsQb->getQuery()->execute();

        $unites = $this->structureService->getAllStructuresAffichablesByType(TypeStructure::CODE_UNITE_RECHERCHE, 'libelle', true, true);
        $ecoles = $this->structureService->getAllStructuresAffichablesByType(TypeStructure::CODE_ECOLE_DOCTORALE, 'libelle', true, true);

        $userTokens = $this->tokenService->findUserTokensByUserId($utilisateur->getId());
        $this->tokenService->injectLogInUriInUserTokens($userTokens);

        return new ViewModel([
            'utilisateur' => $utilisateur,
            'tokens' => $userTokens,
            'roles' => $roles,
            'rolesAffectes' => $rolesAffectes,
            'etablissements' => $etablissements,
            'ecoles' => $ecoles,
            'unites' => $unites,
            'redirect' => $this->url()->fromRoute(null, [], [], true),
        ]);

    }

    /**
     * AJAX.
     *
     * Recherche d'un Individu.
     *
     * @param string|null $type => permet de spécifier un type d'acteur ...
     * @return JsonModel
     */
    public function rechercherIndividuAction(?string $type = null)
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
     * @return ViewModel
     */
    public function ajouterAction()
    {
        $form = $this->creationUtilisateurForm;
//        $form->setData(['individu' => 1]);

        /** @var Request $request */
        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            $form->setData($data);
            if ($form->isValid()) {
                if (!trim($data['nomPatronymique'])) {
                    $data['nomPatronymique'] = $data['nomUsuel'];
                }
                if ($data['individu'] == '1') {
                    $individu = $this->utilisateurService->createIndividuFromFormData($data->toArray());
                    $utilisateur = $this->utilisateurService->createFromIndividuAndFormData($individu, $data->toArray());

                } else {
                    $utilisateur = $this->utilisateurService->createFromFormData($data->toArray());
                }
                $this->userService->updateUserPasswordResetToken($utilisateur);
                $this->notifierService->triggerInitialisationCompte($utilisateur, $utilisateur->getPasswordResetToken());
                $this->flashMessenger()->addSuccessMessage("Utilisateur <strong>{$utilisateur->getUsername()}</strong> créé avec succès.");
                $this->redirect()->toRoute('utilisateur');
            }
        }

        return new ViewModel([
            'form' => $form,
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
            throw new RuntimeException(sprintf(
                "La demande d'usurpation de l'individu '%s' (%d) a échoué car aucun compte utilisateur correspondant " .
                "n'a été trouvé. " .
                "Pour pouvoir usurper l'identité d'un individu, il faut que celui-ci se soit connecté au moins " .
                "une fois à l'application, de manière à ce que son compte utilisateur ait été créé avec " .
                "des données complètes.",
                $individu, $individu->getId()
            ));
        }

        $usernameUsurpe = $utilisateur->getUsername();
        $sessionIdentity = $this->serviceUserContext->usurperIdentite($usernameUsurpe);
        if ($sessionIdentity !== null) {
            // cuisine spéciale si l'utilisateur courant s'est authentifié via Shibboleth
            $this->usurperIdentiteShib($usernameUsurpe);
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
            $this->notifierService->triggerChangementRole("retrait", $role, $individu);
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
            $this->notifierService->triggerChangementRole("ajout", $role, $individu);
        }
        return new ViewModel([]);
    }

    public function gererUtilisateurAction()
    {
        $individu = $this->getIndividuService()->getRequestedIndividu($this);
        $acteurs = $this->acteurService->getRepository()->findActeursByIndividu($individu);
        $utilisateurs = $this->utilisateurService->getRepository()->findByIndividu($individu, $isLocal = true); // done
        // NB: findByIndividu() avec $isLocal = true renverra 1 utilisateur au maximum
        $utilisateur = $utilisateurs ? current($utilisateurs) : null;
        if ($utilisateur === null and $individu->getEmail() !== null) {
            $utilisateur = $this->utilisateurService->getRepository()->findByUsername($individu->getEmail());
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
            $user = $this->utilisateurService->createFromIndividu($individu, $individu->getEmail(), 'none');
            $this->userService->updateUserPasswordResetToken($user);
            $url = $this->url()->fromRoute('utilisateur/init-compte', ['token' => $user->getPasswordResetToken()], ['force_canonical' => true], true);
            $this->notifierService->triggerInitialisationCompte($user, $url);
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
            $this->notifierService->triggerResetCompte($utilisateur, $url);
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

    public function lierIndividuAction()
    {
        $utilisateur = $this->getUtilisateurService()->getRequestedUtilisateur($this);

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
            $vm = new ViewModel([
                'title' => "Création de lien entre un individu et l'utilisateur ".$utilisateur->getDisplayName() . " [".$utilisateur->getId()."]",
                'utilisateur' => $utilisateur,
                'individu' => $individu,
            ]);
            return $vm;
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
                $acteurs = $this->getActeurService()->getRepository()->findActeursByIndividu($individu);
                $roles = $individu->getRoles();

                $vm = new ViewModel([
                    "title" => "Création de lien entre un individu et l'utilisateur " . $utilisateur->getDisplayName() . " [" . $utilisateur->getId() . "]",
                    "utilisateur" => $utilisateur,
                    "individu" => $individu,
                    "acteurs" => $acteurs,
                    "roles" => $roles,
                ]);
                return $vm;
            }
        }

        $vm = new ViewModel([
            'title' => "Création de lien entre un individu et l'utilisateur ".$utilisateur->getDisplayName() . " [".$utilisateur->getId()."]",
            'utilisateur' => $utilisateur,
        ]);
        return $vm;
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