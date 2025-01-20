<?php

namespace Formation\Service\Inscription\Search;

use Application\Entity\AnneeUniv;
use Application\Search\Filter\SearchFilter;
use Application\Search\Filter\SelectSearchFilter;
use Application\Search\Filter\StrReducedTextSearchFilter;
use Application\Search\Filter\TextSearchFilter;
use Application\Search\SearchService;
use Application\Search\Sorter\SearchSorter;
use Application\Service\AnneeUniv\AnneeUnivServiceAwareTrait;
use Doctrine\ORM\QueryBuilder;
use Formation\Entity\Db\Etat;
use Formation\Entity\Db\Inscription;
use Formation\Entity\Db\Repository\EtatRepositoryAwareTrait;
use Formation\Entity\Db\Repository\FormationRepositoryAwareTrait;
use Formation\Entity\Db\Repository\InscriptionRepositoryAwareTrait;
use Formation\Entity\Db\Repository\SessionRepositoryAwareTrait;
use Individu\Service\IndividuServiceAwareTrait;
use Structure\Search\Etablissement\EtablissementSearchFilter;
use Structure\Service\Etablissement\EtablissementServiceAwareTrait;

class InscriptionSearchService extends SearchService
{
    use InscriptionRepositoryAwareTrait;
    use EtatRepositoryAwareTrait;
    use IndividuServiceAwareTrait;
    use FormationRepositoryAwareTrait;
    use EtablissementServiceAwareTrait;
    use AnneeUnivServiceAwareTrait;
    use SessionRepositoryAwareTrait;

    const NAME_libelle = 'libelle';
    const NAME_doctorant = 'doctorant';
    const NAME_liste = 'liste';
    const NAME_etat = 'etat';
    const NAME_individu = 'individu';
    const NAME_seances = 'seances';
    const NAME_structure = 'structure';
    const NAME_site = 'site';
    const NAME_anneeUniv = 'anneeUniv';


    public function init()
    {
        $libelleFilter = $this->createLibelleFilter();
        $doctorantFilter = $this->createDoctorantFilter();
        $siteFilter = $this->createSiteFilter();
        $siteFilter->setDataProvider(fn() => $this->etablissementService->getRepository()->findAllEtablissementsInscriptions(true));
        $structureFilter = $this->createStructureFilter();
        $structureFilter->setDataProvider(fn() => $this->formationRepository->fetchListeStructures());
        $listeFilter = $this->createListeFilter();
        $etatFilter = $this->createEtatFilter();//->setWhereField('s.etat');
        $etatFilter->setDataProvider(fn() => array_combine(
            array_map(fn(Etat $e) => $e->getCode(), $etats = $this->etatRepository->findBy([], ['ordre' => 'asc'])),
            $etats
        ));
        $anneeUnivFilter = $this->createAnneeUnivFilter();
        $anneeUnivFilter->setDataProvider(function(SelectSearchFilter $filter) {
            return $this->fetchAnneesUniv($filter);
        });

        $listeFilter->setData(Inscription::LISTES);

        $annee = $this->anneeUnivService->courante();
        $debut = $this->anneeUnivService->computeDateDebut($annee);

        $this->addFilters([
            $siteFilter,
            $structureFilter,
            $libelleFilter,
            $doctorantFilter,
            $etatFilter,
            $anneeUnivFilter->setAllowsNoneOption()->setAllowsEmptyOption(false)->setDefaultValue($debut->format('Y')),
            $listeFilter,
        ]);

        $this->addSorters([
            $this->createLibelleSorter(),
            $this->createSiteSorter(),
            $this->createDoctorantSorter(),
            $this->createListeSorter(),
            $this->createSeancesSorter(),
        ]);

        $this->addInvisibleSort("i.id", 'DESC');
    }

    public function createQueryBuilder(): QueryBuilder
    {
        // ATTENTION à bien sélectionner les relations utilisées par les filtres/tris et parcourues côté vue.
        return $this->inscriptionRepository->createQueryBuilder('i')
            ->addSelect('sess, doct, ind, seance, these, ecoleDoct, site, site_structure')
            ->leftJoin('i.session', 'sess')
            ->leftJoin('sess.seances', 'seance')
            ->leftJoin('sess.site', 'site')
            ->leftJoin('site.structure', 'site_structure')
            ->join('i.doctorant', 'doct')
            ->join('sess.formation', 'form')
            ->join('doct.individu', 'ind')
            ->join('doct.theses', 'these')
            ->join('these.ecoleDoctorale', 'ecoleDoct');
    }

    /********************************** FILTERS ****************************************/

