<?php

namespace Fichier\Controller\Plugin;

use Application\Filter\IdifyFilterAwareTrait;
use Fichier\Entity\Db\Fichier;
use Laminas\Mvc\Controller\Plugin\Url as UrlPlugin;

class UrlFichier extends UrlPlugin
{
    use IdifyFilterAwareTrait;

    public function telechargerFichier(Fichier $fichier): string
    {
        return $this->fromRoute('fichier/telecharger', [
            'fichier' => $this->idify($fichier),
            'fichierNom' => $fichier->getNom(),
        ], [], true);
    }

    public function telechargerPermanentFichier(Fichier $fichier): string
    {
        return $this->fromRoute('fichier/telecharger-permanent', [
            'idPermanent' => $fichier->getIdPermanent(),
        ], [], true);
    }

    public function telechargerPermanentId($idPermanent): string
    {
        return $this->fromRoute('fichier/telecharger-permanent', [
            'idPermanent' => $idPermanent,
        ], [], true);
    }

    public function afficherFichier(Fichier $fichier): string
    {
        return $this->fromRoute('fichier/afficher', [
            'fichier' => $fichier->getUuid(),
        ], [], true);
    }

    public function modifierFichier(Fichier $fichier): string
    {
        return $this->fromRoute('fichier/modifier', [
            'fichier' => $fichier->getUuid(),
        ], [], true);
    }

    public function supprimerFichier(Fichier $fichier): string
    {
        return $this->fromRoute('fichier/supprimer', [
            'fichier' => $this->idify($fichier),
            'fichierNom' => $fichier->getNom(),
        ], [], true);
    }

    public function listerFichiersCommuns(): string
    {
        return $this->fromRoute('fichier/lister-fichiers-communs', [], [], true);
    }

    public function televerserFichiersCommuns(): string
    {
        return $this->fromRoute('fichier/televerser-fichiers-communs', [], [], true);
    }
}