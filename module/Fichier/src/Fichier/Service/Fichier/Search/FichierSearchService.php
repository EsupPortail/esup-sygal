<?php

namespace Fichier\Service\Fichier\Search;

use Application\Search\SearchService;
use Application\Search\Sorter\SearchSorter;
use Doctrine\ORM\QueryBuilder;
use Fichier\Search\IdPermanentFichierSearchFilter;
use Fichier\Search\NatureFichierSearchFilter;
use Fichier\Search\NomFichierSearchFilter;
use Fichier\Search\VersionFichierSearchFilter;
use Fichier\Service\Fichier\FichierServiceAwareTrait;

class FichierSearchService extends SearchService
{
    use FichierServiceAwareTrait;

    private ?NatureFichierSearchFilter $natureFichierSearchFilter = null;
    private ?VersionFichierSearchFilter $versionFichierSearchFilter = null;

    protected function createQueryBuilder(): QueryBuilder
    {
        $qb = $this->fichierService->getRepository()->createQueryBuilder('f');
        $qb
            ->addSelect('hc')->join('f.histoCreateur', 'hc')
            ->addSelect('i')->join('hc.individu', 'i')
            ->addSelect('nf')->join('f.nature', 'nf')
            ->addSelect('vf')->leftJoin('f.version', 'vf')
            ->andWhere('f.histoDestruction is null');

        return $qb;
    }

    public function init(): void
    {
        $this->natureFichierSearchFilter = $this->getNatureFichierSearchFilter();
        $this->natureFichierSearchFilter
            ->setWhereField('nf.code')
            ->setApplyInValueOptionsConditionToQueryBuilder()
            ->setAttributes([
                'class' => 'selectpicker show-tick',
                'liveSearch' => true,
            ]);
        $this->addFilter($this->natureFichierSearchFilter);

        $this->versionFichierSearchFilter = $this->getVersionFichierSearchFilter();
        $this->versionFichierSearchFilter
            ->setWhereField('vf.code')
            ->setAttributes([
                'class' => 'selectpicker show-tick',
            ]);
        $this->addFilter($this->versionFichierSearchFilter);

        /** @var \Fichier\Search\NomFichierSearchFilter $nomFichierSearchFilter */
        $nomFichierSearchFilter = $this->searchFilterPluginManager->get(NomFichierSearchFilter::class);
        $this->addFilter($nomFichierSearchFilter);

        /** @var IdPermanentFichierSearchFilter $idPermanentFichierSearchFilter */
        $idPermanentFichierSearchFilter = $this->searchFilterPluginManager->get(IdPermanentFichierSearchFilter::class);
        $idPermanentFichierSearchFilter
            ->setLabel('Avec id permanent') // todo : transformer le filtre en select serait plus clair
            ->setUseOnlyIfChecked(true);
        $this->addFilter($idPermanentFichierSearchFilter);

        $this->addSorters([
            new SearchSorter("Id", 'id'),
            (new SearchSorter("Nature", NatureFichierSearchFilter::NAME))->setOrderByField('nf.code'),
            (new SearchSorter("Version", VersionFichierSearchFilter::NAME))->setOrderByField('vf.code'),
            new SearchSorter("Nom", 'nom', true),
            new SearchSorter("Nom original", 'nomOriginal'),
            new SearchSorter("Id permanent", 'idPermanent'),
        ]);
    }

    public function getNatureFichierSearchFilter(): NatureFichierSearchFilter
    {
        if (null === $this->natureFichierSearchFilter) {
            $this->natureFichierSearchFilter = $this->searchFilterPluginManager->get(NatureFichierSearchFilter::class);
        }
        return $this->natureFichierSearchFilter;
    }

    public function getVersionFichierSearchFilter(): VersionFichierSearchFilter
    {
        if (null === $this->versionFichierSearchFilter) {
            $this->versionFichierSearchFilter = $this->searchFilterPluginManager->get(VersionFichierSearchFilter::class);
        }
        return $this->versionFichierSearchFilter;
    }
}