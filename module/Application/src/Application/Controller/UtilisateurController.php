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
use Doctrine\ORM\Query\Expr;
use UnicaenApp\Exception\RuntimeException;
use UnicaenApp\Service\EntityManagerAwareTrait;
use UnicaenAuth\Entity\Shibboleth\ShibUser;
use UnicaenAuth\Options\ModuleOptions;
use UnicaenAuth\Service\ShibService;
use UnicaenAuth\Service\Traits\UserServiceAwareTrait;
use Zend\Authentication\AuthenticationService;
use Zend\Http\Request;
use Zend\Http\Response;
use Zend\Paginator\Paginator as ZendPaginator;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;

class UtilisateurController extends \UnicaenAuth\Controller\UtilisateurController implements SearchControllerInterface
{
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

    use InitCompteFormAwareTrait;
    use SearchControllerTrait;

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
        $etablissementsQb->join('structure.etablissement', 'etab', Expr\Join::WITH, 'etab.estInscription = 1');
        $etablissements = $etablissementsQb->getQuery()->execute();

        $unites = $this->structureService->getAllStructuresAffichablesByType(TypeStructure::CODE_UNITE_RECHERCHE, 'libelle', true, true);
        $ecoles = $this->structureService->getAllStructuresAffichablesByType(TypeStructure::CODE_ECOLE_DOCTORALE, 'libelle', true, true);

        return new ViewModel([
            'utilisateur' => $utilisateur,
            'roles' => $roles,
            'rolesAffectes' => $rolesAffectes,
            'etablissements' => $etablissements,
            'ecoles' => $ecoles,
            'unites' => $unites,
        ]);

    }

    /**
     * AJAX.
     *
     * Recherche d'un Individu.
     *
     * @param string $type => permet de spécifier un type d'acteur ...
     * @return JsonModel
     */
    public function rechercherIndividuAction($type = null)
    {
        $type = $this->params()->fromQuery('type');
        if (($term = $this->params()->fromQuery('term'))) {
            $rows = $this->individuService->getRepository()->findByText($term, $type);
            $result = [];
            foreach ($rows as $row) {
                $prenoms = implode(' ', array_filter([$row['PRENOM1'], $row['PRENOM2'], $row['PRENOM3']]));
                // mise en forme attendue par l'aide de vue FormSearchAndSelect
                $label = $row['NOM_USUEL'] . ' ' . $prenoms;
                $extra = $row['EMAIL'] ?: $row['SOURCE_CODE'];
                $result[] = array(
                    'id' => $row['ID'], // identifiant unique de l'item
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
        /** @var CreationUtilisateurForm $form */
        $form = $this->creationUtilisateurForm;
//        $infos = new CreationUtilisateurInfos();
//        $form->bind($infos);

        /** @var Request $request */
        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            $form->setData($data);
            if ($form->isValid()) {
                if (!trim($data['nomPatronymique'])) {
                    $data['nomPatronymique'] = $data['nomUsuel'];
                }
                $utilisateur = $this->utilisateurService->createFromFormData($data->toArray());
                $this->flashMessenger()->addSuccessMessage("Utilisateur <strong>{$utilisateur->getUsername()}</strong> créé avec succès.");
                $this->redirect()->toRoute('utilisateur');
            }
        }

        return new ViewModel([
            'form' => $form,
        ]);
    }

    /**
     * Usurpe l'identité d'un individu.
     *
     * @return Response
     */
    public function usurperIndividuAction(): Response
    {
        $request = $this->getRequest();
        if (!$request instanceof Request) {
            exit(1);
        }

        $individuId = $request->getPost('individu');
        if (!$individuId) {
            return $this->redirect()->toRoute('home');
        }

        /** @var Utilisateur $utilisateur */
        $utilisateur = $this->utilisateurService->getRepository()->findOneBy(['individu' => $individuId]);
        if ($utilisateur === null) {
            /** @var Individu $individu */
            $individu = $this->individuService->getRepository()->find($individuId);
            try {
                $utilisateur = $this->utilisateurService->createFromIndividuForUsurpationShib($individu);
            } catch (RuntimeException $e) {
                throw new RuntimeException("Impossible d'ajouter l'individu $individu aux utilisateurs de l'application.", null, $e);
            }
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

        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            $individuId = $data['individu']['id'];
            /** @var Individu $individu */
            $individu = $this->getIndividuService()->getRepository()->find($individuId);
            $utilisateur->setIndividu($individu);
            $this->getUtilisateurService()->getEntityManager()->flush($utilisateur);
        }

        $vm = new ViewModel([
            'title' => "Création de lien entre un individu et l'utilisateur ".$utilisateur->getDisplayName() . " [".$utilisateur->getId()."]",
            'utilisateur' => $utilisateur,
        ]);
        return $vm;

    }

}