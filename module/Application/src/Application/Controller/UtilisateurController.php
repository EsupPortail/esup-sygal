<?php

namespace Application\Controller;

use Application\Entity\Db\CreationUtilisateurInfos;
use Application\Entity\Db\Individu;
use Application\Entity\Db\Utilisateur;
use Application\Filter\EtablissementPrefixFilter;
use Application\Form\CreationUtilisateurForm;
use Application\Service\EcoleDoctorale\EcoleDoctoraleServiceAwareTrait;
use Application\Service\Etablissement\EtablissementServiceAwareTrait;
use Application\Service\Individu\IndividuServiceAwareTrait;
use Application\Service\Notification\NotificationServiceAwareTrait;
use Application\Service\Role\RoleServiceAwareTrait;
use Application\Service\UniteRecherche\UniteRechercheServiceAwareTrait;
use Application\Service\UserContextServiceAwareTrait;
use Application\Service\Utilisateur\UtilisateurService;
use Application\Service\Utilisateur\UtilisateurServiceAwareTrait;
use UnicaenApp\Exception\LogicException;
use UnicaenApp\Exception\RuntimeException;
use UnicaenApp\Service\EntityManagerAwareTrait;
use UnicaenAuth\Entity\Shibboleth\ShibUser;
use UnicaenAuth\Options\ModuleOptions;
use UnicaenAuth\Service\ShibService;
use UnicaenLdap\Entity\People;
use UnicaenLdap\Filter\People as LdapPeopleFilter;
use UnicaenLdap\Service\LdapPeopleServiceAwareTrait;
use UnicaenLdap\Service\People as LdapPeopleService;
use Zend\Authentication\AuthenticationService;
use Zend\Form\Form;
use Zend\Http\Request;
use Zend\Http\Response;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;

class UtilisateurController extends \UnicaenAuth\Controller\UtilisateurController
{
    use UtilisateurServiceAwareTrait;
    use UserContextServiceAwareTrait;
    use RoleServiceAwareTrait;
    use LdapPeopleServiceAwareTrait;
    use IndividuServiceAwareTrait;
    use EntityManagerAwareTrait;
    use EcoleDoctoraleServiceAwareTrait;
    use UniteRechercheServiceAwareTrait;
    use EtablissementServiceAwareTrait;
    use NotificationServiceAwareTrait;

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
            $individu = $this->individuService->getIndviduById($data['id']);
            $params = [];
            if ($individu !== null) $params = ["query" => ["id" => $data['id']]];
            $this->redirect()->toRoute(null, [], $params, true);
        }

        $individuId = $this->params()->fromQuery("id");
        if ($individuId !== null) {
            $individu = $this->individuService->getIndviduById($individuId);
            $rolesAffectes = $this->roleService->getRoleByIndividu($individu);
        }

        $roles = $this->roleService->getRoles();
        $etablissements = $this->etablissementService->getEtablissements();
        $unites = $this->uniteRechercheService->getUnitesRecherches();
        $ecoles = $this->ecoleDoctoraleService->getEcolesDoctorales();

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
     * Recherche d'un compte LDAP.
     *
     * @return JsonModel
     * @throws \UnicaenLdap\Exception
     */
    public function rechercherPeopleAction()
    {
        if (($term = $this->params()->fromQuery('term'))) {
            $filter = LdapPeopleFilter::orFilter(
                LdapPeopleFilter::username($term),
                LdapPeopleFilter::nameContains($term)
            );
            /** @var LdapPeopleService $ldapService */
            $ldapService = $this->getServiceLocator()->get('LdapServicePeople');
            $collection = $ldapService->search($filter);
            $result = [];
            /** @var People $people */
            foreach ($collection as $people) {
                // mise en forme attendue par l'aide de vue FormSearchAndSelect
                $label = strtoupper(implode(', ', (array)$people->get('sn'))) . ' ' . $people->get('givenName');
                $result[] = array(
                    'id'    => $people->getId(),     // identifiant unique de l'item
                    'label' => $label,               // libellé de l'item
                    'extra' => $people->get('mail'), // infos complémentaires (facultatives) sur l'item
                );
            }
            uasort($result, function($a, $b) {
                return strcmp($a['label'], $b['label']);
            });

            // todo: chercher pourquoi le tri est foutu en l'air par la conversion en JSON
            return new JsonModel($result);
        }
        exit;
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


    public function creationUtilisateurAction()
    {
        /** @var Form $form */
        $form = $this->getServiceLocator()->get('FormElementManager')->get(CreationUtilisateurForm::class);
        $infos = new CreationUtilisateurInfos();
        $error = null;

        $form->bind($infos);

        /** @var Request $request */
        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            $form->setData($data);

            if ($form->isValid()) {
                if ($data['nomPatronymique'] === '') $data['nomPatronymique'] = $data['nomUsuel'];

                /** @var Individu $individu */
                $individu = new Individu();
                $individu->setCivilite($data['civilite']);
                $individu->setNomUsuel($data['nomUsuel']);
                $individu->setNomPatronymique($data['nomPatronymique']);
                $individu->setPrenom1($data['prenom']);
                $individu->setEmail($data['email']);
                $individu->setSourceCode("COMUE::" . $data['email']);

                /** @var Utilisateur $utilisateur */
                $utilisateur = new Utilisateur();
                $utilisateur->setUsername($data['email']);
                $utilisateur->setPassword(password_hash($data['password'], PASSWORD_DEFAULT));
                $utilisateur->setState(1);
                $utilisateur->setEmail($data['email']);
                $utilisateur->setDisplayName($data['nomUsuel']. " " .$data['prenom']);

                $this->individuService->createFromForm($individu, $utilisateur);

                $this->flashMessenger()->addSuccessMessage("Utilisateur <strong>{$utilisateur->getUsername()}</strong> crée avec succés.");
                $this->redirect()->toRoute('utilisateur');
            }
        }

        $form->prepare();
        return new ViewModel([
            'form' => $form,
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
        $individu = $utilisateur->getIndividu();
        if ($individu === null) {
            throw new RuntimeException("L'utilisateur '$utilisateur' n'a aucun individu lié");
        }

        $filter = new EtablissementPrefixFilter();
        $supannId = $filter->removePrefixFrom($individu->getSourceCode());

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
            $individuId = $this->params()->fromRoute('individu');
            $individu = $this->getIndividuService()->getIndviduById($individuId);
            $roleId = $this->params()->fromRoute('role');
            $role = $this->getRoleService()->getRoleById($roleId);

            $this->roleService->removeRole($individuId, $roleId);
            $this->notificationService->triggerChangementRole("retrait", $role, $individu);
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
            $individuId = $this->params()->fromRoute('individu');
            $individu = $this->getIndividuService()->getIndviduById($individuId);
            $roleId = $this->params()->fromRoute('role');
            $role = $this->getRoleService()->getRoleById($roleId);

            $this->roleService->addRole($individuId, $roleId);
            $this->notificationService->triggerChangementRole("ajout", $role, $individu);
        }
        return new ViewModel([]);
    }
}