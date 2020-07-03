<?php

namespace Application\Service\RapportAnnuel;

use Application\Search\Filter\Provider\SearchFilterProviderServiceAwareTrait;
use Application\Search\Filter\SelectSearchFilter;
use Application\Search\SearchService;

class RapportAnnuelSearchService extends SearchService
{
    use SearchFilterProviderServiceAwareTrait;
    use RapportAnnuelServiceAwareTrait;

    public function init()
    {
        $this->addFilters([
            $this->searchFilterProviderService->createFilterEtablissementInscr(),
            $this->searchFilterProviderService->createFilterOrigineFinancement(),
            $this->searchFilterProviderService->createFilterEcoleDoctorale(),
            $this->searchFilterProviderService->createFilterUniteRecherche(),
            $this->searchFilterProviderService->createFilterNomDoctorant(),
            $this->searchFilterProviderService->createFilterNomDirecteur(),
            $this->searchFilterProviderService->createFilterAnneeRapportAnnuelInscr(),
        ]);
        $this->addSorters([
            $this->searchFilterProviderService->createSorterEtablissementInscr()->setIsDefault(),
            $this->searchFilterProviderService->createSorterNomPrenomDoctorant(),
            $this->searchFilterProviderService->createSorterEcoleDoctorale(),
            $this->searchFilterProviderService->createSorterUniteRecherche(),
            $this->searchFilterProviderService->createSorterAnneeRapportAnnuel(),
        ]);
    }

    /**
     * @inheritDoc
     */
    public function fetchValueOptionsForSelectFilter(SelectSearchFilter $filter)
    {
        return $this->searchFilterProviderService->fetchValueOptionsForSelectFilter($filter);
    }

    /**
     * @inheritDoc
     */
    public function createQueryBuilder()
    {
        $qb = $this->rapportAnnuelService->getRepository()->createQueryBuilder('ra');
        $qb
            ->addSelect('these, f, d, i')
            ->join('ra.these', 'these')
            ->join('these.doctorant', 'd')
            ->join('d.individu', 'i')
            ->join('ra.fichier', 'f');

        return $qb;
    }
}