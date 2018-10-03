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
use UnicaenApp\Service\EntityManagerAwareTrait;
use Zend\Validator\Date;
use Zend\View\Model\ViewModel;

class SubstitutionController extends AbstractController
{
    use EntityManagerAwareTrait;
    use EtablissementServiceAwareTrait;
    use EcoleDoctoraleServiceAwareTrait;
    use UniteRechercheServiceAwareTrait;
    use StructureServiceAwareTrait;

    /** Affiche l'index générale */
    public function indexAction() {
        /**  l'ajax charge la page */
        return new ViewModel();
    }

    /** Affiche l'index d'un type de structure donnée */
    public function indexStructureAction() {
        $type = $this->params()->fromRoute("type");
        $structures = $this->getStructureService()->getStructuresSubstituantes($type);

        return new ViewModel([
            'type' => $type,
            'structures' => $structures,
        ]);
    }

    public function creerAction() {
        $type = $this->params()->fromRoute('type');
        $structures = $this->getStructureService()->getStructuresSubstituableByType($type);

        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();

            // gestion des cas d'erreurs
            if (empty($data['sourceIds'])) {
                $this->flashMessenger()->addErrorMessage("Impossible de créer une substitution sans structure source !");
                return $this->redirect()->toRoute(null, [],[], true);
            }
            if (empty($data['cible']['libelle'])) {
                $this->flashMessenger()->addErrorMessage("Impossible de créer une substitution sans renseigner le libellé de la structure cible !");
                return $this->redirect()->toRoute(null, [],[], true);
            }

            $sources = [];
            foreach($data['sourceIds'] as $sourceId) {
                $structureConcrete = $this->getStructureService()->getStructuresConcreteByTypeAndStructureId($type, $sourceId);
                $sources[] = $structureConcrete;
            }

            //creation de la structureCible adequate
            $structureCibleDataObject = $this->getStructureService()->createStructureConcrete($type);
            $this->structureService->updateFromPostData($structureCibleDataObject, $data['cible']);

            $structureCible = $this->structureService->createStructureSubstitutions($sources, $structureCibleDataObject);
            $id = $structureCible->getStructure()->getId();

            $message = "La substitution <strong>".$structureCible->getLibelle()."</strong> vient d'être créée. Elle regroupe les structures : ";
            $first = true;
            foreach($sources as $source) {
                if (!$first) $message .= ", ";
                $message .= "<i>".$source->getLibelle()."</i>";
                $first = false;
            }
            $this->flashMessenger()->addSuccessMessage($message);

            return $this->redirect()->toRoute('substitution-modifier', ['cible' => $id], [], true);

        } else {
            $cible = new Structure();
            $structuresConcretesSubstituees = [];
        }

        $vm = new ViewModel([
            'title' => "Création d'une substitution (".$type.")",
            'cible' => $cible,
            'structuresConcretesSubstituees' => $structuresConcretesSubstituees,
            'structuresConcretes' => $structures,
            'type' => $type,
        ]);
        $vm->setTemplate('application/substitution/modifier');

