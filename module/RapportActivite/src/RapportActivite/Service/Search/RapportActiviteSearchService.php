<?php

namespace RapportActivite\Service\Search;

use Application\Entity\Db\Interfaces\TypeValidationAwareTrait;
use Application\Entity\Db\TypeValidation;
use Application\QueryBuilder\DefaultQueryBuilder;
use Application\Search\Filter\SearchFilter;
use Application\Search\Filter\SelectSearchFilter;
use Application\Search\Filter\TextSearchFilter;
use Application\Search\Financement\OrigineFinancementSearchFilter;
use Application\Search\SearchService;
use Application\Search\Sorter\SearchSorter;
use Application\Service\Financement\FinancementServiceAwareTrait;
use Application\Service\Validation\ValidationServiceAwareTrait;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use InvalidArgumentException;
use RapportActivite\Entity\Db\RapportActiviteAvis;
use RapportActivite\Entity\Db\RapportActiviteValidation;
use RapportActivite\Rule\Operation\RapportActiviteOperationRuleAwareTrait;
use RapportActivite\Search\AnneeRapportActiviteSearchFilter;
use RapportActivite\Service\Operation\RapportActiviteOperationServiceAwareTrait;
use RapportActivite\Service\RapportActiviteServiceAwareTrait;
use Structure\Entity\Db\TypeStructure;
use Structure\Search\EcoleDoctorale\EcoleDoctoraleSearchFilter;
use Structure\Search\Etablissement\EtablissementSearchFilter;
use Structure\Search\UniteRecherche\UniteRechercheSearchFilter;
use Structure\Service\Etablissement\EtablissementServiceAwareTrait;
use Structure\Service\Structure\StructureServiceAwareTrait;
use These\Search\These\TheseTextSearchFilter;
use These\Service\Acteur\ActeurServiceAwareTrait;
use These\Service\These\TheseSearchServiceAwareTrait;
use These\Service\TheseAnneeUniv\TheseAnneeUnivServiceAwareTrait;
use UnicaenAvis\Entity\Db\AvisType;
use Webmozart\Assert\Assert;

class RapportActiviteSearchService extends SearchService
{
    use FinancementServiceAwareTrait;
    use TheseSearchServiceAwareTrait;
    use TheseAnneeUnivServiceAwareTrait;
    use StructureServiceAwareTrait;
    use EtablissementServiceAwareTrait;
    use ActeurServiceAwareTrait;
    use RapportActiviteServiceAwareTrait;
    use RapportActiviteOperationRuleAwareTrait;
    use RapportActiviteOperationServiceAwareTrait;
    use ValidationServiceAwareTrait;
    use TypeValidationAwareTrait;

    const NAME_nom_doctorant = 'nom_doctorant';
    const NAME_nom_directeur = 'nom_directeur';
    const NAME_type = 'type';
    const NAME_avis_attendu = 'avis_attendu';
    const NAME_avis_fourni = 'avis_fourni';
    const NAME_validation = 'est_valide';
    const NAME_est_dematerialise = 'est_dematerialise';

    private ?EtablissementSearchFilter $etablissementTheseSearchFilter = null;
    private ?OrigineFinancementSearchFilter $origineFinancementSearchFilter = null;
    private ?EcoleDoctoraleSearchFilter $ecoleDoctoraleSearchFilter = null;
    private ?UniteRechercheSearchFilter $uniteRechercheSearchFilter = null;
    private ?AnneeRapportActiviteSearchFilter $anneeRapportActiviteSearchFilter = null;
    private ?SelectSearchFilter $validationSearchFilter = null;
    private ?SelectSearchFilter $finalSearchFilter = null;
    private ?SelectSearchFilter $operationRealiseeSearchFilter = null;
    private ?SelectSearchFilter $operationAttendueSearchFilter = null;
    private ?SelectSearchFilter $dematerialiseSearchFilter = null;

