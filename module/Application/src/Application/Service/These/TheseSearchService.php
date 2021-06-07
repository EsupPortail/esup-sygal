<?php

namespace Application\Service\These;

use Application\Entity\Db\Role;
use Application\Entity\Db\These;
use Application\Entity\Db\TypeStructure;
use Application\Filter\AnneeUnivFormatter;
use Application\Search\DomaineScientifique\DomaineScientifiqueSearchFilterAwareTrait;
use Application\Search\EcoleDoctorale\EcoleDoctoraleSearchFilterAwareTrait;
use Application\Search\Etablissement\EtablissementInscSearchFilterAwareTrait;
use Application\Search\Filter\SearchFilter;
use Application\Search\Filter\SelectSearchFilter;
use Application\Search\Filter\TextCriteriaSearchFilter;
use Application\Search\Financement\OrigineFinancementSearchFilterAwareTrait;
use Application\Search\SearchService;
use Application\Search\Sorter\SearchSorter;
use Application\Search\These\EtatTheseSearchFilterAwareTrait;
use Application\Search\These\TheseTextSearchFilter;
use Application\Search\These\TheseTextSearchFilterAwareTrait;
use Application\Search\UniteRecherche\UniteRechercheSearchFilterAwareTrait;
use Application\Service\DomaineScientifiqueServiceAwareTrait;
use Application\Service\EcoleDoctorale\EcoleDoctoraleServiceAwareTrait;
use Application\Service\Etablissement\EtablissementServiceAwareTrait;
use Application\Service\Financement\FinancementServiceAwareTrait;
use Application\Service\Structure\StructureServiceAwareTrait;
use Application\Service\TheseAnneeUniv\TheseAnneeUnivServiceAwareTrait;
use Application\Service\UniteRecherche\UniteRechercheServiceAwareTrait;
use Application\Service\UserContextServiceAwareTrait;
use Application\View\Helper\Sortable;
use Doctrine\ORM\QueryBuilder;
use Exception;
use UnicaenApp\Exception\LogicException;
use UnicaenApp\Exception\RuntimeException;
use UnicaenApp\Util;
use Webmozart\Assert\Assert;

class TheseSearchService extends SearchService
{
    use EtablissementInscSearchFilterAwareTrait;
    use OrigineFinancementSearchFilterAwareTrait;
    use UniteRechercheSearchFilterAwareTrait;
    use EcoleDoctoraleSearchFilterAwareTrait;
    use EtatTheseSearchFilterAwareTrait;
    use TheseTextSearchFilterAwareTrait;
    use DomaineScientifiqueSearchFilterAwareTrait;

    use UserContextServiceAwareTrait;
    use TheseServiceAwareTrait;
    use EtablissementServiceAwareTrait;
    use UniteRechercheServiceAwareTrait;
    use EcoleDoctoraleServiceAwareTrait;
    use StructureServiceAwareTrait;
    use DomaineScientifiqueServiceAwareTrait;
    use FinancementServiceAwareTrait;
    use TheseAnneeUnivServiceAwareTrait;

    const NAME_etatThese = 'etatThese';
    const NAME_anneeCivile1ereInscription = 'anneePremiereInscription';
    const NAME_anneeUniv1ereInscription = 'anneeUniv1ereInscription';
    const NAME_anneeUnivInscription = 'anneeUnivInscription';
    const NAME_anneeSoutenance = 'anneeSoutenance';
    const NAME_domaineScientifique = 'domaineScientifique';

    const SORTER_NAME_titre = 'titre';
    const SORTER_NAME_numeroEtudiant = 'numeroEtudiant';
    const SORTER_NAME_doctorant = 'doctorant';
    const SORTER_NAME_datePremiereInscription = 'datePremiereInscription';
    const SORTER_NAME_dateSoutenance = 'dateSoutenance';

    /**
     * @var Role|null
     */
    private $role;

