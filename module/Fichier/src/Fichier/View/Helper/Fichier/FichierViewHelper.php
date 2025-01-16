<?php /** @noinspection PhpUnusedAliasInspection */


namespace Fichier\View\Helper\Fichier;

use Application\View\Renderer\PhpRenderer;
use Fichier\Entity\Db\Fichier;
use Formation\Entity\Db\Inscription;
use Laminas\View\Helper\AbstractHelper;
use Laminas\View\Helper\Partial;
use Laminas\View\Resolver\TemplatePathStack;

class FichierViewHelper extends AbstractHelper
{
    /**
     * Génère la représentation d'un fichier dans la vue.
     *
     * @param Fichier $object                   L'entité Fichier à afficher.
     * @param string $urlFichier                L'URL pour accéder au fichier.
     * @param string $urlSuppressionFichier     L'URL pour supprimer le fichier.
     * @param bool $canGererFichier             Indique si l'utilisateur peut gérer le fichier.
     * @param string $libelleOptionnel          Un libellé optionnel à afficher (à la place du nom du fichier).
     * @param bool $seeHistoInfo                Indique si les informations concernant l'historique (Nom du créateur, date de création) doivent être affichées.
     * @param array $options                    Options supplémentaires pour le rendu.
     *
     * @return string                           Le rendu HTML du fichier.
     */
    public function __invoke(Fichier $object, string $urlFichier, string $urlSuppressionFichier, bool $canGererFichier, string $libelleOptionnel = "", bool $seeHistoInfo = true, array $options = [])
    {
        /** @var PhpRenderer $view */
        $view = $this->getView();
        $view->resolver()->attach(new TemplatePathStack(['script_paths' => [__DIR__ . "/partial"]]));

        return $view->partial('fichier', [
            'fichier' => $object,
            'urlFichier' => $urlFichier,
            'urlSuppressionFichier' => $urlSuppressionFichier,
            'canGererFichier' => $canGererFichier,
            'libelleOptionnel' => $libelleOptionnel,
            'seeHistoInfo' => $seeHistoInfo,
            'options' => $options]
        );
    }
}