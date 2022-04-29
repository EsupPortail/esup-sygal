<?php

namespace RapportActivite\Controller\Recherche;

use Application\Controller\AbstractController;
use Application\Entity\Db\Interfaces\TypeRapportAwareTrait;
use Application\Entity\Db\Interfaces\TypeValidationAwareTrait;
use Application\Entity\Db\OrigineFinancement;
use Application\Entity\Db\Role;
use Application\Entity\Db\TypeRapport;
use Application\Search\Controller\SearchControllerInterface;
use Application\Search\Controller\SearchControllerTrait;
use Application\Search\SearchServiceAwareTrait;
use Application\Service\Fichier\Exception\FichierServiceException;
use Application\Service\Fichier\FichierServiceAwareTrait;
use Application\Service\Financement\FinancementServiceAwareTrait;
use Application\Service\Structure\StructureServiceAwareTrait;
use Laminas\Http\Response;
use Laminas\Paginator\Paginator as LaminasPaginator;
use Laminas\View\Model\ViewModel;
use RapportActivite\Entity\Db\RapportActivite;
use RapportActivite\Entity\Db\RapportActiviteAvis;
use RapportActivite\Provider\Privilege\RapportActivitePrivileges;
use RapportActivite\Service\Avis\RapportActiviteAvisServiceAwareTrait;
use RuntimeException;
use UnexpectedValueException;
use UnicaenAuth\Provider\Privilege\Privileges;
use UnicaenAvis\Entity\Db\Avis;

/**
 * @property \RapportActivite\Service\Search\RapportActiviteSearchService $searchService
 */
class RapportActiviteRechercheController extends AbstractController implements SearchControllerInterface
{
    use SearchServiceAwareTrait;
    use SearchControllerTrait;

    use StructureServiceAwareTrait;
    use FichierServiceAwareTrait;
    use RapportActiviteAvisServiceAwareTrait;
    use FinancementServiceAwareTrait;

    use TypeRapportAwareTrait;
    use TypeValidationAwareTrait;

    protected string $privilege_LISTER_TOUT = RapportActivitePrivileges::RAPPORT_ACTIVITE_LISTER_TOUT;
    protected string $privilege_LISTER_SIEN = RapportActivitePrivileges::RAPPORT_ACTIVITE_LISTER_SIEN;
    protected string $privilege_TELEVERSER_TOUT = RapportActivitePrivileges::RAPPORT_ACTIVITE_TELEVERSER_TOUT;
    protected string $privilege_TELEVERSER_SIEN = RapportActivitePrivileges::RAPPORT_ACTIVITE_TELEVERSER_SIEN;
    protected string $privilege_SUPPRIMER_TOUT = RapportActivitePrivileges::RAPPORT_ACTIVITE_SUPPRIMER_TOUT;
    protected string $privilege_SUPPRIMER_SIEN = RapportActivitePrivileges::RAPPORT_ACTIVITE_SUPPRIMER_SIEN;
    protected string $privilege_RECHERCHER_TOUT = RapportActivitePrivileges::RAPPORT_ACTIVITE_RECHERCHER_TOUT;
    protected string $privilege_RECHERCHER_SIEN = RapportActivitePrivileges::RAPPORT_ACTIVITE_RECHERCHER_SIEN;
    protected string $privilege_TELECHARGER_TOUT = RapportActivitePrivileges::RAPPORT_ACTIVITE_TELECHARGER_TOUT;
    protected string $privilege_TELECHARGER_SIEN = RapportActivitePrivileges::RAPPORT_ACTIVITE_TELECHARGER_SIEN;
    protected string $privilege_TELECHARGER_ZIP = RapportActivitePrivileges::RAPPORT_ACTIVITE_TELECHARGER_ZIP;
    protected string $privilege_VALIDER_TOUT = RapportActivitePrivileges::RAPPORT_ACTIVITE_VALIDER_TOUT;
    protected string $privilege_VALIDER_SIEN = RapportActivitePrivileges::RAPPORT_ACTIVITE_VALIDER_SIEN;
    protected string $privilege_DEVALIDER_TOUT = RapportActivitePrivileges::RAPPORT_ACTIVITE_DEVALIDER_TOUT;
    protected string $privilege_DEVALIDER_SIEN = RapportActivitePrivileges::RAPPORT_ACTIVITE_DEVALIDER_SIEN;

