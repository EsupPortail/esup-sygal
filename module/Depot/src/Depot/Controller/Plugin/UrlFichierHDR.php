<?php

namespace Depot\Controller\Plugin;

use Application\Entity\Db\Utilisateur;
use Application\Filter\IdifyFilterAwareTrait;
use Depot\Entity\Db\FichierHDR;
use Fichier\Entity\Db\Fichier;
use Fichier\Entity\Db\NatureFichier;
use Fichier\Entity\Db\VersionFichier;
use Laminas\Mvc\Controller\Plugin\Url as UrlPlugin;
use HDR\Entity\Db\HDR;

class UrlFichierHDR extends UrlPlugin
{
    use IdifyFilterAwareTrait;

    public function televerserFichierHDR(HDR $hdr)
    {
        // NB: nature et version sont transmises en POST.

        return $this->fromRoute('fichier/hdr/televerser',
            ['hdr' => $this->idify($hdr)], [], true
        );
    }

    /**
     * @param HDR $hdr
     * @param FichierHDR|Fichier $fichier
     * @return string
     */
    public function telechargerFichierHDR(HDR $hdr, $fichier)
    {
        if ($fichier instanceof FichierHDR) {
            $fichier = $fichier->getFichier();
        }

        return $this->fromRoute('fichier/hdr/telecharger', [
            'hdr'      => $this->idify($hdr),
            'fichier'    => $this->idify($fichier),
            'fichierNom' => $fichier->getNom(),
        ], [], true);
    }

    public function supprimerFichierHDR(HDR $hdr, FichierHDR $fichier)
    {
        return $this->fromRoute('fichier/hdr/supprimer', [
            'hdr'      => $this->idify($hdr),
            'fichier'    => $this->idify($fichier),
            'fichierNom' => $fichier->getFichier()->getNom(),
        ], [], true);
    }

    /**
     * @param HDR                 $hdr
     * @param NatureFichier|string  $nature
     * @param VersionFichier|string $version
     * @param array                 $queryParams
     * @return string
     */
    public function listerFichiers(HDR $hdr, $nature, $version = null, array $queryParams = [])
    {
        $queryParams['nature'] = $this->idify($nature);

        if ($version !== null) {
            $queryParams['version'] = $this->idify($version);
        }

        return $this->fromRoute('fichier/hdr/lister-fichiers',
            ['hdr' => $this->idify($hdr)],
            ['query' => $queryParams],
        true
        );
    }

    /**
     * @param HDR $hdr
     * @param Utilisateur $utilisateur
     * @return string
     */
    public function listerFichiersPreRapportByUtilisateur(HDR $hdr, Utilisateur $utilisateur)
    {
        return $this->fromRoute('soutenance_hdr/lister-rapport-presoutenance-by-utilisateur', ['hdr' => $hdr->getId(), 'utilisateur' => $utilisateur->getId()], [], true);
    }

}