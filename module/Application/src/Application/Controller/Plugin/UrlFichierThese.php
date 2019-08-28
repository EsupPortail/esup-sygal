<?php

namespace Application\Controller\Plugin;

use Application\Entity\Db\FichierThese;
use Application\Entity\Db\NatureFichier;
use Application\Entity\Db\These;
use Application\Entity\Db\VersionFichier;
use Application\Filter\IdifyFilter;
use Application\Filter\IdifyFilterAwareTrait;
use Zend\Mvc\Controller\Plugin\Url as UrlPlugin;

class UrlFichierThese extends UrlPlugin
{
    use IdifyFilterAwareTrait;

    public function televerserFichierThese(These $these)
    {
        // NB: nature et version sont transmises en POST.

        return $this->fromRoute('fichier/these/televerser',
            ['these' => $this->idify($these)], [], true
        );
    }

    public function telechargerFichierThese(These $these, FichierThese $fichier)
    {
        return $this->fromRoute('fichier/these/telecharger', [
            'these'      => $this->idify($these),
            'fichier'    => $this->idify($fichier),
            'fichierNom' => $fichier->getFichier()->getNom(),
        ], [], true);
    }

    public function apercevoirPageDeCouverture(These $these, array $queryParams = [])
    {
        return $this->fromRoute('fichier/these/apercevoir-page-de-couverture', [
            'these'      => $this->idify($these),
        ], [
            'query' => $queryParams,
        ], true);
    }

    public function supprimerFichierThese(These $these, FichierThese $fichier)
    {
        return $this->fromRoute('fichier/these/supprimer', [
            'these'      => $this->idify($these),
            'fichier'    => $this->idify($fichier),
            'fichierNom' => $fichier->getFichier()->getNom(),
        ], [], true);
    }

    /**
     * @param These                 $these
     * @param NatureFichier|string  $nature
     * @param VersionFichier|string $version
     * @param int|bool|string       $retraite
     * @param array                 $queryParams
     * @return string
     */
    public function listerFichiers(These $these, $nature, $version = null, $retraite = false, array $queryParams = [])
    {
        $queryParams['nature'] = $this->idify($nature);

        if ($version !== null) {
            $queryParams['version'] = $this->idify($version);
        }
        if ($retraite !== null) {
            $queryParams['retraite'] = $retraite;
        }

        return $this->fromRoute('fichier/these/lister-fichiers',
            ['these' => $this->idify($these)],
            ['query' => $queryParams],
        true
        );
    }
}