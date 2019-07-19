<?php

namespace Application\Controller;

use Application\Entity\Db\Individu;
use Application\Entity\Db\TypeStructure;
use Application\Entity\Db\Utilisateur;
use Application\Form\CreationUtilisateurForm;
use Application\Form\CreationUtilisateurFromIndividuForm;
use Application\Service\EcoleDoctorale\EcoleDoctoraleServiceAwareTrait;
use Application\Service\Etablissement\EtablissementServiceAwareTrait;
use Application\Service\Individu\IndividuServiceAwareTrait;
use Application\Service\Notification\NotifierServiceAwareTrait;
use Application\Service\Role\RoleServiceAwareTrait;
use Application\Service\Structure\StructureServiceAwareTrait;
use Application\Service\UniteRecherche\UniteRechercheServiceAwareTrait;
use Application\Service\UserContextServiceAwareTrait;
use Application\Service\Utilisateur\UtilisateurService;
use Application\Service\Utilisateur\UtilisateurServiceAwareTrait;
use Application\SourceCodeStringHelperAwareTrait;
use UnicaenApp\Exception\LogicException;
use UnicaenApp\Exception\RuntimeException;
use UnicaenApp\Service\EntityManagerAwareTrait;
use UnicaenAuth\Entity\Shibboleth\ShibUser;
use UnicaenAuth\Options\ModuleOptions;
use UnicaenAuth\Service\ShibService;
use UnicaenLdap\Entity\People;
use Zend\Authentication\AuthenticationService;
use Zend\Http\Request;
use Zend\Http\Response;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;

class UtilisateurController extends \UnicaenAuth\Controller\UtilisateurController
{
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

    /**
     * NOTA BENE : il s'agit des individus et non des utilisateurs car ils sont ceux qui portent les rôles
     */
    public function indexAction()
    {
        $individu = null;
        $roles = null;

        /** @var Request $request */
        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost()['individu'];
            $individu = $this->individuService->getRepository()->find($data['id']);
            $params = [];
            if ($individu !== null) $params = ["query" => ["id" => $data['id']]];
            $this->redirect()->toRoute(null, [], $params, true);
        }

        $individuId = $this->params()->fromQuery("id");
        if ($individuId !== null) {
            $individu = $this->individuService->getRepository()->find($individuId);
            $rolesAffectes = $this->roleService->getRepository()->findAllByIndividu($individu);
        }

        $roles = $this->roleService->getRoles();
        $etablissements = $this->getStructureService()->getAllStructuresAffichablesByType(TypeStructure::CODE_ETABLISSEMENT, 'libelle');
        $unites = $this->getStructureService()->getAllStructuresAffichablesByType(TypeStructure::CODE_UNITE_RECHERCHE, 'libelle');
        $ecoles = $this->getStructureService()->getAllStructuresAffichablesByType(TypeStructure::CODE_ECOLE_DOCTORALE, 'libelle');

