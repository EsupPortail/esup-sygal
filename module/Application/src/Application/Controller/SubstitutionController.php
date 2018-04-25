<?php

namespace Application\Controller;

use Application\Entity\Db\EcoleDoctorale;
use Application\Entity\Db\Etablissement;
use Application\Entity\Db\Structure;
use Application\Entity\Db\StructureConcreteInterface;
use Application\Entity\Db\TypeStructure;
use Application\Entity\Db\UniteRecherche;
use Application\Service\EcoleDoctorale\EcoleDoctoraleServiceAwareTrait;
use Application\Service\Etablissement\EtablissementServiceAwareTrait;
use Application\Service\Structure\StructureServiceAwareTrait;
use Application\Service\UniteRecherche\UniteRechercheServiceAwareTrait;
use UnicaenApp\Exception\RuntimeException;
use Zend\View\Model\ViewModel;

class SubstitutionController extends AbstractController
{
    use EtablissementServiceAwareTrait;
    use EcoleDoctoraleServiceAwareTrait;
    use UniteRechercheServiceAwareTrait;
    use StructureServiceAwareTrait;

    public function indexAction()
    {
        $structuresSubstituees = $this->structureService->getStructuresSubstituees();
        $etablissementsSubstitues = [];
        $ecolesSubstituees = [];
        $unitesSubstituees = [];
        foreach($structuresSubstituees as $structureSubstituee) {
            /** @var StructureConcreteInterface $structureConcrete */
            $structureConcrete = $this->structureService->findStructureConcreteFromStructure($structureSubstituee);
            switch(true) {
                case $structureConcrete->getStructure()->getTypeStructure()->isEtablissement() :
                    $etablissementsSubstitues[] = $structureConcrete;
                break;
                case $structureConcrete->getStructure()->getTypeStructure()->isEcoleDoctorale() :
                    $ecolesSubstituees[] = $structureConcrete;
                break;
                case $structureConcrete->getStructure()->getTypeStructure()->isUniteRecherche() :
                    $unitesSubstituees[] = $structureConcrete;
                break;
            }
        }

        return new ViewModel([
              'etablissementsSubstitues' => $etablissementsSubstitues,
              'ecolesSubstituees' => $ecolesSubstituees,
              'unitesSubstituees' => $unitesSubstituees,
        ]);
    }

    public function creerAction()
    {
        $type = $this->params()->fromRoute('type');
        $structures = $this->structureService->getStructuresConcretesByType($type);

        /** Retrait des structures soient substituées soient substitutantes */
        $toRemove = [];
        /** @var StructureConcreteInterface $structure */
        foreach($structures as $structure) {
            if (count($structure->getStructure()->getStructuresSubstituees()) != 0) {
                $toRemove[] = $structure->getStructure();
                foreach ($structure->getStructure()->getStructuresSubstituees() as $sub) {
                    $toRemove[] = $sub;
                }

            }
        }
        /** @var Structure $remove */
        foreach($toRemove as $remove) {
            $structures = array_filter($structures, function (StructureConcreteInterface $structure) use ($remove) {
                return  $structure->getStructure()->getId() !== $remove->getId();
            });
        }

        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();

            $sources = [];
            foreach($data['sourceIds'] as $sourceId) {
                $structure = $this->structureService->findStructureById($sourceId);
                $structureConcrete = $this->structureService->findStructureConcreteFromStructure($structure);
                if ($structureConcrete === null) {
                    throw new RuntimeException("Aucune structure concrète cible trouvée avec id=$sourceId.");
                }
                $sources[] = $structureConcrete;
            }

            //creation de la structureCible adequate
            $structureCibleDataObject = $this->structureService->createStructureConcrete($type);
            $this->structureService->updateFromPostData($structureCibleDataObject, $data['cible']);

            $structureCible = $this->structureService->createStructureSubstitutions($sources, $structureCibleDataObject);
            $id = $structureCible->getStructure()->getId();
            return $this->redirect()->toRoute('substitution-modifier', ['cible' => $id], [], true);

        } else {
            $cible = new Structure();
            $structuresConcretesSubstituees = [];
        }

        $vm = new ViewModel([
            'cible' => $cible,
            'structuresConcretesSubstituees' => $structuresConcretesSubstituees,
            'structuresConcretes' => $structures,
        ]);
        $vm->setTemplate('application/substitution/modifier');

        return $vm;
    }


    public function modifierAction()
    {
        $idCible = $this->params()->fromRoute('cible');
        $structureCible = $this->structureService->findStructureSubsitutionCibleById($idCible);
        $structuresSubstituees = $structureCible->getStructuresSubstituees();

        $structuresConcretesSubstituees = [];
        foreach($structuresSubstituees as $structureSubstituee) {
            $structureConcreteSubstituee = $this->structureService->findStructureConcreteFromStructure($structureSubstituee);
            $structuresConcretesSubstituees[] = $structureConcreteSubstituee;
        }

        $structures = $this->structureService->getStructuresConcretesByType($structureCible->getTypeStructure()->getCode());

        /** Retrait des structures soient substituées soient substitutantes */
        $toRemove = [];
        /** @var StructureConcreteInterface $structure */
        foreach($structures as $structure) {
            if (count($structure->getStructure()->getStructuresSubstituees()) != 0) {
                $toRemove[] = $structure->getStructure();
                foreach ($structure->getStructure()->getStructuresSubstituees() as $sub) {
                    $toRemove[] = $sub;
                }

            }
        }
        /** @var Structure $remove */
        foreach($toRemove as $remove) {
            $structures = array_filter($structures, function (StructureConcreteInterface $structure) use ($remove) {
                return  $structure->getStructure()->getId() !== $remove->getId();
            });
        }

        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            $sources = [];
            foreach ($data['sourceIds'] as $sourceId) {
                $structure = $this->structureService->findStructureById($sourceId);
                $structureConcrete = $this->structureService->findStructureConcreteFromStructure($structure);
                if ($structureConcrete === null) {
                    throw new RuntimeException("Aucune structure concrète cible trouvée avec id=$sourceId.");
                }
                $sources[] = $structureConcrete;
            }
            $this->structureService->updateFromPostData($structureCible,$data['cible']);
            $this->structureService->updateStructureSubstitutions($sources, $structureCible);

            return $this->redirect()->toRoute(null, [],[], true);
        }



        return new ViewModel([
            'cible' => $structureCible,
            'structuresConcretes' => $structures,
            'structuresConcretesSubstituees' => $structuresConcretesSubstituees,
        ]);
    }

    public function detruireAction()
    {
        $idCible = $this->params()->fromRoute('cible');
        $structure = $this->structureService->findStructureById($idCible);
        $cible = $this->structureService->findStructureConcreteFromStructure($structure);
        $this->structureService->removeSubstitution($cible);

        return $this->redirect()->toRoute('substitution-index', [],[], true);
    }

    public function generateSourceInputAction() {
        $id = $this->params()->fromRoute('id');
        $structure = $this->structureService->findStructureById($id);
        $structureConcrete = $this->structureService->findStructureConcreteFromStructure($structure);

        return new ViewModel([
            'structure' => $structureConcrete,
        ]);
    }
}