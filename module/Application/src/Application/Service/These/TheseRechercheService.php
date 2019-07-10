<?php

namespace Application\Service\These;

use Application\Entity\Db\DomaineScientifique;
use Application\Entity\Db\EcoleDoctorale;
use Application\Entity\Db\Etablissement;
use Application\Entity\Db\OrigineFinancement;
use Application\Entity\Db\These;
use Application\Entity\Db\TheseAnneeUniv;
use Application\Entity\Db\TypeStructure;
use Application\Entity\Db\UniteRecherche;
use Application\Entity\UserWrapperFactory;
use Application\Provider\Privilege\StructurePrivileges;
use Application\QueryBuilder\TheseQueryBuilder;
use Application\Service\AuthorizeServiceAwareTrait;
use Application\Service\DomaineScientifiqueServiceAwareTrait;
use Application\Service\EcoleDoctorale\EcoleDoctoraleServiceAwareTrait;
use Application\Service\Etablissement\EtablissementServiceAwareTrait;
use Application\Service\Financement\FinancementServiceAwareTrait;
use Application\Service\Source\SourceServiceAwareTrait;
use Application\Service\Structure\StructureServiceAwareTrait;
use Application\Service\These\Filter\TheseSelectFilter;
use Application\Service\These\Filter\TheseTextFilter;
use Application\Service\TheseAnneeUniv\TheseAnneeUnivServiceAwareTrait;
use Application\Service\UniteRecherche\UniteRechercheServiceAwareTrait;
use Application\Service\UserContextServiceAwareTrait;
use Application\SourceCodeStringHelperAwareTrait;
use Application\View\Helper\Sortable;
use Doctrine\DBAL\DBALException;
use Doctrine\ORM\Query\Expr\Join;
use UnicaenApp\Exception\RuntimeException;
use UnicaenApp\Util;

class TheseRechercheService
{
    use UserContextServiceAwareTrait;
    use TheseServiceAwareTrait;
    use EtablissementServiceAwareTrait;
    use UniteRechercheServiceAwareTrait;
    use EcoleDoctoraleServiceAwareTrait;
    use StructureServiceAwareTrait;
    use SourceServiceAwareTrait;
    use DomaineScientifiqueServiceAwareTrait;
    use FinancementServiceAwareTrait;
    use AuthorizeServiceAwareTrait;
    use SourceCodeStringHelperAwareTrait;
    use TheseAnneeUnivServiceAwareTrait;

    /**
     * @var bool
     */
    private $unpopulatedOptions = false;

    /**
     * @var TheseSelectFilter[]
     */
    private $filters = [];

    /**
     * @var TheseSorter[]
     */
    private $sorters = [];

    /**
     * @var
     */
    private $role;

    /**
     * @return self
     */
    public function createFiltersWithUnpopulatedOptions()
    {
        $this->createFiltersArray([
            TheseSelectFilter::NAME_etatThese                => [],
            TheseSelectFilter::NAME_etablissement            => [],
            TheseSelectFilter::NAME_ecoleDoctorale           => [],
            TheseSelectFilter::NAME_uniteRecherche           => [],
            TheseSelectFilter::NAME_financement              => [],
            TheseSelectFilter::NAME_anneePremiereInscription => [],
            TheseSelectFilter::NAME_anneeUniv1ereInscription => [],
            TheseSelectFilter::NAME_anneeUnivInscription     => [],
            TheseSelectFilter::NAME_anneeSoutenance          => [],
//            TheseSelectFilter::NAME_discipline               => [],
            TheseSelectFilter::NAME_domaineScientifique      => [],
        ]);

        $this->unpopulatedOptions = true;

        return $this;
    }

