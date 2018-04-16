<?php

namespace Application\Controller;

use Application\Service\Etablissement\EtablissementServiceAwareTrait;
use Application\Service\Structure\StructureServiceAwareTrait;
use Zend\View\Model\ViewModel;

class SubstitutionController extends AbstractController
{
    use EtablissementServiceAwareTrait;
    use StructureServiceAwareTrait;

    public function indexAction()
    {
        $cibles = $this->structureService->findStructuresSubstitutions();
        $etablissements = $this->etablissementService->getEtablissements();

        return new ViewModel([
            'cibles' => $cibles,
            'etablissements' => $etablissements,
        ]);
    }

//    public function ajouterAction() {
//        $idCible = $this->params()->fromRoute('cible');
//        $idAjout = $this->params()->fromRoute('ajout');
//
//        $liste = $this->structure
//
//        $this->redirect()->toRoute('substitution-selection', ['cible' => $idCible]);
//    }
//
//    public function retirerAction() {
//        $idCible = $this->params()->fromRoute('cible');
//        $idAjout = $this->params()->fromRoute('ajout');
//
//        //HERE DO THE STUFF
//
//        $this->redirect()->toRoute('substitution-selection', ['cible' => $idCible]);
//    }

    public function selectionAction()
    {

        $idGeneralisation = $this->params()->fromRoute('generalisation');
        $idEtablissements = explode(",",$this->params()->fromRoute('etablissements'));
        $idAjout = $this->params()->fromQuery('nouveau');
        $idRetrait = $this->params()->fromQuery('retrait');

        $etablissements = $this->etablissementService->getEtablissements();
        $etablissement = $this->etablissementService->getEtablissementById($idGeneralisation);
        $selection = [];
        foreach($idEtablissements as $idEtablissement) {
            $selection[] = $this->etablissementService->getEtablissementById($idEtablissement);
        }
        if($idAjout !== null && $idAjout !== '') {
            $selection[] = $this->etablissementService->getEtablissementById($idAjout);
        }
        if($idRetrait !== null && $idRetrait !== '') {
            $selection = array_filter($selection, function($a) use ($idRetrait) {return $a->getId() != $idRetrait;});
//            $selection_bis = [];
//            foreach ($selection as $s) {
//                if ($s->getId() != ($idRetrait)) $selection_bis[] = $s;
//            }
//            $selection = $selection_bis;
        }
        return new ViewModel([
            'nouvelEtablissement' => $etablissement,
            'selectedEtablissements' => $selection,
            'etablissements' => $etablissements,
        ]);
    }
}