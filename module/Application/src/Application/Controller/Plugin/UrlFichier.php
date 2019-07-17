<?php

namespace Application\Controller\Plugin;

use Application\Entity\Db\Fichier;
use Application\Filter\IdifyFilterAwareTrait;
use Zend\Mvc\Controller\Plugin\Url as UrlPlugin;

class UrlFichier extends UrlPlugin
{
    use IdifyFilterAwareTrait;

    public function telechargerFichier(Fichier $fichier)
    {
        return $this->fromRoute('fichier/telecharger', [
            'fichier'    => $this->idify($fichier),
            'fichierNom' => $fichier->getNom(),
        ], [], true);
    }

    public function supprimerFichier(Fichier $fichier)
    {
        return $this->fromRoute('fichier/supprimer', [
            'fichier'    => $this->idify($fichier),
            'fichierNom' => $fichier->getNom(),
        ], [], true);
    }

    public function listerFichiersCommuns()
    {
        return $this->fromRoute('fichier/lister-fichiers-communs', [], [], true);
    }

    public function televerserFichiersCommuns()
    {
        return $this->fromRoute('fichier/televerser-fichiers-communs', [], [], true);
    }
}