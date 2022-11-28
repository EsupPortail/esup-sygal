<?php

namespace Individu\Controller;

use Application\Entity\Db\Role;
use Application\Search\Controller\SearchControllerInterface;
use Application\Search\Controller\SearchControllerTrait;
use Application\Search\SearchServiceAwareTrait;
use Application\Service\Role\RoleServiceAwareTrait;
use Application\Service\UserContextServiceAwareTrait;
use Application\Service\Utilisateur\UtilisateurServiceAwareTrait;
use Doctorant\Service\DoctorantServiceAwareTrait;
use Doctrine\ORM\Query\Expr\Join;
use Individu\Entity\Db\Individu;
use Individu\Form\IndividuForm;
use Individu\Service\IndividuServiceAwareTrait;
use Laminas\Http\Request;
use Laminas\Http\Response;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\Mvc\Plugin\FlashMessenger\FlashMessenger;
use Laminas\Paginator\Paginator as LaminasPaginator;
use Laminas\View\Model\JsonModel;
use Laminas\View\Model\ViewModel;
use Structure\Entity\Db\TypeStructure;
use Structure\Service\Structure\StructureServiceAwareTrait;
use These\Entity\Db\Acteur;
use These\Service\Acteur\ActeurServiceAwareTrait;

/**
 * @property \Individu\Service\Search\IndividuSearchService $searchService
 * @method FlashMessenger flashMessenger()
 */
class IndividuController extends AbstractActionController implements SearchControllerInterface
{
    use SearchServiceAwareTrait;
    use SearchControllerTrait;

    use IndividuServiceAwareTrait;
    use UtilisateurServiceAwareTrait;
    use RoleServiceAwareTrait;
    use StructureServiceAwareTrait;
    use ActeurServiceAwareTrait;
    use DoctorantServiceAwareTrait;
    use UserContextServiceAwareTrait;

    private IndividuForm $individuForm;

    /**
     * @param \Individu\Form\IndividuForm $individuForm
     */
    public function setIndividuForm(IndividuForm $individuForm): void
    {
        $this->individuForm = $individuForm;
    }

    /**
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

    public function voirAction(): ViewModel
    {
        $individu = $this->individuService->getRequestedIndividu($this);

        return new ViewModel([
            'individu' => $individu,
            'rolesData' => $this->rolesAction(),
        ]);
    }

    public function rolesAction(): ViewModel
    {
        $individu = $this->individuService->getRequestedIndividu($this);

        $roles = $this->roleService->findAllRoles();

        $rolesAffectes = $individu->getRoles();
        $rolesAffectesAuto = $this->collectRolesDynamiquesForIndividu($individu);

        // établissements : pour l'instant les rôles ne concernent que des établissements d'inscription donc on flitre
        $etablissementsQb = $this->structureService->findAllStructuresAffichablesByTypeQb(TypeStructure::CODE_ETABLISSEMENT, 'libelle', true);
        $etablissementsQb->join('structure.etablissement', 'etab', Join::WITH, 'etab.estInscription = true');
        $etablissements = $etablissementsQb->getQuery()->execute();

        $unites = $this->structureService->findAllStructuresAffichablesByType(TypeStructure::CODE_UNITE_RECHERCHE, 'libelle', true, true);
        $ecoles = $this->structureService->findAllStructuresAffichablesByType(TypeStructure::CODE_ECOLE_DOCTORALE, 'libelle', true, true);

        return new ViewModel([
            'individu' => $individu,
            'roles' => $roles,
            'rolesAffectes' => $rolesAffectes,
            'rolesAffectesAuto' => $rolesAffectesAuto,
            'etablissements' => $etablissements,
            'ecoles' => $ecoles,
            'unites' => $unites,
//            'redirect' => $this->url()->fromRoute(null, [], [], true),
        ]);
    }

    /**
     * @param \Individu\Entity\Db\Individu $individu
     * @return \Application\Entity\Db\Role[]
     */
    private function collectRolesDynamiquesForIndividu(Individu $individu): array
    {
        $roles = [];

        // rôles d'acteur
        $acteurs = $this->acteurService->getRepository()->findActeursForIndividu($individu);
        if ($acteurs) {
            $acteursDirecteurThese = $this->acteurService->filterActeursDirecteurThese($acteurs);
            $acteursCoDirecteurThese = $this->acteurService->filterActeursCoDirecteurThese($acteurs);
            $acteursPresidentJury = $this->acteurService->filterActeursPresidentJury($acteurs);
            $acteursRapporteurJury = $this->acteurService->filterActeursRapporteurJury($acteurs);
            $roles = array_merge($roles, array_map(
                function (Acteur $a) {
                    return $a->getRole();
                },
                array_merge($acteursDirecteurThese, $acteursCoDirecteurThese, $acteursPresidentJury, $acteursRapporteurJury)
            ));
        }

        $doctorant = $this->doctorantService->getRepository()->findOneByIndividu($individu);
        if ($doctorant) {
            $roles[] = $this->roleService->getRepository()
                ->findOneByCodeAndStructureConcrete(Role::CODE_DOCTORANT, $doctorant->getEtablissement());
        }

        return array_unique($roles);
    }