    /**
     * @inheritDoc
     */
    public function init()
    {
        $etablissementInscrFilter = $this->getEtablissementTheseSearchFilter()
            ->setQueryBuilderApplier(function (SelectSearchFilter $filter, DefaultQueryBuilder $qb) {
                $qb
                    ->andWhere('etab.sourceCode = :sourceCodeEtab OR etab_structureSubstituante.sourceCode = :sourceCodeEtab')
                    ->setParameter('sourceCodeEtab', $filter->getValue());
            });
        $ecoleDoctoraleFilter = $this->getEcoleDoctoraleSearchFilter()
            ->setQueryBuilderApplier(function (SelectSearchFilter $filter, DefaultQueryBuilder $qb) {
                $qb
                    ->andWhere('ed.sourceCode = :sourceCodeED OR ed_structureSubstituante.sourceCode = :sourceCodeED')
                    ->setParameter('sourceCodeED', $filter->getValue());
            });
        $uniteRechercheFilter = $this->getUniteRechercheSearchFilter()
            ->setQueryBuilderApplier(function (SelectSearchFilter $filter, DefaultQueryBuilder $qb) {
                $qb
                    ->andWhere('ur.sourceCode = :sourceCodeUR OR ur_structureSubstituante.sourceCode = :sourceCodeUR')
                    ->setParameter('sourceCodeUR', $filter->getValue());
            });
        $origineFinancementFilter = $this->getOrigineFinancementSearchFilter();
        $anneeRapportActiviteInscrFilter = $this->getAnneeRapportActiviteSearchFilter();
        $avisAttenduSearchFilter = $this->getOperationAttendueSearchFilter();
        $finalSearchFilter = $this->getFinalSearchFilter();
        $dematerialiseSearchFilter = $this->getDematerialiseSearchFilter();

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
            $avisAttenduSearchFilter,
            $dematerialiseSearchFilter,
        ]));

        $this->addSorters([
            $this->createSorterEtablissement(),
            $this->createSorterEcoleDoctorale(),
            $this->createSorterUniteRecherche(),
            $this->createSorterAnneeRapportActivite(),
            $this->createSorterNomPrenomDoctorant()->setIsDefault(),
            $this->createSorterValidation(),
        ]);
        $this->addInvisibleSort('at.ordre');
    }

    /**
     * @inheritDoc
     */
    public function createQueryBuilder(): QueryBuilder
    {
        // ATTENTION ! Les relations suivantes doivent être sélectionnées lors du fetch des rapports :
        // 'rapportAvis->avis->avisType'.

        $qb = $this->rapportActiviteService->getRepository()->createQueryBuilder('ra')
            ->addSelect('these, etab, f, d, i, ed, ur, rav, raa, a, at')
            ->join('ra.these', 'these')
            ->join("these.etablissement", 'etab')
            ->join('these.doctorant', 'd')
            ->join('d.individu', 'i')
            ->leftJoin('ra.fichier', 'f')
            ->leftJoin("these.ecoleDoctorale", 'ed')
            ->leftJoin("these.uniteRecherche", 'ur')
            ->leftJoin('ra.rapportValidations', 'rav')
            ->leftJoin('ra.rapportAvis', 'raa')
            ->leftJoin('raa.avis', 'a')
            ->leftJoin('a.avisType', 'at')
            ->andWhereNotHistorise();

        $qb
            ->leftJoin('etab.structure', 'etab_structure')->addSelect('etab_structure')
            ->leftJoin('ed.structure', 'ed_structure')->addSelect('ed_structure')
            ->leftJoin('ur.structure', 'ur_structure')->addSelect('ur_structure')
            ->leftJoinStructureSubstituante('etab_structure', 'etab_structureSubstituante')
            ->leftJoinStructureSubstituante('ed_structure', 'ed_structureSubstituante')
            ->leftJoinStructureSubstituante('ur_structure', 'ur_structureSubstituante');

        return $qb;
    }

    private function fetchEtablissements(): array
    {
        // ETAB non substitués
        return $this->etablissementService->getRepository()->findAllEtablissementsInscriptions(true);
    }

    private function fetchEcolesDoctorales(): array
    {
        // ED non substituées
        return $this->structureService->findAllStructuresAffichablesByType(
            TypeStructure::CODE_ECOLE_DOCTORALE, 'sigle', true, true
        );
    }

    private function fetchUnitesRecherches(): array
    {
        // UR non substituées
        return $this->structureService->findAllStructuresAffichablesByType(
            TypeStructure::CODE_UNITE_RECHERCHE, 'code', false, true
        );
    }

    private function fetchOriginesFinancements(): array
    {
        $values = $this->getFinancementService()->findOriginesFinancements("libelleLong");

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

    public function applyOperationAttendueSearchFilterToQueryBuilder(SearchFilter $filter, QueryBuilder $qb, $alias = 'ra')
    {
        $filterValue = $filter->getValue();
        if (!$filterValue) {
            return;
        }

        $typesOperation = $this->rapportActiviteOperationRule->fetchTypesOperation();
        Assert::allIsInstanceOfAny($typesOperation, [TypeValidation::class, AvisType::class]);

        $i = 1;
        if ($filterValue === 'null') {
            // Aucune opération attendue <=> Toutes les opérations doivent être réalisées.
            foreach ($typesOperation as $type) {
                $this->addOperationDqlToQb($type, true, $qb, $i, $alias);
                $i++;
            }
            return;
        }

        $typeOperationFilterConfig = $this->rapportActiviteOperationRule->getConfigForTypeOperation($filterValue);
        $typeOperationFilter = $this->rapportActiviteOperationService->fetchTypeOperationFromConfig($typeOperationFilterConfig);
        Assert::isInstanceOfAny($typeOperationFilter, [TypeValidation::class, AvisType::class]);

        $typeFilterFound = false;
        foreach ($typesOperation as $typeOperation) {
            if (!$typeFilterFound && $typeOperation === $typeOperationFilter) {
                $typeFilterFound = true;
                // On s'occupera plus tard de l'opération correspondant au filtre spécifié.
                continue;
            }
            if (!$typeFilterFound) {
                // Les opérations précédant celle spécifiée dans le filtre doivent être réalisées.
                $this->addOperationDqlToQb($typeOperation, true, $qb, $i, $alias);
            } else {
                // Les opérations suivant celle spécifiée dans le filtre NE doivent PAS être réalisées.
                $this->addOperationDqlToQb($typeOperation, false, $qb, $i, $alias);
            }
            $i++;
        }
        // Gestion à part de l'opération correspondant au filtre spécifié, qui NE doit PAS être réalisée.
        $this->addOperationFilterDqlToQb($typeOperationFilter, $qb, $i, $alias);
    }

    private function addOperationDqlToQb(object $typeOperation, bool $exists, QueryBuilder $qb, int $i, string $alias)
    {
        if ($typeOperation instanceof TypeValidation) {
            $this->addValidationDqlToQb($typeOperation, $exists, $qb, $i, $alias);
        } elseif ($typeOperation instanceof AvisType) {
            $this->addAvisDqlToQb($typeOperation, $exists, $qb, $i, $alias);
        }
    }

    private function addOperationFilterDqlToQb(object $typeOperation, QueryBuilder $qb, int $i, string $alias)
    {
        if ($typeOperation instanceof TypeValidation) {
            $this->addValidationFilterDqlToQb($typeOperation, $qb, $i, $alias);
        } elseif ($typeOperation instanceof AvisType) {
            $this->addAvisFilterDqlToQb($typeOperation, $qb, $i, $alias);
        }
    }

    private string $validationDqlTemplate =
        'SELECT rapportValidation_attendue_%1$d.id ' .
        'FROM ' . RapportActiviteValidation::class . ' rapportValidation_attendue_%1$d ' .
        'JOIN rapportValidation_attendue_%1$d.typeValidation typeValidation_attendue_%1$d WITH typeValidation_attendue_%1$d.code = :code_typeValidation_attendue_%1$d ' .
        'WHERE rapportValidation_attendue_%1$d.histoDestruction is null AND rapportValidation_attendue_%1$d.rapport = ';

    private function addValidationDqlToQb(TypeValidation $typeValidation, bool $exists, QueryBuilder $qb, int $i, string $alias)
    {
        $dql = $this->validationDqlTemplate . $alias;

        $compl = '';
        if ($enabledAsDql = $this->getEnabledAsDqlComplementForTypeOperation($typeValidation, $alias)) {
            $compl = 'OR NOT (' . $enabledAsDql . ')';
        }

        $qb
            ->andWhere(sprintf('(%s EXISTS (%s) %s)', $exists ? '' : 'NOT', sprintf($dql, $i), $compl))
            ->setParameter("code_typeValidation_attendue_$i", $typeValidation->getCode());
    }

    private function addValidationFilterDqlToQb(TypeValidation $typeValidation, QueryBuilder $qb, int $i, string $alias)
    {
        $dql = $this->validationDqlTemplate . $alias;

        $compl = '';
        if ($enabledAsDql = $this->getEnabledAsDqlComplementForTypeOperation($typeValidation, $alias)) {
            // NB : c'est bien un AND ici
            $compl = 'AND (' . $enabledAsDql . ')';
        }

        $qb
            ->andWhere(sprintf('(NOT EXISTS (%s) %s)', sprintf($dql, $i), $compl))
            ->setParameter("code_typeValidation_attendue_$i", $typeValidation->getCode());
    }

    private string $avisDqlTemplate =
        'SELECT rapportAvis_attendu_%1$d.id ' .
        'FROM ' . RapportActiviteAvis::class . ' rapportAvis_attendu_%1$d ' .
        'JOIN rapportAvis_attendu_%1$d.avis avis_attendu_%1$d ' .
        'JOIN avis_attendu_%1$d.avisType avisType_attendu_%1$d WITH avisType_attendu_%1$d.code = :code_avisType_attendu_%1$d ' .
        'WHERE rapportAvis_attendu_%1$d.histoDestruction is null AND rapportAvis_attendu_%1$d.rapport = ';


    private function addAvisDqlToQb(AvisType $avisType, bool $exists, QueryBuilder $qb, int $i, string $alias)
    {
        $dql = $this->avisDqlTemplate . $alias;

        $compl = '';
        $enabledAsDql = $this->getEnabledAsDqlComplementForTypeOperation($avisType, $alias);
        if ($enabledAsDql) {
            $compl = 'OR NOT (' . $enabledAsDql . ')';
        }

        $qb
            ->andWhere(sprintf('(%s EXISTS (%s) %s)', $exists ? '' : 'NOT', sprintf($dql, $i), $compl))
            ->setParameter("code_avisType_attendu_$i", $avisType->getCode());
    }

    private function addAvisFilterDqlToQb(AvisType $avisType, QueryBuilder $qb, int $i, string $alias)
    {
        $dql = $this->avisDqlTemplate . $alias;

        $compl = '';
        $enabledAsDql = $this->getEnabledAsDqlComplementForTypeOperation($avisType, $alias);
        if ($enabledAsDql) {
            // NB : c'est bien un AND ici
            $compl = 'AND (' . $enabledAsDql . ')';
        }

        $qb
            ->andWhere(sprintf('(NOT EXISTS (%s) %s)', sprintf($dql, $i), $compl))
            ->setParameter("code_avisType_attendu_$i", $avisType->getCode());
    }

    /**
     * @param \Application\Entity\Db\TypeValidation|\UnicaenAvis\Entity\Db\AvisType|string $typeOperation
     */
    private function getEnabledAsDqlComplementForTypeOperation($typeOperation, string $rapportAlias): string
    {
        $typeOperationConfig = $this->rapportActiviteOperationRule->getConfigForTypeOperation($typeOperation);
        $enabledAsDqlCallable = $typeOperationConfig['enabled_as_dql'] ?? null;
        if (is_callable($enabledAsDqlCallable)) {
            return $enabledAsDqlCallable($rapportAlias);
        }

        return '';
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
            ->andWhere("$alias.estFinContrat = :final")
            ->setParameter('final', $filterValue === 'finthese');
    }

    public function applyDematerialiseFilterToQueryBuilder(SearchFilter $filter, QueryBuilder $qb, $alias = 'ra')
    {
        $filterValue = $filter->getValue();
        $qb->andWhere($filterValue === 'oui' ? "$alias.fichier is null" : "$alias.fichier is not null");
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
    public function createSorterEtablissement(): SearchSorter
    {
        $sorter = new SearchSorter("Établissement<br>d'inscr.", EtablissementSearchFilter::NAME);
        $sorter->setOrderByField("etab_structureSubstituante.code, etab_structure.code");

        return $sorter;
    }

    public function createSorterEcoleDoctorale(): SearchSorter
    {
        $sorter = new SearchSorter("École doctorale", EcoleDoctoraleSearchFilter::NAME);
        $sorter->setOrderByField("toNumber(ed_structureSubstituante.code, '9999999'), toNumber(ed_structure.code, '9999999')");

        return $sorter;
    }

    public function createSorterUniteRecherche(): SearchSorter
    {
        $sorter = new SearchSorter("Unité recherche", UniteRechercheSearchFilter::NAME);
        $sorter->setOrderByField("ur_structureSubstituante.code, ur_structure.code");

        return $sorter;
    }

    public function createSorterAnneeRapportActivite(): SearchSorter
    {
        $sorter = new SearchSorter("Année du rapport", AnneeRapportActiviteSearchFilter::NAME);
        $sorter->setQueryBuilderApplier(
            function (SearchSorter $sorter, QueryBuilder $qb, $alias = 'ra') {
                $direction = $sorter->getDirection();
                $qb->addOrderBy("$alias.anneeUniv", $direction);
            }
        );

        return $sorter;
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

    public function getFinalSearchFilter(): ?SelectSearchFilter
    {
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

    public function getDematerialiseSearchFilter(): ?SelectSearchFilter
    {
        if ($this->dematerialiseSearchFilter === null) {
            $this->dematerialiseSearchFilter = new SelectSearchFilter("Dématérialisé ?", self::NAME_est_dematerialise);
            $this->dematerialiseSearchFilter
                ->setDataProvider(function () {
                    return ['oui' => "Oui (nouveau module)", 'non' => "Non (ancien module)"];
                })
                ->setEmptyOptionLabel("(Peu importe)")
                ->setAllowsEmptyOption()
                ->setQueryBuilderApplier([$this, 'applyDematerialiseFilterToQueryBuilder']);
        }

        return $this->dematerialiseSearchFilter;
    }

    public function getOperationAttendueSearchFilter(): SelectSearchFilter
    {
        if ($this->operationAttendueSearchFilter === null) {
            $valueOptions = ['null' => "Aucune (i.e. toutes les opérations réalisées)"];
            foreach($this->rapportActiviteOperationRule->fetchTypesOperation() as $type) {
                $valueOptions[$type->getCode()] = $type->__toString();
            }

            $this->operationAttendueSearchFilter = new SelectSearchFilter("Opération attendue", self::NAME_avis_attendu);
            $this->operationAttendueSearchFilter
                ->setDataProvider(function () use ($valueOptions) {
                    return $valueOptions;
                })
                ->setEmptyOptionLabel("(Peu importe)")
                ->setAllowsEmptyOption()
                ->setQueryBuilderApplier([$this, 'applyOperationAttendueSearchFilterToQueryBuilder']);
        }

        return $this->operationAttendueSearchFilter;
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