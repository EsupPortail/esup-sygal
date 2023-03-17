<?php

namespace Individu\Controller;

use Admission\Service\Inscription\InscriptionServiceAwareTrait;
use Application\Entity\Db\Role;
use Application\Filter\NomCompletFormatter;
use Application\Search\Controller\SearchControllerInterface;
use Application\Search\Controller\SearchControllerTrait;
use Application\Search\SearchServiceAwareTrait;
use Application\Service\Role\ApplicationRoleServiceAwareTrait;
use Application\Service\UserContextServiceAwareTrait;
use Application\Service\Utilisateur\UtilisateurServiceAwareTrait;
use Doctorant\Controller\Plugin\UrlDoctorant;
use Doctorant\Service\DoctorantServiceAwareTrait;
use Doctrine\ORM\Query\Expr\Join;
use Individu\Entity\Db\Individu;
use Individu\Entity\Db\IndividuRole;
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
 * @method UrlDoctorant urlDoctorant()
 */
class IndividuController extends AbstractActionController implements SearchControllerInterface
{
    use SearchServiceAwareTrait;
    use SearchControllerTrait;

    use IndividuServiceAwareTrait;
    use UtilisateurServiceAwareTrait;
    use ApplicationRoleServiceAwareTrait;
    use StructureServiceAwareTrait;
    use ActeurServiceAwareTrait;
    use DoctorantServiceAwareTrait;
    use UserContextServiceAwareTrait;
    use InscriptionServiceAwareTrait;

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
        if ($individu === null) {
            throw new \InvalidArgumentException("Individu introuvable.");
        }

