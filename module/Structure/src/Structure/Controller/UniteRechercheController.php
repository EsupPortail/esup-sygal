<?php

namespace Structure\Controller;

use Application\Service\DomaineScientifiqueServiceAwareTrait;
use InvalidArgumentException;
use Laminas\Http\Response;
use Laminas\View\Model\JsonModel;
use Laminas\View\Model\ViewModel;
use Structure\Entity\Db\StructureConcreteInterface;
use Structure\Entity\Db\TypeStructure;
use Structure\Entity\Db\UniteRecherche;
use Structure\Service\Etablissement\EtablissementServiceAwareTrait;
use Structure\Service\StructureDocument\StructureDocumentServiceAwareTrait;
use Structure\Service\UniteRecherche\UniteRechercheService;
use Structure\Service\UniteRecherche\UniteRechercheServiceAwareTrait;
use These\Service\CoEncadrant\CoEncadrantServiceAwareTrait;

class UniteRechercheController extends StructureConcreteController
{
    use CoEncadrantServiceAwareTrait;
    use UniteRechercheServiceAwareTrait;
    use EtablissementServiceAwareTrait;
    use DomaineScientifiqueServiceAwareTrait;
    use StructureDocumentServiceAwareTrait;

    protected $codeTypeStructure = TypeStructure::CODE_UNITE_RECHERCHE;

    /**
     * @var string
     */
    protected string $routeName = 'unite-recherche';
    protected string $routeParamName = 'unite-recherche';

    /**
     * @return UniteRechercheService
     */
    protected function getStructureConcreteService()
    {
        return $this->uniteRechercheService;
    }

    /**
     * @return ViewModel
     */
    public function indexAction()
    {
        $viewModel = parent::indexAction();

        return new ViewModel([
            'unites' => $viewModel->getVariable('structures'),
        ]);
    }

    /**
     * @return ViewModel
     */
    public function voirAction(): ViewModel
    {
        $id = $this->params()->fromRoute('unite-recherche');

        /** @var UniteRecherche $structureConcrete */
        $structureConcrete = $this->uniteRechercheService->getRepository()->find($id);
        if ($structureConcrete === null) {
            throw new InvalidArgumentException("Unite de recherche introuvable avec cet id");
        }

        $vars = $this->loadInformationForStructure($structureConcrete);

        return (new ViewModel($vars))
            ->setTemplate('structure/unite-recherche/information');
    }

    protected function loadInformationForStructure(StructureConcreteInterface $structureConcrete): array
    {
        $vars = parent::loadInformationForStructure($structureConcrete);

        /** @var UniteRecherche $structureConcrete */
        $coencadrants = $this->getCoEncadrantService()->findCoEncadrantsByStructureConcrete($structureConcrete, false);
        $contenus = $this->getStructureDocumentService()->getContenusFichiers($structureConcrete->getStructure());

        $etablissementsRattachements = $this->uniteRechercheService->findEtablissementRattachement($structureConcrete);

        return array_merge($vars, [
            'unite' => $structureConcrete,
            'etablissementsRattachements' => $etablissementsRattachements,
            'coencadrants' => $coencadrants,
            'contenus' => $contenus,
        ]);
    }

    /**
     * @return Response|ViewModel
     */
    public function modifierAction()
    {
        $viewModel = parent::modifierAction();

        if ($viewModel instanceof Response) {
            return $viewModel;
        }

        /** @var UniteRecherche $unite */
        $unite = $viewModel->getVariable('structure');

        $etablissements = $this->etablissementService->getRepository()->findAll();
        $etablissementsRattachements = $this->uniteRechercheService->findEtablissementRattachement($unite);
        $domaineScientifiques = $this->domaineScientifiqueService->getRepository()->findAll();

        // envoie vers le formulaire de modification
        $viewModel->setVariables([
            'etablissements'              => $etablissements,
            'etablissementsRattachements' => $etablissementsRattachements,
            'domainesAssocies'            => $unite->getDomaines(),
            'domainesScientifiques'       => $domaineScientifiques,
        ]);

        $viewModel->setTemplate('structure/unite-recherche/modifier');

        return $viewModel;
    }

    /**
     * @return Response|ViewModel
     */
    public function ajouterAction()
    {
        $viewModel = parent::ajouterAction();

        if ($viewModel instanceof Response) {
            return $viewModel;
        }

        $viewModel->setTemplate('structure/unite-recherche/modifier');

        return $viewModel;
    }

