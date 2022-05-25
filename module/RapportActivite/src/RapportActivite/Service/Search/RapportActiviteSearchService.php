<?php

namespace RapportActivite\Service\Search;

use Application\Entity\Db\Interfaces\TypeRapportAwareTrait;
use Application\Entity\Db\Interfaces\TypeValidationAwareTrait;
use Application\Entity\Db\TypeStructure;
use Application\Filter\AnneeUnivFormatter;
use Application\Search\EcoleDoctorale\EcoleDoctoraleSearchFilter;
use Application\Search\Etablissement\EtablissementSearchFilter;
use Application\Search\Filter\SearchFilter;
use Application\Search\Filter\SelectSearchFilter;
use Application\Search\Filter\TextSearchFilter;
use Application\Search\Financement\OrigineFinancementSearchFilter;
use Application\Search\SearchService;
use Application\Search\Sorter\SearchSorter;
use Application\Search\These\TheseTextSearchFilter;
use Application\Search\UniteRecherche\UniteRechercheSearchFilter;
use Application\Service\Acteur\ActeurServiceAwareTrait;
use Application\Service\Etablissement\EtablissementServiceAwareTrait;
use Application\Service\Financement\FinancementServiceAwareTrait;
use Application\Service\Structure\StructureServiceAwareTrait;
use Application\Service\These\TheseSearchServiceAwareTrait;
use Application\Service\TheseAnneeUniv\TheseAnneeUnivServiceAwareTrait;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use InvalidArgumentException;
use RapportActivite\Entity\Db\RapportActiviteAvis;
use RapportActivite\Search\AnneeRapportActiviteSearchFilter;
use RapportActivite\Service\Avis\RapportActiviteAvisServiceAwareTrait;
use RapportActivite\Service\RapportActiviteServiceAwareTrait;

class RapportActiviteSearchService extends SearchService
{
    use FinancementServiceAwareTrait;
    use TheseSearchServiceAwareTrait;
    use TheseAnneeUnivServiceAwareTrait;
    use StructureServiceAwareTrait;
    use EtablissementServiceAwareTrait;
    use ActeurServiceAwareTrait;
    use RapportActiviteServiceAwareTrait;
    use RapportActiviteAvisServiceAwareTrait;
    use TypeRapportAwareTrait;
    use TypeValidationAwareTrait;

    const NAME_nom_doctorant = 'nom_doctorant';
    const NAME_nom_directeur = 'nom_directeur';
    const NAME_type = 'type';
    const NAME_avis_attendu = 'avis_attendu';
    const NAME_avis_fourni = 'avis_fourni';
    const NAME_validation = 'est_valide';

    private ?EtablissementSearchFilter $etablissementTheseSearchFilter = null;
    private ?OrigineFinancementSearchFilter $origineFinancementSearchFilter = null;
    private ?EcoleDoctoraleSearchFilter $ecoleDoctoraleSearchFilter = null;
    private ?UniteRechercheSearchFilter $uniteRechercheSearchFilter = null;
    private ?AnneeRapportActiviteSearchFilter $anneeRapportActiviteSearchFilter = null;
    private ?SelectSearchFilter $validationSearchFilter = null;
    private ?SelectSearchFilter $finalSearchFilter = null;
    private ?SelectSearchFilter $avisFourniSearchFilter = null;
    private ?SelectSearchFilter $avisAttenduSearchFilter = null;

