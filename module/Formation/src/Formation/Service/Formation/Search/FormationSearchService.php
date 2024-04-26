<?php

namespace Formation\Service\Formation\Search;

use Application\Entity\AnneeUniv;
use Application\QueryBuilder\DefaultQueryBuilder;
use Application\Search\Filter\SearchFilter;
use Application\Search\Filter\SelectSearchFilter;
use Application\Search\Filter\TextSearchFilter;
use Application\Search\SearchService;
use Application\Search\Sorter\SearchSorter;
use Application\Service\AnneeUniv\AnneeUnivServiceAwareTrait;
use Doctrine\ORM\QueryBuilder;
use Formation\Entity\Db\Interfaces\HasModaliteInterface;
use Formation\Entity\Db\Repository\FormationRepositoryAwareTrait;
use Structure\Search\Etablissement\EtablissementSearchFilter;
use Structure\Service\Etablissement\EtablissementServiceAwareTrait;

class FormationSearchService extends SearchService
{
    use FormationRepositoryAwareTrait;

    use EtablissementServiceAwareTrait;
    use AnneeUnivServiceAwareTrait;

    const NAME_libelle = 'libelle';
    const NAME_modalite = 'modalite';
    const NAME_responsable = 'responsable';
    const NAME_site = 'site';
    const NAME_structure = 'structure';
    const NAME_anneeUniv = 'anneeUniv';


    public function init()
    {
        $siteFilter = $this->createSiteFilter();
        $structureFilter = $this->createStructureFilter();
        $libelleFilter = $this->createLibelleFilter();
        $responsableFilter = $this->createResponsableFilter();
        $modaliteFilter = $this->createModaliteFilter();
        $anneeUnivFilter = $this->createAnneeUnivFilter();

        $siteFilter->setDataProvider(fn() => $this->etablissementService->getRepository()->findAllEtablissementsInscriptions(true));
        $structureFilter->setDataProvider(fn() => $this->formationRepository->fetchListeStructures());
        $responsableFilter->setDataProvider(fn() => $this->formationRepository->fetchListeResponsable());
        $modaliteFilter->setData(HasModaliteInterface::MODALITES);
        $anneeUnivFilter->setDataProvider(function(SelectSearchFilter $filter) {
            return $this->fetchAnneesUniv($filter);
        });

        $annee = $this->anneeUnivService->courante();
        $debut = $this->anneeUnivService->computeDateDebut($annee);

        $this->addFilters([
            $siteFilter,
            $responsableFilter,
            $structureFilter,
            $libelleFilter,
            $modaliteFilter,
            $anneeUnivFilter->setAllowsNoneOption()->setAllowsEmptyOption(false)->setDefaultValue($debut->format('Y')),
        ]);

        $this->addSorters([
            $this->createLibelleSorter(),
            $this->createSiteSorter(),
            $this->createStructureSorter(),
            $this->createResponsableSorter(),
            $this->createModaliteSorter(),
        ]);

        //tri descendant par les dates des séances (lorsqu'aucun sorter n'est sélectionné)
        $this->addInvisibleSort('CASE WHEN seance.fin IS NULL THEN 1 ELSE 0 END', 'ASC');
        $this->addInvisibleSort('seance.debut', 'DESC');
    }

    public function createQueryBuilder(): QueryBuilder
    {
        // ATTENTION à bien sélectionner les relations utilisées par les filtres/tris et parcourues côté vue.
        $queryBuilder = $this->formationRepository->createQueryBuilder('f');
        $queryBuilder->leftJoin('f.sessions', 'session')->addSelect('session')
            ->leftJoin('session.seances', 'seance')->addSelect('seance');
        return $queryBuilder;
    }

    /********************************** FILTERS ****************************************/

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
        $filter->setQueryBuilderApplier(function(SelectSearchFilter $filter, DefaultQueryBuilder $qb) {
            $qb
                ->andWhere("struct = :structure")
                ->setParameter('structure', $filter->getValue());
        });

        return $filter;
    }

    private function createResponsableFilter(): SelectSearchFilter
    {
        $filter = new SelectSearchFilter("Responsable", self::NAME_responsable);
        $filter->setQueryBuilderApplier(function(SelectSearchFilter $filter, QueryBuilder $qb) {
            $qb
                ->andWhere("resp = :responsable")
                ->setParameter('responsable', $filter->getValue());
        });

        return $filter;
    }

    private function createModaliteFilter(): SelectSearchFilter
    {
        $filter = new SelectSearchFilter("Modalité", self::NAME_modalite);
        $filter->setWhereField('f.modalite');

        return $filter;
    }

    private function createLibelleFilter(): TextSearchFilter
    {
        $filter = new TextSearchFilter("Libellé", self::NAME_libelle);
        $filter->setQueryBuilderApplier(function (SearchFilter $filter, QueryBuilder $qb, string $alias = 'f') {
            $filterValue = $filter->getValue();
            if ($filterValue !== null && strlen($filterValue) > 1) {
                $qb
                    ->andWhere("strReduce($alias.libelle) LIKE strReduce(:text)")
                    ->setParameter('text', '%' . $filterValue . '%');
            }
        });

        return $filter;
    }

    /**
     * @return SelectSearchFilter
     */
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

    /********************************** FILTERS ****************************************/

    private function fetchAnneesUniv(SelectSearchFilter $filter): array
    {
        $formations = $this->formationRepository->findAll();
        $anneesUniv = [];
        foreach ($formations as $formation) {
            $sessions = $formation->getSessions()->toArray();
            foreach ($sessions as $session) {
                $anneeUniv = $session->getDateDebut() ? $this->anneeUnivService->fromDate($session->getDateDebut()) : $this->anneeUnivService->courante();
                if(!isset($anneesUniv[$anneeUniv->getPremiereAnnee()])) $anneesUniv[$anneeUniv->getPremiereAnnee()] = $anneeUniv->getAnneeUnivToString();
            }
        }

        return $anneesUniv;
    }

    /********************************** SORTERS ****************************************/

    public function createSiteSorter(): SearchSorter
    {
        $sorter = new SearchSorter("Site", self::NAME_site);
        $sorter->setOrderByField("site_structure.code");

        return $sorter;
    }

    private function createStructureSorter(): SearchSorter
    {
        $sorter = new SearchSorter("Structure associée", self::NAME_structure);
        $sorter->setOrderByField("struct.libelle");

        return $sorter;
    }

    private function createResponsableSorter(): SearchSorter
    {
        $sorter = new SearchSorter("Responsable", self::NAME_responsable);
        $sorter->setOrderByField("resp.nomUsuel, resp.prenom1");

        return $sorter;
    }

    private function createModaliteSorter(): SearchSorter
    {
        return new SearchSorter("Modalité", self::NAME_modalite);
        // $sorter->setOrderByField() inutile car self::NAME_modalite === nom de l'attribut d'entité.
    }

    private function createLibelleSorter(): SearchSorter
    {
        return new SearchSorter("Libellé", self::NAME_libelle);
        // $sorter->setOrderByField() inutile car self::NAME_libelle === nom de l'attribut d'entité.
    }
}