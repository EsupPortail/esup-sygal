<?php

namespace Application\Service\Rapport;

use Application\Entity\Db\TypeStructure;
use Application\Search\Filter\SearchFilter;
use Application\Search\Filter\SelectSearchFilter;
use Application\Search\Filter\TextSearchFilter;
use Application\Search\SearchService;
use Application\Search\Sorter\SearchSorter;
use Application\Service\Acteur\ActeurServiceAwareTrait;
use Application\Service\EcoleDoctorale\EcoleDoctoraleSearchFilter;
use Application\Service\Etablissement\EtablissementInscSearchFilter;
use Application\Service\Etablissement\EtablissementServiceAwareTrait;
use Application\Service\Financement\FinancementServiceAwareTrait;
use Application\Service\Financement\OrigineFinancementSearchFilter;
use Application\Service\Structure\StructureServiceAwareTrait;
use Application\Service\These\TheseRechercheService;
use Application\Service\These\TheseRechercheServiceAwareTrait;
use Application\Service\TheseAnneeUniv\TheseAnneeUnivServiceAwareTrait;
use Application\Service\UniteRecherche\UniteRechercheSearchFilter;
use Doctrine\ORM\QueryBuilder;

class RapportSearchService extends SearchService
{
    use FinancementServiceAwareTrait;
    use TheseRechercheServiceAwareTrait;
    use TheseAnneeUnivServiceAwareTrait;
    use StructureServiceAwareTrait;
    use EtablissementServiceAwareTrait;
    use ActeurServiceAwareTrait;
    use RapportServiceAwareTrait;
    use RapportServiceAwareTrait;

    const NAME_financement = OrigineFinancementSearchFilter::NAME;
    const NAME_etab_inscription = EtablissementInscSearchFilter::NAME;
    const NAME_ecole_doctorale = EcoleDoctoraleSearchFilter::NAME;
    const NAME_unite_recherche = UniteRechercheSearchFilter::NAME;
    const NAME_nom_doctorant = 'nom_doctorant';
    const NAME_nom_directeur = 'nom_directeur';
    const NAME_annee_rapport_annuel = AnneeRapportActiviteSearchFilter::NAME;

    /**
     * @var EtablissementInscSearchFilter
     */
    private $etablissementInscSearchFilter;
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
     * @inheritDoc
     */
    public function init()
    {
        $etablissementInscrFilter = $this->getEtablissementInscSearchFilter();
        $origineFinancementFilter = $this->getOrigineFinancementSearchFilter();
        $uniteRechercheFilter = $this->getUniteRechercheSearchFilter();
        $ecoleDoctoraleFilter = $this->getEcoleDoctoraleSearchFilter();
        $anneeRapportActiviteInscrFilter = $this->getAnneeRapportActiviteSearchFilter();

        $this->addFilters([
            $etablissementInscrFilter,
            $origineFinancementFilter,
            $ecoleDoctoraleFilter,
            $uniteRechercheFilter,
            $anneeRapportActiviteInscrFilter,
            $this->createFilterNomDoctorant(),
            $this->createFilterNomDirecteur(),
        ]);
        $this->addSorters([
            $etablissementInscrFilter->createSorter()->setIsDefault(),
            $ecoleDoctoraleFilter->createSorter(),
            $uniteRechercheFilter->createSorter(),
            $anneeRapportActiviteInscrFilter->createSorter(),
            $this->createSorterNomPrenomDoctorant(),
        ]);
    }