    /**
     * @inheritDoc
     */
    public function init()
    {
        $etablissementInscrFilter = $this->getEtablissementTheseSearchFilter();
        $origineFinancementFilter = $this->getOrigineFinancementSearchFilter();
        $uniteRechercheFilter = $this->getUniteRechercheSearchFilter();
        $ecoleDoctoraleFilter = $this->getEcoleDoctoraleSearchFilter();
        $anneeRapportActiviteInscrFilter = $this->getAnneeRapportActiviteSearchFilter();
        $avisFourniSearchFilter = $this->getAvisFourniSearchFilter();
        $avisAttenduSearchFilter = $this->getAvisAttenduSearchFilter();
        $validationSearchFilter = $this->getValidationSearchFilter();
        $finalSearchFilter = $this->getFinalSearchFilter();

        $etablissementInscrFilter->setDataProvider(fn() => $this->fetchEtablissements());
        $origineFinancementFilter->setDataProvider(fn() => $this->fetchOriginesFinancements());
        $uniteRechercheFilter->setDataProvider(fn() => $this->fetchUnitesRecherches());
        $ecoleDoctoraleFilter->setDataProvider(fn() => $this->fetchEcolesDoctorales());
        $anneeRapportActiviteInscrFilter->setDataProvider(fn() => $this->fetchAnneesRapportActivite());

        $this->addFilters(array_filter([
            $etablissementInscrFilter,
            $origineFinancementFilter,
            $ecoleDoctoraleFilter,
            $uniteRechercheFilter,
            $finalSearchFilter,
            $anneeRapportActiviteInscrFilter,
            $this->createFilterNomDoctorant(),
            $this->createFilterNomDirecteur(),
            $avisFourniSearchFilter,
            $avisAttenduSearchFilter,
            $validationSearchFilter,
        ]));

        $this->addSorters([
            $etablissementInscrFilter->createSorter(),
            $this->createSorterTypeRapport(),
            $ecoleDoctoraleFilter->createSorter(),
            $uniteRechercheFilter->createSorter(),
            $anneeRapportActiviteInscrFilter->createSorter(),
            $this->createSorterNomPrenomDoctorant()->setIsDefault(),
            $this->createSorterValidation(),
        ]);
    }

    /**
     * @inheritDoc
     */
    public function createQueryBuilder(): QueryBuilder
    {
        // ATTENTION ! Les relations suivantes doivent être sélectionnées lors du fetch des rapports :
        // 'rapportAvis->avis->avisType'.

        $qb = $this->rapportActiviteService->getRepository()->createQueryBuilder('ra')
            ->addSelect('tr, these, f, d, i, rav, raa, a, at')
            ->join('ra.typeRapport', 'tr')
            ->join('ra.these', 'these')
            ->join('these.doctorant', 'd')
            ->join('d.individu', 'i')
            ->join('ra.fichier', 'f')
            ->leftJoin('ra.rapportValidations', 'rav')
            ->leftJoin('ra.rapportAvis', 'raa')
            ->leftJoin('raa.avis', 'a')
            ->leftJoin('a.avisType', 'at')
            ->addOrderBy('at.ordre')
            ->andWhereNotHistorise();

        if ($this->typeRapport !== null) {
            $qb->andWhere('tr = :type')->setParameter('type', $this->typeRapport);
        }

        return $qb;
    }

    private function fetchEtablissements(): array
    {
        return $this->etablissementService->getRepository()->findAllEtablissementsInscriptions(true);
    }

    private function fetchEcolesDoctorales(): array
    {
        return $this->structureService->getAllStructuresAffichablesByType(
            TypeStructure::CODE_ECOLE_DOCTORALE, 'sigle', true, true);
    }

    private function fetchUnitesRecherches(): array
    {
        return $this->structureService->getAllStructuresAffichablesByType(TypeStructure::CODE_UNITE_RECHERCHE, 'code', false, true);
    }

    private function fetchOriginesFinancements(): array
    {
        $values = $this->getFinancementService()->getOriginesFinancements("libelleLong");

        // dédoublonnage (sur le code origine) car chaque établissement pourrait fournir les mêmes données
        $origines = [];
        foreach (array_filter($values) as $origine) {
            $origines[$origine->getCode()] = $origine;
        }

        return $origines;
    }

    private function fetchAnneesRapportActivite(): array
    {
        $annees = $this->rapportActiviteService->findDistinctAnnees();
        $annees = array_reverse(array_filter($annees));

        return  array_combine($annees, $annees);
    }

    /**
     * @param SearchFilter $filter
     * @param QueryBuilder $qb
     */
    public function applyFilterToQueryBuilder(SearchFilter $filter, QueryBuilder $qb)
    {
        // todo: permettre la spécification de l'alias Doctrine à utiliser via $filter->getAlias() ?

        switch ($filter->getName()) {
            case self::NAME_nom_doctorant:
                $this->applyNomDoctorantFilterToQueryBuilder($filter, $qb);
                break;
            case self::NAME_nom_directeur:
                $this->applyNomDirecteurFilterToQueryBuilder($filter, $qb);
                break;
            case self::NAME_validation:
                $this->applyValidationFilterToQueryBuilder($filter, $qb);
                break;
            default:
                throw new InvalidArgumentException("Cas imprévu");
        }
    }

