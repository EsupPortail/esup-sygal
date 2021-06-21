<?php

namespace Application\Service\Rapport;

use Application\Entity\Db\Interfaces\TypeRapportAwareTrait;
use Application\Entity\Db\Interfaces\TypeValidationAwareTrait;
use Application\Entity\Db\TypeStructure;
use Application\Filter\AnneeUnivFormatter;
use Application\Search\EcoleDoctorale\EcoleDoctoraleSearchFilter;
use Application\Search\Etablissement\EtablissementSearchFilter;
use Application\Search\Filter\CheckboxSearchFilter;
use Application\Search\Filter\SearchFilter;
use Application\Search\Filter\SelectSearchFilter;
use Application\Search\Filter\TextSearchFilter;
use Application\Search\Financement\OrigineFinancementSearchFilter;
use Application\Search\Rapport\AnneeRapportActiviteSearchFilter;
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

class RapportSearchService extends SearchService
{
    use FinancementServiceAwareTrait;
    use TheseSearchServiceAwareTrait;
    use TheseAnneeUnivServiceAwareTrait;
    use StructureServiceAwareTrait;
    use EtablissementServiceAwareTrait;
    use ActeurServiceAwareTrait;
    use RapportServiceAwareTrait;
    use TypeRapportAwareTrait;
    use TypeValidationAwareTrait;

    const NAME_nom_doctorant = 'nom_doctorant';
    const NAME_nom_directeur = 'nom_directeur';
    const NAME_type = 'type';
    const NAME_validation = 'est_valide';

    /**
     * @var EtablissementSearchFilter
     */
    private $etablissementTheseSearchFilter;
    /**
     * @var OrigineFinancementSearchFilter
     */
    private $origineFinancementSearchFilter;
    /**
     * @var EcoleDoctoraleSearchFilter
     */
    private $ecoleDoctoraleSearchFilter;
    /**
     * @var UniteRechercheSearchFilter
     */
    private $uniteRechercheSearchFilter;
    /**
     * @var AnneeRapportActiviteSearchFilter
     */
    private $anneeRapportActiviteSearchFilter;
    /**
     * @var SelectSearchFilter
     */
    private $validationSearchFilter;

    /**
     * @inheritDoc
     */
    public function init()
    {
        $etablissementInscrFilter = $this->getEtablissementTheseSearchFilter()
            ->setDataProvider(function() {
                return $this->fetchEtablissements();
            });
        $origineFinancementFilter = $this->getOrigineFinancementSearchFilter()
            ->setDataProvider(function() {
                return $this->fetchOriginesFinancements();
            });
        $uniteRechercheFilter = $this->getUniteRechercheSearchFilter()
            ->setDataProvider(function() {
                return $this->fetchUnitesRecherches();
            });
        $ecoleDoctoraleFilter = $this->getEcoleDoctoraleSearchFilter()
            ->setDataProvider(function() {
                return $this->fetchEcolesDoctorales();
            });
        $anneeRapportActiviteInscrFilter = $this->getAnneeRapportActiviteSearchFilter()
            ->setDataProvider(function() {
                return $this->fetchAnneesRapportActivite();
            });
        $validationSearchFilter = $this->getValidationSearchFilter();

        $this->addFilters([
            $etablissementInscrFilter,
            $origineFinancementFilter,
            $ecoleDoctoraleFilter,
            $uniteRechercheFilter,
            $anneeRapportActiviteInscrFilter,
            $this->createFilterNomDoctorant(),
            $this->createFilterNomDirecteur(),
            $validationSearchFilter,
        ]);
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

        return array_filter($values);
    }

    private function fetchAnneesRapportActivite(): array
    {
        $annees = $this->rapportService->findDistinctAnnees($this->typeRapport);
        $annees = array_reverse(array_filter($annees));
        $annees = array_combine($annees, $annees);

        return $annees;
    }

    /**
     * @param array $annees
     * @return array
     */
    static public function formatToAnneesUniv(array $annees): array
    {
        // formattage du label, ex: "2018" devient "2018/2019"
        $f = new AnneeUnivFormatter();

        return array_map(function($annee) use ($f) {
            if (! is_numeric($annee)) {
                return $annee; // déjà formattée
            }
            return $f->filter($annee);
        }, $annees);
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

    public function applyValidationFilterToQueryBuilder(SearchFilter $filter, QueryBuilder $qb, $alias = 'ra')
    {
        $filterValue = $filter->getValue();
        if ($filterValue === 'oui') {
            $qb
                ->join("$alias.rapportValidations", 'v_filter', Join::WITH, 'pasHistorise(v_filter) = 1');
        } elseif ($filterValue === 'non') {
            $qb
                ->leftJoin("$alias.rapportValidations", 'v_filter', Join::WITH, 'pasHistorise(v_filter) = 1')
                ->andWhere('v_filter is null');
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
     * @inheritDoc
     */
    public function createQueryBuilder(): QueryBuilder
    {
        $qb = $this->rapportService->getRepository()->createQueryBuilder('ra')
            ->addSelect('tr, these, f, d, i')
            ->join('ra.typeRapport', 'tr')
            ->join('ra.these', 'these')
            ->join('these.doctorant', 'd')
            ->join('d.individu', 'i')
            ->join('ra.fichier', 'f')
            ->andWhereNotHistorise();

        if ($this->typeRapport !== null) {
            $qb->andWhere('tr = :type')->setParameter('type', $this->typeRapport);
        }

        return $qb;
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
                        ->leftJoin("$alias.rapportValidations", 'v_sort', Join::WITH, 'pasHistorise(v) = 1')
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
     * @return RapportSearchService
     */
    public function setEtablissementTheseSearchFilter(EtablissementSearchFilter $etablissementTheseSearchFilter): RapportSearchService
    {
        $this->etablissementTheseSearchFilter = $etablissementTheseSearchFilter;
        return $this;
    }

    /**
     * @param OrigineFinancementSearchFilter $origineFinancementSearchFilter
     * @return RapportSearchService
     */
    public function setOrigineFinancementSearchFilter(OrigineFinancementSearchFilter $origineFinancementSearchFilter): RapportSearchService
    {
        $this->origineFinancementSearchFilter = $origineFinancementSearchFilter;
        return $this;
    }

    /**
     * @param EcoleDoctoraleSearchFilter $ecoleDoctoraleSearchFilter
     * @return RapportSearchService
     */
    public function setEcoleDoctoraleSearchFilter(EcoleDoctoraleSearchFilter $ecoleDoctoraleSearchFilter): RapportSearchService
    {
        $this->ecoleDoctoraleSearchFilter = $ecoleDoctoraleSearchFilter;
        return $this;
    }

    /**
     * @param UniteRechercheSearchFilter $uniteRechercheSearchFilter
     * @return RapportSearchService
     */
    public function setUniteRechercheSearchFilter(UniteRechercheSearchFilter $uniteRechercheSearchFilter): RapportSearchService
    {
        $this->uniteRechercheSearchFilter = $uniteRechercheSearchFilter;
        return $this;
    }

    /**
     * @param AnneeRapportActiviteSearchFilter $anneeRapportActiviteSearchFilter
     * @return RapportSearchService
     */
    public function setAnneeRapportActiviteSearchFilter(AnneeRapportActiviteSearchFilter $anneeRapportActiviteSearchFilter): RapportSearchService
    {
        $this->anneeRapportActiviteSearchFilter = $anneeRapportActiviteSearchFilter;
        return $this;
    }
}