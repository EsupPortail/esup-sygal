<?php

namespace Application\Controller;

use Application\Entity\Db\CreationUtilisateurInfos;
use Application\Entity\Db\Role;
use Application\Entity\Db\Utilisateur;
use Application\Form\CreationUtilisateurForm;
use Application\RouteMatch;
use Application\Service\EcoleDoctorale\EcoleDoctoraleServiceAwareTrait;
use Application\Service\Etablissement\EtablissementServiceAwareTrait;
use Application\Service\Individu\IndividuServiceAwareTrait;
use Application\Service\Role\RoleServiceAwareTrait;
use Application\Service\UniteRecherche\UniteRechercheServiceAwareTrait;
use Application\Service\UserContextServiceAwareTrait;
use Application\Service\Utilisateur\UtilisateurServiceAwareTrait;
use Application\View\Helper\Sortable;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator;
use UnicaenApp\Exception\RuntimeException;
use UnicaenApp\Service\EntityManagerAwareTrait;
use UnicaenImport\Entity\Db\Source;
use UnicaenLdap\Entity\People;
use UnicaenLdap\Filter\People as LdapPeopleFilter;
use UnicaenLdap\Service\LdapPeopleServiceAwareTrait;
use UnicaenLdap\Service\People as LdapPeopleService;
use Zend\Db\Sql\Predicate\In;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;
use Zend\Http\Request;
use Zend\Form\Form;
use Application\Entity\Db\Individu;

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
            /** @var Individu $individu */
            $individu = $this->individuService->getIndviduById($data['id']);
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


    public function retirerRoleAction()
    {
        $individuId = $this->params()->fromRoute('individu');
        $roleId = $this->params()->fromRoute('role');

        $this->roleService->removeRole($individuId, $roleId);

        return new ViewModel([]);
    }

    public function ajouterRoleAction()
    {
        $individuId = $this->params()->fromRoute('individu');
        $roleId = $this->params()->fromRoute('role');

        $this->roleService->addRole($individuId, $roleId);
        return new ViewModel([]);
    }
}