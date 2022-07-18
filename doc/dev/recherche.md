Concevoir une page de recherche
===============================

Idée
----

Cette doc est une feuille de route / exemple pour créer une page de recherche d'enregistrements.

La conception d'un telle page de recherche présentée ici peut paraître coûteux mais il faut noter qu'il s'agit d'une 
pagee ayant les caractéristiques suivantes :
  - des filtres dont l'alimentation/affichage ne ralentit pas l'affichage des résultats de recherche (requête AJAX) ;
  - les valeurs des filtres sont ajoutées aux paramètres GET de la requête (post-redirect-get) donc chaque "page" de 
    résultats possède sa propre URL ;
  - possibilité dans le tableau des résultats de créer des entêtes de colonne déclenchant le tri les résultats 
    (tri côté serveur ; 1 seule colonne à la fois).

Dans la suite, on prendra l'exemple d'une page de recherche de formations.


Feuille de route
----------------

### Service `FormationSearchService`

Un service de recherche dédié, héritant de `SearchService`, est nécessaire. 
Il sera injecté dans le contrôleur de recherche (cf. plus bas).

```php
namespace Formation\Service\Formation\Search;

use Application\Search\Filter\SearchFilter;
use Application\Search\Filter\SelectSearchFilter;
use Application\Search\Filter\StrReducedTextSearchFilter;
use Application\Search\Filter\TextSearchFilter;
use Application\Search\SearchService;
use Application\Search\Sorter\SearchSorter;
use Doctrine\ORM\QueryBuilder;
use Formation\Entity\Db\Interfaces\HasModaliteInterface;
use Formation\Entity\Db\Repository\FormationRepositoryAwareTrait;

class FormationSearchService extends SearchService
{
    use FormationRepositoryAwareTrait;

    const NAME_libelle = 'libelle';
    const NAME_modalite = 'modalite';
    const NAME_responsable = 'responsable';
    
    protected SearchFilter $libelleFilter;
    protected SearchFilter $responsableFilter;
    protected SearchFilter $modaliteFilter;
    
    public function __construct() 
    {
        $this->libelleFilter = $this->createLibelleFilter()->setWhereField('f.libelle');
        $this->responsableFilter = $this->createResponsableFilter();
        $this->modaliteFilter = $this->createModaliteFilter()->setWhereField('f.modalite');
        
        $this->addFilters([
            $this->libelleFilter,
            $this->responsableFilter,
            $this->modaliteFilter,
        ]);

        $this->addSorters([
            $this->createLibelleSorter()->setIsDefault(),
            $this->createResponsableSorter(),
            $this->createModaliteSorter(),
        ]);
    }
    
    public function init()
    {
        $this->responsableFilter->setDataProvider(fn() => $this->formationRepository->fetchListeResponsable());
        $this->modaliteFilter->setData(HasModaliteInterface::MODALITES);
    }

    public function createQueryBuilder(): QueryBuilder
    {
        // ATTENTION à bien sélectionner les relations amenées à être parcourues dans la vue.
        return $this->formationRepository->createQueryBuilder('f')
            ->addSelect('resp')
            ->join("f.responsable", 'resp');
    }

    /********************************** FILTERS ****************************************/

    private function createResponsableFilter(): SelectSearchFilter
    {
        $filter = new SelectSearchFilter("Responsable", self::NAME_responsable);
        $filter->setQueryBuilderApplier(
            function(SelectSearchFilter $filter, QueryBuilder $qb) {
                $qb
                    ->andWhere("resp = :responsable")
                    ->setParameter('responsable', $filter->getValue());
            }
        );

        return $filter;
    }

    private function createModaliteFilter(): SelectSearchFilter
    {
        return new SelectSearchFilter("Modalité", self::NAME_modalite);
    }
    private function createLibelleFilter(): TextSearchFilter
    {
        $filter = new StrReducedTextSearchFilter("Libellé", self::NAME_libelle);
        $filter->setUseLikeOperator();

        return $filter;
    }

    /********************************** SORTERS ****************************************/

    private function createResponsableSorter(): SearchSorter
    {
        $sorter = new SearchSorter("Responsable", self::NAME_responsable);
        $sorter->setOrderByField("resp.nomUsuel");

        return $sorter;
    }

    private function createModaliteSorter(): SearchSorter
    {
        return new SearchSorter("Modalité", self::NAME_modalite);
        // $sorter->setOrderByFieldSpec() inutile car self::NAME_modalite === nom de l'attribut d'entité.
    }

    private function createLibelleSorter(): SearchSorter
    {
        return new SearchSorter("Libellé", self::NAME_libelle);
        // $sorter->setOrderByField() inutile car self::NAME_libelle === nom de l'attribut d'entité.
    }
}
```

