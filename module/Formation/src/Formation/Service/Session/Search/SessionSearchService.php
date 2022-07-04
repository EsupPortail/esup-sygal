<?php

namespace Formation\Service\Session\Search;

use Application\Search\Filter\SelectSearchFilter;
use Application\Search\Filter\StrReducedTextSearchFilter;
use Application\Search\Filter\TextSearchFilter;
use Application\Search\SearchService;
use Application\Search\Sorter\SearchSorter;
use Doctrine\ORM\QueryBuilder;
use Formation\Entity\Db\Etat;
use Formation\Entity\Db\Interfaces\HasModaliteInterface;
use Formation\Entity\Db\Repository\EtatRepositoryAwareTrait;
use Formation\Entity\Db\Repository\FormationRepositoryAwareTrait;
use Formation\Entity\Db\Repository\SessionRepositoryAwareTrait;
use Structure\Search\Etablissement\EtablissementSearchFilter;
use Structure\Service\Etablissement\EtablissementServiceAwareTrait;

class SessionSearchService extends SearchService
{
    use FormationRepositoryAwareTrait;
    use EtatRepositoryAwareTrait;
    use SessionRepositoryAwareTrait;

    use EtablissementServiceAwareTrait;

    const NAME_libelle = 'libelle';
    const NAME_modalite = 'modalite';
    const NAME_etat = 'etat';
    const NAME_responsable = 'responsable';
    const NAME_site = 'site';
    const NAME_structure = 'structure';

    public function init()
    {
        $siteFilter = $this->createSiteFilter();
        $structureFilter = $this->createStructureFilter();
        $etatFilter = $this->createEtatFilter();//->setWhereField('s.etat');
        $libelleFilter = $this->createLibelleFilter()->setWhereField('form.libelle');
        $responsableFilter = $this->createResponsableFilter();
        $modaliteFilter = $this->createModaliteFilter();

        $siteFilter->setDataProvider(fn() => $this->etablissementService->getRepository()->findAllEtablissementsInscriptions(true));
        $structureFilter->setDataProvider(fn() => $this->formationRepository->fetchListeStructures());
        $responsableFilter->setDataProvider(fn() => $this->formationRepository->fetchListeResponsable());
        $modaliteFilter->setData(HasModaliteInterface::MODALITES);
        $etatFilter->setDataProvider(fn() => array_combine(
            array_map(fn(Etat $e) => $e->getCode(), $etats = $this->etatRepository->findBy([], ['ordre' => 'asc'])),
            $etats
        ));

        $this->addFilters([
            $siteFilter,
            $responsableFilter,
            $structureFilter,
            $libelleFilter,
            $etatFilter,
            $modaliteFilter,
        ]);

        $this->addSorters([
            $this->createLibelleSorter()->setOrderByField('form.libelle')->setIsDefault(),
            $this->createSiteSorter(),
            $this->createStructureSorter(),
            $this->createResponsableSorter(),
            $this->createEtatSorter(),
            $this->createModaliteSorter(),
        ]);
    }

    public function createQueryBuilder(): QueryBuilder
    {
        // ATTENTION à bien sélectionner les relations utilisées par les filtres/tris et parcourues côté vue.
        return $this->sessionRepository->createQueryBuilder('s')
            ->addSelect('form, resp, site, struct')
            ->join('s.formation', 'form')
            ->join("s.responsable", 'resp')
            ->join("s.site", 'site')
            ->leftJoin("s.typeStructure", 'struct');
    }

    /********************************** FILTERS ****************************************/

    public function createSiteFilter(): EtablissementSearchFilter
    {
        return EtablissementSearchFilter::newInstance()
            ->setName(self::NAME_site)
            ->setLabel("Site")
            ->setWhereField('site.sourceCode'); // cf. `join("f.site", 'site')` fait dans `createQueryBuilder()`
    }

    private function createStructureFilter(): SelectSearchFilter
    {
        $filter = new SelectSearchFilter("Structure associée", self::NAME_structure);
        $filter->setQueryBuilderApplier(
            function(SelectSearchFilter $filter, QueryBuilder $qb, string $alias = 'f') {
                $qb
                    ->andWhere("struct = :structure")
                    ->setParameter('structure', $filter->getValue());
            }
        );

        return $filter;
    }

    private function createResponsableFilter(): SelectSearchFilter
    {
        $filter = new SelectSearchFilter("Responsable", self::NAME_responsable);
        $filter->setQueryBuilderApplier(
            function(SelectSearchFilter $filter, QueryBuilder $qb) {
                $qb
                    ->andWhere("resp = :responsable")
                    ->setParameter('responsable', $filter->getValue());
            }
        );

        return $filter;
    }

    private function createEtatFilter(): SelectSearchFilter
    {
        return new SelectSearchFilter("État", self::NAME_etat);
    }

    private function createModaliteFilter(): SelectSearchFilter
    {
        $filter = new SelectSearchFilter("Modalité", self::NAME_modalite);
        $filter->setWhereField('s.modalite');

        return $filter;
    }

    private function createLibelleFilter(): TextSearchFilter
    {
        $filter = new StrReducedTextSearchFilter("Libellé", self::NAME_libelle);
        $filter->setUseLikeOperator();

        return $filter;
    }


    /********************************** SORTERS ****************************************/

    /**
     * @return SearchSorter
     */
    public function createSiteSorter(): SearchSorter
    {
        $sorter = new SearchSorter("Site", EtablissementSearchFilter::NAME);
        $sorter->setQueryBuilderApplier(
            function (SearchSorter $sorter, QueryBuilder $qb) {
                $qb
                    ->join('site.structure', 's_sort')
                    ->addOrderBy('s_sort.code', $sorter->getDirection());
            }
        );

        return $sorter;
    }

    private function createStructureSorter(): SearchSorter
    {
        $sorter = new SearchSorter("Structure associée", self::NAME_structure);
        $sorter->setQueryBuilderApplier([$this, 'applyStructureSorter']);

        return $sorter;
    }

    public function applyStructureSorter(SearchSorter $sorter, QueryBuilder $qb, string $alias = 'f')
    {
        $qb->addOrderBy("struct.libelle", $sorter->getDirection());
    }

    private function createResponsableSorter(): SearchSorter
    {
        $sorter = new SearchSorter("Responsable", self::NAME_responsable);
        $sorter->setOrderByField("resp.nomUsuel");

        return $sorter;
    }

    private function createEtatSorter(): SearchSorter
    {
        return new SearchSorter("État", self::NAME_etat);
        // $sorter->setOrderByField() inutile car self::NAME_etat === nom de l'attribut d'entité.
    }

    private function createModaliteSorter(): SearchSorter
    {
        return new SearchSorter("Modalité", self::NAME_modalite);
        // $sorter->setOrderByField() inutile car self::NAME_modalite === nom de l'attribut d'entité.
    }

    private function createLibelleSorter(): SearchSorter
    {
        return new SearchSorter("Libellé", self::NAME_libelle);
    }
}