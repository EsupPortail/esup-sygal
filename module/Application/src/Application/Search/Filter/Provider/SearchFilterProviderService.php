<?php

namespace Application\Search\Filter\Provider;

use Application\Entity\Db\TypeStructure;
use Application\Filter\AnneeUnivFormatter;
use Application\Search\Filter\SearchFilter;
use Application\Search\Filter\SelectSearchFilter;
use Application\Search\Filter\TextSearchFilter;
use Application\Search\Sorter\SearchSorter;
use Application\Service\Acteur\ActeurServiceAwareTrait;
use Application\Service\Etablissement\EtablissementServiceAwareTrait;
use Application\Service\Financement\FinancementServiceAwareTrait;
use Application\Service\RapportAnnuel\RapportAnnuelServiceAwareTrait;
use Application\Service\Structure\StructureServiceAwareTrait;
use Application\Service\These\TheseRechercheService;
use Application\Service\These\TheseRechercheServiceAwareTrait;
use Application\Service\TheseAnneeUniv\TheseAnneeUnivServiceAwareTrait;
use Doctrine\ORM\QueryBuilder;

class SearchFilterProviderService
{
    use FinancementServiceAwareTrait;
    use TheseRechercheServiceAwareTrait;
    use TheseAnneeUnivServiceAwareTrait;
    use StructureServiceAwareTrait;
    use EtablissementServiceAwareTrait;
    use ActeurServiceAwareTrait;
    use RapportAnnuelServiceAwareTrait;

    const NAME_financement = 'financement';
    const NAME_annee_1ere_inscription = 'annee_1ere_inscription';
    const NAME_etab_inscription = 'etab_inscription';
    const NAME_ecole_doctorale = 'ecole_doctorale';
    const NAME_unite_recherche = 'unite_recherche';
    const NAME_nom_doctorant = 'nom_doctorant';
    const NAME_nom_directeur = 'nom_directeur';
    const NAME_annee_rapport_annuel = 'annee_rapport';

    /**
     * @return SelectSearchFilter
     */
    public function createFilterOrigineFinancement()
    {
        $filter = new SelectSearchFilter(
            "Origine<br>financement",
            self::NAME_financement,
            [],
            ['liveSearch' => true]
        );

        $filter->setApplyToQueryBuilderCallable([$this, 'applyFilterToQueryBuilder']);

        return $filter;
    }

    /**
     * @return SelectSearchFilter
     */
    public function createFilterAnneeRapportAnnuelInscr()
    {
        $filter = new SelectSearchFilter(
            "Année du<br>rapport",
            self::NAME_annee_rapport_annuel,
            []
        );

        $filter->setApplyToQueryBuilderCallable([$this, 'applyFilterToQueryBuilder']);

        return $filter;
    }

    /**
     * @return SelectSearchFilter
     */
    public function createFilterAnneeUniv1ereInscr()
    {
        $filter = new SelectSearchFilter(
            "Année univ.<br>1ère inscr.",
            self::NAME_annee_1ere_inscription,
            []
        );

        $filter->setApplyToQueryBuilderCallable([$this, 'applyFilterToQueryBuilder']);

        return $filter;
    }

    /**
     * @return SelectSearchFilter
     */
    public function createFilterEtablissementInscr()
    {
        $filter = new SelectSearchFilter(
            "Établissement<br>d'inscr.",
            self::NAME_etab_inscription,
            []
        );

        $filter->setApplyToQueryBuilderCallable([$this, 'applyFilterToQueryBuilder']);

        return $filter;
    }

    /**
     * @return SelectSearchFilter
     */
    public function createFilterEcoleDoctorale()
    {
        $filter = new SelectSearchFilter(
            "École doctorale",
            self::NAME_ecole_doctorale,
            [],
            ['liveSearch' => true]
        );

        $filter->setApplyToQueryBuilderCallable([$this, 'applyFilterToQueryBuilder']);

        return $filter;
    }

    /**
     * @return SelectSearchFilter
     */
    public function createFilterUniteRecherche()
    {
        $filter = new SelectSearchFilter(
            "Unité de recherche",
            self::NAME_unite_recherche,
            [],
            ['liveSearch' => true]
        );

        $filter->setApplyToQueryBuilderCallable([$this, 'applyFilterToQueryBuilder']);

        return $filter;
    }