    /**
     * @return self
     */
    public function createFilters()
    {
        if (! $this->unpopulatedOptions && ! empty($this->filters)) {
            return $this;
        }

        $this->createFiltersArray([
            TheseSelectFilter::NAME_etatThese                => $this->fetchEtatsTheseOptions(),
            TheseSelectFilter::NAME_etablissement            => $this->fetchEtablissementsOptions(),
            TheseSelectFilter::NAME_ecoleDoctorale           => $this->fetchEcolesDoctoralesOptions(),
            TheseSelectFilter::NAME_uniteRecherche           => $this->fetchUnitesRecherchesOptions(),
            TheseSelectFilter::NAME_financement              => $this->fetchOriginesFinancementsOptions(),
            TheseSelectFilter::NAME_anneePremiereInscription => $this->fetchAnneesInscriptionOptions(),
            TheseSelectFilter::NAME_anneeUniv1ereInscription => $this->fetchAnneesUniv1ereInscriptionOptions(),
            TheseSelectFilter::NAME_anneeUnivInscription     => $this->fetchAnneesUnivInscriptionOptions(),
            TheseSelectFilter::NAME_anneeSoutenance          => $this->fetchAnneesSoutenance(),
//            TheseSelectFilter::NAME_discipline               => $this->fetchDisciplinesOptions(),
            TheseSelectFilter::NAME_domaineScientifique      => $this->fetchDomainesScientifiquesOptions(),
        ]);

        $this->unpopulatedOptions = false;

        return $this;
    }

    /**
     * @param array $optionsArray
     */
    private function createFiltersArray(array $optionsArray)
    {
        $this->filters = [
            TheseSelectFilter::NAME_etatThese                => new TheseSelectFilter(
                "État",
                TheseSelectFilter::NAME_etatThese,
                $optionsArray[TheseSelectFilter::NAME_etatThese]
            ),
            TheseSelectFilter::NAME_etablissement            => new TheseSelectFilter(
                "Établissement",
                TheseSelectFilter::NAME_etablissement,
                $optionsArray[TheseSelectFilter::NAME_etablissement]
            ),
            TheseSelectFilter::NAME_ecoleDoctorale           => new TheseSelectFilter(
                "ED",
                TheseSelectFilter::NAME_ecoleDoctorale,
                $optionsArray[TheseSelectFilter::NAME_ecoleDoctorale],
                ['liveSearch' => true]
            ),
            TheseSelectFilter::NAME_uniteRecherche           => new TheseSelectFilter(
                "UR",
                TheseSelectFilter::NAME_uniteRecherche,
                $optionsArray[TheseSelectFilter::NAME_uniteRecherche],
                ['liveSearch' => true]
            ),
            TheseSelectFilter::NAME_financement           => new TheseSelectFilter(
                "Origine financement",
                TheseSelectFilter::NAME_financement,
                $optionsArray[TheseSelectFilter::NAME_financement],
                ['width' => '125px', 'liveSearch' => true]
            ),
            TheseSelectFilter::NAME_anneePremiereInscription => new TheseSelectFilter(
                "Année civile<br>1ère inscr.",
                TheseSelectFilter::NAME_anneePremiereInscription,
                $optionsArray[TheseSelectFilter::NAME_anneePremiereInscription]
            ),
            TheseSelectFilter::NAME_anneeUniv1ereInscription => new TheseSelectFilter(
                "Année univ.<br>1ère inscr.",
                TheseSelectFilter::NAME_anneeUniv1ereInscription,
                $optionsArray[TheseSelectFilter::NAME_anneeUniv1ereInscription]
            ),
            TheseSelectFilter::NAME_anneeUnivInscription => new TheseSelectFilter(
                "Année univ.<br>d'inscr.",
                TheseSelectFilter::NAME_anneeUnivInscription,
                $optionsArray[TheseSelectFilter::NAME_anneeUnivInscription]
            ),
            TheseSelectFilter::NAME_anneeSoutenance => new TheseSelectFilter(
                "Soutenance",
                TheseSelectFilter::NAME_anneeSoutenance,
                $optionsArray[TheseSelectFilter::NAME_anneeSoutenance]
            ),
//            TheseSelectFilter::NAME_discipline               => new TheseSelectFilter(
//                "Discipline",
//                TheseSelectFilter::NAME_discipline,
//                $optionsArray[TheseSelectFilter::NAME_discipline],
//                ['width' => '125px', 'liveSearch' => true]
//            ),
            TheseSelectFilter::NAME_domaineScientifique      => new TheseSelectFilter(
                "Domaine scientifique",
                TheseSelectFilter::NAME_domaineScientifique,
                $optionsArray[TheseSelectFilter::NAME_domaineScientifique],
                ['width' => '125px', 'liveSearch' => true]
            ),
            TheseTextFilter::NAME_text                       => new TheseTextFilter(
                "Recherche de texte",
                TheseTextFilter::NAME_text
            ),
        ];
    }