        return $vm;
    }

    public function modifierAction()
    {
        $idCible = $this->params()->fromRoute('cible');
        $structureCible = $this->getStructureService()->findStructureSubsitutionCibleById($idCible);
        $structuresSubstituees = $structureCible->getStructuresSubstituees();

        $structuresConcretesSubstituees = [];
        foreach($structuresSubstituees as $structureSubstituee) {
            $structureConcreteSubstituee = $this->getStructureService()->findStructureConcreteFromStructure($structureSubstituee);
            $structuresConcretesSubstituees[] = $structureConcreteSubstituee;
        }

        $type=$structureCible->getTypeStructure();
        $structures = $this->getStructureService()->getStructuresSubstituableByType($type);

        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            $sources = [];
            foreach ($data['sourceIds'] as $sourceId) {
                $structureConcrete = $this->getStructureService()->getStructuresConcreteByTypeAndStructureId($type, $sourceId);
                if ($structureConcrete === null) {
                    throw new RuntimeException("Aucune structure concrète cible trouvée avec id=$sourceId.");
                }
                $sources[] = $structureConcrete;
            }
            $this->structureService->updateFromPostData($structureCible,$data['cible']);
            $this->structureService->updateStructureSubstitutions($sources, $structureCible);

            $message = "La substitution <strong>".$structureCible->getLibelle()."</strong> vient d'être mise à jour. Elle regroupe les structures : ";
            $first = true;
            foreach($sources as $source) {
                if (!$first) $message .= ", ";
                $message .= "<i>".$source->getLibelle()."</i>";
                $first = false;
            }
            $this->flashMessenger()->addSuccessMessage($message);

            return $this->redirect()->toRoute(null, [],[], true);
        }



        return new ViewModel([
            'title' => "Modification d'une substitution",
            'cible' => $structureCible,
            'structuresConcretes' => $structures,
            'structuresConcretesSubstituees' => $structuresConcretesSubstituees,
        ]);
    }

    public function detruireAction() {
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

    public function substitutionAutomatiqueAction()
    {
        $substitutionsEcolesDoctorales  = $this->checkStructure(TypeStructure::CODE_ECOLE_DOCTORALE);
        $substitutionsEtablissements    = $this->checkStructure(TypeStructure::CODE_ETABLISSEMENT);
        $substitutionsUnitesRecherches  = $this->checkStructure(TypeStructure::CODE_UNITE_RECHERCHE);

        return new ViewModel([
            'substitutionsEcolesDoctorales' => $substitutionsEcolesDoctorales,
            'substitutionsEtablissements' => $substitutionsEtablissements,
            'substitutionsUnitesRecherches' => $substitutionsUnitesRecherches,
        ]);
    }


    public function checkStructure($type)
    {
        $structures = [];
        switch($type) {
            case (TypeStructure::CODE_ECOLE_DOCTORALE):
                $structures = $this->getEcoleDoctoraleService()->getRepository()->findAll();
                break;
            case (TypeStructure::CODE_ETABLISSEMENT):
                $structures = $this->getEtablissementService()->getRepository()->findAll();
                break;
            case (TypeStructure::CODE_UNITE_RECHERCHE):
                $structures = $this->getUniteRechercheService()->getRepository()->findAll();
                break;
        }

        $dictionnaire = [];
        foreach ($structures as $structure) {
            $identifiant = explode("::", $structure->getSourceCode())[1];
            $dictionnaire[$identifiant][] = $structure;
        }

        $substitutions = [];
        foreach ($dictionnaire as $identifiant => $structures) {
            if (count($structures) >= 2) {
                $sources = [];
                $cible = null;

                foreach ($structures as $structure) {
                    $prefix = explode("::",$structure->getSourceCode())[0];
                    if ($prefix === "SyGAL" || $prefix === "COMUE") {
                        $cible = $structure;
                    } else {
                        $sources[] = $structure;
                    }
                }
                $substitutions[$identifiant] = [$sources, $cible];
            }
        }

        return $substitutions;
    }


    public function enregistrerAutomatiqueAction()
    {
        $type = $this->params()->fromRoute('type');
        $identifiant = $this->params()->fromRoute('identifiant');

        $structures = $this->getStructureService()->getStructuresBySuffixe($identifiant, $type);
        $sources = [];
        $cible = null;

        /** @var StructureConcreteInterface $structure */
        foreach ($structures as $structure) {
            $prefix = explode("::", $structure->getSourceCode())[0];
            if ($prefix === "SyGAL") {
                $cible = $structure;
            } else {
                $sources[] = $structure;
            }
        }

        if ($cible != null) $this->structureService->updateStructureSubstitutions($sources, $cible->getStructure());

        return new ViewModel();
    }

    public function modifierAutomatiqueAction()
    {
        $type           = $this->params()->fromRoute('type');
        $identifiant    = $this->params()->fromRoute('identifiant');

        $structures = $this->getStructureService()->getStructuresBySuffixe($identifiant, $type);
        $sources = [];
        $cible = null;

        /** @var StructureConcreteInterface $structure */
        foreach ($structures as $structure) {
            $prefix = explode("::",$structure->getSourceCode())[0];
            if ($prefix === "SyGAL") {
                $cible = $structure;
            } else {
                $sources[] = $structure;
            }
        }


        $structures = $this->structureService->getStructuresConcretes($type);
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

            if ($cible === null) {
                $cible = $this->getStructureService()->createStructureConcrete($type);
                $this->structureService->updateFromPostData($cible,$data['cible']);
                $cible->setSourceCode("SyGAL" . "::" . $identifiant);
                $this->getEntityManager()->persist($cible);
                $this->getEntityManager()->flush($cible);
            } else {
                $this->structureService->updateFromPostData($cible,$data['cible']);
            }
            $this->structureService->updateStructureSubstitutions($sources, $cible->getStructure());



            $message = "La substitution <strong>".$cible->getLibelle()."</strong> vient d'être mise à jour. Elle regroupe les structures : ";
            $first = true;
            foreach($sources as $source) {
                if (!$first) $message .= ", ";
                $message .= "<i>".$source->getLibelle()."</i>";
                $first = false;
            }
            $this->flashMessenger()->addSuccessMessage($message);

            return $this->redirect()->toRoute(null, [],[], true);
        }

        if ($cible === null) {
            $cible = $this->getStructureService()->createStructureConcrete($type);
            $cible->setSourceCode("SyGAL" . "::" . $identifiant);
        }

        $vm = new ViewModel();
        $vm->setVariables([
            'title' => "Substitution de structures",
            'type' => $type,
            'cible' => $cible->getStructure(),
            'identifiant' => $identifiant,
            'structuresConcretes' => $structures,
            'structuresConcretesSubstituees' => $sources,
        ]);
        $vm->setTemplate('application/substitution/modifier');
        return $vm;

    }

    public function afficherAutomatiqueAction()
    {
        $type           = $this->params()->fromRoute('type');
        $identifiant    = $this->params()->fromRoute('identifiant');

        $structures = $this->getStructureService()->getStructuresBySuffixe($identifiant, $type);
        $sources = [];
        $cible = null;

        /** @var StructureConcreteInterface $structure */
        foreach ($structures as $structure) {
            $prefix = explode("::",$structure->getSourceCode())[0];
            if ($prefix === "SyGAL") {
                $cible = $structure;
            } else {
                $sources[] = $structure;
            }
        }

        return new ViewModel([
            'substituees' => $sources,
            'substituante' => $cible,
            'type' => $type,
            'identifiant' => $identifiant,
        ]);
    }
}