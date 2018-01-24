<?php

namespace Application\Controller;

use Application\Entity\Db\Role;
use Application\Entity\Db\Utilisateur;
use Application\RouteMatch;
use Application\Service\Role\RoleServiceAwareInterface;
use Application\Service\Role\RoleServiceAwareTrait;
use Application\Service\UserContextServiceAwareTrait;
use Application\Service\Utilisateur\UtilisateurServiceAwareInterface;
use Application\Service\Utilisateur\UtilisateurServiceAwareTrait;
use Application\View\Helper\Sortable;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator;
use UnicaenLdap\Entity\People;
use UnicaenLdap\Filter\People as LdapPeopleFilter;
use UnicaenLdap\Service\LdapPeopleServiceAwareInterface;
use UnicaenLdap\Service\LdapPeopleServiceAwareTrait;
use UnicaenLdap\Service\People as LdapPeopleService;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;

class UtilisateurController extends \UnicaenAuth\Controller\UtilisateurController
    implements UtilisateurServiceAwareInterface, RoleServiceAwareInterface, LdapPeopleServiceAwareInterface
{
    use UtilisateurServiceAwareTrait;
    use UserContextServiceAwareTrait;
    use RoleServiceAwareTrait;
    use LdapPeopleServiceAwareTrait;

    public function indexAction()
    {
        /**
         * Application des filtres et tris par défaut.
         */
        $needsRedirect = false;
        $queryParams = $this->params()->fromQuery();
        // filtres
//        $etatThese = $this->params()->fromQuery($name = 'etatThese');
//        if ($etatThese === null) { // null <=> paramètre absent
//            // filtrage par défaut : thèse en préparation
//            $queryParams = array_merge($queryParams, [$name => Utilisateur::ETAT_EN_COURS]);
//            $needsRedirect = true;
//        }
        // tris
        $sort = $this->params()->fromQuery('sort');
        if ($sort === null) { // null <=> paramètre absent
            // tri par défaut : datePremiereInscription
            $queryParams = array_merge($queryParams, ['sort' => 'u.displayName', 'direction' => Sortable::ASC]);
            $needsRedirect = true;
        }
        // redirection si nécessaire
        if ($needsRedirect) {
            return $this->redirect()->toRoute(null, [], ['query' => $queryParams], true);
        }

        $text = $this->params()->fromQuery('text');
        $dir  = $this->params()->fromQuery('direction', Sortable::ASC);
        $maxi = $this->params()->fromQuery('maxi', 40);
        $page = $this->params()->fromQuery('page', 1);

        $qb = $this->utilisateurService->getRepository()->createQueryBuilder('u');
//        if ($etatThese) {
//            $qb->andWhere('t.etatThese = :etat')->setParameter('etat', $etatThese);
//        }
        foreach (explode('+', $sort) as $sortProp) {
            $qb->addOrderBy($sortProp, $dir);
        }

        /**
         * Filtres découlant du rôle de l'utilisateur.
         */
        $this->decorateQbFromSelectedRole($qb);

        /**
         * Prise en compte du champ de recherche textuelle.
         */
//        if (strlen($text) > 1) {
//            $results = $this->theseService->rechercherThese($text);
//            $sourceCodes = array_unique(array_keys($results));
//            if ($sourceCodes) {
//                $qb
//                    ->andWhere($qb->expr()->in('t.sourceCode', ':sourceCodes'))
//                    ->setParameter('sourceCodes', $sourceCodes);
//            }
//            else {
//                $qb->andWhere("0 = 1"); // i.e. aucune thèse trouvée
//            }
//        }

        $paginator = new \Zend\Paginator\Paginator(new DoctrinePaginator(new Paginator($qb, true)));
        $paginator
            ->setPageRange(30)
            ->setItemCountPerPage((int)$maxi)
            ->setCurrentPageNumber((int)$page);

        $qb = $this->roleService->getRepository()->createQueryBuilder('r');
        $qb ->join('r.source', 's', Join::WITH, 's.importable = 0')
            ->andWhere('r.attributionAutomatique = 0')
            ->andWhere($qb->expr()->notIn('r.roleId', [
                Role::ROLE_ID_ECOLE_DOCT,
                Role::ROLE_ID_UNITE_RECH,
            ]))
            ->andWhere('1 = pasHistorise(r)')
            ->orderBy('r.roleId');
        $rolesStatiques = $qb->getQuery()->getResult();

        $qb = $this->roleService->getRepository()->createQueryBuilder('r')
            ->andWhere('r.attributionAutomatique = 1')
            ->andWhere('1 = pasHistorise(r)')
            ->orderBy('r.roleId');
        $rolesDynamiques = $qb->getQuery()->getResult();

        return new ViewModel([
            'utilisateurs'    => $paginator,
            'roles'           => $rolesStatiques,
            'rolesDynamiques' => $rolesDynamiques,
            'text'            => $text,
        ]);
    }

    private function decorateQbFromSelectedRole(QueryBuilder $qb)
    {
        return $qb;
    }

    public function ajouterAction()
    {
        $data = $this->params()->fromPost('people');

        if (!empty($data['id'])) {
            /** @var People $people */
            if ($people = $this->ldapPeopleService->get($data['id'])) {
                $username = $people->get('supannAliasLogin');
                $utilisateur = $this->utilisateurService->getRepository()->findOneBy(['username' => $username]);
                if (! $utilisateur) {
                    $utilisateur = $this->utilisateurService->createFromPeople($people);

                    $this->flashMessenger()->addSuccessMessage("$utilisateur a été ajouté-e avec succès à la liste des utilisateurs");
                }
                else {
                    $this->flashMessenger()->addErrorMessage("$utilisateur existe déjà dans la liste des utilisateurs");
                }
            }
        }

        return $this->redirect()->toRoute('utilisateur');
    }

    public function attribuerRoleAction()
    {
        $utilisateur = $this->requestUtilisateur();
        $role = $this->postRole();
        if (! $role) {
            exit;
        }

        $qb = $this->utilisateurService->getRepository()->createQueryBuilder('u')
            ->addSelect('r')
            ->join('u.roles', 'r', Join::WITH, 'r = :role')
            ->andWhere('u = :user')
            ->setParameter('role', $role)
            ->setParameter('user', $utilisateur);
        $result = $qb->getQuery()->getResult();
        
        // retrait du rôle
        if (count($result) > 0) {
            $utilisateur->removeRole($role);
            $message = "Le rôle '$role' a été retiré avec succès à l'utilisateur '$utilisateur'.";
        }
        // accord du rôle
        else {
            $utilisateur->addRole($role);
            $message = "Le rôle '$role' a été accordé avec succès à l'utilisateur '$utilisateur'.";
        }

        $this->utilisateurService->getEntityManager()->flush($utilisateur);

        return new JsonModel(['status' => 'success', 'message' => $message]);
    }

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
     * @return Utilisateur
     */
    private function requestUtilisateur()
    {
        /** @var RouteMatch $match */
        $match = $this->getEvent()->getRouteMatch();

        return $match->getUtilisateur();
    }

    /**
     * @return Role
     */
    private function postRole()
    {
        $roleId = $this->params()->fromPost('role');
        if (! $roleId) {
            return null;
        }

        /** @var Role $role */
        $role = $this->roleService->getRepository()->findOneBy([(is_numeric($roleId) ? 'id' : 'roleId') => $roleId]);

        return $role;
    }
}