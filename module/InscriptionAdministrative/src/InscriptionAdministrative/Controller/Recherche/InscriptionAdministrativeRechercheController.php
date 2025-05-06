<?php

namespace InscriptionAdministrative\Controller\Recherche;

use Application\Controller\AbstractController;
use UnicaenSearch\Controller\SearchControllerInterface;
use UnicaenSearch\Controller\SearchControllerTrait;
use UnicaenSearch\SearchServiceAwareTrait;
use Application\Service\AnneeUniv\AnneeUnivServiceAwareTrait;
use Doctrine\ORM\AbstractQuery;
use Laminas\Http\Response;
use Laminas\Paginator\Paginator as LaminasPaginator;
use Laminas\View\Model\ViewModel;
use RapportActivite\Provider\Privilege\RapportActivitePrivileges;
use UnexpectedValueException;
use UnicaenPrivilege\Provider\Privilege\Privileges;

/**
 * @property \InscriptionAdministrative\Service\Search\InscriptionAdministrativeSearchService $searchService
 */
class InscriptionAdministrativeRechercheController extends AbstractController implements SearchControllerInterface
{
    use SearchServiceAwareTrait;
    use SearchControllerTrait;

    use AnneeUnivServiceAwareTrait;

    protected string $indexActionTemplate = 'inscription-administrative/inscription-administrative/recherche/index';
    protected string $filtersActionTemplate = 'inscription-administrative/inscription-administrative/recherche/filters';

    public function indexAction(): Response|ViewModel
    {
        $this->restrictFilterEcolesDoctorales();

        $text = $this->params()->fromQuery('text');

        $result = $this->search();
        if ($result instanceof Response) {
            return $result;
        }
        /** @var LaminasPaginator $paginator */
        $paginator = $result;

        $model = new ViewModel([
            'title' => "Inscriptions administratives",
            'paginator' => $paginator,
            'text' => $text,
            'anneeUnivService' => $this->anneeUnivService,
        ]);
        $model->setTemplate($this->indexActionTemplate);

        return $model;
    }

    /**
     * Surcharge de la méthode {@see SearchControllerTrait::filtersAction()}.
     *
     * @return ViewModel
     */
    public function filtersAction(): ViewModel
    {
        $this->restrictFilterEcolesDoctorales();

        $filters = $this->filters();

        $model = new ViewModel([
            'filters' => $filters,
            'message' => "coucou!",
        ]);
        $model->setTemplate($this->filtersActionTemplate);

        return $model;
    }

    private function restrictFilterEcolesDoctorales(): void
    {
        $edFilter = $this->searchService->getEcoleDoctoraleSearchFilter();

        if ($this->isAllowed(Privileges::getResourceId(RapportActivitePrivileges::RAPPORT_ACTIVITE_LISTER_TOUT))) {
            // aucune restriction sur les ED sélectionnables
        } elseif ($this->isAllowed(Privileges::getResourceId(RapportActivitePrivileges::RAPPORT_ACTIVITE_LISTER_SIEN))) {
            // restrictions en fonction du rôle
            if ($roleEcoleDoctorale = $this->userContextService->getSelectedRoleEcoleDoctorale()) {
                $ed = $roleEcoleDoctorale->getStructure()->getEcoleDoctorale();
                $edFilter->setData([$ed]);
                $edFilter->setDefaultValueAsObject($ed);
                $edFilter->setAllowsEmptyValueOption(false);
            }
        } else {
            throw new UnexpectedValueException(
                "Anomalie : l'action aurait dû être bloquée en amont (controller guard) car l'utilisateur n'a aucun des privilèges suivants : " .
                implode(', ', [RapportActivitePrivileges::RAPPORT_ACTIVITE_LISTER_TOUT, RapportActivitePrivileges::RAPPORT_ACTIVITE_LISTER_SIEN])
            );
        }
    }

    /**
     * Redéfinition de la méthode {@see SearchControllerTrait::filtersAction()}
     * pour pouvoir faire des choses avant l'affichage.
     */
    public function search(): Response|LaminasPaginator
    {
        $result = $this->getSearchPluginController()->search(null, AbstractQuery::HYDRATE_ARRAY);
        if ($result instanceof Response) {
            return $result;
        }

        $result->setItemCountPerPage(25);

        return $result;
    }
}