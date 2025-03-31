<?php

namespace Structure\View\Helper;

use Application\View\Renderer\PhpRenderer;
use Laminas\View\Helper\AbstractHelper;
use Laminas\View\Resolver\TemplatePathStack;
use Structure\Entity\Db\EcoleDoctorale;
use Structure\Entity\Db\Etablissement;
use Structure\Entity\Db\Structure;
use Structure\Entity\Db\StructureInterface;
use Structure\Entity\Db\UniteRecherche;

class StructureViewHelper extends AbstractHelper
{
    public function __invoke(Structure|Etablissement|EcoleDoctorale|UniteRecherche $structure = null, bool $afficherLibelle = true, bool $sansOmbre = false): string
    {
        /** @var PhpRenderer $view */
        $view = $this->getView();
        $view->resolver()->attach(new TemplatePathStack(['script_paths' => [__DIR__ . "/partial"]]));

        if($structure instanceof Etablissement){
            $typeStructureClass = "etablissement";
            $structureCode = $structure->getStructure()->getSourceCode();
            $structureLibelle = $structure?->getStructure()->getLibelle();
        }else if($structure instanceof UniteRecherche){
            $typeStructureClass = "ur";
            $structureCode = $structure->getStructure()->getCode();
            $structureLibelle = $structure;
        }else if($structure instanceof EcoleDoctorale){
            $typeStructureClass = "ed";
            $structureCode = $structure->getStructure()->getSigle();
            $structureLibelle = $structure;
        }elseif($structure instanceof Structure){
            $typeStructureClass = "structure";
            $structureCode = $structure->getSigle();
            $structureLibelle = $structure;
        }else{
            $typeStructureClass = "";
            $structureCode = "";
            $structureLibelle = "";
        }

        return $this->view->partial('structure.phtml', [
            'typeStructureClass' => $typeStructureClass,
            'structureLibelle' => $structureLibelle,
            'structureCode' => $structureCode,
            'afficherLibelle' => $afficherLibelle,
            'sansOmbre' => $sansOmbre
        ]);
    }
}