    /**
     * @return TheseSelectFilter[]
     */
    public function getFilters()
    {
        return $this->filters;
    }

    /**
     * @param array $queryParams
     * @return self
     */
    public function processQueryParams(array $queryParams)
    {
        foreach ($this->filters as $filter) {
            $filter->processQueryParams($queryParams);
        }
        foreach ($this->sorters as $sorter) {
            $sorter->processQueryParams($queryParams);
        }

        return $this;
    }

    /**
     * @param $name
     * @return string|null
     */
    public function getFilterValueByName($name)
    {
        if (isset($this->filters[$name])) {
            return $this->filters[$name]->getValue();
        }

        return null;
    }

    /**
     * @return self
     */
    public function createSorters()
    {
        if (! empty($this->sorters)) {
            return $this;
        }

        $this->sorters = [
            TheseSorter::NAME_titre                   => new TheseSorter("", TheseSorter::NAME_titre),
            TheseSorter::NAME_etablissement           => new TheseSorter("", TheseSorter::NAME_etablissement),
            TheseSorter::NAME_etatThese               => new TheseSorter("", TheseSorter::NAME_etatThese),
            TheseSorter::NAME_numeroEtudiant          => new TheseSorter("", TheseSorter::NAME_numeroEtudiant),
            TheseSorter::NAME_doctorant               => new TheseSorter("", TheseSorter::NAME_doctorant),
            TheseSorter::NAME_ecoleDoctorale          => new TheseSorter("", TheseSorter::NAME_ecoleDoctorale),
            TheseSorter::NAME_uniteRecherche          => new TheseSorter("", TheseSorter::NAME_uniteRecherche),
            TheseSorter::NAME_datePremiereInscription => new TheseSorter("", TheseSorter::NAME_datePremiereInscription),
            TheseSorter::NAME_dateSoutenance          => new TheseSorter("", TheseSorter::NAME_dateSoutenance),
        ];

        return $this;
    }

    /**
     * @param string $name
     * @param array  $queryParams
     * @return string
     */
    private function paramFromQueryParams($name, array $queryParams)
    {
        if (! array_key_exists($name, $queryParams)) {
            // null <=> paramètre absent
            return null;
        }

        // NB: '' <=> "Tous"

        return $queryParams[$name];
    }

    /**
     * @param array $queryParams
     * @return bool
     */
    public function updateQueryParamsWithDefaultFilters(array &$queryParams)
    {
        $updated = false;

        // Si le filtre "état de la thèse" n'est présent alors on le force : état = These::ETAT_EN_COURS
        $name = TheseSelectFilter::NAME_etatThese;
        $etatThese = $this->paramFromQueryParams($name, $queryParams); // NB: null <=> filtre absent
        if ($etatThese === null) {
            $queryParams = array_merge($queryParams, [$name => These::ETAT_EN_COURS]);
            $updated = true;
        }

        return $updated;
    }

    /**
     * @param array $queryParams
     * @return bool
     */
    public function updateQueryParamsWithDefaultSorters(array &$queryParams)
    {
        $updated = false;

        $sort = $this->paramFromQueryParams('sort', $queryParams);

        // Si aucun tri n'est présent, on trie par date de 1ere inscription
        if ($sort === null || $sort === '') {
            $queryParams = array_merge($queryParams, [
                'sort' => TheseSorter::NAME_datePremiereInscription,
                'direction' => Sortable::ASC
            ]);
            $updated = true;
        }

        return $updated;
    }