    private function applyNomDoctorantFilterToQueryBuilder(SearchFilter $filter, QueryBuilder $qb, $alias = 'these')
    {
        $this->applyTextFilterToQueryBuilder($filter, $qb, [TheseTextSearchFilter::CRITERIA_nom_doctorant], $alias);
    }

    private function applyNomDirecteurFilterToQueryBuilder(SearchFilter $filter, QueryBuilder $qb, $alias = 'these')
    {
        $this->applyTextFilterToQueryBuilder($filter, $qb, [TheseTextSearchFilter::CRITERIA_nom_directeur], $alias);
    }

    private function applyTextFilterToQueryBuilder(SearchFilter $filter, QueryBuilder $qb, array $criteria, $alias = 'these')
    {
        $filterValue = $filter->getValue();

        if ($filterValue !== null && strlen($filterValue) > 1) {
            $results = $this->theseSearchService->findThesesSourceCodesByText($filterValue, $criteria);
            $sourceCodes = array_unique(array_keys($results));
            if ($sourceCodes) {
                $paramName = 'sourceCodes_' . $filter->getName();
                $qb
                    ->andWhere($qb->expr()->in("$alias.sourceCode", ":$paramName"))
                    ->setParameter($paramName, $sourceCodes);
            }
            else {
                $qb->andWhere("0 = 1"); // i.e. aucune thèse trouvée
            }
        }
    }

    public function applyAvisFourniSearchFilterToQueryBuilder(SearchFilter $filter, QueryBuilder $qb, $alias = 'ra')
    {
        $filterValue = $filter->getValue();
        if (!$filterValue) {
            return;
        }

        $dql = 'SELECT rapportAvis_fourni_%1$d.id ' .
            'FROM ' . RapportActiviteAvis::class . ' rapportAvis_fourni_%1$d ' .
            'JOIN rapportAvis_fourni_%1$d.avis avis_fourni_%1$d ' .
            'JOIN avis_fourni_%1$d.avisType avisType_fourni_%1$d WITH avisType_fourni_%1$d.code = :code_fourni_%1$d ' .
            'WHERE rapportAvis_fourni_%1$d.rapport = ra';

        switch ($filterValue) {
            case 'null':
                $qb
                    ->andWhere('NOT EXISTS (' . sprintf($dql, 1) . ')')
                    ->setParameter('code_fourni_1', RapportActiviteAvis::AVIS_TYPE__CODE__AVIS_RAPPORT_ACTIVITE_GEST)
                    ->andWhere('NOT EXISTS (' . sprintf($dql, 2) . ')')
                    ->setParameter('code_fourni_2', RapportActiviteAvis::AVIS_TYPE__CODE__AVIS_RAPPORT_ACTIVITE_DIR);
                break;

            case RapportActiviteAvis::AVIS_TYPE__CODE__AVIS_RAPPORT_ACTIVITE_GEST:
                $qb
                    ->andWhere('EXISTS (' . sprintf($dql, 1) . ')')
                    ->setParameter('code_fourni_1', RapportActiviteAvis::AVIS_TYPE__CODE__AVIS_RAPPORT_ACTIVITE_GEST);
                break;

            case RapportActiviteAvis::AVIS_TYPE__CODE__AVIS_RAPPORT_ACTIVITE_DIR:
                $qb
                    ->andWhere('EXISTS (' . sprintf($dql, 2) . ')')
                    ->setParameter('code_fourni_2', RapportActiviteAvis::AVIS_TYPE__CODE__AVIS_RAPPORT_ACTIVITE_DIR);
                break;

            case 'tous':
                $qb
                    ->andWhere('EXISTS (' . sprintf($dql, 1) . ')')
                    ->setParameter('code_fourni_1', RapportActiviteAvis::AVIS_TYPE__CODE__AVIS_RAPPORT_ACTIVITE_GEST)
                    ->andWhere('EXISTS (' . sprintf($dql, 2) . ')')
                    ->setParameter('code_fourni_2', RapportActiviteAvis::AVIS_TYPE__CODE__AVIS_RAPPORT_ACTIVITE_DIR);
                break;

            default:
                throw new InvalidArgumentException("Valeur inattendue pour le filtre " . $filter->getName());
        }
    }

