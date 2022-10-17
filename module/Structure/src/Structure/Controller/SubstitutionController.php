<?php

namespace Structure\Controller;

use Application\Controller\AbstractController;
use Application\SourceCodeStringHelperAwareTrait;
use Laminas\Http\Response;
use Laminas\View\Model\ViewModel;
use Structure\Entity\Db\Structure;
use Structure\Entity\Db\StructureConcreteInterface;
use Structure\Entity\Db\TypeStructure;
use Structure\Service\EcoleDoctorale\EcoleDoctoraleServiceAwareTrait;
use Structure\Service\Etablissement\EtablissementServiceAwareTrait;
use Structure\Service\Structure\StructureServiceAwareTrait;
use Structure\Service\UniteRecherche\UniteRechercheServiceAwareTrait;
use UnicaenApp\Service\EntityManagerAwareTrait;

class SubstitutionController extends AbstractController
{
    use EntityManagerAwareTrait;
    use EtablissementServiceAwareTrait;
    use EcoleDoctoraleServiceAwareTrait;
    use UniteRechercheServiceAwareTrait;
    use StructureServiceAwareTrait;
    use SourceCodeStringHelperAwareTrait;

    /** Affiche l'index générale */
    public function indexAction()
    {
        /**  l'ajax charge la page */
        return new ViewModel();
    }

    /** Affiche l'index d'un type de structure donnée */
    public function indexStructureAction()
    {
        $type = $this->params()->fromRoute("type");
        $structures = $this->getStructureService()->getStructuresSubstituantes($type, 'libelle');

        return new ViewModel([
            'type' => $type,
            'structures' => $structures,
        ]);
    }

    /**
     * Créer une substitution manuelle
     */
    public function creerAction()
    {
        $type = $this->params()->fromRoute('type');
        $structures = $this->getStructureService()->getStructuresSubstituablesByType($type);

        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();

            // gestion des cas d'erreurs
            if (empty($data['sourceIds'])) {
                $this->flashMessenger()->addErrorMessage("Impossible de créer une substitution sans structure source !");
                return $this->redirect()->toRoute(null, [], [], true);
            }
            if (empty($data['cible']['libelle'])) {
                $this->flashMessenger()->addErrorMessage("Impossible de créer une substitution sans renseigner le libellé de la structure cible !");
                return $this->redirect()->toRoute(null, [], [], true);
            }

            // récupération des structures sources
            $sources = [];
            foreach ($data['sourceIds'] as $sourceId) {
                $structureConcrete = $this->getStructureService()->getStructureConcreteByTypeAndStructureId($type, $sourceId);
                $sources[] = $structureConcrete;
            }

            //creation de la structureCible adequate
            $structureCibleDataObject = $this->getStructureService()->createStructureConcrete($type);
            $this->structureService->updateFromPostData($structureCibleDataObject, $data['cible']);
            $structureCible = $this->structureService->createStructureSubstitutions($sources, $structureCibleDataObject);

            if ($data['cible']['code'] !== null && $data['cible']['code'] !== '') {
                $sourceCode = $this->sourceCodeStringHelper->addDefaultPrefixTo($data['cible']['code']);
                $structureCible->getStructure()->setSourceCode($sourceCode);
                $structureCible->getStructure()->setCode($data['cible']['code']);
                $this->structureService->updateStructureSubstitutions($sources, $structureCible->getStructure());
            }

            $message = "La structure substituante <strong>$structureCible</strong> a été créée. Elle regroupe les structures de type '$type' suivantes : ";
            $message .= implode(", ", array_map(fn(StructureConcreteInterface $s) => sprintf("<i>%s (%d)</i>", $s, $s->getId()), $sources));
            $this->flashMessenger()->addSuccessMessage($message);

            return $this->redirect()->toRoute('substitution-modifier', ['cible' => $structureCible->getStructure()->getId()], [], true);

        } else {
            $cible = new Structure();
            $structuresConcretesSubstituees = [];
        }

        $vm = new ViewModel([
            'cible' => $cible,
            'structuresConcretesSubstituees' => $structuresConcretesSubstituees,
            'structuresConcretes' => $structures,
            'type' => $type,
            'structureCibleLogoContent' => $this->structureService->getLogoStructureContent(),
        ]);
        $vm->setTemplate('structure/substitution/modifier');