    /**
     * @return TheseQueryBuilder
     */
    public function createQueryBuilder()
    {
        $qb = $this->theseService->getRepository()->createQueryBuilder('t');
        $qb
            ->addSelect('di')->leftJoin('th.individu', 'di')
            ->addSelect('a')->leftJoin('t.acteurs', 'a')
            ->addSelect('i')->leftJoin('a.individu', 'i')
            ->addSelect('r')->leftJoin('a.role', 'r')
            ->addSelect('f')->leftJoin('t.financements', 'f')
            ->addSelect('fi')->leftJoin('t.fichierTheses', 'fi')
            ->addSelect('ta')->leftJoin('t.titreAcces', 'ta')
//            ->addSelect('ds')->leftJoin('ur.domaines', 'ds')
            ->andWhere('1 = pasHistorise(t)');

        foreach ($this->filters as $filter) {
            $filter->applyToQueryBuilder($qb);
        }

        foreach ($this->sorters as $sorter) {
            $sorter->applyToQueryBuilder($qb);
        }

        /**
         * NB (2019/03/20) : désactiver pour donner l'accès à toutes les thèses pour les rôles doctorant et directeur/co-directeur
         * Filtres découlant du rôle de l'utilisateur.
         */
//        $this->decorateQbFromUserContext($qb);

        /**
         * Prise en compte du texte recherché éventuel.
         */
        $text = $this->getFilterValueByName(TheseTextFilter::NAME_text);
        if ($text !== null && strlen($text) > 1) {
            $results = $this->rechercherThese($text);
            $sourceCodes = array_unique(array_keys($results));
            if ($sourceCodes) {
                $qb
                    ->andWhere($qb->expr()->in('t.sourceCode', ':sourceCodes'))
                    ->setParameter('sourceCodes', $sourceCodes);
            }
            else {
                $qb->andWhere("0 = 1"); // i.e. aucune thèse trouvée
            }
        }

        return $qb;
    }

    /**
     * @param TheseQueryBuilder  $qb
     */
    public function decorateQbFromUserContext(TheseQueryBuilder $qb)
    {
        $role = $this->getSelectedIdentityRole();

        if ($role->isTheseDependant()) {
            if ($role->isDoctorant()) {
                $qb
                    ->andWhere('t.doctorant = :doctorant')
                    ->setParameter('doctorant', $this->userContextService->getIdentityDoctorant());
            }
            elseif ($role->isDirecteurThese()) {
//                switch (true) {
//                    case $identity = $this->userContextService->getIdentityLdap():
//                    case $identity = $this->userContextService->getIdentityShib():
//                    case $identity = $this->userContextService->getIdentityDb():
//                        $userWrapper = UserWrapper::inst($identity);
//                        break;
//                    default:
//                        throw new RuntimeException("Cas imprévu!");
//                }
                $userWrapperFactory = new UserWrapperFactory();
                $userWrapper = $userWrapperFactory->createInstanceFromIdentity($this->userContextService->getIdentity());
                if ($userWrapper->getIndividu() !== null) {
                    $qb
                        ->join('t.acteurs', 'adt', Join::WITH, 'adt.role = :role')
                        ->join('adt.individu', 'idt', Join::WITH, 'idt = :individu')
                        ->setParameter('individu', $userWrapper->getIndividu())
                        ->setParameter('role', $role);
                } else {
                    $individuSourceCode = $this->sourceCodeStringHelper
                        ->addPrefixTo($userWrapper->getSupannId(), $role->getStructure()->getCode());
                    $qb
                        ->join('t.acteurs', 'adt', Join::WITH, 'adt.role = :role')
                        ->join('adt.individu', 'idt', Join::WITH, 'idt.sourceCode = :idtSourceCode')
                        ->setParameter('idtSourceCode', $individuSourceCode)
                        ->setParameter('role', $role);
                }
            }
            // sinon role = membre jury
            // ...
        }

//        elseif ($role->isStructureDependant()) {
//            if ($role->isEtablissementDependant()) {
//                /**
//                 * On ne voit que les thèses de son établissement.
//                 */
//                $qb
//                    ->andWhere('t.etablissement = :etab')
//                    ->setParameter('etab', $role->getStructure()->getEtablissement());
//            }
//            elseif ($role->isEcoleDoctoraleDependant()) {
//                /**
//                 * On ne voit que les thèses concernant son ED.
//                 */
//                $qb
//                    ->addSelect('ed2')->join('t.ecoleDoctorale', 'ed2')
//                    ->andWhere('ed2 = :ed')
//                    ->setParameter('ed', $role->getStructure()->getEcoleDoctorale());
//            }
//            elseif ($role->isUniteRechercheDependant()) {
//                /**
//                 * On ne voit que les thèses concernant son UR.
//                 */
//                $qb
//                    ->addSelect('ur2')->join('t.uniteRecherche', 'ur2')
//                    ->andWhere('ur2 = :ur')
//                    ->setParameter('ur', $role->getStructure()->getUniteRecherche());
//            }
//        }
    }

