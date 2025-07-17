<?php

namespace Doctorant\Service\Search;

use Application\QueryBuilder\DefaultQueryBuilder;
use Doctorant\Service\DoctorantServiceAwareTrait;
use Doctrine\ORM\QueryBuilder;
use Individu\Service\IndividuServiceAwareTrait;
use Structure\Entity\Db\TypeStructure;
use Structure\Search\EcoleDoctorale\EcoleDoctoraleSearchFilterAwareTrait;
use Structure\Search\Etablissement\EtablissementInscSearchFilterAwareTrait;
use Structure\Search\Etablissement\EtablissementSearchFilter;
use Structure\Search\UniteRecherche\UniteRechercheSearchFilterAwareTrait;
use Structure\Service\Etablissement\EtablissementServiceAwareTrait;
use Structure\Service\Structure\StructureServiceAwareTrait;
use UnicaenSearch\Filter\SearchFilter;
use UnicaenSearch\Filter\SelectSearchFilter;
use UnicaenSearch\Filter\TextSearchFilter;
use UnicaenSearch\SearchService;
use UnicaenSearch\Sorter\SearchSorter;

class DoctorantSearchService extends SearchService
{
    use IndividuServiceAwareTrait;
    use DoctorantServiceAwareTrait;
    use EtablissementServiceAwareTrait;
    use StructureServiceAwareTrait;

    use EtablissementInscSearchFilterAwareTrait;
    use UniteRechercheSearchFilterAwareTrait;
    use EcoleDoctoraleSearchFilterAwareTrait;

    /**
     * @var EtablissementSearchFilter
     */
    private $etablissementTheseSearchFilter;

    protected function createQueryBuilder(): QueryBuilder
    {
        $qb = $this->doctorantService->getRepository()->createQueryBuilder('d');
        $qb
            ->addSelect('etab')->join('d.etablissement', 'etab')
            ->addSelect('i')->join('d.individu', 'i')
            ->addSelect('u')->leftJoin('i.utilisateurs', 'u')
            ->addSelect('cu')->leftJoin('i.complements', 'cu')
            ->leftJoin('d.theses', 'these')->andWhereNotHistorise('these')
            ->leftJoin('these.acteurs', 'act')->andWhereNotHistorise('act')
            ->leftJoin('act.role', 'actr')->andWhereNotHistorise('actr')
            ->leftJoin('these.ecoleDoctorale', 'ed')->andWhereNotHistorise('ed')
            ->leftJoin('these.uniteRecherche', 'ur')->andWhereNotHistorise('ur')
            ->andWhereNotHistorise('d');

        return $qb;
    }

    public function init(): void
    {
        // filtre établissement
        $etablissementInscrFilter = $this->getEtablissementInscSearchFilter()
            ->setDataProvider(function() {
                return $this->fetchEtablissements();
            })
            ->setQueryBuilderApplier(function(SearchFilter $filter, QueryBuilder $qb, string $alias = 'd') {
                $qb
                    ->andWhere($qb->expr()->orX('etab.sourceCode = :sourceCodeEtab'))
                    ->setParameter('sourceCodeEtab', $filter->getValue());
            });
        $this->addFilter($etablissementInscrFilter);

        $ecoleDoctoraleFilter = $this->getEcoleDoctoraleSearchFilter()
            ->setDataProvider(function(SelectSearchFilter $filter) {
                return $this->fetchEcolesDoctorales($filter);
            })
            ->setQueryBuilderApplier(function(SearchFilter $filter, QueryBuilder $qb, string $alias = 'these') {
                $qb
                    ->andWhere('ed.sourceCode = :sourceCodeED')
                    ->setParameter('sourceCodeED', $filter->getValue());
            });
        $this->addFilter($ecoleDoctoraleFilter);

        $uniteRechercheFilter = $this->getUniteRechercheSearchFilter()
            ->setDataProvider(function(SelectSearchFilter $filter) {
                return $this->fetchUnitesRecherches($filter);
            })
            ->setQueryBuilderApplier(function(SearchFilter $filter, QueryBuilder $qb, string $alias = 'these') {
                $qb
                    ->andWhere('ur.sourceCode = :sourceCodeUR')
                    ->setParameter('sourceCodeUR', $filter->getValue());
            });
        $this->addFilter($uniteRechercheFilter);

        // filtre textuel
        $textFilter = new TextSearchFilter("Recherche textuelle", 'text');
        $textFilter->setAttributes(['title' => "Recherche sur le nom d'usage, le prénom, l'adresse électronique ou l'INE"]);
        $textFilter->setQueryBuilderApplier(function(SearchFilter $filter, QueryBuilder $qb) {
            $entities = $this->doctorantService->getRepository()->findByText($filter->getValue());
            if (empty($entities)) {
                $qb->andWhere('0 = 1');
            } else {
                $ids = array_map(fn(array $individu) => $individu['id'], $entities);
                $qb->andWhere($qb->expr()->in('d', $ids));
            }
        });
        $this->addFilter($textFilter);

        $this->addSorters([
            (new SearchSorter("Id", 'id')),
            (new SearchSorter("Nom d'usage", 'nomUsuel'))->setOrderByField('i.nomUsuel')->setIsDefault(),
            $this->createSorterEtablissement(),
        ]);
    }

    ////////////////////////////////// Fetch /////////////////////////////////////

    private function fetchEtablissements(): array
    {
        return $this->etablissementService->getRepository()->findAllEtablissementsInscriptions(true);
    }

    private function fetchEcolesDoctorales(SelectSearchFilter $filter): array
    {
        return $this->structureService->findAllStructuresAffichablesByType(
            TypeStructure::CODE_ECOLE_DOCTORALE, 'structure.sigle');
    }

    private function fetchUnitesRecherches(SelectSearchFilter $filter): array
    {
        return $this->structureService->findAllStructuresAffichablesByType(
            TypeStructure::CODE_UNITE_RECHERCHE, 'structure.code');
    }

    /////////////////////////////////////// Sorters /////////////////////////////////////////

    /**
     * @return SearchSorter
     */
    public function createSorterEtablissement(): SearchSorter
    {
        $sorter = new SearchSorter("Établissement<br>d'inscr.", EtablissementSearchFilter::NAME);
        $sorter->setQueryBuilderApplier(
            function (SearchSorter $sorter, DefaultQueryBuilder $qb) {
                $qb->addOrderBy('etab_structure.code', $sorter->getDirection());
            }
        );

        return $sorter;
    }

    /**
     * @param EtablissementSearchFilter $etablissementTheseSearchFilter
     * @return DoctorantSearchService
     */
    public function setEtablissementTheseSearchFilter(EtablissementSearchFilter $etablissementTheseSearchFilter): DoctorantSearchService
    {
        $this->etablissementTheseSearchFilter = $etablissementTheseSearchFilter;
        return $this;
    }
}