    /**
     * @inheritDoc
     */
    public function init()
    {
        $etablissementInscrFilter = $this->getEtablissementInscSearchFilter()
            ->setDataProvider(function(SelectSearchFilter $filter) {
                return $this->fetchEtablissements($filter);
            });
        $origineFinancementFilter = $this->getOrigineFinancementSearchFilter()
            ->setDataProvider(function(SelectSearchFilter $filter) {
                return $this->fetchOriginesFinancements($filter);
            });
        $uniteRechercheFilter = $this->getUniteRechercheSearchFilter()
            ->setDataProvider(function(SelectSearchFilter $filter) {
                return $this->fetchUnitesRecherches($filter);
            });
        $ecoleDoctoraleFilter = $this->getEcoleDoctoraleSearchFilter()
            ->setDataProvider(function(SelectSearchFilter $filter) {
                return $this->fetchEcolesDoctorales($filter);
            });
        $domaineScientifiqueFilter = $this->getDomaineScientifiqueSearchFilter()
            ->setDataProvider(function(SelectSearchFilter $filter) {
                return $this->fetchDomainesScientifiques($filter);
            });
        $etatTheseSearchFilter = $this->getEtatTheseSearchFilter()
            ->setDataProvider(function(SelectSearchFilter $filter) {
                return $this->fetchEtatsThese($filter);
            });
        $textSearchFilter = $this->getTheseTextSearchFilter()
            ->setQueryBuilderApplier(function(SearchFilter $filter, QueryBuilder $qb, string $alias = 'these') {
                /** @var TextCriteriaSearchFilter $filter */
                $this->applyTextFilterToQueryBuilder($filter, $qb, $alias);
            });

        $anneeCivile1ereInscriptionFilter = $this->createFilterAnneeCivile1ereInscription()
            ->setDataProvider(function(SelectSearchFilter $filter) {
                return $this->fetchAnneesInscription($filter);
            });
        $anneeUniv1ereInscriptionFilter = $this->createFilterAnneeUniv1ereInscription()
            ->setDataProvider(function(SelectSearchFilter $filter) {
                return $this->fetchAnneesUniv1ereInscription($filter);
            });
        $anneeUnivInscriptionFilter = $this->createFilterAnneeUnivInscription()
            ->setDataProvider(function(SelectSearchFilter $filter) {
                return $this->fetchAnneesUnivInscription($filter);
            });
        $anneesSoutenanceFilter = $this->createFilterAnneesSoutenance()
            ->setDataProvider(function(SelectSearchFilter $filter) {
                return $this->fetchAnneesSoutenance($filter);
            });

        $this->addFilters([
            $etatTheseSearchFilter->setDefaultValue(These::ETAT_EN_COURS),
            $etablissementInscrFilter,
            $ecoleDoctoraleFilter,
            $uniteRechercheFilter,
            $origineFinancementFilter->setAllowsNoneOption(),
            $anneeCivile1ereInscriptionFilter,
            $anneeUniv1ereInscriptionFilter,
            $anneeUnivInscriptionFilter,
            $anneesSoutenanceFilter,
            $domaineScientifiqueFilter,
            $textSearchFilter,
        ]);
        $this->addSorters([
            $etablissementInscrFilter->createSorter(),
            $ecoleDoctoraleFilter->createSorter(),
            $uniteRechercheFilter->createSorter(),
            $this->createSorterTitre(),
            $etatTheseSearchFilter->createSorter(),
            $this->createSorterNumeroEtudiant(),
            $this->createSorterDoctorant(),
            $this->createSorterDatePremiereInscription()->setDirection(Sortable::DESC)->setIsDefault(),
        ]);
    }