    /**
     * Recherche de thèses à l'aide de la vue matérialisée MV_RECHERCHE_THESE.
     *
     * @param string  $text
     * @param integer $limit
     *
     * @return array
     */
    public function rechercherThese($text, $limit = 100)
    {
        if (strlen($text) < 2) return [];

        $text = Util::reduce($text);
        $criteres = explode(' ', $text);

        $sql     = sprintf('SELECT * FROM MV_RECHERCHE_THESE MV WHERE rownum <= %s ', (int)$limit);
        $sqlCri  = '';
        $criCode = 0;

        foreach ($criteres as $c) {
            if (! is_numeric($c)) {
                if ($sqlCri != '') {
                    $sqlCri .= ' AND ';
                }
                $sqlCri .= "haystack LIKE LOWER(q'[%" . $c . "%]')"; // q'[] : double les quotes
            } else {
                $criCode = (int) $c;
            }
        }
        $orc = [];
        if ($sqlCri != '') {
            $orc[] = '(' . $sqlCri . ')';
        }
        if ($criCode) {
            $orc[] = "(code_doctorant like '%" . $criCode . "%' OR code_ecole_doct = '" . $criCode . "')";
        }
        $orc = implode(' OR ', $orc);
        $sql .= ' AND (' . $orc . ') ';

        try {
            $stmt = $this->theseService->getEntityManager()->getConnection()->executeQuery($sql);
        } catch (DBALException $e) {
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

    private function fetchEtatsTheseOptions()
    {
        $etatsThese = [
            $this->optionify('',                          "Tous"),
            $this->optionify($v = These::ETAT_EN_COURS,   These::$etatsLibelles[$v]),
            $this->optionify($v = These::ETAT_ABANDONNEE, These::$etatsLibelles[$v]),
            $this->optionify($v = These::ETAT_SOUTENUE,   These::$etatsLibelles[$v]),
            $this->optionify($v = These::ETAT_TRANSFEREE, These::$etatsLibelles[$v]),
        ];

        return $etatsThese;
    }

    private function fetchEtablissementsOptions()
    {
        $role = $this->getSelectedIdentityRole();

        $privilege = StructurePrivileges::STRUCTURE_CONSULTATION_TOUTES_STRUCTURES;
        $toutesStructuresAllowed = $this->authorizeService->isAllowed(StructurePrivileges::getResourceId($privilege));
        if ($role && !$toutesStructuresAllowed) {
            return [
                $this->optionify($role->getStructure()->getEtablissement())
            ];
        }

        $etablissements = $this->getEtablissementService()->getRepository()->findAllEtablissementsMembres();

        $options = [];
        foreach ($etablissements as $etablissement) {
            $options[] = $this->optionify($etablissement);
        }

        return $this->addEmptyOption($options, 'Tous');
    }

    private function fetchEcolesDoctoralesOptions()
    {
        $eds = $this->getStructureService()->getAllStructuresAffichablesByType(TypeStructure::CODE_ECOLE_DOCTORALE);

        $options = [];
        foreach ($eds as $ed) {
            $options[] = $this->optionify($ed);
        }
//        usort($options, function($a, $b) {
//            return strcmp($a['label'], $b['label']);
//        });

        return $this->addEmptyOption($options, "Toutes");
    }

    private function fetchUnitesRecherchesOptions()
    {
        //$urs = $this->getStructureService()->getAllStructuresAffichablesByType(TypeStructure::CODE_UNITE_RECHERCHE, 'libelle');
        $all = $this->getStructureService()->getUnitesRechercheSelection();

        $options = [];
//        foreach ($urs as $ur) {
//            $options[] = $this->optionify($ur);
//        }
        foreach ($all as $a) {
            $options[] = [
                'value' =>      $a[4], //['sourceCode'],
                'label' =>      $a[3], //['sigle'],
                'subtext' =>    $a[2], //['libelle']
            ];
        }
//        usort($options, function($a, $b) {
//            return strcmp($a['label'], $b['label']);
//        });

        return $this->addEmptyOption($options, "Toutes");
    }

    private function fetchAnneesInscriptionOptions()
    {
        $role = $this->getSelectedIdentityRole();

        if ($role && $role->isEtablissementDependant()) {
            $etablissement = $role->getStructure()->getEtablissement();
            $annees = $this->theseService->getRepository()->fetchDistinctAnneesPremiereInscription($etablissement);
        } else {
            $annees = $this->theseService->getRepository()->fetchDistinctAnneesPremiereInscription();
        }
        $annees = $this->theseService->getRepository()->fetchDistinctAnneesPremiereInscription();

        $annees = array_reverse(array_filter($annees));

        $options = [];
        $options[] = $this->optionify(null); // option spéciale pour valeur === null
        foreach ($annees as $annee) {
            $options[] = $this->optionify($annee);
        }

        return $this->addEmptyOption($options, "Toutes");
    }

    private function fetchAnneesUniv1ereInscriptionOptions()
    {
        $role = $this->getSelectedIdentityRole();

        $etablissement = null;
        if ($role && $role->isEtablissementDependant()) {
            $etablissement = $role->getStructure()->getEtablissement();
        }
        $annees = $this->theseAnneeUnivService->getRepository()->fetchDistinctAnneesUniv1ereInscription($etablissement);
        $annees = array_reverse(array_filter($annees));

        $options = [];
        $options[] = $this->optionify(null); // option spéciale pour valeur === null
        foreach ($annees as $annee) {
            $options[] = $this->optionify($annee);
        }

        // formattage spécial du label: "2018" devient "2018/2019"
        $options = array_map(function($value) {
            if (! is_numeric($value['label'])) {
                return $value;
            }
            $annee = (int) $value['label'];
            $value['label'] = $annee . '/' . ($annee+1);
            return $value;
        }, $options);

        return $this->addEmptyOption($options, "Toutes");
    }

    private function fetchAnneesUnivInscriptionOptions()
    {
        // Vilaine entorse au SOC: on fetche directement TheseAnneeUniv dans TheseRechercheService !
        // TODO: déplacer dans TheseAnneeUnivService existant
        $annees = $this->fetchDistinctAnneesUnivInscription();

        $annees = array_reverse(array_filter($annees));

        $options = [];
        $options[] = $this->optionify(null); // option spéciale pour valeur === null
        foreach ($annees as $annee) {
            $options[] = $this->optionify($annee);
        }

        // formattage spécial du label: "2018" devient "2018/2019"
        $options = array_map(function($value) {
            if (! is_numeric($value['label'])) {
                return $value;
            }
            $annee = (int) $value['label'];
            $value['label'] = $annee . '/' . ($annee+1);
            return $value;
        }, $options);

        return $this->addEmptyOption($options, "Toutes");
    }

    /**
     * Vilaine entorse au SOC: on fetche directement TheseAnneeUniv dans TheseRechercheService !
     * TODO: crééer un TheseAnneeUnivService
     *
     * @return int[]
     */
    private function fetchDistinctAnneesUnivInscription()
    {
        $qb = $this->theseService->getEntityManager()->getRepository(TheseAnneeUniv::class)->createQueryBuilder('t');
        $qb
            ->distinct()
            ->select("t.anneeUniv")
            ->orderBy("t.anneeUniv");

        $results = array_map(function($value) {
            return current($value);
        }, $qb->getQuery()->getScalarResult());

        return $results;
    }

    private function fetchAnneesSoutenance()
    {
        $role = $this->getSelectedIdentityRole();

        if ($role && $role->isEtablissementDependant()) {
            $etablissement = $role->getStructure()->getEtablissement();
            $annees = $this->theseService->getRepository()->fetchDistinctAnneesSoutenance($etablissement);
        } else {
            $annees = $this->theseService->getRepository()->fetchDistinctAnneesSoutenance();
        }

        $annees = array_reverse(array_filter($annees));

        $options = [];
        $options[] = $this->optionify(null); // option spéciale pour valeur === null
        foreach ($annees as $annee) {
            $options[] = $this->optionify($annee);
        }

        return $this->addEmptyOption($options, "Toutes");
    }

    private function fetchDisciplinesOptions()
    {
        $role = $this->getSelectedIdentityRole();

        if ($role->isEtablissementDependant()) {
            $etablissement = $role->getStructure()->getEtablissement();
            $disciplines = $this->theseService->getRepository()->fetchDistinctDisciplines($etablissement);
        } else {
            $disciplines = $this->theseService->getRepository()->fetchDistinctDisciplines();
        }

        $disciplines = array_filter($disciplines);

        sort($disciplines);

        $options = [];
        $options[] = $this->optionify(null); // option spéciale pour valeur === null
        foreach ($disciplines as $discipline) {
            $options[] = $this->optionify($discipline);
        }

        return $this->addEmptyOption($options, "Toutes");
    }

    private function fetchDomainesScientifiquesOptions()
    {
        $domaines = $this->getDomaineScientifiqueService()->getRepository()->findAll();
        $domaines = array_filter($domaines);

        sort($domaines);

        $options = [];
        $options[] = $this->optionify(null); // option spéciale pour valeur === null
        /** @var DomaineScientifique $domaine */
        foreach ($domaines as $domaine) {
            $options[] = $this->optionify($domaine);
        }

        return $this->addEmptyOption($options, "Tous");
    }

    private function fetchOriginesFinancementsOptions()
    {
        $origines = $this->getFinancementService()->getOriginesFinancements("libelleLong");
        $origines = array_filter($origines);

        sort($origines);

        $options = [];
        $options[] = $this->optionify(null); // option spéciale pour valeur === null
        /** @var DomaineScientifique $domaine */
        foreach ($origines as $origine) {
            $options[] = $this->optionify($origine);
        }

        return $this->addEmptyOption($options, "Toutes");
    }
    /**
     * @return \Application\Entity\Db\Role|null|\Zend\Permissions\Acl\Role\RoleInterface
     */
    private function getSelectedIdentityRole()
    {
        if ($this->role === null) {
            $this->role = $this->userContextService->getSelectedIdentityRole();
        }

        return $this->role;
    }

    private function addEmptyOption(array $options, $label = "Tous")
    {
        $emptyOption = $this->optionify('', $label);

        return array_merge([$emptyOption], $options);
    }

    /**
     * N.B. attention value doit être une chaine de caractère car le test dans est non permissif SelectsFilterPanelHelper.php:33
     *
     * @param Etablissement|EcoleDoctorale|UniteRecherche|string|null $value
     * @param string                                                  $label
     * @return array
     */
    private function optionify($value = null, $label = null)
    {
        if ($value instanceof Etablissement) {
            return ['value' => $value->getStructure()->getCode(), 'label' => $value->getSigle()];
        } elseif ($value instanceof EcoleDoctorale) {
            return ['value' => $value->getSourceCode(), 'label' => $value->getSigle(), 'subtext' => $value->getLibelle()];
        } elseif ($value instanceof UniteRecherche) {
            return ['value' => $value->getSourceCode(), 'label' => $value->getCode(), 'subtext' => $value->getLibelle()];
        } elseif ($value instanceof DomaineScientifique) {
            return ['value' => (string) $value->getId(), 'label' => $value->getLibelle()];
        } elseif ($value instanceof OrigineFinancement) {
            return ['value' => (string) $value->getId(), 'label' => $value->getLibelleLong()];
        } elseif ($value === null) {
            return ['value' => 'NULL', 'label' => $label ?: "Inconnu(e)"];
        } elseif ($value === '') {
            return ['value' => '', 'label' => $label];
        } else {
            return ['value' => $value, 'label' => $label ?: $value];
        }
    }
}