    public function ajouterEtablissementRattachementAction()
    {
        $structureId = $this->params()->fromRoute("structure");
        $unite = $this->getUniteRechercheService()->getRepository()->findByStructureId($structureId);
        $etablissementId = $this->params()->fromRoute("etablissement");

        if ($etablissementId == 0) {
            $this->flashMessenger()->addErrorMessage(
                "Pour ajouter un établissement de rattachement, veuillez sélectionner un établissement.");
        } else {
            /** @var \Structure\Entity\Db\Etablissement $etablissement */
            $etablissement = $this->getEtablissementService()->getRepository()->find($etablissementId);
            if ($this->getUniteRechercheService()->existEtablissementRattachement($unite, $etablissement)) {
                $this->flashMessenger()->addErrorMessage(
                    "L'établissement de rattachement <strong>" . $etablissement->getStructure()->getLibelle() .
                    "</strong> n'a pas pu être ajouté car déjà enregistré comme établissement de rattachement de l'unité de recherche <strong>" .
                    $unite->getStructure()->getLibelle() . "</strong>.");
            } else {
                $this->getUniteRechercheService()->addEtablissementRattachement($unite, $etablissement);
                $this->flashMessenger()->addSuccessMessage(
                    "L'établissement <strong>" . $etablissement->getStructure()->getLibelle() .
                    "</strong> vient d'être ajouté comme établissement de rattachement de l'unité de recherche <strong>" .
                    $unite->getStructure()->getLibelle() . "</strong>.");
            }
        }

        $this->redirect()->toRoute("unite-recherche/modifier", ['unite-recherche' => $unite->getId()], [], true);
    }

    public function retirerEtablissementRattachementAction()
    {
        $structureId = $this->params()->fromRoute("structure");
        $unite = $this->getUniteRechercheService()->getRepository()->findByStructureId($structureId);
        $etablissementId = $this->params()->fromRoute("etablissement");
        /** @var \Structure\Entity\Db\Etablissement $etablissement */
        $etablissement = $this->getEtablissementService()->getRepository()->find($etablissementId);

        $this->getUniteRechercheService()->removeEtablissementRattachement($unite, $etablissement);
        $this->flashMessenger()->addSuccessMessage("L'établissement <strong>" . $etablissement->getStructure()->getLibelle() . "</strong> n'est plus un établissement de rattachement de l'unité de recherche <strong>" . $unite->getStructure()->getLibelle() . "</strong>.");

        $this->redirect()->toRoute("unite-recherche/modifier", ['unite-recherche' => $unite->getId()], [], true);
    }

    /**
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function ajouterDomaineScientifiqueAction()
    {
        $structureId = $this->params()->fromRoute("structure");
        $domaineId = $this->params()->fromRoute("domaineScientifique");
        $unite = $this->getUniteRechercheService()->getRepository()->findByStructureId($structureId);
        $domaine = $this->getDomaineScientifiqueService()->getRepository()->find($domaineId);

        if ($domaine !== null && !array_search($domaine, $unite->getDomaines())) {
            $domaine = $domaine->addUnite($unite);
            $unite = $unite->addDomaine($domaine);

            $this->getDomaineScientifiqueService()->updateDomaineScientifique($domaine);

            $this->flashMessenger()->addSuccessMessage("Le domaine scientifique <strong>" . $domaine->getLibelle() . "</strong> est maintenant un des domaines scientifiques de l'unité de recherche <strong>" . $unite->getStructure()->getLibelle() . "</strong>.");
        }
        $this->redirect()->toRoute("unite-recherche/modifier", ['unite-recherche' => $unite->getId()], [], true);
    }

    /**
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function retirerDomaineScientifiqueAction()
    {
        $structureId = $this->params()->fromRoute("structure");
        $domaineId = $this->params()->fromRoute("domaineScientifique");
        $unite = $this->getUniteRechercheService()->getRepository()->findByStructureId($structureId);
        $domaine = $this->getDomaineScientifiqueService()->getRepository()->find($domaineId);

        $domaine = $domaine->removeUnite($unite);
        $unite = $unite->removeDomaine($domaine);

        $this->getDomaineScientifiqueService()->updateDomaineScientifique($domaine);

        $this->flashMessenger()->addSuccessMessage("Le domaine scientifique <strong>" . $domaine->getLibelle() . "</strong> ne fait plus parti des domaines scientifiques de l'unité de recherche <strong>" . $unite->getStructure()->getLibelle() . "</strong>.");

        return $this->redirect()->toRoute("unite-recherche/modifier", ['unite-recherche' => $unite->getId()], [], true);
    }

    public function rechercherAction()
    {
        if (($term = $this->params()->fromQuery('term'))) {
            $unites = $this->getUniteRechercheService()->getRepository()->findByText($term);
            $result = [];
            foreach ($unites as $unite) {
                $result[] = array(
                    'id' => $unite->getId(),            // identifiant unique de l'item
                    'label' => $unite->getStructure()->getLibelle(),    // libellé de l'item
                    'extra' => $unite->getStructure()->getSigle(),      // infos complémentaires (facultatives) sur l'item
                );
            }
            usort($result, function ($a, $b) {
                return strcmp($a['label'], $b['label']);
            });

            return new JsonModel($result);
        }
        exit;
    }
}