        return new ViewModel([
            'individu' => $individu,
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
        if (($term = $this->params()->fromQuery('term'))) {
            $rows = $this->individuService->getRepository()->findByText($term, $type);
            $result = [];
            foreach ($rows as $row) {
                $prenoms = implode(' ', array_filter([$row['PRENOM1'], $row['PRENOM2'], $row['PRENOM3']]));
                // mise en forme attendue par l'aide de vue FormSearchAndSelect
                $label = $row['NOM_USUEL'] . ' ' . $prenoms;
                $extra = $row['EMAIL'] ?: $row['SOURCE_CODE'];
                $result[] = array(
                    'id'    => $row['ID'], // identifiant unique de l'item
                    'label' => $label,     // libellé de l'item
                    'extra' => $extra,     // infos complémentaires (facultatives) sur l'item
                );
            }
            usort($result, function($a, $b) {
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
        $form = $this->getServiceLocator()->get('FormElementManager')->get(CreationUtilisateurForm::class);
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
     * @return ViewModel
     */
    public function ajouterFromIndividuAction()
    {
        /** @var Individu $individu */
        $individuId = $this->params('individu');
        $individu = $this->individuService->getRepository()->findOneBy(["id"=>$individuId]);
        if ($individu === null) {
            throw new RuntimeException("Individu introuvable avec cet id");
        }

        $utilisateurs = $this->utilisateurService->getRepository()->findByIndividu($individu);
        if (count($utilisateurs) > 0) {
            throw new RuntimeException("Il existe déjà un utilisateur lié à l'individu $individu.");
        }

        /** @var CreationUtilisateurFromIndividuForm $form */
        $form = $this->getServiceLocator()->get('FormElementManager')->get(CreationUtilisateurFromIndividuForm::class);
        $form->setIndividu($individu);

        /** @var Request $request */
        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            $form->setData($data);
            if ($form->isValid()) {
                if (!empty($data['email'])) {
                    $individu->setEmail($data['email']);
                }
                $utilisateur = $this->utilisateurService->createFromIndividuAndFormData($individu, $data->toArray());
                $this->flashMessenger()->addSuccessMessage(
                    "Utilisateur <strong>{$utilisateur->getUsername()}</strong> créé avec succès à partir de l'individu $individu.");
                $this->redirect()->toRoute('utilisateur');
            }
        }

        return new ViewModel([
            'form' => $form,
            'individu' => $individu,
        ]);
    }

    /**
     * Usurpe l'identité d'un autre utilisateur.
     *
     * @return Response
     */
    public function usurperIdentiteAction()
    {
        $request = $this->getRequest();
        if (! $request instanceof Request) {
            exit(1);
        }

        $newIdentity = $request->getQuery('identity', $request->getPost('identity'));
        if (! $newIdentity) {
            return $this->redirect()->toRoute('home');
        }

        /** @var AuthenticationService $authenticationService */
        $authenticationService = $this->getServiceLocator()->get(AuthenticationService::class);

        $currentIdentity = $authenticationService->getIdentity();
        if (! $currentIdentity || ! is_array($currentIdentity)) {
            return $this->redirect()->toRoute('home');
        }

        if (isset($currentIdentity['shib'])) {
            /** @var ShibUser $currentIdentity */
            $currentIdentity = $currentIdentity['shib'];
        }
        elseif (isset($currentIdentity['ldap'])) {
            /** @var People $currentIdentity */
            $currentIdentity = $currentIdentity['ldap'];
        } else {
            return $this->redirect()->toRoute('home');
        }

        // seuls les logins spécifiés dans la config sont habilités à usurper des identités
        /** @var ModuleOptions $options */
        $options = $this->getServiceLocator()->get('unicaen-auth_module_options');
        if (! in_array($currentIdentity->getUsername(), $options->getUsurpationAllowedUsernames())) {
            throw new LogicException("Usurpation non autorisée");
        }

        // cuisine spéciale pour Shibboleth
        if ($currentIdentity instanceof ShibUser) {
            $fromShibUser = $currentIdentity;
            $toShibUser = $this->createShibUserFromUtilisateurUsername($newIdentity);
            /** @var ShibService $shibService */
            $shibService = $this->getServiceLocator()->get(ShibService::class);
            $shibService->activateUsurpation($fromShibUser, $toShibUser);
        }

        $authenticationService->getStorage()->write($newIdentity);

        return $this->redirect()->toRoute('home');
    }

    /**
     * Usurpe l'identité d'un individu.
     *
     * @return Response
     */
    public function usurperIndividuAction()
    {
        $request = $this->getRequest();
        if (! $request instanceof Request) {
            exit(1);
        }

        $individuId = $request->getPost('individu');
        if (! $individuId) {
            return $this->redirect()->toRoute('home');
        }

        /** @var AuthenticationService $authenticationService */
        $authenticationService = $this->getServiceLocator()->get(AuthenticationService::class);
        $currentIdentity = $authenticationService->getIdentity();
        if (! $currentIdentity || ! is_array($currentIdentity)) {
            return $this->redirect()->toRoute('home');
        }

        if (isset($currentIdentity['shib'])) {
            /** @var ShibUser $currentIdentity */
            $currentIdentity = $currentIdentity['shib'];
        }
        elseif (isset($currentIdentity['ldap'])) {
            /** @var People $currentIdentity */
            $currentIdentity = $currentIdentity['ldap'];
        } else {
            return $this->redirect()->toRoute('home');
        }

        // seuls les logins spécifiés dans la config sont habilités à usurper des identités
        /** @var ModuleOptions $options */
        $options = $this->getServiceLocator()->get('unicaen-auth_module_options');
        if (! in_array($currentIdentity->getUsername(), $options->getUsurpationAllowedUsernames())) {
            throw new LogicException("Usurpation non autorisée");
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

        // cuisine spéciale pour Shibboleth
        if ($currentIdentity instanceof ShibUser) {
            $fromShibUser = $currentIdentity;
            $toShibUser = $this->createShibUserFromUtilisateur($utilisateur);
            /** @var ShibService $shibService */
            $shibService = $this->getServiceLocator()->get(ShibService::class);
            $shibService->activateUsurpation($fromShibUser, $toShibUser);
        }

        $authenticationService->getStorage()->write($individuId);

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

    /**
     * Recherche l'utilisateur dont le login est spécifié puis instancie un ShibUser à partir
     * des attibuts de cet utilisateur.
     *
     * @param string $username
     * @return ShibUser
     */
    public function createShibUserFromUtilisateurUsername($username)
    {
        /** @var UtilisateurService $utilisateurService */
        $utilisateurService = $this->getServiceLocator()->get('UtilisateurService');

        /** @var Utilisateur $utilisateur */
        $utilisateur = $utilisateurService->getRepository()->findOneBy(['username' => $username]);
        if ($utilisateur === null) {
            throw new RuntimeException("L'utilisateur '$username' introuvable");
        }

        return $this->createShibUserFromUtilisateur($utilisateur);
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
}