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

    public function indexAction()
    {
        $structuresSubstituees = $this->structureService->getStructuresSubstituantes();
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

    public function indexStructureAction() {
        $type = $this->params()->fromRoute("type");
        $structures = $this->getStructureService()->getStructuresSubstituantes($type);

        return new ViewModel([
            'type' => $type,
            'structures' => $structures,
        ]);
    }

    public function creerAction()
    {
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

    public function detruireAction()
    {
        $idCible = $this->params()->fromRoute('cible');
        $structure = $this->structureService->findStructureById($idCible);
        $cible = $this->structureService->findStructureConcreteFromStructure($structure);
        $this->structureService->removeSubstitution($cible);

        $message = "La substitution <strong>".$cible->getLibelle()."</strong> vient d'être détruite.";
        $this->flashMessenger()->addSuccessMessage($message);

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

        $substitutions = $this->structureService->getSubstitutions(null);

        foreach($substitutions as $substitution) {
//            var_dump("This subsitution has " . count($substitution) . " elements.");
            $idsSubsitutantes = [];
            /** @var StructureConcreteInterface $element */
            foreach ($substitution as $element) {
//                $id = $element->getId();
//                $type = $element->getStructure()->getTypeStructure();
                $subs = $this->structureService->findStructureSubstituante($element);
                $sid = ($subs) ? $subs->getId() : -1;
                $idsSubsitutantes[$sid][] = $element;
            }
            $keys = array_keys($idsSubsitutantes);
            $first_key = $keys[0];
            $first_elements = $idsSubsitutantes[$first_key];

            /**  ------- CREATION D'UN SUBSTITUTION -------
             * Une seule clé de présente et celle-ci est -1
             */
            if (count($keys) == 1 && $first_key === -1) {
                $elements = $first_elements;
                $data["sigle"] = $elements[0]->getSigle();
                $data["libelle"] = $elements[0]->getLibelle();
                $data["cheminLogo"] = $elements[0]->getCheminLogo();
                if ($elements[0]->getStructure()->getTypeStructure()->isUniteRecherche()) {
                    $data["etablissementsSupport"] = $elements[0]->getEtablissementsSupport();
                    $data["autresEtablissements"] = $elements[0]->getAutresEtablissements();
                }
                if ($elements[0]->getStructure()->getTypeStructure()->isEtablissement()) {
                    $data["code"] = $substitution[0]->getCode() . uniqid();
                    $data["domaine"] = $substitution[0]->getDomaine();
                }

                $structureCibleDataObject = $this->structureService->createStructureConcrete($elements[0]->getStructure()->getTypeStructure()->getCode());
                $this->structureService->updateFromPostData($structureCibleDataObject, $data);
                $this->structureService->createStructureSubstitutions($elements, $structureCibleDataObject);

                $message = "Création de la substitution <strong>".$data["libelle"]."</strong> regroupant les <strong>".count($first_elements)."</strong> structures suivantes&nbsp;: ";
                $first = true;
                foreach ($first_elements as $element) {
                    if (!$first) $message .= ", ";
                    $first = false;
                    $message .= "<i>".$element->getLibelle()."</i>";
                }
                $message .= ".";
                $this->flashMessenger()->addSuccessMessage($message);

            } elseif(count($keys) == 1 || count($keys) == 2 && array_search(-1, $keys) !== false) {
                /** --------- UPDATE D'UNE SUBSTITUTION -------
                 * Deux clefs avec une à -1 ou une clef différente de -1
                 */
                $key = -1;
                foreach ($keys as $key_tmp) {
                    if ($key_tmp !== -1) {
                        $key = $key_tmp;
                        break;
                    }
                }
                $first_element = $idsSubsitutantes[$key][0];

                $elements = [];
                foreach ($idsSubsitutantes as $key => $element) {
                    $elements = array_merge($elements, $element);
                }
                $cible = $this->structureService->findStructureSubstituante($first_element);

                if (count($elements) != count($cible->getStructure()->getStructuresSubstituees())) {

                    $this->structureService->updateStructureSubstitutions($elements, $cible->getStructure());

                    $message = "Mise à jour de la substitution <strong>" . $cible->getLibelle() . "</strong> regroupant maintenant les <strong>" . count($elements) . "</strong> structures suivantes&nbsp;: ";
                    $first = true;
                    foreach ($elements as $element) {
                        if (!$first) $message .= ", ";
                        $first = false;
                        $message .= "<i>" . $element->getLibelle() . "</i>";
                    }
                    $message .= ".";
                    $this->flashMessenger()->addSuccessMessage($message);
                }

            } else {
                return new RuntimeException("Erreur cas de substitution imprévue");
            }
        }

        $this->flashMessenger()->addInfoMessage("Substitution automatique effectuée.");
        $this->redirect()->toRoute("substitution-index");
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
                    if ($prefix === "SyGAL") {
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