    /**
     * @return EtablissementInscSearchFilter
     */
    public function getEtablissementInscSearchFilter(): EtablissementInscSearchFilter
    {
        return $this->etablissementInscSearchFilter;
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
     * @param EtablissementInscSearchFilter $etablissementInscSearchFilter
     * @return RapportSearchService
     */
    public function setEtablissementInscSearchFilter(EtablissementInscSearchFilter $etablissementInscSearchFilter): RapportSearchService
    {
        $this->etablissementInscSearchFilter = $etablissementInscSearchFilter;
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

    /**
     * @inheritDoc
     */
    public function fetchValueOptionsForSelectFilter(SelectSearchFilter $filter): array
    {
        switch ($filter->getName()) {
            case self::NAME_financement:
                return $this->fetchOriginesFinancementsValueOptions($filter);
            case self::NAME_etab_inscription:
                return $this->fetchEtablissementsValueOptions($filter);
            case self::NAME_ecole_doctorale:
                return $this->fetchEcolesDoctoralesValueOptions($filter);
            case self::NAME_unite_recherche:
                return $this->fetchUnitesRecherchesValueOptions($filter);
            case self::NAME_annee_rapport_annuel:
                return $this->fetchAnneesRapportActiviteValueOptions($filter);
            default:
                throw new \InvalidArgumentException("Cas imprévu");
        }
    }

    /**
     * @param OrigineFinancementSearchFilter $filter
     * @return array
     */
    private function fetchOriginesFinancementsValueOptions(SelectSearchFilter $filter): array
    {
        $origines = $this->financementService->getOriginesFinancements("libelleLong", true);
        $origines = array_filter($origines);

        return $filter->createValueOptionsFromData($origines);
    }

    /**
     * @param EtablissementInscSearchFilter $filter
     * @return array
     */
    private function fetchEtablissementsValueOptions(SelectSearchFilter $filter): array
    {
        $etablissements = $this->getEtablissementService()->getRepository()->findAllEtablissementsInscriptions(true);

        return $filter->createValueOptionsFromData($etablissements);
    }

    /**
     * @param EcoleDoctoraleSearchFilter $filter
     * @return array
     */
    private function fetchEcolesDoctoralesValueOptions(SelectSearchFilter $filter): array
    {
        // si des valeurs ont déjà été fournies, pas besoin de fetch.
        $eds = $this->ecoleDoctoraleSearchFilter->getData();
        if ($eds === null) {
            $eds = $this->structureService->getAllStructuresAffichablesByType(TypeStructure::CODE_ECOLE_DOCTORALE, 'libelle', true, true);
        }

        return $filter->createValueOptionsFromData($eds);
    }

    /**
     * @param UniteRechercheSearchFilter $filter
     * @return array
     */
    private function fetchUnitesRecherchesValueOptions(SelectSearchFilter $filter): array
    {
        $urs = $this->structureService->getAllStructuresAffichablesByType(TypeStructure::CODE_UNITE_RECHERCHE, 'libelle');

        return $filter->createValueOptionsFromData($urs);
    }

    /**
     * @param AnneeRapportActiviteSearchFilter $filter
     * @return array
     */
    private function fetchAnneesRapportActiviteValueOptions(SelectSearchFilter $filter): array
    {
        $annees = $this->rapportService->findDistinctAnnees();

        return $filter->createValueOptionsFromData($annees);
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
            default:
                throw new \InvalidArgumentException("Cas imprévu");
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
                throw new \InvalidArgumentException("Cas imprévu");
        }
    }

    private function applyNomDoctorantFilterToQueryBuilder(SearchFilter $filter, QueryBuilder $qb, $alias = 'these')
    {
        $this->applyTextFilterToQueryBuilder($filter, $qb, [TheseRechercheService::CRITERIA_nom_doctorant], $alias);
    }

    private function applyNomDirecteurFilterToQueryBuilder(SearchFilter $filter, QueryBuilder $qb, $alias = 'these')
    {
        $this->applyTextFilterToQueryBuilder($filter, $qb, [TheseRechercheService::CRITERIA_nom_directeur], $alias);
    }

    private function applyTextFilterToQueryBuilder(SearchFilter $filter, QueryBuilder $qb, array $criteria, $alias = 'these')
    {
        $filterValue = $filter->getValue();

        if ($filterValue !== null && strlen($filterValue) > 1) {
            $results = $this->theseRechercheService->rechercherThese($filterValue, $criteria);
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
        $qb = $this->rapportService->getRepository()->createQueryBuilder('ra');
        $qb
            ->addSelect('these, f, d, i')
            ->join('ra.these', 'these')
            ->join('these.doctorant', 'd')
            ->join('d.individu', 'i')
            ->join('ra.fichier', 'f');

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

        $filter->setApplyToQueryBuilderCallable([$this, 'applyFilterToQueryBuilder']);

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

        $filter->setApplyToQueryBuilderCallable([$this, 'applyFilterToQueryBuilder']);

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

        $sorter->setApplyToQueryBuilderCallable([$this, 'applySorterToQueryBuilder']);

        return $sorter;
    }
}