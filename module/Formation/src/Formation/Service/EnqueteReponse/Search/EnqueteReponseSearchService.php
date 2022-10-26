<?php

namespace Formation\Service\EnqueteReponse\Search;

use Application\Search\Filter\SearchFilter;
use Application\Search\Filter\SelectSearchFilter;
use Application\Search\Filter\StrReducedTextSearchFilter;
use Application\Search\Filter\TextSearchFilter;
use Application\Search\SearchService;
use Application\Search\Sorter\SearchSorter;
use Doctrine\ORM\QueryBuilder;
use Formation\Entity\Db\Interfaces\HasModaliteInterface;
use Formation\Entity\Db\Repository\EnqueteReponseRepositoryAwareTrait;
use Formation\Entity\Db\Repository\EtatRepositoryAwareTrait;
use Formation\Entity\Db\Repository\FormationRepositoryAwareTrait;
use Formation\Entity\Db\Repository\SessionRepositoryAwareTrait;
use Structure\Search\Etablissement\EtablissementSearchFilter;
use Structure\Service\Etablissement\EtablissementServiceAwareTrait;

class EnqueteReponseSearchService extends SearchService
{
    use FormationRepositoryAwareTrait;
    use EtatRepositoryAwareTrait;
    use SessionRepositoryAwareTrait;
    use EnqueteReponseRepositoryAwareTrait;

    use EtablissementServiceAwareTrait;

    const NAME_libelle = 'libelle';
    const NAME_modalite = 'modalite';
    const NAME_responsable = 'responsable';
    const NAME_formateur = 'formateur';
    const NAME_site = 'site';

    protected SearchFilter $siteFilter;
    protected SearchFilter $libelleFilter;
    protected SearchFilter $responsableFilter;
    protected SearchFilter $formateurFilter;
    protected SearchFilter $modaliteFilter;

    public function __construct()
    {
        $this->siteFilter = $this->createSiteFilter();
        $this->libelleFilter = $this->createLibelleFilter();
        $this->responsableFilter = $this->createResponsableFilter();
        $this->formateurFilter = $this->createFormateurFilter();
        $this->modaliteFilter = $this->createModaliteFilter();

        $this->addFilters([
            $this->siteFilter,
            $this->responsableFilter,
            $this->formateurFilter,
            $this->libelleFilter,
            $this->modaliteFilter,
        ]);

        $this->addSorters([
            $this->createLibelleSorter()->setOrderByField('form.libelle')->setIsDefault(),
            $this->createSiteSorter(),
            $this->createResponsableSorter(),
            $this->createFormateurSorter(),
            $this->createModaliteSorter(),
        ]);
    }

    public function init()
    {
        $this->siteFilter->setDataProvider(fn() => $this->etablissementService->getRepository()->findAllEtablissementsInscriptions(true));
        $this->responsableFilter->setDataProvider(fn() => $this->formationRepository->fetchListeResponsable());
        //$this->formateurFilter->setDataProvider() : fait dans le contrôleur car dépend de la Session éventuelle.
        $this->modaliteFilter->setData(HasModaliteInterface::MODALITES);
    }

    public function createQueryBuilder(): QueryBuilder
    {
        // ATTENTION à bien sélectionner les relations utilisées par les filtres/tris et parcourues côté vue.
        return $this->enqueteReponseRepository->createQueryBuilder('er')
            ->addSelect('q, insc, sess, form, resp, site')
            ->join('er.question', 'q')
            ->join('er.inscription', 'insc')
            ->join('insc.session', 'sess')
            ->join("sess.formation", 'form')
            ->join("sess.formateurs", 'formateur')
            ->join("formateur.individu", 'indf')
            ->leftJoin("form.responsable", 'resp')
            ->leftJoin("form.site", 'site')
            ->leftJoin("site.structure", 'site_structure')->addSelect('site_structure')
            ->leftJoinStructureSubstituante('site_structure', 'site_structureSubstituante')
            ->andWhere('er.histoDestruction is null')
            ->addOrderBy('q.id');
    }

    /********************************** FILTERS ****************************************/

    public function createSiteFilter(): EtablissementSearchFilter
    {
        return EtablissementSearchFilter::newInstance()
            ->setName(self::NAME_site)
            ->setLabel("Site")
            ->setQueryBuilderApplier(function(SearchFilter $filter, QueryBuilder $qb) {
                $qb
                    ->andWhere('site.sourceCode = :sourceCodeSite OR site_structureSubstituante.sourceCode = :sourceCodeSite')
                    ->setParameter('sourceCodeSite', $filter->getValue());
            });
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

    private function createFormateurFilter(): SelectSearchFilter
    {
        $filter = new SelectSearchFilter("Formateur", self::NAME_formateur);
        $filter->setWhereField('indf');

        return $filter;
    }

    private function createModaliteFilter(): SelectSearchFilter
    {
        $filter = new SelectSearchFilter("Modalité", self::NAME_modalite);
        $filter->setWhereField('sess.modalite');

        return $filter;
    }

    private function createLibelleFilter(): TextSearchFilter
    {
        $filter = new StrReducedTextSearchFilter("Libellé formation", self::NAME_libelle);
        $filter->setUseLikeOperator()->setWhereField('form.libelle');

        return $filter;
    }


    /********************************** SORTERS ****************************************/

    public function createSiteSorter(): SearchSorter
    {
        $sorter = new SearchSorter("Site", self::NAME_site);
        $sorter->setOrderByField("site_structureSubstituante.code, site_structure.code");

        return $sorter;
    }

    private function createResponsableSorter(): SearchSorter
    {
        $sorter = new SearchSorter("Responsable", self::NAME_responsable);
        $sorter->setOrderByField("resp.nomUsuel, resp.prenom1");

        return $sorter;
    }

    private function createFormateurSorter(): SearchSorter
    {
        $sorter = new SearchSorter("Formateur", self::NAME_formateur);
        $sorter->setOrderByField("indf.nomUsuel, indf.prenom1");

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