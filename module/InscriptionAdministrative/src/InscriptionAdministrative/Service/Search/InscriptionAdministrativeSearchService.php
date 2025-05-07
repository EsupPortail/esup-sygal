<?php

namespace InscriptionAdministrative\Service\Search;

use Application\QueryBuilder\DefaultQueryBuilder;
use UnicaenSearch\Filter\CheckboxSearchFilter;
use UnicaenSearch\Filter\SearchFilter;
use UnicaenSearch\Filter\SelectSearchFilter;
use UnicaenSearch\Filter\StrReducedTextSearchFilter;
use UnicaenSearch\Filter\TextSearchFilter;
use UnicaenSearch\SearchService;
use UnicaenSearch\Sorter\SearchSorter;
use Doctrine\ORM\QueryBuilder;
use InscriptionAdministrative\Service\InscriptionAdministrativeServiceAwareTrait;
use InvalidArgumentException;
use Structure\Entity\Db\TypeStructure;
use Structure\Search\EcoleDoctorale\EcoleDoctoraleSearchFilter;
use Structure\Search\Etablissement\EtablissementSearchFilter;
use Structure\Service\Etablissement\EtablissementServiceAwareTrait;
use Structure\Service\Structure\StructureServiceAwareTrait;
use These\Service\TheseAnneeUniv\TheseAnneeUnivServiceAwareTrait;
use UnicaenApp\Exception\LogicException;
use UnicaenApp\Exception\RuntimeException;
use UnicaenApp\Util;
use Webmozart\Assert\Assert;

class InscriptionAdministrativeSearchService extends SearchService
{
    use InscriptionAdministrativeServiceAwareTrait;
    use StructureServiceAwareTrait;
    use TheseAnneeUnivServiceAwareTrait;
    use EtablissementServiceAwareTrait;

    const NAME_apprenant = 'apprenant';

    private ?EcoleDoctoraleSearchFilter $ecoleDoctoraleSearchFilter;

    public function __construct()
    {
        $this->ecoleDoctoraleSearchFilter = new EcoleDoctoraleSearchFilter();
    }

    public function init(): void
    {
        $this->ecoleDoctoraleSearchFilter
            ->setQueryBuilderApplier(function (SelectSearchFilter $filter, DefaultQueryBuilder $qb) {
                $qb
                    ->andWhere('ed.sourceCode = :sourceCodeED')
                    ->setParameter('sourceCodeED', $filter->getValue());
            });

        $this->ecoleDoctoraleSearchFilter->setDataProvider(fn() => $this->fetchEcolesDoctorales());

        $this->addFilters(array_filter([
            $this->ecoleDoctoraleSearchFilter,
            $this->createFilterApprenant(),
        ]));

        // filtre "sans lien avec un doctorant"
        $sansDoctorantFilter = new CheckboxSearchFilter("Non lié à un<br>doctorant", 'sansDoctorant');
        $sansDoctorantFilter->setQueryBuilderApplier(function(SearchFilter $filter, QueryBuilder $qb) {
            if ($filter->getValue() === true) {
                $qb->andWhere('iad IS NULL');
            }
        });
        $this->addFilter($sansDoctorantFilter);
    }

    public function createQueryBuilder(): QueryBuilder
    {
        return $this->inscriptionAdministrativeService->getRepository()->createQueryBuilder('ia')
            ->orderBy('ia.histoCreation', 'desc');
    }

    private function fetchEcolesDoctorales(): array
    {
        return $this->structureService->findAllStructuresAffichablesByType(
            TypeStructure::CODE_ECOLE_DOCTORALE, 'structure.sigle'
        );
    }

    private function createFilterApprenant(): TextSearchFilter
    {
        $filter = new StrReducedTextSearchFilter(
            "Apprenant",
            self::NAME_apprenant
        );

        $filter->useLikeOperator()->setWhereField('iaa.haystack');

        return $filter;
    }

    public function getEcoleDoctoraleSearchFilter(): EcoleDoctoraleSearchFilter
    {
        return $this->ecoleDoctoraleSearchFilter;
    }
}