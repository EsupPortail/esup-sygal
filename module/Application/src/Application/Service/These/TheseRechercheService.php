<?php

namespace Application\Service\These;

use Application\Entity\Db\EcoleDoctorale;
use Application\Entity\Db\Etablissement;
use Application\Entity\Db\SourceInterface;
use Application\Entity\Db\These;
use Application\Entity\Db\UniteRecherche;
use Application\Entity\UserWrapper;
use Application\QueryBuilder\TheseQueryBuilder;
use Application\Service\EcoleDoctorale\EcoleDoctoraleServiceAwareTrait;
use Application\Service\Etablissement\EtablissementServiceAwareTrait;
use Application\Service\Source\SourceServiceAwareTrait;
use Application\Service\These\Filter\TheseSelectFilter;
use Application\Service\These\Filter\TheseTextFilter;
use Application\Service\UniteRecherche\UniteRechercheServiceAwareTrait;
use Application\Service\UserContextServiceAwareTrait;
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
    use SourceServiceAwareTrait;

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
        $etatsThese = [];
        $etablissements = [];
        $ecolesDoctorales = [];
        $unitesRecherches = [];
        $anneesPremiereInscription = [];
        $disciplines = [];

        $this->filters = [
            TheseSelectFilter::NAME_etatThese                => new TheseSelectFilter(
                "État",
                TheseSelectFilter::NAME_etatThese,
                $etatsThese
            ),
            TheseSelectFilter::NAME_etablissement            => new TheseSelectFilter(
                "Établissement",
                TheseSelectFilter::NAME_etablissement,
                $etablissements
            ),
            TheseSelectFilter::NAME_ecoleDoctorale           => new TheseSelectFilter(
                "ED",
                TheseSelectFilter::NAME_ecoleDoctorale,
                $ecolesDoctorales,
                ['liveSearch' => true]
            ),
            TheseSelectFilter::NAME_uniteRecherche           => new TheseSelectFilter(
                "UR",
                TheseSelectFilter::NAME_uniteRecherche,
                $unitesRecherches,
                ['liveSearch' => true]
            ),
            TheseSelectFilter::NAME_anneePremiereInscription => new TheseSelectFilter(
                "1ère inscr.",
                TheseSelectFilter::NAME_anneePremiereInscription,
                $anneesPremiereInscription
            ),
            TheseSelectFilter::NAME_discipline               => new TheseSelectFilter(
                "Discipline",
                TheseSelectFilter::NAME_discipline,
                $disciplines,
                ['width' => '200px', 'liveSearch' => true]
            ),
            TheseTextFilter::NAME_text                       => new TheseTextFilter(
                "Recherche de texte",
                TheseTextFilter::NAME_text
            ),
        ];

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

        $etatsThese = $this->fetchEtatsTheseOptions();
        $etablissements = $this->fetchEtablissementsOptions();
        $ecolesDoctorales = $this->fetchEcolesDoctoralesOptions();
        $unitesRecherches = $this->fetchUnitesRecherchesOptions();
        $anneesPremiereInscription = $this->fetchAnneesInscriptionOptions();
        $disciplines = $this->fetchDisciplinesOptions();

        $this->filters = [
            TheseSelectFilter::NAME_etatThese                => new TheseSelectFilter(
                "État",
                TheseSelectFilter::NAME_etatThese,
                $etatsThese
            ),
            TheseSelectFilter::NAME_etablissement            => new TheseSelectFilter(
                "Établissement",
                TheseSelectFilter::NAME_etablissement,
                $etablissements
            ),
            TheseSelectFilter::NAME_ecoleDoctorale           => new TheseSelectFilter(
                "ED",
                TheseSelectFilter::NAME_ecoleDoctorale,
                $ecolesDoctorales,
                ['liveSearch' => true]
            ),
            TheseSelectFilter::NAME_uniteRecherche           => new TheseSelectFilter(
                "UR",
                TheseSelectFilter::NAME_uniteRecherche,
                $unitesRecherches,
                ['liveSearch' => true]
            ),
            TheseSelectFilter::NAME_anneePremiereInscription => new TheseSelectFilter(
                "1ère inscr.",
                TheseSelectFilter::NAME_anneePremiereInscription,
                $anneesPremiereInscription
            ),
            TheseSelectFilter::NAME_discipline               => new TheseSelectFilter(
                "Discipline",
                TheseSelectFilter::NAME_discipline,
                $disciplines,
                ['width' => '200px', 'liveSearch' => true]
            ),
            TheseTextFilter::NAME_text                       => new TheseTextFilter(
                "Recherche de texte",
                TheseTextFilter::NAME_text
            ),
        ];

        $this->unpopulatedOptions = false;

        return $this;
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
            ->andWhere('1 = pasHistorise(t)');

        foreach ($this->filters as $filter) {
            $filter->applyToQueryBuilder($qb);
        }

        foreach ($this->sorters as $sorter) {
            $sorter->applyToQueryBuilder($qb);
        }

        /**
         * Filtres découlant du rôle de l'utilisateur.
         */
        $this->decorateQbFromUserContext($qb);

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
                switch (true) {
                    case $identity = $this->userContextService->getIdentityLdap():
                    case $identity = $this->userContextService->getIdentityShib():
                        $userWrapper = UserWrapper::inst($identity);
                        break;
                    default:
                        throw new RuntimeException("Cas imprévu!");
                }
                $qb
                    ->join('t.acteurs', 'adt', Join::WITH, 'adt.role = :role')
                    ->join('adt.individu', 'idt', Join::WITH, 'idt.sourceCode like :idtSourceCode')
                    ->setParameter('idtSourceCode', '%::' . $userWrapper->getSupannId())
                    ->setParameter('role', $role);
            }
            // sinon role = membre jury
            // ...
        }

        elseif ($role->isStructureDependant()) {
            if ($role->isEtablissementDependant()) {
                /**
                 * On ne voit que les thèses de son établissement.
                 */
                $qb
                    ->andWhere('t.etablissement = :etab')
                    ->setParameter('etab', $role->getStructure()->getEtablissement());
            }
            elseif ($role->isEcoleDoctoraleDependant()) {
                /**
                 * On ne voit que les thèses concernant son ED.
                 */
                $qb
                    ->addSelect('ed')->join('t.ecoleDoctorale', 'ed')
                    ->andWhere('ed = :ed')
                    ->setParameter('ed', $role->getStructure()->getEcoleDoctorale());
            }
            elseif ($role->isUniteRechercheDependant()) {
                /**
                 * On ne voit que les thèses concernant son UR.
                 */
                $qb
                    ->addSelect('ur')->join('t.uniteRecherche', 'ur')
                    ->andWhere('ur = :ur')
                    ->setParameter('ur', $role->getStructure()->getUniteRecherche());
            }
        }
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

        if ($role->isEtablissementDependant()) {
            return [
                $this->optionify($role->getStructure()->getEtablissement())
            ];
        }

        /**
         * @var Etablissement[] $etablissements
         * $etablissements stocke la liste des établissements qui seront utilisés pour le filtrage
         * les critères sont les suivants:
         * - être un établissement crée par sygal (et ne pas liste les établissements de co-tutelles)
         * - ne pas être des établissements provenant de substitutions
         * - ne pas être la COMUE ... suite à l'interrogation obtenue en réunion
         */
        $etablissements = $this->getEtablissementService()->getRepository()->findAllBySource(SourceInterface::CODE_SYGAL);
        $etablissements = array_filter($etablissements, function (Etablissement $etablissement) { return count($etablissement->getStructure()->getStructuresSubstituees())==0; });
        $etablissements = array_filter($etablissements, function (Etablissement $etablissement) { return $etablissement->getSigle() != "NU";});

        $options = [];
        foreach ($etablissements as $etablissement) {
            $options[] = $this->optionify($etablissement);
        }

        return $this->addEmptyOption($options, 'Tous');
    }

    private function fetchEcolesDoctoralesOptions()
    {
        $eds = $this->ecoleDoctoraleService->getEcolesDoctorales();

        $options = [];
        foreach ($eds as $ed) {
            $options[] = $this->optionify($ed);
        }
        usort($options, function($a, $b) {
            return strcmp($a['label'], $b['label']);
        });

        return $this->addEmptyOption($options, "Toutes");
    }

    private function fetchUnitesRecherchesOptions()
    {
        $urs = $this->uniteRechercheService->getUnitesRecherches();

        $options = [];
        foreach ($urs as $ur) {
            $options[] = $this->optionify($ur);
        }
        usort($options, function($a, $b) {
            return strcmp($a['label'], $b['label']);
        });

        return $this->addEmptyOption($options, "Toutes");
    }

    private function fetchAnneesInscriptionOptions()
    {
        $role = $this->getSelectedIdentityRole();

        if ($role->isEtablissementDependant()) {
            $etablissement = $role->getStructure()->getEtablissement();
            $annees = $this->theseService->getRepository()->fetchDistinctAnneesPremiereInscription($etablissement);
        } else {
            $annees = $this->theseService->getRepository()->fetchDistinctAnneesPremiereInscription();
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
        } elseif ($value === null) {
            return ['value' => 'NULL', 'label' => $label ?: "Inconnue"];
        } elseif ($value === '') {
            return ['value' => '', 'label' => $label];
        } else {
            return ['value' => $value, 'label' => $label ?: $value];
        }
    }
}
