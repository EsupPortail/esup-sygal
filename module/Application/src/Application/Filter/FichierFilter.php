<?php

namespace Application\Filter;

use Application\Entity\Db\FichierThese;
use Application\Entity\Db\FichierTheseFiltering;
use Application\Entity\Db\VersionFichier;
use Doctrine\Common\Collections\Collection;
use Zend\Filter\FilterChain;
use Zend\Filter\FilterInterface;

class FichierFilter implements FilterInterface
{
    /**
     * @return static
     */
    static public function inst()
    {
        return new static();
    }

    /**
     * Filtre la collection de fichiers.
     *
     * @param  Collection $collection
     * @return Collection
     */
    public function filter($collection)
    {
        $chain = new FilterChain();
        if ($this->estAnnexe !== null) {
            $chain->attach(FichierTheseFiltering::getFilterByAnnexe($this->estAnnexe));
        }
        if ($this->estExpurge !== null) {
            $chain->attach(FichierTheseFiltering::getFilterByExpurge($this->estExpurge));
        }
        if ($this->estRetraite !== null) {
            $chain->attach(FichierTheseFiltering::getFilterByRetraitement($this->estRetraite));
        }
        if ($this->version !== null) {
            $chain->attach(FichierTheseFiltering::getFilterByVersion($this->version));
        }

        return $collection->filter(function(FichierThese $f) use ($chain) {
            return (bool) $chain->filter($f);
        });
    }

    /**
     * @var int|bool
     */
    protected $estAnnexe;

    /**
     * @var int|bool
     */
    protected $estExpurge;

    /**
     * @var int|bool|string
     */
    protected $estRetraite;

    /**
     * @var VersionFichier|string
     */
    protected $version;

    /**
     * @param bool|int $estAnnexe
     * @return FichierFilter
     */
    public function annexe($estAnnexe)
    {
        $this->estAnnexe = $estAnnexe;

        return $this;
    }

    /**
     * @param bool|int $estExpurge
     * @return FichierFilter
     */
    public function expurge($estExpurge)
    {
        $this->estExpurge = $estExpurge;

        return $this;
    }

    /**
     * @param bool|int|string $estRetraite
     * @return FichierFilter
     */
    public function retraite($estRetraite)
    {
        $this->estRetraite = $estRetraite;

        return $this;
    }

    /**
     * @param VersionFichier|string $version
     * @return FichierFilter
     */
    public function version($version)
    {
        $this->version = $version;

        return $this;
    }
}
