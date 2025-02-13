<?php

namespace HDR\Service;

use Application\Entity\Db\Role;
use Application\QueryBuilder\DefaultQueryBuilder;
use Application\Search\Filter\SearchFilter;
use Application\Search\Filter\SelectSearchFilter;
use Application\Search\SearchService;
use Application\Search\Sorter\SearchSorter;
use Application\Service\UserContextServiceAwareTrait;
use Doctrine\ORM\QueryBuilder;
use HDR\Entity\Db\HDR;
use HDR\Search\HDR\EtatHDRSearchFilterAwareTrait;
use Structure\Entity\Db\TypeStructure;
use Structure\Search\EcoleDoctorale\EcoleDoctoraleSearchFilter;
use Structure\Search\EcoleDoctorale\EcoleDoctoraleSearchFilterAwareTrait;
use Structure\Search\Etablissement\EtablissementInscSearchFilterAwareTrait;
use Structure\Search\Etablissement\EtablissementSearchFilter;
use Structure\Search\UniteRecherche\UniteRechercheSearchFilter;
use Structure\Search\UniteRecherche\UniteRechercheSearchFilterAwareTrait;
use Structure\Service\EcoleDoctorale\EcoleDoctoraleServiceAwareTrait;
use Structure\Service\Etablissement\EtablissementServiceAwareTrait;
use Structure\Service\Structure\StructureServiceAwareTrait;
use Structure\Service\UniteRecherche\UniteRechercheServiceAwareTrait;

class HDRSearchService extends SearchService
{
    use EtablissementInscSearchFilterAwareTrait;
    use UniteRechercheSearchFilterAwareTrait;
    use EcoleDoctoraleSearchFilterAwareTrait;
    use EtatHDRSearchFilterAwareTrait;

    use UserContextServiceAwareTrait;
    use HDRServiceAwareTrait;
    use EtablissementServiceAwareTrait;
    use UniteRechercheServiceAwareTrait;
    use EcoleDoctoraleServiceAwareTrait;
    use StructureServiceAwareTrait;

    const NAME_etatHDR = 'etatHDR';
    const NAME_anneeSoutenance = 'anneeSoutenance';

    const SORTER_NAME_candidat = 'candidat';
    const SORTER_NAME_dateSoutenance = 'date';

    /**
     * @var Role|null
     */
    private $role;

    /**
     * @inheritDoc
     */
    protected function createQueryBuilder(): QueryBuilder
    {
        $qb = $this->hdrService->getRepository()->createQueryBuilder('hdr');
        $qb
            ->addSelect('etab')->leftJoin('hdr.etablissement', 'etab')
            ->addSelect('ed')->leftJoin('hdr.ecoleDoctorale', 'ed')
            ->addSelect('ur')->leftJoin('hdr.uniteRecherche', 'ur')
            ->addSelect('ca')->leftJoin('hdr.candidat', 'ca')
            ->addSelect('di')->leftJoin('ca.individu', 'di')
            ->addSelect('a')->leftJoin('hdr.acteurs', 'a')
            ->addSelect('i')->leftJoin('a.individu', 'i')
            ->addSelect('r')->leftJoin('a.role', 'r')
            ->addSelect('am')->leftJoin('a.membre', 'am') // réduit le nombre de requêtes car a.membre est un one-to-one
            ->addSelect('prop')->leftJoin('hdr.propositionsHDR', 'prop') // réduit le nombre de requêtes car a.membre est un one-to-one
            ->andWhereNotHistorise('hdr');

        return $qb;
    }

    /**
     * @inheritDoc
     */
    public function init()
    {
        $etablissementInscrFilter = $this->getEtablissementInscSearchFilter()
            ->setDataProvider(function(SelectSearchFilter $filter) {
                return $this->fetchEtablissements($filter);
            })
            ->setQueryBuilderApplier(function(SearchFilter $filter, QueryBuilder $qb, string $alias = 'hdr') {
                $qb
                    ->andWhere('etab.sourceCode = :sourceCodeEtab')
                    ->setParameter('sourceCodeEtab', $filter->getValue());
            });
        $ecoleDoctoraleFilter = $this->getEcoleDoctoraleSearchFilter()
            ->setDataProvider(function(SelectSearchFilter $filter) {
                return $this->fetchEcolesDoctorales($filter);
            })
            ->setQueryBuilderApplier(function(SearchFilter $filter, QueryBuilder $qb, string $alias = 'hdr') {
                $qb
                    ->andWhere('ed.sourceCode = :sourceCodeED')
                    ->setParameter('sourceCodeED', $filter->getValue());
            });
        $uniteRechercheFilter = $this->getUniteRechercheSearchFilter()
            ->setDataProvider(function(SelectSearchFilter $filter) {
                return $this->fetchUnitesRecherches($filter);
            })
            ->setQueryBuilderApplier(function(SearchFilter $filter, QueryBuilder $qb, string $alias = 'hdr') {
                $qb
                    ->andWhere('ur.sourceCode = :sourceCodeUR')
                    ->setParameter('sourceCodeUR', $filter->getValue());
            });
        $etatHDRSearchFilter = $this->getEtatHDRSearchFilter()
            ->setDataProvider(function(SelectSearchFilter $filter) {
                return $this->fetchEtatsHDR($filter);
            });
        $anneesSoutenanceFilter = $this->createFilterAnneesSoutenance()
            ->setDataProvider(function(SelectSearchFilter $filter) {
                return $this->fetchAnneesSoutenance($filter);
            });

        $this->addFilters([
            $etatHDRSearchFilter,
            $etablissementInscrFilter,
            $ecoleDoctoraleFilter,
            $uniteRechercheFilter,
            $anneesSoutenanceFilter,
        ]);
        $this->addSorters([
            $this->createSorterEtablissement(),
            $this->createSorterUniteRecherche(),
            $this->createSorterDoctorant(),
            $this->createSorterDateSoutenance(),
        ]);
    }