    private function createDoctorantFilter(): TextSearchFilter
    {
        $filter = new TextSearchFilter("Doctorant", self::NAME_doctorant);
        $filter->setAttributes(['title' => "Recherche sur le nom d'usage, le prénom"]);
        $filter->setQueryBuilderApplier(function(SearchFilter $filter, QueryBuilder $qb) {
            $individus = $this->individuService->getRepository()->findByText($filter->getValue());
            if (empty($individus)) {
                $qb->andWhere('0 = 1');
            } else {
                $individusIds = array_map(fn(array $individu) => $individu['id'], $individus);
                $qb->andWhere($qb->expr()->in('ind.id', $individusIds));
            }
        });

        return $filter;
    }

    public function createSiteFilter(): EtablissementSearchFilter
    {
        return EtablissementSearchFilter::newInstance()
            ->setName(self::NAME_site)
            ->setLabel("Site")
            ->setQueryBuilderApplier(function(SearchFilter $filter, QueryBuilder $qb) {
                $qb
                    ->andWhere('site.sourceCode = :sourceCodeSite')
                    ->setParameter('sourceCodeSite', $filter->getValue());
            });
    }

    private function createStructureFilter(): SelectSearchFilter
    {
        $filter = new SelectSearchFilter("Structure associée", self::NAME_structure);
        $filter->setQueryBuilderApplier(function(SelectSearchFilter $filter, QueryBuilder $qb) {
            $qb
                ->andWhere("ecoleDoct.structure = :structure")
                ->setParameter('structure', $filter->getValue());
        });

        return $filter;
    }

    private function createEtatFilter(): SelectSearchFilter
    {
        $filter = new SelectSearchFilter("État", self::NAME_etat);
        $filter->setWhereField('sess.etat');

        return $filter;
    }

    private function createListeFilter(): SelectSearchFilter
    {
        $filter = new SelectSearchFilter("Liste", self::NAME_liste);
        $filter
            ->setAllowsNoneOption()
            ->setNoneOptionLabel("Aucune")
            ->setWhereField('i.liste');

        return $filter;
    }

    private function createLibelleFilter(): TextSearchFilter
    {
        $filter = new StrReducedTextSearchFilter("Libellé de la session", self::NAME_libelle);
        $filter
            ->useLikeOperator()
            ->setWhereField('form.libelle');

        return $filter;
    }

    private function createAnneeUnivFilter(): SelectSearchFilter
    {
        $filter = new SelectSearchFilter(
            "An. univ.",
            self::NAME_anneeUniv
        );
        $filter->setQueryBuilderApplier(function(SearchFilter $filter, QueryBuilder $qb, string $alias = 'session') {
            $filterValue = $filter->getValue();
            $annee = $filterValue === 'NULL' ? $this->anneeUnivService->courante() : AnneeUniv::fromPremiereAnnee((int)$filterValue);
            $debut = $this->anneeUnivService->computeDateDebut($annee);
            $fin = $this->anneeUnivService->computeDateFin($annee);
            if($filterValue !== 'NULL'){
                if ($debut !== null && $fin !== null) {
                    $qb->andWhere('seance.debut >= :debut')->setParameter('debut', $debut)
                        ->andWhere('seance.fin <= :fin')->setParameter('fin', $fin);
                }
            }
        });

        return $filter;
    }

    private function fetchAnneesUniv(SelectSearchFilter $filter): array
    {
        $sessions = $this->sessionRepository->findAll();
        $anneesUniv = [];
        foreach ($sessions as $session) {
            $anneeUniv = $session->getDateDebut() ? $this->anneeUnivService->fromDate($session->getDateDebut()) : $this->anneeUnivService->courante();
            if(!isset($anneesUniv[$anneeUniv->getPremiereAnnee()])) $anneesUniv[$anneeUniv->getPremiereAnnee()] = $anneeUniv->getAnneeUnivToString();
        }

        uksort($anneesUniv, function($a, $b) {
            return $a <=> $b;
        });

        return $anneesUniv;
    }

    /********************************** SORTERS ****************************************/

    private function createLibelleSorter(): SearchSorter
    {
        $sorter = new SearchSorter("Session", self::NAME_libelle);
        $sorter->setOrderByField('sess.libelle');

        return $sorter;
    }

    private function createDoctorantSorter(): SearchSorter
    {
        $sorter = new SearchSorter("Individu", self::NAME_individu);
        $sorter->setOrderByField("ind.nomUsuel");

        return $sorter;
    }

    private function createListeSorter(): SearchSorter
    {
        $sorter = new SearchSorter("Liste", self::NAME_libelle);
        $sorter->setOrderByField("i.liste");

        return $sorter;
    }

    private function createSeancesSorter(): SearchSorter
    {
        $sorter = new SearchSorter("Séances", self::NAME_seances);
        $sorter->setQueryBuilderApplier(function (SearchSorter $sorter, QueryBuilder $qb){
            $qb->addOrderBy('seance.debut', $sorter->getDirection());
        });
        return $sorter;
    }

    public function createSiteSorter(): SearchSorter
    {
        $sorter = new SearchSorter("Site", self::NAME_site);
        $sorter->setOrderByField("site_structure.code");

        return $sorter;
    }
}