    /**
     * ATTENTION : cette action peut aussi être appelée par
     * {@see \Application\Controller\UtilisateurController::lierNouvelIndividuAction()}.
     *
     * @return \Laminas\Http\Response|\Laminas\View\Model\ViewModel
     */
    public function ajouterAction()
    {
        /** @var Request $request */
        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            $this->individuForm->setData($data);
            if ($this->individuForm->isValid()) {
                $individu = $this->individuService->newIndividuFromData($data->toArray());
                $this->individuService->saveIndividu($individu);

                $this->flashMessenger()->addSuccessMessage("L'individu &laquo; $individu &raquo; a été créé avec succès.");

                // On positionne dans le Header un champ 'individu' pour le cas où l'action est appelée par un autre contrôleur
                // ayant besoin de connaître l'individu créé :
                $response = $this->redirect()->toRoute('individu/voir', ['individu' => $individu->getId()]);
                $response->getHeaders()->addHeaderLine('individu', $individu->getId());

                return $response;
            }
        } else {
            // si un utilisateur est spécifié, on initialise le formulaire avec.
            if ($utilisateur = $this->utilisateurService->getRequestedUtilisateur($this)) {
                $individu = $this->individuService->newIndividuFromUtilisateur($utilisateur);
                $this->individuForm->bind($individu);
            }
        }

        return new ViewModel([
            'form' => $this->individuForm,
        ]);
    }

    /**
     * @return \Laminas\Http\Response|\Laminas\View\Model\ViewModel
     */
    public function modifierAction()
    {
        $individu = $this->individuService->getRequestedIndividu($this);
        $this->individuForm->bind($individu);

        /** @var Request $request */
        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            $this->individuForm->setData($data);
            if ($this->individuForm->isValid()) {
                /** @var Individu $individu */
                $individu = $this->individuForm->getData();
                $this->individuService->saveIndividu($individu);

                $this->flashMessenger()->addSuccessMessage("L'individu &laquo; $individu &raquo; a été modifié avec succès.");

                return $this->redirect()->toRoute('individu/voir', ['individu' => $individu->getId()]);
            }
        }

        return new ViewModel([
            'form' => $this->individuForm,
        ]);
    }

    /**
     * @return \Laminas\Http\Response
     */
    public function supprimerAction(): Response
    {
        $individu = $this->individuService->getRequestedIndividu($this);
        $this->individuService->historiser($individu, $this->userContextService->getIdentityDb());

        $this->flashMessenger()->addSuccessMessage("L'individu &laquo; $individu &raquo; a été supprimé avec succès.");

        return $this->redirect()->toRoute('individu/voir', ['individu' => $individu->getId()]);
    }

    /**
     * @return \Laminas\Http\Response
     */
    public function restaurerAction(): Response
    {
        $individu = $this->individuService->getRequestedIndividu($this);
        $this->individuService->dehistoriser($individu);

        $this->flashMessenger()->addSuccessMessage("L'individu &laquo; $individu &raquo; a été restauré avec succès.");

        return $this->redirect()->toRoute('individu/voir', ['individu' => $individu->getId()]);
    }

    public function rechercherAction()
    {
        if (($term = $this->params()->fromQuery('term'))) {
            $individus = $this->getIndividuService()->getRepository()->findByText($term);

            $result = [];
            foreach ($individus as $xxx =>  $individu) {
                $result[] = array(
                    'id' => $individu['id'],
                    'label' => $individu['prenom1']. " " . $individu['nom_usuel'],
                    'extra' => $individu['source_code'],
                );
            }
            usort($result, function ($a, $b) {
                return strcmp($a['label'], $b['label']);
            });

            return new JsonModel($result);
        }
        exit;
    }
}