    public function applyAvisAttenduSearchFilterToQueryBuilder(SearchFilter $filter, QueryBuilder $qb, $alias = 'ra')
    {
        $filterValue = $filter->getValue();
        if (!$filterValue) {
            return;
        }

        $dql = 'SELECT rapportAvis_attendu_%1$d.id ' .
            'FROM ' . RapportActiviteAvis::class . ' rapportAvis_attendu_%1$d ' .
            'JOIN rapportAvis_attendu_%1$d.avis avis_attendu_%1$d ' .
            'JOIN avis_attendu_%1$d.avisType avisType_attendu_%1$d WITH avisType_attendu_%1$d.code = :code_attendu_%1$d ' .
            'WHERE rapportAvis_attendu_%1$d.rapport = ra';

        switch ($filterValue) {
            case 'null':
                $qb
                    ->andWhere('EXISTS (' . sprintf($dql, 1) . ')')
                    ->setParameter('code_attendu_1', RapportActiviteAvis::AVIS_TYPE__CODE__AVIS_RAPPORT_ACTIVITE_GEST)
                    ->andWhere('EXISTS (' . sprintf($dql, 2) . ')')
                    ->setParameter('code_attendu_2', RapportActiviteAvis::AVIS_TYPE__CODE__AVIS_RAPPORT_ACTIVITE_DIR);
                break;

            case RapportActiviteAvis::AVIS_TYPE__CODE__AVIS_RAPPORT_ACTIVITE_GEST:
                $qb
                    ->andWhere('NOT EXISTS (' . sprintf($dql, 1) . ')')
                    ->setParameter('code_attendu_1', RapportActiviteAvis::AVIS_TYPE__CODE__AVIS_RAPPORT_ACTIVITE_GEST);
                break;

            case RapportActiviteAvis::AVIS_TYPE__CODE__AVIS_RAPPORT_ACTIVITE_DIR:
                $qb
                    ->andWhere('EXISTS (' . sprintf($dql, 1) . ')')
                    ->setParameter('code_attendu_1', RapportActiviteAvis::AVIS_TYPE__CODE__AVIS_RAPPORT_ACTIVITE_GEST)
                    ->andWhere('NOT EXISTS (' . sprintf($dql, 2) . ')')
                    ->setParameter('code_attendu_2', RapportActiviteAvis::AVIS_TYPE__CODE__AVIS_RAPPORT_ACTIVITE_DIR);
                break;

            default:
                throw new InvalidArgumentException("Valeur inattendue pour le filtre " . $filter->getName());
        }
    }

    public function applyValidationFilterToQueryBuilder(SearchFilter $filter, QueryBuilder $qb, $alias = 'ra')
    {
        $filterValue = $filter->getValue();
        if ($filterValue === 'oui') {
            $qb
                ->join("$alias.rapportValidations", 'v_filter', Join::WITH, 'v_filter.histoDestruction is null');
        } elseif ($filterValue === 'non') {
            $qb
                ->leftJoin("$alias.rapportValidations", 'v_filter', Join::WITH, 'v_filter.histoDestruction is null')
                ->andWhere('v_filter is null');
        }
    }

    public function applyFinalFilterToQueryBuilder(SearchFilter $filter, QueryBuilder $qb, $alias = 'ra')
    {
        $filterValue = $filter->getValue();
        $qb
            ->andWhere("$alias.estFinal = :final")
            ->setParameter('final', $filterValue === 'finthese');
    }

    /**
     * @param SearchSorter $sorter
     * @param QueryBuilder $qb
     */
    public function applySorterToQueryBuilder(SearchSorter $sorter, QueryBuilder $qb)
    {
        // todo: permettre la spécification de l'alias Doctrine à utiliser via $sorter->getAlias() ?

        switch ($sorter->getName()) {
            case self::NAME_nom_doctorant:
                $this->applyNomDoctorantSorterToQueryBuilder($sorter, $qb);
                break;
            default:
                throw new InvalidArgumentException("Cas imprévu");
        }
    }

    public function applyNomDoctorantSorterToQueryBuilder(SearchSorter $sorter, QueryBuilder $qb, $alias = 'these')
    {
        $direction = $sorter->getDirection();
        $qb
            ->join("$alias.doctorant", 'd_sort')
            ->join('d_sort.individu', 'i_sort')
            ->addOrderBy('i_sort.nomUsuel', $direction)
            ->addOrderBy('i_sort.prenom1', $direction);
    }