    /**
     * @param SearchSorter $sorter
     * @param QueryBuilder $qb
     */
    public function applySorterToQueryBuilder(SearchSorter $sorter, QueryBuilder $qb)
    {
        // todo: permettre la spécification de l'alias Doctrine à utiliser via $sorter->getAlias() ?
        $alias = 'hdr';

        $name = $sorter->getName();
        $direction = $sorter->getDirection();

        switch ($name) {

            case self::SORTER_NAME_candidat:
                $qb
                    ->addOrderBy('di.nomUsuel', $direction)
                    ->addOrderBy('di.prenom1', $direction);
                break;

            case self::SORTER_NAME_dateSoutenance:
                $qb
                    ->addOrderBy("prop.date", $direction);
                break;

            default:
                throw new \InvalidArgumentException("Cas imprévu");
        }
    }
    
    ////////////////////////////////// Fetch /////////////////////////////////////

    private function fetchEtatsHDR(SelectSearchFilter $filter): array
    {
        return [
            $v = HDR::ETAT_EN_COURS => HDR::$etatsLibelles[$v],
            $v = HDR::ETAT_ABANDONNEE => HDR::$etatsLibelles[$v],
            $v = HDR::ETAT_SOUTENUE => HDR::$etatsLibelles[$v],
        ];
    }

    private function fetchEtablissements(SelectSearchFilter $filter): array
    {
        return $this->etablissementService->getRepository()->findAllEtablissementsInscriptions(true);
    }

    private function fetchEcolesDoctorales(SelectSearchFilter $filter): array
    {
        return $this->structureService->findAllStructuresAffichablesByType(
            TypeStructure::CODE_ECOLE_DOCTORALE, 'structure.code');
    }

    private function fetchUnitesRecherches(SelectSearchFilter $filter): array
    {
        return $this->structureService->findAllStructuresAffichablesByType(
            TypeStructure::CODE_UNITE_RECHERCHE, ['structure.sigle', 'structure.libelle']);
    }

    private function fetchAnneesSoutenance(SelectSearchFilter $filter): array
    {
        $role = $this->getSelectedIdentityRole();

        if ($role && $role->isEtablissementDependant()) {
            $etablissement = $role->getStructure()->getEtablissement();
            $annees = $this->hdrService->getRepository()->fetchDistinctAnneesSoutenance($etablissement);
        } else {
            $annees = $this->hdrService->getRepository()->fetchDistinctAnneesSoutenance();
        }

        $annees = array_reverse(array_filter($annees));

        return array_combine($annees, $annees);
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
    private function createFilterAnneesSoutenance(): SelectSearchFilter
    {
        $filter = new SelectSearchFilter(
            "Soutenance",
            self::NAME_anneeSoutenance
        );
        $filter->setQueryBuilderApplier(function(SearchFilter $filter, QueryBuilder $qb, string $alias = 'hdr') {
            $filterValue = $filter->getValue();
            if ($filterValue === 'NULL') {
                $qb
                    ->andWhere("prop.date IS NULL");
            } else {
                $qb
                    ->andWhere("year(prop.date) = :anneeSoutenance")
                    ->setParameter('anneeSoutenance', $filterValue);
            }
        });
        return $filter;
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

    public function createSorterUniteRecherche(): SearchSorter
    {
        $sorter = new SearchSorter("Unité recherche", UniteRechercheSearchFilter::NAME);
        $sorter->setQueryBuilderApplier(
            function (SearchSorter $sorter, DefaultQueryBuilder $qb) {
                $qb->addOrderBy('ur_structure.code', $sorter->getDirection());
            }
        );

        return $sorter;
    }

    /**
     * @return SearchSorter
     */
    private function createSorterDoctorant(): SearchSorter
    {
        $sorter = new SearchSorter(
            "",
            HDRSorter::NAME_candidat
        );
        $sorter->setQueryBuilderApplier([$this, 'applySorterToQueryBuilder']);
        return $sorter;
    }

    /**
     * @return SearchSorter
     */
    private function createSorterDateSoutenance(): SearchSorter
    {
        $sorter = new SearchSorter(
            "",
            HDRSorter::NAME_dateSoutenance
        );
        $sorter->setQueryBuilderApplier(
            function (SearchSorter $sorter, DefaultQueryBuilder $qb) {
                $qb->addOrderBy('prop.date', $sorter->getDirection());
            }
        );
        return $sorter;
    }
}