        return $vm;
    }

    /** Éditer une substitution manuelle */
    public function modifierAction()
    {
        $idCible = $this->params()->fromRoute('cible');
        $structureCible = $this->getStructureService()->findStructureSubsitutionCibleById($idCible);
        $type = $structureCible->getTypeStructure();

        $structuresConcretesSubstituees = $structureCible->getStructuresConcretesSubstituees()->toArray();
        $structures = $this->getStructureService()->getStructuresSubstituablesByType($type);

        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();

            // récupération des structures sources
            $sources = [];
            foreach ($data['sourceIds'] as $sourceId) {
                $structureConcrete = $this->getStructureService()->getStructureConcreteByTypeAndStructureId($type, $sourceId);
                $sources[] = $structureConcrete;
            }

            //mise à jour de la structureCible adequate
            $this->structureService->updateFromPostData($structureCible, $data['cible']);
            $this->structureService->updateStructureSubstitutions($sources, $structureCible);

            $message = "La structure substituante <strong>$structureCible</strong> a été mise à jour. Elle regroupe les structures de type '$type' suivantes : ";
            $message .= implode(", ", array_map(fn(StructureConcreteInterface $s) => sprintf("<i>%s (%d)</i>", $s->getStructure(false)->getLibelle(), $s->getId()), $sources));
            $this->flashMessenger()->addSuccessMessage($message);

            return $this->redirect()->toRoute(null, [], [], true);
        }

        return new ViewModel([
            'cible' => $structureCible,
            'structuresConcretes' => $structures,
            'structuresConcretesSubstituees' => $structuresConcretesSubstituees,
            'structureCibleLogoContent' => $this->structureService->getLogoStructureContent($structureCible),
            'structuresConcretesSubstitueesLogosContents' => array_map(function (StructureConcreteInterface $structureConcreteSubstituee) {
                return $this->structureService->getLogoStructureContent($structureConcreteSubstituee->getStructure(false));
            }, $structuresConcretesSubstituees),
        ]);
    }

    /** Fonction de destruction */
    public function detruireAction(): Response
    {
        $idCible = $this->params()->fromRoute('cible');
        $structure = $this->structureService->findStructureById($idCible);
        $this->structureService->removeSubstitution($structure->getStructureConcrete());

        return $this->redirect()->toRoute('substitution-index', [], [], true);
    }

    /** Fonction appelée pour construire la div associée à une structure source */
    public function generateSourceInputAction()
    {
        $id = $this->params()->fromRoute('id');
        $structure = $this->structureService->findStructureById($id);
        $structureConcrete = $this->structureService->findStructureConcreteFromStructure($structure);

        return new ViewModel([
            'structure' => $structureConcrete,
            'structureSourceLogoContent' => $this->structureService->getLogoStructureContent($structureConcrete->getStructure(false)),
        ]);
    }

    /** Fonction principale des substitutions (appelle checkStructure) */
    public function substitutionAutomatiqueAction(): ViewModel
    {
        $typeStructure = ($code = $this->params()->fromRoute('type')) ? $this->structureService->fetchTypeStructure($code) : null;

        if ($typeStructure !== null) {
            $substitutionsEcolesDoctorales = $typeStructure->isEcoleDoctorale() ? $this->structureService->findStructuresSubstituablesSelonSourceCode($typeStructure->getCode()) : null;
            $substitutionsEtablissements = $typeStructure->isEtablissement() ? $this->structureService->findStructuresSubstituablesSelonSourceCode($typeStructure->getCode()) : null;
            $substitutionsUnitesRecherches = $typeStructure->isUniteRecherche() ? $this->structureService->findStructuresSubstituablesSelonSourceCode($typeStructure->getCode()) : null;
        } else {
            $substitutionsEcolesDoctorales  = $this->getStructureService()->findStructuresSubstituablesSelonSourceCode(TypeStructure::CODE_ECOLE_DOCTORALE);
            $substitutionsEtablissements    = $this->getStructureService()->findStructuresSubstituablesSelonSourceCode(TypeStructure::CODE_ETABLISSEMENT);
            $substitutionsUnitesRecherches  = $this->getStructureService()->findStructuresSubstituablesSelonSourceCode(TypeStructure::CODE_UNITE_RECHERCHE);

        }
        return new ViewModel([
            'typeStructure' => $typeStructure,
            'substitutionsEcolesDoctorales' => $substitutionsEcolesDoctorales,
            'substitutionsEtablissements' => $substitutionsEtablissements,
            'substitutionsUnitesRecherches' => $substitutionsUnitesRecherches,
        ]);
    }

    /** Enregistrer une substitution automatique (! créer une structure cible si aucune n'existe */
    public function enregistrerAutomatiqueAction()
    {
        $type = $this->params()->fromRoute('type');
        $identifiant = $this->params()->fromRoute('identifiant');

        /** @var StructureConcreteInterface[] $sources */
        /** @var StructureConcreteInterface $cible */
        $dictionnary = $this->getStructureService()->getSubstitutionDictionnary($identifiant, $type);
        $sources = $dictionnary["sources"];
        $cible = $dictionnary["cible"];

        if ($cible != null) {
            $this->structureService->updateStructureSubstitutions($sources, $cible);
        } else {
            /** @var StructureConcreteInterface $cible */
            $cible = $this->getStructureService()->createStructureConcrete($type);
            $cible->getStructure()->setLibelle($sources[0]->getStructure(false)->getLibelle());
            $cible->getStructure()->setSigle($sources[0]->getStructure(false)->getSigle());
            $cible->getStructure()->setCode($sources[0]->getStructure(false)->getCode());
            $this->getStructureService()->createStructureSubstitutions($sources, $cible);
        }

        return new ViewModel();
    }

    public function modifierAutomatiqueAction()
    {
        $type = $this->params()->fromRoute('type');
        $identifiant = $this->params()->fromRoute('identifiant');

        $dictionnary = $this->getStructureService()->getSubstitutionDictionnary($identifiant, $type);
        $sources = $dictionnary["sources"];
        $cible = $dictionnary["cible"];

        $structures = $this->getStructureService()->getStructuresSubstituablesByType($type);

        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            $sources = [];
            foreach ($data['sourceIds'] as $sourceId) {
                $structure = $this->structureService->findStructureById($sourceId);
                $structureConcrete = $this->structureService->findStructureConcreteFromStructure($structure);
                $sources[] = $structureConcrete;
            }

            if ($cible === null) {
                /** @var StructureConcreteInterface $cible */
                $cible = $this->getStructureService()->createStructureConcrete($type);
                $cible->getStructure()->setLibelle($sources[0]->getStructure(false)->getLibelle());
                $cible->getStructure()->setSigle($sources[0]->getStructure(false)->getSigle());
                $cible->getStructure()->setCode($sources[0]->getStructure(false)->getCode());
                $this->getStructureService()->createStructureSubstitutions($sources, $cible);
            } else {
                $this->structureService->updateFromPostData($cible, $data['cible']);
                $this->structureService->updateStructureSubstitutions($sources, $cible->getStructure());
            }

            $message = "La substitution <strong>" . $cible->getStructure()->getLibelle() . "</strong> vient d'être mise à jour. Elle regroupe les structures : ";
            $message .= implode(", ", array_map(function (StructureConcreteInterface $s) {
                return "<i>" . $s->getStructure(false)->getLibelle() . "</i>";
            }, $sources));
            $this->flashMessenger()->addSuccessMessage($message);

            return $this->redirect()->toRoute(null, [], [], true);
        }

        if ($cible === null) {
            $cible = $this->getStructureService()->createStructureConcrete($type);
            $cible->setSourceCode($this->sourceCodeStringHelper->addDefaultPrefixTo($identifiant));
        }

        $vm = new ViewModel();
        $vm->setVariables([
            'title' => "Substitution de structures",
            'type' => $type,
            'cible' => $cible->getStructure(),
            'identifiant' => $identifiant,
            'structuresConcretes' => $structures,
            'structuresConcretesSubstituees' => $sources,
            'structureCibleLogoContent' => $this->structureService->getLogoStructureContent($cible->getStructure(false)),
        ]);
        $vm->setTemplate('structure/substitution/modifier');
        return $vm;

    }
}