    /**
     * @return TextSearchFilter
     */
    public function createFilterNomDoctorant()
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
    public function createFilterNomDirecteur()
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
    public function createSorterEtablissementInscr()
    {
        $sorter = new SearchSorter(
            "Établissement<br>d'inscr.",
            self::NAME_etab_inscription
        );

        $sorter->setApplyToQueryBuilderCallable([$this, 'applySorterToQueryBuilder']);

        return $sorter;
    }

    /**
     * @return SearchSorter
     */
    public function createSorterEcoleDoctorale()
    {
        $sorter = new SearchSorter(
            "Ecole doctorale",
            self::NAME_ecole_doctorale
        );

        $sorter->setApplyToQueryBuilderCallable([$this, 'applySorterToQueryBuilder']);

        return $sorter;
    }

    /**
     * @return SearchSorter
     */
    public function createSorterUniteRecherche()
    {
        $sorter = new SearchSorter(
            "Unité recherche",
            self::NAME_unite_recherche
        );

        $sorter->setApplyToQueryBuilderCallable([$this, 'applySorterToQueryBuilder']);

        return $sorter;
    }

    /**
     * @return SearchSorter
     */
    public function createSorterAnneeRapportAnnuel()
    {
        $sorter = new SearchSorter(
            "Annee rapport",
            self::NAME_annee_rapport_annuel
        );

        $sorter->setApplyToQueryBuilderCallable([$this, 'applySorterToQueryBuilder']);

        return $sorter;
    }

    /**
     * @return SearchSorter
     */
    public function createSorterNomPrenomDoctorant()
    {
        $sorter = new SearchSorter(
            "Doctorant",
            self::NAME_nom_doctorant
        );

        $sorter->setApplyToQueryBuilderCallable([$this, 'applySorterToQueryBuilder']);

        return $sorter;
    }

    /**
     * @param SelectSearchFilter $filter
     * @return array
     */
    public function fetchValueOptionsForSelectFilter(SelectSearchFilter $filter)
    {
        switch ($filter->getName()) {
            case self::NAME_financement:
                return $this->fetchOriginesFinancementsValueOptions();
                break;
            case self::NAME_annee_1ere_inscription:
                return $this->fetchAnneesUniv1ereInscriptionValueOptions();
                break;
            case self::NAME_etab_inscription:
                return $this->fetchEtablissementsValueOptions();
                break;
            case self::NAME_ecole_doctorale:
                return $this->fetchEcolesDoctoralesValueOptions();
                break;
            case self::NAME_unite_recherche:
                return $this->fetchUnitesRecherchesValueOptions();
                break;
            case self::NAME_annee_rapport_annuel:
                return $this->fetchAnneesRapportAnnuelValueOptions();
                break;
        }

        return [];
    }

    /**
     * @param SearchFilter $filter
     * @param QueryBuilder $qb
     */
    public function applyFilterToQueryBuilder(SearchFilter $filter, QueryBuilder $qb)
    {
        // todo: permettre la spécification de l'alias Doctrine à utiliser via $filter->getAlias() ?

        switch ($filter->getName()) {
            case self::NAME_financement:
                $this->applyOrigineFinancementFilterToQueryBuilder($filter, $qb);
                break;
            case self::NAME_annee_1ere_inscription:
                $this->applyAnneesUniv1ereInscriptionFilterToQueryBuilder($filter, $qb);
                break;
            case self::NAME_etab_inscription:
                $this->applyEtablissementInscrFilterToQueryBuilder($filter, $qb);
                break;
            case self::NAME_ecole_doctorale:
                $this->applyEcoleDoctoraleFilterToQueryBuilder($filter, $qb);
                break;
            case self::NAME_unite_recherche:
                $this->applyUniteRechercheFilterToQueryBuilder($filter, $qb);
                break;
            case self::NAME_nom_doctorant:
                $this->applyNomDoctorantFilterToQueryBuilder($filter, $qb);
                break;
            case self::NAME_nom_directeur:
                $this->applyNomDirecteurFilterToQueryBuilder($filter, $qb);
                break;
            case self::NAME_annee_rapport_annuel:
                $this->applyAnneeRapportAnnuelFilterToQueryBuilder($filter, $qb);
                break;
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
            case self::NAME_financement:
                $this->applyOrigineFinancementSorterToQueryBuilder($sorter, $qb);
                break;
            case self::NAME_etab_inscription:
                $this->applyEtablissementInscrSorterToQueryBuilder($sorter, $qb);
                break;
            case self::NAME_ecole_doctorale:
                $this->applyEcoleDoctoraleSorterToQueryBuilder($sorter, $qb);
                break;
            case self::NAME_unite_recherche:
                $this->applyUniteRechercheSorterToQueryBuilder($sorter, $qb);
                break;
            case self::NAME_nom_doctorant:
                $this->applyNomDoctorantSorterToQueryBuilder($sorter, $qb);
                break;
            case self::NAME_annee_rapport_annuel:
                $this->applyAnneeRapportAnnuelSorterToQueryBuilder($sorter, $qb);
                break;
        }
    }