Sa factory :

```php
namespace Formation\Service\Formation\Search;

use Formation\Entity\Db\Formation;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class FormationSearchServiceFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): FormationSearchService
    {
        $service = new FormationSearchService();

        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $container->get('doctrine.entitymanager.orm_default');
        /** @var \Formation\Entity\Db\Repository\FormationRepository $formationRepository */
        $formationRepository = $em->getRepository(Formation::class);
        $service->setFormationRepository($formationRepository);

        return $service;
    }
}
```


### Contrôleur `FormationRechercheController`

C'est une bonne idée de créer un contrôleur dédié à la recherche mais évidemment il est possible d'ajouter 
le nécessaire à un contrôleur existant.

Ce contôleur doit nécessairement :
  - implémenter l'interface  `SearchControllerInterface` ;
  - utiliser le trait `SearchServiceAwareTrait` (permet d'injecter le service de recherche) ;
  - utiliser le trait `SearchControllerTrait`.

```php
namespace Formation\Controller\Recherche;

use Application\Controller\AbstractController;
use Application\Search\Controller\SearchControllerInterface;
use Application\Search\Controller\SearchControllerTrait;
use Application\Search\SearchServiceAwareTrait;
use Laminas\Http\Response;
use Laminas\Paginator\Paginator as LaminasPaginator;
use Laminas\View\Model\ViewModel;

/**
 * @property \Formation\Service\Formation\Search\FormationSearchService $searchService
 */
class FormationRechercheController extends AbstractController implements SearchControllerInterface
{
    use SearchServiceAwareTrait;
    use SearchControllerTrait;

    protected string $routeName = 'formation/formation';
    protected string $indexActionTemplate = 'formation/formation/recherche/index';
    protected string $filtersActionTemplate = 'formation/formation/recherche/filters';
    protected string $title = "Formations";

    public function indexAction()
    {
        $result = $this->search();
        if ($result instanceof Response) {
            return $result;
        }
        /** @var LaminasPaginator $paginator */
        $paginator = $result;

        $model = new ViewModel([
            'title' => $this->title,
            'paginator' => $paginator,
            'routeName' => $this->routeName,
            'returnUrl' => $this->getRequest()->getRequestUri(),
        ]);
        $model->setTemplate($this->indexActionTemplate);

        return $model;
    }

    /** 
     * Surcharge de la méthode {@see SearchControllerTrait::filtersAction()}.
     */
    public function filtersAction(): ViewModel
    {
        $filters = $this->filters();

        $model = new ViewModel([
            'filters' => $filters,
        ]);
        $model->setTemplate($this->filtersActionTemplate);

        return $model;
    }
}
```

Sa factory :

```php
namespace Formation\Controller\Recherche;

use Formation\Service\Formation\Search\FormationSearchService;
use Psr\Container\ContainerInterface;

class FormationRechercheControllerFactory
{
    public function __invoke(ContainerInterface $container): FormationRechercheController
    {
        /** @var \Formation\Service\Formation\Search\FormationSearchService $searchService */
        $searchService = $container->get(FormationSearchService::class);

        $controller = new FormationRechercheController();
        $controller->setSearchService($searchService);

        return $controller;
    }
}
```


### Vue `index.phtml`

```php
namespace Application;

use Formation\Service\Formation\Search\FormationSearchService;
use Laminas\Paginator\Paginator as LaminasPaginator;

/**
 * @var string $title
 * @var LaminasPaginator $paginator
 * @var string $routeName
 * @var string $returnUrl
 *
 * @see \Formation\Controller\Recherche\FormationRechercheController::indexAction()
 */

$this->headTitle($this->translate($title));
?>

<h1 class="page-header"><?php echo $title ?></h1>

<!-- Formulaire de filtrage -->
<div class="float-start">
    <?php $loadFiltersUrl = $this->url($routeName . '/filters', [], ['query' => $this->queryParams()], true); ?>
    <div id="filters" data-url="<?php echo $loadFiltersUrl ?>" style="min-height: 160px">
        <!-- Contenu chargé en AJAX -->
    </div>
</div>
<div class="clearfix"></div>

<?php if (count($paginator) > 0): ?>

    <p>
        <?php echo $paginator->getTotalItemCount() . ' ' . $this->translate("formation·s trouvée·s.") ?>
    </p>
    <table class="table table-sm table-hover">
        <thead>
        <tr>
            <th>
                <a href="<?php echo $s = $this->sortable(FormationSearchService::NAME_libelle) ?>"
                   title="<?php echo $this->translate("Libellé") ?> ">
                    <?php echo $this->translate("Libellé") ?>
                </a> <?php echo $s->icon() ?>
            </th>
            <th>
                <a href="<?php echo $s = $this->sortable(FormationSearchService::NAME_responsable) ?>"
                   title="<?php echo $this->translate("Responsable") ?> ">
                    <?php echo $this->translate("Responsable") ?>*
                </a> <?php echo $s->icon() ?>
            </th>
            <th>
                <a href="<?php echo $s = $this->sortable(FormationSearchService::NAME_modalite) ?>"
                   title="<?php echo $this->translate("Modalité") ?> ">
                    <?php echo $this->translate("Modalité") ?>*
                </a> <?php echo $s->icon() ?>
            </th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($paginator as $formation) : ?>
            <tr>
                <td>
                    <?php echo $formation->getLibelle(); ?>
                </td>
                <td>
                    <?php echo $formation->getResponsable()->getNomComplet(); ?>
                </td>
                <td>
                    <?php echo $this->modalite($formation); ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php echo $this->paginationControl($paginator, 'sliding', 'paginator', ['route' => $routeName . '/recherche/index']) ?>

<?php else: ?>

    <p>Aucune formation trouvée.</p>

<?php endif ?>


<script>
    $(function() {
        $("#filters").addClass("loading").refresh(null, function() { $(this).removeClass("loading"); });
    });
</script>
```


### Vue `filters.phtml`

```php
use Application\View\Renderer\PhpRenderer;

/**
 * @var PhpRenderer $this
 * @var array       $filters
 *
 * @see \Formation\Controller\Recherche\FormationRechercheController::filtersAction()
 */

echo $this->filtersPanel($filters);
```


### Vue `paginator.phtml`

Un peu hors-cadre, en tout cas aucune spécificité lié à la recherche...
Cf. [paginator.phtml](../../module/Formation/view/formation/paginator.phtml)


### Config BjyAuthorize

```php
    'router' => [
        'routes' => [
            'formation' => [
                'child_routes' => [
                    'formation' => [
                        'type'  => Literal::class,
                        'may_terminate' => true,
                        'options' => [
                            'route'    => '/formation',
                            //'route'    => '/formation[/:type]', <<< Désolé mais c'est impossible !
                            'defaults' => [
                                'controller' => FormationRechercheController::class,
                                'action'     => 'index',
                            ],
                        ],
                        'child_routes' => [
                            'filters' => [
                                'type' => 'Literal',
                                'options' => [
                                    'route' => '/filters',
                                    'defaults' => [
                                        'action' => 'filters',
                                    ],
                                ],
                            ],
    // ...

    'bjyauthorize' => [
        'guards' => [
            PrivilegeController::class => [
                [
                    'controller' => FormationRechercheController::class,
                    'action' => [
                        'index',
                        'filters',
                    ],
                    'privileges' => [
                        FormationPrivileges::FORMATION_INDEX,
                    ],
                ],
    // ...
```

**Remarques importantes** : 
- La route menant à l'action de recherche ne peut pas faire mention d'un paramètre facultatif.
  Une solution possible :
```php
                        'options' => [
                            'route'    => '/formation/:type',
                            'constraints' => [
                                'type' => '(\d+)|(tous)',
                            ],
                            'defaults' => [
                                'controller' => FormationRechercheController::class,
                                'action'     => 'index',
                                'type'       => 'tous',
                            ],
```
