<?php

namespace Application\Controller;

use Application\Entity\Db\Etablissement;
use Application\Entity\Db\Structure;
use Application\Service\Etablissement\EtablissementServiceAwareTrait;
use Application\Service\Structure\StructureServiceAwareTrait;
use UnicaenApp\Exception\RuntimeException;
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

    public function creerAction()
    {
        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            $sources = [];
            foreach($data['sources'] as $sourceId) {
                $etablissement = $this->etablissementService->getEtablissementById($sourceId);
                $sources[] = $etablissement;
            }

            $structureCible = new Etablissement();
            $structureCible->setCode(uniqid());
            $this->etablissementService->updateFromPostData($structureCible, $data['cible']);

//            $this->structureService->createStructureSubstitutions($sources, $structureCible);
            return $this->redirect()->toRoute('substitution-index');

//            $id = $structureCible->getStructure()->getId();
//            return $this->redirect()->toRoute('substitution-modifier', ['cible' => $id], [], true);

        } else {
            $cible = new Structure();
            $structuresConcretesSubstituees = [];
            $etablissements = $this->etablissementService->findEtablissementsNonSubstitues();
        }

        $vm = new ViewModel([
            'cible' => $cible,
            'structuresConcretesSubstituees' => $structuresConcretesSubstituees,
            'etablissements' => $etablissements,
        ]);
        $vm->setTemplate('application/substitution/modifier');

        return $vm;
    }


    public function modifierAction()
    {
        $idCible = $this->params()->fromRoute('cible');
        $structureCible = $this->structureService->findStructureSubsitutionCibleById($idCible);
        $structuresSubstituees = $structureCible->getStructuresSubstituees();

        $structureConcreteCible = $this->structureService->findStructureConcreteFromStructure($structureCible);
        $structuresConcretesSubstituees = [];
        foreach($structuresSubstituees as $structureSubstituee) {
            $structureConcreteSubstituee = $this->structureService->findStructureConcreteFromStructure($structureSubstituee);
            $structuresConcretesSubstituees[] = $structureConcreteSubstituee;
        }

        $etablissements = $this->etablissementService->findEtablissementsNonSubstitues();

        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            $sources = [];
            foreach ($data['sourceIds'] as $sourceId) {
                $etablissement = $this->etablissementService->findEtablissementByStructureId($sourceId);
                if ($etablissement === null) {
                    throw new RuntimeException("Etablissement cible non trouvé avec id=$sourceId.");
                }
                $sources[] = $etablissement;
            }
            $this->etablissementService->updateFromPostData($structureCible,$data['cible']);
            $this->structureService->updateStructureSubstitutions($sources, $structureCible);

            return $this->redirect()->toRoute(null, [],[], true);
        }



        return new ViewModel([
            'cible' => $structureCible,
            'etablissements' => $etablissements,
            'structuresConcretesSubstituees' => $structuresConcretesSubstituees,
        ]);
    }

    public function generateSourceInputAction() {
        $id = $this->params()->fromRoute('id');
        $etablissement = $this->etablissementService->findEtablissementByStructureId($id);

        return new ViewModel([
            'structure' => $etablissement,
        ]);
    }
}