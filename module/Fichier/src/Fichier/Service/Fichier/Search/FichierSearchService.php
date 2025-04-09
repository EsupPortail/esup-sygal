<?php

namespace Fichier\Service\Fichier\Search;

use UnicaenSearch\SearchService;
use UnicaenSearch\Sorter\SearchSorter;
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
        $qb->addSelect('nf')->join('f.nature', 'nf');
        $qb->addSelect('vf')->join('f.version', 'vf');

        return $qb;
    }

    public function init(): void
    {
        $this->natureFichierSearchFilter = $this->getNatureFichierSearchFilter();
        $this->natureFichierSearchFilter
            ->setWhereField('nf.code')
            ->setMultiple()
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
        $this->addFilter($idPermanentFichierSearchFilter);

        $this->addSorters([
            new SearchSorter("Id", 'id'),
            new SearchSorter("Nature", 'nf.code'),
            new SearchSorter("Version", 'vf.code'),
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