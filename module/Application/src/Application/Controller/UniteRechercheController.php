<?php

namespace Application\Controller;

use Application\Entity\Db\TypeStructure;
use Application\Entity\Db\UniteRecherche;
use Application\Service\CoEncadrant\CoEncadrantServiceAwareTrait;
use Application\Service\DomaineScientifiqueServiceAwareTrait;
use Application\Service\Etablissement\EtablissementServiceAwareTrait;
use Application\Service\StructureDocument\StructureDocumentServiceAwareTrait;
use Application\Service\UniteRecherche\UniteRechercheService;
use Application\Service\UniteRecherche\UniteRechercheServiceAwareTrait;
use Application\SourceCodeStringHelperAwareTrait;
use Laminas\Http\Response;
use Laminas\View\Model\ViewModel;

class UniteRechercheController extends StructureConcreteController
{
    use CoEncadrantServiceAwareTrait;
    use UniteRechercheServiceAwareTrait;
    use EtablissementServiceAwareTrait;
    use DomaineScientifiqueServiceAwareTrait;
    use SourceCodeStringHelperAwareTrait;
    use StructureDocumentServiceAwareTrait;

    protected $codeTypeStructure = TypeStructure::CODE_UNITE_RECHERCHE;

    /**
     * @var string
     */
    protected $routeName = 'unite-recherche';

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
    public function informationAction()
    {
        $id = $this->params()->fromRoute('structure');
        $structureConcrete = $this->getStructureConcreteService()->getRepository()->findByStructureId($id);
        $coencadrants = $this->getCoEncadrantService()->getCoEncadrantsByStructureConcrete($structureConcrete, false);
        $contenus = $this->getStructureDocumentService()->getContenusFichiers($structureConcrete->getStructure());

        $viewModel = parent::informationAction();

        $unite = $viewModel->getVariable('structure');

        $etablissementsRattachements = $this->uniteRechercheService->findEtablissementRattachement($unite);

        $viewModel->setVariables([
            'unite'                       => $unite,
            'etablissementsRattachements' => $etablissementsRattachements,
            'coencadrants'                => $coencadrants,
            'contenus'                    => $contenus,
        ]);

        return $viewModel;
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

        $viewModel->setTemplate('application/unite-recherche/modifier');

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

        $viewModel->setTemplate('application/unite-recherche/modifier');

        return $viewModel;
    }

    public function ajouterEtablissementRattachementAction()
    {
        $structureId = $this->params()->fromRoute("structure");
        $unite = $this->getUniteRechercheService()->getRepository()->findByStructureId($structureId);
        $etablissementId = $this->params()->fromRoute("etablissement");

        if ($etablissementId == 0) {
            $this->flashMessenger()->addErrorMessage("Pour ajouter un établissement de rattachement, veuillez sélectionner un établissement.");
        } else {
            $etablissement = $this->getEtablissementService()->getRepository()->find($etablissementId);
            if ($this->getUniteRechercheService()->existEtablissementRattachement($unite, $etablissement)) {
                $this->flashMessenger()->addErrorMessage("L'établissement de rattachement <strong>" . $etablissement->getLibelle() . "</strong> n'a pas pu être ajouter car déjà enregistré comme établissement de rattachement de l'unité de recherche <strong>" . $unite->getLibelle() . "</strong>.");
            } else {
                $this->getUniteRechercheService()->addEtablissementRattachement($unite, $etablissement);
                $this->flashMessenger()->addSuccessMessage("L'établissement <strong>" . $etablissement->getLibelle() . "</strong> vient d'être ajouter comme établissement de rattachement de l'unité de recherche <strong>" . $unite->getLibelle() . "</strong>.");
            }
        }

        $this->redirect()->toRoute("unite-recherche/modifier", [], [], true);
    }

    public function retirerEtablissementRattachementAction()
    {
        $structureId = $this->params()->fromRoute("structure");
        $unite = $this->getUniteRechercheService()->getRepository()->findByStructureId($structureId);
        $etablissementId = $this->params()->fromRoute("etablissement");
        $etablissement = $this->getEtablissementService()->getRepository()->find($etablissementId);

        $this->getUniteRechercheService()->removeEtablissementRattachement($unite, $etablissement);
        $this->flashMessenger()->addSuccessMessage("L'établissement <strong>" . $etablissement->getLibelle() . "</strong> n'est plus un établissement de rattachement de l'unité de recherche <strong>" . $unite->getLibelle() . "</strong>.");

        $this->redirect()->toRoute("unite-recherche/modifier", [], [], true);
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

            $this->flashMessenger()->addSuccessMessage("Le domaine scientifique <strong>" . $domaine->getLibelle() . "</strong> est maintenant un des domaines scientifiques de l'unité de recherche <strong>" . $unite->getLibelle() . "</strong>.");
        }
        $this->redirect()->toRoute("unite-recherche/modifier", [], [], true);
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

        $this->flashMessenger()->addSuccessMessage("Le domaine scientifique <strong>" . $domaine->getLibelle() . "</strong> ne fait plus parti des domaines scientifiques de l'unité de recherche <strong>" . $unite->getLibelle() . "</strong>.");

        return $this->redirect()->toRoute("unite-recherche/modifier", [], [], true);
    }
}