    protected string $routeName = 'rapport-activite';

    protected string $title = "Rapports d'activité";


    /**
     * @var string
     */
    protected string $indexActionTemplate = 'rapport-activite/rapport-recherche/index';
    protected string $filtersActionTemplate = 'rapport-activite/rapport-recherche/filters';

    /**
     * @return ViewModel|Response
     */
    public function indexAction()
    {
        $this->restrictFilterEcolesDoctorales();
        $this->restrictFilterOrigineFinancement();
        $this->initFilterAvisAttendu();

        $text = $this->params()->fromQuery('text');

        $result = $this->search();
        if ($result instanceof Response) {
            return $result;
        }
        /** @var LaminasPaginator $paginator */
        $paginator = $result;

        $model = new ViewModel([
            'title' => $this->title,
            'paginator' => $paginator,
            'text' => $text,

            'typeValidation' => $this->typeValidation,
            'routeName' => $this->routeName,

            'privilege_LISTER_TOUT' => $this->privilege_LISTER_TOUT,
            'privilege_LISTER_SIEN' => $this->privilege_LISTER_SIEN,
            'privilege_TELEVERSER_TOUT' => $this->privilege_TELEVERSER_TOUT,
            'privilege_TELEVERSER_SIEN' => $this->privilege_TELEVERSER_SIEN,
            'privilege_SUPPRIMER_TOUT' => $this->privilege_SUPPRIMER_TOUT,
            'privilege_SUPPRIMER_SIEN' => $this->privilege_SUPPRIMER_SIEN,
            'privilege_RECHERCHER_TOUT' => $this->privilege_RECHERCHER_TOUT,
            'privilege_RECHERCHER_SIEN' => $this->privilege_RECHERCHER_SIEN,
            'privilege_TELECHARGER_TOUT' => $this->privilege_TELECHARGER_TOUT,
            'privilege_TELECHARGER_SIEN' => $this->privilege_TELECHARGER_SIEN,
            'privilege_TELECHARGER_ZIP' => $this->privilege_TELECHARGER_ZIP,
            'privilege_VALIDER_TOUT' => $this->privilege_VALIDER_TOUT,
            'privilege_VALIDER_SIEN' => $this->privilege_VALIDER_SIEN,
            'privilege_DEVALIDER_TOUT' => $this->privilege_DEVALIDER_TOUT,
            'privilege_DEVALIDER_SIEN' => $this->privilege_DEVALIDER_SIEN,

            'returnUrl' => $this->getRequest()->getRequestUri(),

            'displayEtablissement' => true,
            'displayType' => true,
            'displayDoctorant' => true,
            'displayDirecteurThese' => true,
            'displayEcoleDoctorale' => true,
            'displayUniteRecherche' => true,
            'displayAvis' => true,
            'displayValidation' => true,
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
        $this->restrictFilterOrigineFinancement();
        $this->initFilterAvisAttendu();

        $filters = $this->filters();

        $model = new ViewModel([
            'filters' => $filters,
            'message' => "coucou!",
        ]);
        $model->setTemplate($this->filtersActionTemplate);

        return $model;
    }

    private function restrictFilterEcolesDoctorales()
    {
        $edFilter = $this->searchService->getEcoleDoctoraleSearchFilter();

        if ($this->isAllowed(Privileges::getResourceId($this->privilege_LISTER_TOUT))) {
            // aucune restriction sur les ED sélectionnables
        } elseif ($this->isAllowed(Privileges::getResourceId($this->privilege_LISTER_SIEN))) {
            // restrictions en fonction du rôle
            if ($roleEcoleDoctorale = $this->userContextService->getSelectedRoleEcoleDoctorale()) {
                $ed = $roleEcoleDoctorale->getStructure()->getEcoleDoctorale();
                $edFilter->setData([$ed]);
                $edFilter->setDefaultValueAsObject($ed);
                $edFilter->setAllowsEmptyOption(false);
            }
        } else {
            throw new UnexpectedValueException(
                "Anomalie : l'action aurait dû être bloquée en amont (controller guard) car l'utilisateur n'a aucun des privilèges suivants : " .
                implode(', ', [$this->privilege_LISTER_TOUT, $this->privilege_LISTER_SIEN])
            );
        }
    }

    private function restrictFilterOrigineFinancement()
    {
        $filter = $this->searchService->getOrigineFinancementSearchFilter();

        // rôle "Obervateur COMUE" : restrictions au type de financement "Région"
        // (todo : supprimer cette particularité localo-locale temporaire quand ce sera possible)
        if ($this->userContextService->getSelectedIdentityRole()->getCode() === Role::CODE_OBSERVATEUR_COMUE) {
            $of = $this->financementService->getOrigineFinancementByCode($code = OrigineFinancement::CODE_REGION_NORMANDIE);
            $filter->setData([$of]);
            $filter->setDefaultValue($code);
            $filter->setAllowsEmptyOption(false);
        }
    }

    /**
     * Initialisations du filtre "Avis attendu".
     */
    private function initFilterAvisAttendu()
    {
        $filter = $this->searchService->getAvisManquantSearchFilter();

        /**
         * Valeur par défaut (NB : empêche de sélectionner la valeur "Peu importe") :
         *   - pour le rôle Gestionnaire d'ED : "Avis gestionnaire d'ED"
         *   - pour le rôle Responsable d'ED : "Avis direction d'ED"
         */
        if ($roleEcoleDoctorale = $this->userContextService->getSelectedRoleEcoleDoctorale()) {
            if ($roleEcoleDoctorale->getCode() === Role::CODE_GEST_ED) {
                $filter->setDefaultValue(RapportActiviteAvis::AVIS_TYPE__CODE__AVIS_RAPPORT_ACTIVITE_GEST);
            } elseif ($roleEcoleDoctorale->getCode() === Role::CODE_RESP_ED) {
                $filter->setDefaultValue(RapportActiviteAvis::AVIS_TYPE__CODE__AVIS_RAPPORT_ACTIVITE_DIR);
            }
        }

        /**
         * Valeur par défaut (NB : empêche de sélectionner la valeur "Peu importe") :
         *   - pour le rôle Observateur COMUE : "Avis gestionnaire d'ED"
         */
        if ($this->userContextService->getSelectedIdentityRole()->getCode() === Role::CODE_OBSERVATEUR_COMUE) {
            $filter->setDefaultValue(RapportActiviteAvis::AVIS_TYPE__CODE__AVIS_RAPPORT_ACTIVITE_GEST);
        }
    }

    /**
     * Redéfinition de la méthode {@see SearchControllerTrait::filtersAction()}
     * pour injecter des choses dans les rapports d'activité avant affichage.
     *
     * @return Response|LaminasPaginator
     */
    public function search()
    {
        $result = $this->getSearchPluginController()->search();
        if ($result instanceof Response) {
            return $result;
        }

        /** @var RapportActivite $rapport */
        foreach ($result as $rapport) {
            $avisTypeDispo = $this->rapportActiviteAvisService->findNextExpectedAvisTypeForRapport($rapport);
            if ($avisTypeDispo === null) {
                $rapport->setRapportAvisPossible(null);
                continue;
            }

            $rapportAvisPossible = new RapportActiviteAvis();
            $rapportAvisPossible
                ->setRapportActivite($rapport)
                ->setAvis((new Avis())->setAvisType($avisTypeDispo));

            $rapport->setRapportAvisPossible($rapportAvisPossible);
        }

        return $result;
    }

    /**
     * @return void|Response
     */
    public function telechargerZipAction(): Response
    {
        $this->restrictFilterEcolesDoctorales();

        $result = $this->search();
        if ($result instanceof Response) {
            return $result; // théoriquement, on ne devrait pas arriver ici.
        }
        /** @var LaminasPaginator $paginator */
        $paginator = $result;

        $fichiers = [];
        /** @var RapportActivite $rapport */
        foreach ($paginator as $rapport) {
            $fichier = $rapport->getFichier();
            $fichier->setPath($rapport->generateInternalPathForZipArchive());
            $fichiers[] = $rapport->getFichier();
        }

        $filename = sprintf("sygal_%s.zip", strtolower(TypeRapport::RAPPORT_ACTIVITE));
        try {
            $fichierZip = $this->fichierService->compresserFichiers($fichiers, $filename);
        } catch (FichierServiceException $e) {
            throw new RuntimeException("Une erreur est survenue empêchant la création de l'archive zip", null, $e);

        }
        $this->fichierService->telechargerFichier($fichierZip);
    }
}