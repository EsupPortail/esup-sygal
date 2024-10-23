<?php

namespace Formation\Service\Session\Search;

use Application\Entity\AnneeUniv;
use Application\QueryBuilder\DefaultQueryBuilder;
use Application\Search\Filter\SearchFilter;
use Application\Search\Filter\SelectSearchFilter;
use Application\Search\Filter\StrReducedTextSearchFilter;
use Application\Search\Filter\TextSearchFilter;
use Application\Search\SearchService;
use Application\Search\Sorter\SearchSorter;
use Application\Service\AnneeUniv\AnneeUnivServiceAwareTrait;
use Doctrine\ORM\QueryBuilder;
use Formation\Entity\Db\Etat;
use Formation\Entity\Db\Interfaces\HasModaliteInterface;
use Formation\Entity\Db\Interfaces\HasTypeInterface;
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
    use AnneeUnivServiceAwareTrait;

    use EtablissementServiceAwareTrait;

    const NAME_libelle = 'libelle';
    const NAME_modalite = 'modalite';
    const NAME_etat = 'etat';
    const NAME_responsable = 'responsable';
    const NAME_site = 'site';
    const NAME_structure = 'structure';
    const NAME_anneeUniv = 'anneeUniv';
    const NAME_type = 'type';
    const NAME_seances = 'seances';


    public function init()
    {
        $siteFilter = $this->createSiteFilter();
        $structureFilter = $this->createStructureFilter();
        $etatFilter = $this->createEtatFilter();//->setWhereField('s.etat');
        $libelleFilter = $this->createLibelleFilter()->setWhereField('formation.libelle');
        $responsableFilter = $this->createResponsableFilter();
        $modaliteFilter = $this->createModaliteFilter();
        $anneeUnivFilter = $this->createAnneeUnivFilter();
        $typeFilter = $this->createTypeFilter();

        $siteFilter->setDataProvider(fn() => $this->etablissementService->getRepository()->findAllEtablissementsInscriptions(true));
        $structureFilter->setDataProvider(fn() => $this->formationRepository->fetchListeStructures());
        $responsableFilter->setDataProvider(fn() => $this->formationRepository->fetchListeResponsable());
        $modaliteFilter->setData(HasModaliteInterface::MODALITES);
        $etatFilter->setDataProvider(fn() => array_combine(
            array_map(fn(Etat $e) => $e->getCode(), $etats = $this->etatRepository->findBy([], ['ordre' => 'asc'])),
            $etats
        ));
        $anneeUnivFilter->setDataProvider(function(SelectSearchFilter $filter) {
            return $this->fetchAnneesUniv($filter);
        });
        $typeFilter->setData(HasTypeInterface::TYPES);

        $annee = $this->anneeUnivService->courante();
        $debut = $this->anneeUnivService->computeDateDebut($annee);

        $this->addFilters([
            $siteFilter,
            $responsableFilter,
            $structureFilter,
            $libelleFilter,
            $etatFilter,
            $modaliteFilter,
            $anneeUnivFilter->setAllowsNoneOption()->setAllowsEmptyOption(false)->setDefaultValue($debut->format('Y')),
            $typeFilter
        ]);

        $this->addSorters([
            $this->createLibelleSorter(),
            $this->createSiteSorter(),
            $this->createStructureSorter(),
            $this->createResponsableSorter(),
            $this->createEtatSorter(),
            $this->createModaliteSorter(),
            $this->createSeancesSorter(),
        ]);

        //tri descendant par les dates des séances (lorsqu'aucun sorter n'est sélectionné)
        $this->addInvisibleSort('CASE WHEN seance.fin IS NULL THEN 1 ELSE 0 END', 'ASC');
        $this->addInvisibleSort('seance.debut', 'DESC');
    }

    public function createQueryBuilder(): QueryBuilder
    {
        return $this->sessionRepository->createQueryBuilder('s');
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

    private function createLibelleFilter(): TextSearchFilter
    {
        $filter = new StrReducedTextSearchFilter("Libellé", self::NAME_libelle);
        $filter->useLikeOperator();

        return $filter;
    }

    private function createTypeFilter(): SelectSearchFilter
    {
        $filter = new SelectSearchFilter("Type", self::NAME_type);
        $filter->setQueryBuilderApplier(function(SelectSearchFilter $filter, DefaultQueryBuilder $qb) {
            $qb
                ->andWhere("s.type = :type")
                ->setParameter('type', $filter->getValue());
        });

        return $filter;
    }

    /********************************** FILTERS ****************************************/

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

    private function createSeancesSorter(): SearchSorter
    {
        $sorter = new SearchSorter("Séances", self::NAME_seances);
        $sorter->setQueryBuilderApplier(function (SearchSorter $sorter, QueryBuilder $qb){
            $qb->addOrderBy('seance.debut', $sorter->getDirection());
        });
        return $sorter;
    }
}