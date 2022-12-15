<?php

namespace Depot\Controller\Plugin;

use Application\Entity\Db\Utilisateur;
use Application\Filter\IdifyFilterAwareTrait;
use Depot\Entity\Db\FichierThese;
use Fichier\Entity\Db\Fichier;
use Fichier\Entity\Db\NatureFichier;
use Fichier\Entity\Db\VersionFichier;
use Laminas\Mvc\Controller\Plugin\Url as UrlPlugin;
use These\Entity\Db\These;

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

    /**
     * @param These $these
     * @param FichierThese|Fichier $fichier
     * @return string
     */
    public function telechargerFichierThese(These $these, $fichier)
    {
        if ($fichier instanceof FichierThese) {
            $fichier = $fichier->getFichier();
        }

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

    /**
     * @param These $these
     * @param Utilisateur $utilisateur
     * @return string
     */
    public function listerFichiersPreRapportByUtilisateur(These $these, Utilisateur $utilisateur)
    {
        return $this->fromRoute('soutenance/lister-rapport-presoutenance-by-utilisateur', ['these' => $these->getId(), 'utilisateur' => $utilisateur->getId()], [], true);
    }

}