    private function fetchOriginesFinancementsValueOptions()
    {
        $origines = $this->financementService->getOriginesFinancements("libelleLong", true);
        $origines = array_filter($origines);

        $options = [];
        $options[] = $this->valueOptionEmpty();
        $options[] = $this->valueOptionUnknown(); // option spéciale pour valeur === null
        foreach ($origines as $origine) {
            $options[] = $this->valueOptionEntity($origine, 'getLibelleLong');
        }

        return $options;
    }

    private function fetchAnneesUniv1ereInscriptionValueOptions()
    {
        $annees = $this->theseAnneeUnivService->getRepository()->fetchDistinctAnneesUniv1ereInscription(null, true);
        $annees = array_reverse(array_filter($annees));

        return $this->formatAnneesValueOptions($annees);
    }

    private function fetchEtablissementsValueOptions()
    {
        $etablissements = $this->getEtablissementService()->getRepository()->findAllEtablissementsInscriptions(true);

        $options = [];
        $options[] = $this->valueOptionEmpty();
        foreach ($etablissements as $etablissement) {
            $options[] = $this->valueOptionEntity($etablissement);
        }

        return $options;
    }

    private function fetchEcolesDoctoralesValueOptions()
    {
        $eds = $this->structureService->getAllStructuresAffichablesByType(TypeStructure::CODE_ECOLE_DOCTORALE, 'libelle', true, true);

        $options = [];
        $options[] = $this->valueOptionEmpty("Toutes");
        foreach ($eds as $ed) {
            $options[] = $this->valueOptionEntity($ed);
        }

        return $options;
    }

    private function fetchUnitesRecherchesValueOptions()
    {
        $urs = $this->structureService->getAllStructuresAffichablesByType(TypeStructure::CODE_UNITE_RECHERCHE, 'libelle');

        $options = [];
        $options[] = $this->valueOptionEmpty("Toutes");
        foreach ($urs as $ur) {
            $options[] = $this->valueOptionEntity($ur);
        }

        return $options;
    }

    private function fetchAnneesRapportAnnuelValueOptions()
    {
        $annees = $this->rapportAnnuelService->findDistinctAnnees();

        return $this->formatAnneesValueOptions($annees);
    }

    /**
     * @param array $annees
     * @return array
     */
    private function formatAnneesValueOptions(array $annees)
    {
        $options = [];
        $options[] = $this->valueOptionEmpty();
        foreach ($annees as $annee) {
            $options[] = $this->valueOptionScalar($annee);
        }

        // formattage du label, ex: "2018" devient "2018/2019"
        $f = new AnneeUnivFormatter();
        $options = array_map(function($value) use ($f) {
            if (! is_numeric($value['label'])) {
                return $value;
            }
            $value['label'] = $f->filter($value['label']);
            return $value;
        }, $options);

        return $options;
    }

    private function applyOrigineFinancementFilterToQueryBuilder(SearchFilter $filter, QueryBuilder $qb, $alias = 'these')
    {
        $filterValue = $filter->getValue();

        $qb
            ->leftJoin("$alias.financements", 'fin')
            ->leftJoin('fin.origineFinancement', 'orig')
        ;
        if ($filterValue === 'NULL') {
            $qb
                ->andWhere('orig.id IS NULL');
        } else {
            $qb
                ->andWhere('orig.id = :origine')
                ->setParameter('origine', $filterValue);
        }
    }

    private function applyAnneesUniv1ereInscriptionFilterToQueryBuilder(SearchFilter $filter, QueryBuilder $qb, $alias = 'these')
    {
        $filterValue = $filter->getValue();

        if ($filterValue === 'NULL') {
            $qb
                ->leftJoin("$alias.anneesUniv1ereInscription", 'aui1')
                ->andWhere('aui1.anneeUniv IS NULL');
        } else {
            $qb
                ->join("$alias.anneesUniv1ereInscription", 'aui1')
                ->andWhere('aui1.anneeUniv = :anneeUniv1ereInscription')
                ->setParameter('anneeUniv1ereInscription', $filterValue);
        }
    }