    /**
     * @param TextCriteriaSearchFilter $filter
     * @param QueryBuilder $qb
     * @param string $alias
     */
    private function applyTextFilterToQueryBuilder(TextCriteriaSearchFilter $filter, QueryBuilder $qb, $alias = 'these')
    {
        $filterValue = $filter->getTextValue();
        $criteria = $filter->getCriteriaValue();
        if (empty($criteria)) { // NB: aucun critère spécifié => texte pas pris en compte.
            return;
        }

        if ($filterValue !== null && strlen($filterValue) > 1) {
            $results = $this->findThesesSourceCodesByText($filterValue, $criteria);
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

    /**
     * @param SearchSorter $sorter
     * @param QueryBuilder $qb
     */
    public function applySorterToQueryBuilder(SearchSorter $sorter, QueryBuilder $qb)
    {
        // todo: permettre la spécification de l'alias Doctrine à utiliser via $sorter->getAlias() ?
        $alias = 'these';

        $name = $sorter->getName();
        $direction = $sorter->getDirection();

        switch ($name) {

            case self::SORTER_NAME_titre:
                // trim et suppression des guillemets
                $orderBy = "TRIM(REPLACE($name, CHR(34), ''))"; // CHR(34) <=> "
                $qb->addOrderBy($orderBy, $direction);
                break;

            case self::SORTER_NAME_numeroEtudiant:
                $qb
                    ->addOrderBy('th.sourceCode', $direction);
                break;

            case self::SORTER_NAME_doctorant:
                $qb
                    ->addOrderBy('di.nomUsuel', $direction)
                    ->addOrderBy('di.prenom1', $direction);
                break;

            case self::SORTER_NAME_datePremiereInscription:
                $qb
                    ->addOrderBy("$alias.datePremiereInscription", $direction);
                break;

            case self::SORTER_NAME_dateSoutenance:
                $qb
                    ->addOrderBy("$alias.dateSoutenance", $direction);
                break;

            default:
                throw new \InvalidArgumentException("Cas imprévu");
        }
    }

    /**
     * @inheritDoc
     */
    protected function createQueryBuilder(): QueryBuilder
    {
        $qb = $this->theseService->getRepository()->createQueryBuilder('these');
        $qb
            ->addSelect('di')->leftJoin('th.individu', 'di')
            ->addSelect('a')->leftJoin('these.acteurs', 'a')
            ->addSelect('i')->leftJoin('a.individu', 'i')
            ->addSelect('r')->leftJoin('a.role', 'r')
            ->addSelect('f')->leftJoin('these.financements', 'f')
            ->addSelect('fi')->leftJoin('these.fichierTheses', 'fi')
            ->addSelect('ta')->leftJoin('these.titreAcces', 'ta')
            ->andWhere('1 = pasHistorise(these)');

        return $qb;
    }

    /**
     * Recherche textuelle de thèses dans la vue matérialisée MV_RECHERCHE_THESE.
     *
     * Le contenu de la colonne MV_RECHERCHE_THESE.HAYSTACK suit le format suivant :
     * <pre>
     * code-ed{...} code-ur{...} titre{...} doctorant-numero{...} doctorant-nom{...} doctorant-prenom{...} directeur-nom{...}
     * </pre>
     * Exemple :
     * <pre>
     * code-ed{591} code-ur{umr6614} titre{bivalve dreissena polymorpha} doctorant-numero{85982906} doctorant-nom{hochon hochon} doctorant-prenom{paule} directeur-nom{terieur}
     * </pre>
     *
     * L'expression régulière utilisée est donc de la forme suivante :
     * <pre>
     * (<critere>|<critere>)\{[^{]*<terme>[^}]*\}
     * </pre>
     * Exemple :
     * <pre>
     * (doctorant-nom|directeur-nom)\{[^{]*hochon[^}]*\}
     * </pre>
     *
     * Lorsque le texte recherché est "hochon" par exemple, la requête SQL générée est la suivante :
     * <pre>
     *      SELECT * FROM MV_RECHERCHE_THESE MV WHERE rownum <= 100 AND (
     *          REGEXP_LIKE(haystack, q'[(doctorant-nom|directeur-nom)\{[^{]*hochon[^}]*\}]', 'i')
     *      )
     * </pre>
     *
     * Lorsque le texte recherché contient plusieurs mots séparés par des espaces, "hochon bivalve" par exemple,
     * la requête SQL générée est la suivante :
     * <pre>
     *      SELECT * FROM MV_RECHERCHE_THESE MV WHERE rownum <= 100 AND (
     *          REGEXP_LIKE(haystack, q'[(doctorant-nom|directeur-nom)\{[^{]*hochon[^}]*\}]', 'i') OR
     *          REGEXP_LIKE(haystack, q'[(doctorant-nom|directeur-nom)\{[^{]*bivalve[^}]*\}]', 'i')
     *      )
     * </pre>
     *
     * @param string $text Texte recherché. Ex: "hochon", "hochon bivalve"
     * @param string[] $criteria Critères sur lesquels porte la recherche. EX: ['doctorant-nom', 'directeur-nom']
     * @param integer $limit
     *
     * @return array [<CODE_THESE> => ['code' => <CODE_THESE>, 'code-doctorant' => <CODE_DOCTORANT>]]
     */
    public function findThesesSourceCodesByText(string $text, array $criteria, $limit = 1000): array
    {
        Assert::notEmpty($criteria, "Un tableau de critère vide n'est pas acceptée");

        $text = trim($text);

        if (strlen($text) < 2) return [];

        if ($unknown = array_diff($criteria, array_keys(TheseTextSearchFilter::CRITERIA))) {
            throw new LogicException("Les critères de recherche suivants ne sont pas supportés : " . implode(', ', $unknown));
        }

        $words = explode(' ', $text);
        $words = array_map('trim', $words);
        $words = array_map([Util::class, 'reduce'], $words);

        $orc = [];
        foreach ($words as $word) {
            // le caractère '*' est autorisé pour signifier "n'importe quel caractère répété 0 ou N fois"
            $word = str_replace('*', '.*', $word);
            if (count($criteria) === count(TheseTextSearchFilter::CRITERIA)) {
                // si tous les critères possibles sont spécifiés, on peut simplifier la regexp :
                // regexp : \{[^}]*<terme>.*\}
                $regexp = "\{[^}]*" . $word . ".*\}";
            } else {
                // regexp : (<critere>|<critere>)\{[^}]*<terme>.*\}
                $regexp = '(' . implode('|', $criteria) . ')' . "\{[^}]*" . $word . ".*\}";
            }
            $orc[] = "    REGEXP_LIKE(haystack, q'[" . $regexp . "]', 'i')"; // la syntaxe q'[]' dispense de doubler les '
        }
        $orc = implode(' OR ' . PHP_EOL, $orc);

        $sql = <<<EOS
SELECT distinct CODE_THESE, CODE_DOCTORANT, CODE_ECOLE_DOCT, to_char(HAYSTACK) HAYSTACK 
FROM MV_RECHERCHE_THESE MV 
WHERE rownum <= $limit AND (
$orc
)
EOS;

        try {
            $stmt = $this->theseService->getEntityManager()->getConnection()->executeQuery($sql);
        } catch (Exception $e) {
            throw new RuntimeException("Erreur rencontrée lors de la requête", null, $e);
        }

        $theses = [];
        while ($r = $stmt->fetch()) {
            $theses[$r['CODE_THESE']] = [
                'code'           => $r['CODE_THESE'],
                'code-doctorant' => $r['CODE_DOCTORANT'],
            ];
        }

        return $theses;
    }

    ////////////////////////////////// Fetch /////////////////////////////////////

    private function fetchEtatsThese(SelectSearchFilter $filter): array
    {
        return [
            $v = These::ETAT_EN_COURS => These::$etatsLibelles[$v],
            $v = These::ETAT_ABANDONNEE => These::$etatsLibelles[$v],
            $v = These::ETAT_SOUTENUE => These::$etatsLibelles[$v],
            $v = These::ETAT_TRANSFEREE => These::$etatsLibelles[$v],
        ];
    }

    private function fetchEtablissements(SelectSearchFilter $filter): array
    {
        return $this->etablissementService->getRepository()->findAllEtablissementsInscriptions(true);
    }

    private function fetchEcolesDoctorales(SelectSearchFilter $filter): array
    {
        return $this->structureService->getAllStructuresAffichablesByType(
            TypeStructure::CODE_ECOLE_DOCTORALE, 'sigle', true, true);
    }

    private function fetchUnitesRecherches(SelectSearchFilter $filter): array
    {
        return $this->structureService->getAllStructuresAffichablesByType(TypeStructure::CODE_UNITE_RECHERCHE, 'code', false, true);
    }

    private function fetchOriginesFinancements(SelectSearchFilter $filter): array
    {
        $values = $this->getFinancementService()->getOriginesFinancements("libelleLong");

        // dédoublonnage (sur le code origine) car chaque établissement pourrait fournir les mêmes données
        $origines = [];
        foreach (array_filter($values) as $origine) {
            $origines[$origine->getCode()] = $origine;
        }

        return $origines;
    }

    private function fetchAnneesInscription(SelectSearchFilter $filter): array
    {
        $annees = $this->theseService->getRepository()->fetchDistinctAnneesPremiereInscription();
        $annees = array_reverse(array_filter($annees));

        return array_combine($annees, $annees);
    }

    private function fetchAnneesUniv1ereInscription(SelectSearchFilter $filter): array
    {
        $role = $this->getSelectedIdentityRole();

        $etablissement = null;
        if ($role && $role->isEtablissementDependant()) {
            $etablissement = $role->getStructure()->getEtablissement();
        }
        $annees = $this->theseAnneeUnivService->fetchDistinctAnneesUniv1ereInscription($etablissement);
        $annees = array_reverse(array_filter($annees));
        $annees = array_combine($annees, $annees);

        return self::formatToAnneesUniv($annees);
    }

    private function fetchAnneesUnivInscription(SelectSearchFilter $filter): array
    {
        $annees = $this->theseAnneeUnivService->fetchDistinctAnneesUniv1ereInscription();
        $annees = array_reverse(array_filter($annees));
        $annees = array_combine($annees, $annees);

        return self::formatToAnneesUniv($annees);
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

    private function fetchAnneesSoutenance(SelectSearchFilter $filter): array
    {
        $role = $this->getSelectedIdentityRole();

        if ($role && $role->isEtablissementDependant()) {
            $etablissement = $role->getStructure()->getEtablissement();
            $annees = $this->theseService->getRepository()->fetchDistinctAnneesSoutenance($etablissement);
        } else {
            $annees = $this->theseService->getRepository()->fetchDistinctAnneesSoutenance();
        }

        $annees = array_reverse(array_filter($annees));

        return array_combine($annees, $annees);
    }

    private function fetchDomainesScientifiques(SelectSearchFilter $filter): array
    {
        $values = $this->getDomaineScientifiqueService()->getRepository()->findAll();
        $values = array_filter($values);

        sort($values);

        return $values;
    }
    /**
     * @return Role|null
     */
    private function getSelectedIdentityRole(): ?Role
    {
        if ($this->role === null) {
            $this->role = $this->userContextService->getSelectedIdentityRole();
        }

        return $this->role;
    }

    /////////////////////////////////////// Filters /////////////////////////////////////////

    /**
     * @return SelectSearchFilter
     */
    private function createFilterAnneeUnivInscription(): SelectSearchFilter
    {
        $filter = new SelectSearchFilter(
            "Année univ.<br>d'inscr.",
            self::NAME_anneeUnivInscription
        );
        $filter->setQueryBuilderApplier(function(SearchFilter $filter, QueryBuilder $qb, string $alias = 'these') {
            $filterValue = $filter->getValue();
            if ($filterValue === 'NULL') {
                $qb
                    ->leftJoin("$alias.anneesUnivInscription", 'aui')
                    ->andWhere('aui.anneeUniv IS NULL');
            } else {
                $qb
                    ->join("$alias.anneesUnivInscription", 'aui')
                    ->andWhere('aui.anneeUniv = :anneeUniv')
                    ->setParameter('anneeUniv', $filterValue);
            }
        });
        return $filter;
    }

    /**
     * @return SelectSearchFilter
     */
    private function createFilterAnneeUniv1ereInscription(): SelectSearchFilter
    {
        $filter = new SelectSearchFilter(
            "Année univ.<br>1ère inscr.",
            self::NAME_anneeUniv1ereInscription
        );
        $filter->setQueryBuilderApplier(function(SearchFilter $filter, QueryBuilder $qb, string $alias = 'these') {
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
        });
        return $filter;
    }

    /**
     * @return SelectSearchFilter
     */
    private function createFilterAnneeCivile1ereInscription(): SelectSearchFilter
    {
        $filter = new SelectSearchFilter(
            "Année civile<br>1ère inscr.",
            self::NAME_anneeCivile1ereInscription
        );
        $filter->setQueryBuilderApplier(function(SearchFilter $filter, QueryBuilder $qb, string $alias = 'these') {
            $filterValue = $filter->getValue();
            if ($filterValue === 'NULL') {
                $qb
                    ->andWhere("$alias.datePremiereInscription IS NULL");
            } else {
                $qb
                    ->andWhere("year($alias.datePremiereInscription) = :anneePremiereInscription")
                    ->setParameter('anneePremiereInscription', $filterValue);
            }
        });
        return $filter;
    }

    /**
     * @return SelectSearchFilter
     */
    private function createFilterAnneesSoutenance(): SelectSearchFilter
    {
        $filter = new SelectSearchFilter(
            "Soutenance",
            self::NAME_anneeSoutenance
        );
        $filter->setQueryBuilderApplier(function(SearchFilter $filter, QueryBuilder $qb, string $alias = 'these') {
            $filterValue = $filter->getValue();
            if ($filterValue === 'NULL') {
                $qb
                    ->andWhere("$alias.dateSoutenance IS NULL");
            } else {
                $qb
                    ->andWhere("year($alias.dateSoutenance) = :anneeSoutenance")
                    ->setParameter('anneeSoutenance', $filterValue);
            }
        });
        return $filter;
    }

    /////////////////////////////////////// Sorters /////////////////////////////////////////

    /**
     * @return SearchSorter
     */
    private function createSorterTitre(): SearchSorter
    {
        $sorter = new SearchSorter(
            "",
            TheseSorter::NAME_titre
        );
        $sorter->setQueryBuilderApplier([$this, 'applySorterToQueryBuilder']);
        return $sorter;
    }

    /**
     * @return SearchSorter
     */
    private function createSorterNumeroEtudiant(): SearchSorter
    {
        $sorter = new SearchSorter(
            "",
            TheseSorter::NAME_numeroEtudiant
        );
        $sorter->setQueryBuilderApplier([$this, 'applySorterToQueryBuilder']);
        return $sorter;
    }

    /**
     * @return SearchSorter
     */
    private function createSorterDoctorant(): SearchSorter
    {
        $sorter = new SearchSorter(
            "",
            TheseSorter::NAME_doctorant
        );
        $sorter->setQueryBuilderApplier([$this, 'applySorterToQueryBuilder']);
        return $sorter;
    }

    /**
     * @return SearchSorter
     */
    private function createSorterDatePremiereInscription(): SearchSorter
    {
        $sorter = new SearchSorter(
            "",
            TheseSorter::NAME_datePremiereInscription
        );
        $sorter->setQueryBuilderApplier([$this, 'applySorterToQueryBuilder']);
        return $sorter;
    }
}