    /**
     * @return TextSearchFilter
     */
    private function createFilterNomDoctorant(): TextSearchFilter
    {
        $filter = new TextSearchFilter(
            "Nom du doctorant",
            self::NAME_nom_doctorant
        );

        $filter->setQueryBuilderApplier([$this, 'applyFilterToQueryBuilder']);

        return $filter;
    }

    /**
     * @return TextSearchFilter
     */
    private function createFilterNomDirecteur(): TextSearchFilter
    {
        $filter = new TextSearchFilter(
            "Nom du directeur",
            self::NAME_nom_directeur
        );

        $filter->setQueryBuilderApplier([$this, 'applyFilterToQueryBuilder']);

        return $filter;
    }

    /**
     * @return SearchSorter
     */
    private function createSorterNomPrenomDoctorant(): SearchSorter
    {
        $sorter = new SearchSorter(
            "Doctorant",
            self::NAME_nom_doctorant
        );

        $sorter->setQueryBuilderApplier([$this, 'applySorterToQueryBuilder']);

        return $sorter;
    }

    /**
     * @return SearchSorter
     */
    public function createSorterTypeRapport(): SearchSorter
    {
        $sorter = new SearchSorter("Type", self::NAME_type);
        $sorter->setQueryBuilderApplier(
            function (SearchSorter $sorter, QueryBuilder $qb) {
                $direction = $sorter->getDirection();
                $qb
                    ->addOrderBy("tr.libelleCourt", $direction)
                    ->addOrderBy("ra.estFinal", $direction);
            }
        );

        return $sorter;
    }

    /**
     * @return SearchSorter
     */
    public function createSorterValidation(): SearchSorter
    {
        $sorter = new SearchSorter("Type", self::NAME_validation);
        $sorter->setQueryBuilderApplier(
            function (SearchSorter $sorter, QueryBuilder $qb, $alias = 'ra') {
                $direction = $sorter->getDirection();
                    $qb
                        ->leftJoin("$alias.rapportValidations", 'v_sort', Join::WITH, 'v.histoDestruction is null')
                        ->addOrderBy("v_sort.histoCreation", $direction);
            }
        );

        return $sorter;
    }

    public function getValidationSearchFilter(): SelectSearchFilter
    {
        if ($this->validationSearchFilter === null) {
            $this->validationSearchFilter = new SelectSearchFilter("Validés ?", self::NAME_validation);
            $this->validationSearchFilter
                ->setDataProvider(function () {
                    return ['oui' => "Oui", 'non' => "Non"];
                })
                ->setEmptyOptionLabel("(Peu importe)")
                ->setAllowsEmptyOption()
                ->setQueryBuilderApplier([$this, 'applyValidationFilterToQueryBuilder']);
        }

        return $this->validationSearchFilter;
    }

    public function getFinalSearchFilter(): ?SelectSearchFilter
    {
        if (! $this->typeRapport->estRapportActivite()) {
            return null;
        }

        if ($this->finalSearchFilter === null) {
            $this->finalSearchFilter = new SelectSearchFilter("Type", self::NAME_type);
            $this->finalSearchFilter
                ->setDataProvider(function () {
                    return ['annuel' => "Annuel", 'finthese' => "Fin de contrat"];
                })
                ->setEmptyOptionLabel("(Peu importe)")
                ->setAllowsEmptyOption()
                ->setQueryBuilderApplier([$this, 'applyFinalFilterToQueryBuilder']);
        }

        return $this->finalSearchFilter;
    }

    public function getAvisFourniSearchFilter(): SelectSearchFilter
    {
        if ($this->avisFourniSearchFilter === null) {
            $valueOptions = ['null' => "Aucun"];
            foreach($this->rapportActiviteAvisService->findAllSortedAvisTypes() as $avisType) {
                $valueOptions[$avisType->getCode()] = $avisType->__toString();
            }
            $valueOptions['tous'] = "Tous (i.e. rapport validé)";

            $this->avisFourniSearchFilter = new SelectSearchFilter("Avis fourni", self::NAME_avis_fourni);
            $this->avisFourniSearchFilter
                ->setDataProvider(function () use ($valueOptions) {
                    return $valueOptions;
                })
                ->setEmptyOptionLabel("(Peu importe)")
                ->setAllowsEmptyOption()
                ->setQueryBuilderApplier([$this, 'applyAvisFourniSearchFilterToQueryBuilder']);
        }

        return $this->avisFourniSearchFilter;
    }