    private function applyEtablissementInscrFilterToQueryBuilder(SearchFilter $filter, QueryBuilder $qb, $alias = 'these')
    {
        $filterValue = $filter->getValue();

        $qb
            ->andWhere("$alias.etablissement = :etab")
            ->setParameter('etab', $filterValue);
    }

    private function applyEcoleDoctoraleFilterToQueryBuilder(SearchFilter $filter, QueryBuilder $qb, $alias = 'these')
    {
        $filterValue = $filter->getValue();

        if ($filterValue === 'NULL') {
            $qb
                ->andWhere("$alias.ecoleDoctorale IS NULL");
        } else {
            $qb
                ->andWhere("$alias.ecoleDoctorale = :ed")
                ->setParameter('ed', $filterValue);
        }
    }

    private function applyUniteRechercheFilterToQueryBuilder(SearchFilter $filter, QueryBuilder $qb, $alias = 'these')
    {
        $filterValue = $filter->getValue();

        if ($filterValue === 'NULL') {
            $qb
                ->andWhere("$alias.uniteRecherche IS NULL");
        } else {
            $qb
                ->andWhere("$alias.uniteRecherche = :ur")
                ->setParameter('ur', $filterValue);
        }
    }

    private function applyAnneeRapportAnnuelFilterToQueryBuilder(SearchFilter $filter, QueryBuilder $qb, $alias = 'ra')
    {
        $filterValue = $filter->getValue();

        $qb
            ->andWhere("$alias.anneeUniv = :anneeRapportAnnuel")
            ->setParameter('anneeRapportAnnuel', $filterValue);
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

    public function applyEtablissementInscrSorterToQueryBuilder(SearchSorter $sorter, QueryBuilder $qb, $alias = 'these')
    {
        $direction = $sorter->getDirection();

        $qb
            ->join("$alias.etablissement", 'e_sort')
            ->join('e_sort.structure', 's_sort')
            ->addOrderBy('s_sort.code', $direction);
    }

    public function applyOrigineFinancementSorterToQueryBuilder(SearchSorter $sorter, QueryBuilder $qb, $alias = 'these')
    {
        $direction = $sorter->getDirection();

        $qb
            ->leftJoin("$alias.financements", 'fin_sort')
            ->leftJoin('fin_sort.origineFinancement', 'orig_sort')
            ->addOrderBy('orig_sort.libelleLong', $direction);
    }

    public function applyEcoleDoctoraleSorterToQueryBuilder(SearchSorter $sorter, QueryBuilder $qb, $alias = 'these')
    {
        $direction = $sorter->getDirection();

        $qb
            ->leftJoin("$alias.ecoleDoctorale", 'ed_sort')
            ->leftJoin("ed_sort.structure", 'ed_s_sort')
            ->addOrderBy('ed_s_sort.code', $direction);
    }

    public function applyUniteRechercheSorterToQueryBuilder(SearchSorter $sorter, QueryBuilder $qb, $alias = 'these')
    {
        $direction = $sorter->getDirection();

        $qb
            ->leftJoin("$alias.uniteRecherche", 'ur_sort')
            ->leftJoin("ur_sort.structure", 'ur_s_sort')
            ->addOrderBy('ur_s_sort.code', $direction);
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

    public function applyAnneeRapportAnnuelSorterToQueryBuilder(SearchSorter $sorter, QueryBuilder $qb, $alias = 'ra')
    {
        $direction = $sorter->getDirection();

        $qb->addOrderBy("$alias.anneeUniv", $direction);
    }


    /**
     * @param string $label
     * @return array
     */
    protected function valueOptionUnknown($label = "(Inconnu.e)")
    {
        return ['value' => 'NULL', 'label' => $label];
    }

    /**
     * @param string $label
     * @return array
     */
    protected function valueOptionEmpty($label = "(Peu importe)")
    {
        return ['value' => '', 'label' => $label];
    }

    /**
     * @param object $entity
     * @param string $getterNameForLabel
     * @param string $getterNameForValue
     * @return array
     */
    protected function valueOptionEntity($entity, $getterNameForLabel = '__toString', $getterNameForValue = 'getId')
    {
        return ['value' => (string) $entity->$getterNameForValue(), 'label' => $entity->$getterNameForLabel()];
    }

    /**
     * @param mixed $scalar
     * @return array
     */
    protected function valueOptionScalar($scalar)
    {
        return ['value' => $scalar, 'label' => $scalar];
    }
}