        return new ViewModel([
            'individu' => $individu,
            'rolesData' => $this->rolesAction()->getVariables(),
        ]);
    }

    public function rolesAction(): ViewModel
    {
        $individu = $this->individuService->getRequestedIndividu($this);

        $rolesEtablissement = $this->applicationRoleService->findRolesByTypeStructureDependant(TypeStructure::CODE_ETABLISSEMENT);
        $rolesEcoleDoctorale = $this->applicationRoleService->findRolesByTypeStructureDependant(TypeStructure::CODE_ECOLE_DOCTORALE);
        $rolesUniteRecherche = $this->applicationRoleService->findRolesByTypeStructureDependant(TypeStructure::CODE_UNITE_RECHERCHE);
        $rolesStatiques = $this->applicationRoleService->findRolesByTypeStructureDependant(null);

        $individusRoles = $this->applicationRoleService->findIndividuRolesByIndividu($individu);
        $rolesAffectes = $individu->getRoles();
        $rolesAffectesAuto = $this->collectRolesDynamiquesForIndividu($individu);

        // établissements : pour l'instant les rôles ne concernent que des établissements d'inscription donc on flitre
        $etablissementsQb = $this->structureService->findAllStructuresAffichablesByTypeQb(TypeStructure::CODE_ETABLISSEMENT, 'structure.libelle');
        $etablissementsQb->join('structure.etablissement', 'etab', Join::WITH, 'etab.estInscription = true');
        $etablissements = $etablissementsQb->getQuery()->execute();

        $unites = $this->structureService->findAllStructuresAffichablesByType(TypeStructure::CODE_UNITE_RECHERCHE, 'structure.libelle');
        $ecoles = $this->structureService->findAllStructuresAffichablesByType(TypeStructure::CODE_ECOLE_DOCTORALE, 'structure.libelle');

        return new ViewModel([
            'individu' => $individu,
            'rolesEtablissement' => $rolesEtablissement,
            'rolesEcoleDoctorale' => $rolesEcoleDoctorale,
            'rolesUniteRecherche' => $rolesUniteRecherche,
            'rolesStatiques' => $rolesStatiques,
            'individusRoles' => $individusRoles,
            'rolesAffectes' => $rolesAffectes,
            'rolesAffectesAuto' => $rolesAffectesAuto,
            'etablissements' => $etablissements,
            'ecoles' => $ecoles,
            'unites' => $unites,
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
            $roles[] = $this->applicationRoleService->getRepository()
                ->findOneByCodeAndStructureConcrete(Role::CODE_DOCTORANT, $doctorant->getEtablissement());
        }

        $individuRoles = $this->applicationRoleService->findIndividuRolesByIndividu($individu);
        if($individuRoles){
            $individuPotentielDirecteurRole = $this->applicationRoleService->filterIndividuRolePotentielDirecteurThese($individuRoles);
            $individuPotentielCoDirecteurRole = $this->applicationRoleService->filterIndividuRolePotentielCoDirecteurThese($individuRoles);
            $individuCandidatRole = $this->applicationRoleService->filterIndividuRoleCandidat($individuRoles);
            $roles = array_merge($roles, array_map(
                function (IndividuRole $ir) {
                    return $ir->getRole();
                },
                array_merge($individuPotentielDirecteurRole, $individuPotentielCoDirecteurRole, $individuCandidatRole)
            ));
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
        $redirectUrl = $this->params()->fromQuery('redirect');
        if ($request->isPost()) {
            $data = $request->getPost();
            $this->individuForm->setData($data);
            if ($this->individuForm->isValid()) {
                /** @var Individu $individu */
                $individu = $this->individuForm->getData();
                $this->individuService->saveIndividu($individu);

                $this->flashMessenger()->addSuccessMessage("L'individu &laquo; $individu &raquo; a été créé avec succès.");

                if ($redirectUrl !== null) return $this->redirect()->toUrl($redirectUrl. '&individu=' . $individu->getId());

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
            if ($redirectUrl !== null) $this->individuForm->setAttribute('action', $this->url()->fromRoute('individu/ajouter',[],["query" => ["redirect" => $redirectUrl]]));
        }

        return (new ViewModel([
            'form' => $this->individuForm,
        ]))->setTemplate('individu/individu/modifier');
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
        $inModal = $this->params()->fromQuery('inModal');
        $this->individuForm->setAttribute('action', $this->url()->fromRoute('individu/modifier', ['individu' => $individu->getId()], ["query" => ["inModal" => $inModal]]));

        if ($request->isPost()) {
            $data = $request->getPost();
            $this->individuForm->setData($data);
            if ($this->individuForm->isValid()) {
                /** @var Individu $individu */
                $individu = $this->individuForm->getData();
                $this->individuService->saveIndividu($individu);

                $this->flashMessenger()->addSuccessMessage("L'individu &laquo; $individu &raquo; a été modifié avec succès.");

                if ($inModal === null) {
                    return $this->redirect()->toRoute('individu/voir', ['individu' => $individu->getId()]);
                }
            }
        }

        return new ViewModel([
            'form' => $this->individuForm,
            'title' => "Modification des informations de ".$individu
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

    /**
     * AJAX.
     *
     * Recherche d'un Individu.
     *
     * @return JsonModel
     */
    public function rechercherAction(): JsonModel
    {
        $type = $this->params()->fromQuery('type');
        if (($term = $this->params()->fromQuery('term'))) {
            $individus = $this->getIndividuService()->getRepository()->findByText($term, $type);
            $f = new NomCompletFormatter(true);
            $result = [];
            foreach ($individus as $individu) {
                $prenoms23 = implode(' ', array_filter([$individu['prenom2'], $individu['prenom3']]));
                // mise en forme attendue par l'aide de vue FormSearchAndSelect
                $label = trim($f->filter($individu) . ' ' . $prenoms23);
                $extra = $individu['email'] ?: $individu['source_code'];
                $result[] = [
                    'id' => $individu['id'],
                    'label' => $label,
                    'text' => $label, // pour Select2.js
                    'extra' => $extra,
                ];
            }
            usort($result, fn($a, $b) => $a['label'] <=> $b['label']);

            return new JsonModel($result);
        }
        exit;
    }
}