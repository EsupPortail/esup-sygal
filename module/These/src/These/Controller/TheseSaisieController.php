<?php

namespace These\Controller;

use Application\Controller\AbstractController;
use Application\Service\DomaineHal\DomaineHalServiceAwareTrait;
use Application\Service\Financement\FinancementServiceAwareTrait;
use Application\Service\Pays\PaysServiceAwareTrait;
use Application\Service\Source\SourceServiceAwareTrait;
use Application\SourceCodeStringHelperAwareTrait;
use Individu\Service\IndividuServiceAwareTrait;
use Laminas\Form\FieldsetInterface;
use Laminas\Form\Form;
use Laminas\View\Model\ViewModel;
use Soutenance\Service\Qualite\QualiteServiceAwareTrait;
use Structure\Service\Etablissement\EtablissementServiceAwareTrait;
use These\Entity\Db\These;
use These\Form\Direction\DirectionForm;
use These\Form\Encadrement\EncadrementForm;
use These\Form\Generalites\GeneralitesForm;
use These\Form\Structures\StructuresForm;
use These\Form\TheseFormsManagerAwareTrait;
use These\Form\TheseSaisie\TheseSaisieFormAwareTrait;
use These\Service\Acteur\ActeurServiceAwareTrait;
use These\Service\These\TheseServiceAwareTrait;
use UnicaenApp\Form\Element\Collection;
use UnicaenDbImport\Entity\Db\Traits\SourceAwareTrait;

class TheseSaisieController extends AbstractController {
    use ActeurServiceAwareTrait;
    use EtablissementServiceAwareTrait;
    use IndividuServiceAwareTrait;
    use QualiteServiceAwareTrait;
    use TheseServiceAwareTrait;
    use TheseSaisieFormAwareTrait;
    use SourceAwareTrait;
    use DomaineHalServiceAwareTrait;
    use TheseServiceAwareTrait;
    use TheseFormsManagerAwareTrait;
    use FinancementServiceAwareTrait;
    use SourceServiceAwareTrait;
    use SourceCodeStringHelperAwareTrait;
    use PaysServiceAwareTrait;

    private ?GeneralitesForm $generalitesForm = null;
    private ?DirectionForm $directionForm = null;
    private ?StructuresForm $structuresForm = null;
    private ?EncadrementForm $encadrementForm = null;

    public function ajouterAction()
    {
        $request = $this->getRequest();
        $form = $this->getTheseSaisieForm();

        $viewModel = new ViewModel([
            'form' => $form,
        ]);

        $form->bind($this->theseService->newThese());

        if (!$request->isPost()) {
            return $viewModel;
        }

        $data = $request->getPost();
        $form->setData($data);
        if (!$form->isValid()) {
            $messages = $this->getErrorMessages();
            $viewModel->setVariable("errorMessages", $messages);
            return $viewModel;
        }

        /** @var These $these */
        $these = $form->getData();
        $these->setSource($this->source);
        $these->setSourceCode(uniqid());
        $this->theseService->saveThese($these);

        $this->flashMessenger()->addSuccessMessage("Thèse créée avec succès.");

        return $this->redirect()->toRoute('these/identite', ['these' => $these->getId()], [], true);
    }

    public function indexAction()
    {
        return $this->modifier($this->getTheseSaisieForm(), 'index');
    }

    public function modifier(Form $form, string $domaine)
    {
        $request = $this->getRequest();
        $these = $this->requestedThese();

        $viewModel = new ViewModel([
            'these' => $these,
            'form' => $form,
        ]);
        $viewModel->setTemplate('these/these-saisie/modifier');

        $form->bind($these);

        if (!$request->isPost()) {
            return $viewModel;
        }

        $data = $request->getPost();
        //Permet de gérer le cas où aucune sélection n'est effectuée (afin de passer dans l'hydrateur)
        if (!isset($data["generalites"]['domaineHal'])) {
            $generalites = $data->get('generalites', []);
            $generalites['domaineHal'] = ['domaineHal' => ['']];
            $data->set('generalites', $generalites);
        }

        $form->setData($data);
        if (!$form->isValid()) {
            $messages = $this->getErrorMessages();
            $viewModel->setVariable("errorMessages", $messages);
            return $viewModel;
        }

        /** @var These $these */
        $these = $form->getData();
        $this->theseService->saveThese($these);

        $this->flashMessenger()->addSuccessMessage("Thèse modifiée avec succès.");

        return $this->redirect()->toRoute('these/identite', ['these' => $these->getId()], ['fragment' => $domaine], true);
    }

    private function getErrorMessages() : array
    {
        $messages = [];

        // Récupère les messages d'erreur de chaque fieldset
        foreach ($this->getTheseSaisieForm()->getFieldsets() as $fieldset) {
            if($fieldset instanceof Collection){
                // Récupère les messages d'erreur de chaque élément du fieldset
                foreach ($fieldset->getFieldsets() as $f) {
                    // Récupère les messages d'erreur de chaque élément du fieldset
                    foreach ($f->getElements() as $element) {
                        $elementMessages = $element->getMessages();
                        if (!empty($elementMessages)) {
                            $messages[$fieldset->getLabel()][$element->getLabel()] = $elementMessages;
                        }
                    }
                }
            }else if($fieldset instanceof FieldsetInterface){
                // Récupère les messages d'erreur de chaque élément du fieldset
                foreach ($fieldset->getElements() as $element) {
                    $elementMessages = $element->getMessages();
                    if (!empty($elementMessages)) {
                        $messages[$fieldset->getLabel()][$element->getLabel()] = $elementMessages;
                    }
                }
            }
        }
        return $messages;
    }
}