    public function getAvisAttenduSearchFilter(): SelectSearchFilter
    {
        if ($this->avisAttenduSearchFilter === null) {
            $valueOptions = ['null' => "Aucun (i.e. rapport validé)"];
            foreach($this->rapportActiviteAvisService->findAllSortedAvisTypes() as $avisType) {
                $valueOptions[$avisType->getCode()] = $avisType->__toString();
            }

            $this->avisAttenduSearchFilter = new SelectSearchFilter("Avis attendu", self::NAME_avis_attendu);
            $this->avisAttenduSearchFilter
                ->setDataProvider(function () use ($valueOptions) {
                    return $valueOptions;
                })
                ->setEmptyOptionLabel("(Peu importe)")
                ->setAllowsEmptyOption()
                ->setQueryBuilderApplier([$this, 'applyAvisAttenduSearchFilterToQueryBuilder']);
        }

        return $this->avisAttenduSearchFilter;
    }

    /**
     * @return EtablissementSearchFilter
     */
    public function getEtablissementTheseSearchFilter(): EtablissementSearchFilter
    {
        return $this->etablissementTheseSearchFilter;
    }

    /**
     * @return OrigineFinancementSearchFilter
     */
    public function getOrigineFinancementSearchFilter(): OrigineFinancementSearchFilter
    {
        return $this->origineFinancementSearchFilter;
    }

    /**
     * @return EcoleDoctoraleSearchFilter
     */
    public function getEcoleDoctoraleSearchFilter(): EcoleDoctoraleSearchFilter
    {
        return $this->ecoleDoctoraleSearchFilter;
    }

    /**
     * @return UniteRechercheSearchFilter
     */
    public function getUniteRechercheSearchFilter(): UniteRechercheSearchFilter
    {
        return $this->uniteRechercheSearchFilter;
    }

    /**
     * @return AnneeRapportActiviteSearchFilter
     */
    public function getAnneeRapportActiviteSearchFilter(): AnneeRapportActiviteSearchFilter
    {
        return $this->anneeRapportActiviteSearchFilter;
    }

    /**
     * @param EtablissementSearchFilter $etablissementTheseSearchFilter
     * @return RapportActiviteSearchService
     */
    public function setEtablissementTheseSearchFilter(EtablissementSearchFilter $etablissementTheseSearchFilter): RapportActiviteSearchService
    {
        $this->etablissementTheseSearchFilter = $etablissementTheseSearchFilter;
        return $this;
    }

    /**
     * @param OrigineFinancementSearchFilter $origineFinancementSearchFilter
     * @return RapportActiviteSearchService
     */
    public function setOrigineFinancementSearchFilter(OrigineFinancementSearchFilter $origineFinancementSearchFilter): RapportActiviteSearchService
    {
        $this->origineFinancementSearchFilter = $origineFinancementSearchFilter;
        return $this;
    }

    /**
     * @param EcoleDoctoraleSearchFilter $ecoleDoctoraleSearchFilter
     * @return RapportActiviteSearchService
     */
    public function setEcoleDoctoraleSearchFilter(EcoleDoctoraleSearchFilter $ecoleDoctoraleSearchFilter): RapportActiviteSearchService
    {
        $this->ecoleDoctoraleSearchFilter = $ecoleDoctoraleSearchFilter;
        return $this;
    }

    /**
     * @param UniteRechercheSearchFilter $uniteRechercheSearchFilter
     * @return RapportActiviteSearchService
     */
    public function setUniteRechercheSearchFilter(UniteRechercheSearchFilter $uniteRechercheSearchFilter): RapportActiviteSearchService
    {
        $this->uniteRechercheSearchFilter = $uniteRechercheSearchFilter;
        return $this;
    }

    /**
     * @param \RapportActivite\Search\AnneeRapportActiviteSearchFilter $anneeRapportActiviteSearchFilter
     * @return RapportActiviteSearchService
     */
    public function setAnneeRapportActiviteSearchFilter(AnneeRapportActiviteSearchFilter $anneeRapportActiviteSearchFilter): RapportActiviteSearchService
    {
        $this->anneeRapportActiviteSearchFilter = $anneeRapportActiviteSearchFilter;
        return $this;
    }
}