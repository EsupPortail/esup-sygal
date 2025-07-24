<?php

namespace Doctorant\View\Helper;

use Application\View\Renderer\PhpRenderer;
use Doctorant\Entity\Db\MissionEnseignement;
use Laminas\View\Helper\AbstractHelper;
use Laminas\View\Resolver\TemplatePathStack;
use These\Entity\Db\These;

class MissionEnseignementViewHelper extends AbstractHelper
{
    /**
     * Génère la représentation d'une mission d'enseignement dans la vue.
     *
     * @param MissionEnseignement $object  entité MissionEnseignement
     * @param These $these entité These
     * @param array{
     *      canMissionGerer: bool,
     *      canEditThese: bool,
     *  } $options Options d'affichage supplémentaires.
     *
     * @return string   Le rendu HTML de la mission d'enseignement.
     */
    public function __invoke(MissionEnseignement $object, These $these, array $options = [])
    {
        $missionEnseignement = $object;

        /** @var PhpRenderer $view */
        $view = $this->getView();
        $view->resolver()->attach(new TemplatePathStack(['script_paths' => [__DIR__ . "/partial"]]));

        return $view->partial('missionEnseignement', ['missionEnseignement' => $missionEnseignement, 'these' => $these, 'options' => $options]);
    }

    public function __toString()
    {
        return '<div>Mission d\'enseignement par défaut</div>';
    }
}