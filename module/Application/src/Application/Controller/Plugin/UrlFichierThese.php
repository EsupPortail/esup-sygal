<?php

namespace Application\Controller\Plugin;

use Application\Entity\Db\Fichier;
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

    public function telechargerFichierThese(These $these, Fichier $fichier)
    {
        return $this->fromRoute('fichier/these/telecharger', [
            'these'      => $this->idify($these),
            'fichier'    => $this->idify($fichier),
            'fichierNom' => $fichier->getNom(),
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

    public function supprimerFichierThese(These $these, Fichier $fichier)
    {
        return $this->fromRoute('fichier/these/supprimer', [
            'these'      => $this->idify($these),
            'fichier'    => $this->idify($fichier),
            'fichierNom' => $fichier->getNom(),
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

    /**
     * @param These                 $these
     * @param NatureFichier|string  $nature
     * @param VersionFichier|string $version
     * @param bool                  $annexe
     * @param bool|string           $retraite
     * @param array                 $queryParams
     * @return string
     */
    public function listerFichiersThese(These $these, $nature, $version, $annexe = false, $retraite = false, array $queryParams = [])
    {
        $queryParams['nature'] = $this->idify($nature);
        $queryParams['version'] = $this->idify($version);

        if ($annexe !== null) {
            // todo: vérif provisoire
            if ($annexe && $this->idify($nature) !== NatureFichier::CODE_FICHIER_NON_PDF ||
                !$annexe && $this->idify($nature) !== NatureFichier::CODE_THESE_PDF) {
                throw new \LogicException("Incohérence annexe et nature!");
            }

            $queryParams['annexe'] = $annexe;
        }
        if ($retraite !== null) {
            $queryParams['retraite'] = $retraite;
        }

        return $this->fromRoute('fichier/these/lister',
            ['these' => $this->idify($these)],
            ['query' => $queryParams], true
        );
    }
}