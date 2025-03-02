<?php

namespace These\Controller;

use Application\Controller\AbstractController;
use Application\Entity\Db\Role;
use Application\Service\Role\ApplicationRoleServiceAwareTrait;
use Doctorant\Service\DoctorantServiceAwareTrait;
use Individu\Service\IndividuServiceAwareTrait;
use Laminas\Form\FieldsetInterface;
use Laminas\Form\Form;
use Laminas\Http\Response;
use Laminas\View\Model\ViewModel;
use These\Entity\Db\These;
use These\Form\Direction\DirectionFormAwareTrait;
use These\Form\Financement\FinancementsFormAwareTrait;
use These\Form\Generalites\GeneralitesFormAwareTrait;
use These\Form\Structures\StructuresFormAwareTrait;
use These\Form\TheseSaisie\TheseSaisieFormAwareTrait;
use These\Service\These\TheseServiceAwareTrait;
use UnicaenApp\Form\Element\Collection;

class TheseSaisieController extends AbstractController
{
    use IndividuServiceAwareTrait;
    use TheseServiceAwareTrait;
    use TheseSaisieFormAwareTrait;
    use DoctorantServiceAwareTrait;
    use ApplicationRoleServiceAwareTrait;
    use GeneralitesFormAwareTrait;
    use StructuresFormAwareTrait;
    use FinancementsFormAwareTrait;
    use DirectionFormAwareTrait;

    public function ajouterAction()
    {
        $request = $this->getRequest();
        $form = $this->getTheseSaisieForm();
        $form->bind($this->theseService->newThese());

        $viewModel = new ViewModel([
            'form' => $form,
        ]);

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
        $individu = $data["generalites"]['doctorant']["id"] ? $this->individuService->getRepository()->find($data["generalites"]['doctorant']["id"]) : null;
        if ($individu) {
            $etablissement = $these->getEtablissement();
            $doctorant = $this->doctorantService->newDoctorant($individu, $etablissement);
            $these->setDoctorant($doctorant);
        }
        $this->theseService->saveThese($these);

        $this->flashMessenger()->addSuccessMessage("Thèse créée avec succès.");

        return $this->redirect()->toRoute('these/identite', ['these' => $these->getId()], [], true);
    }

    public function modifierAction()
    {
        $form = $this->getTheseSaisieForm();
        $request = $this->getRequest();
        $these = $this->requestedThese();

        $form->setAttribute('action', $this->url()->fromRoute('these/modifier', ['these' => $these->getId()], [], true));
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

        $this->theseService->saveThese($these);

        $this->flashMessenger()->addSuccessMessage("Thèse modifiée avec succès.");

        return $this->redirect()->toRoute('these/identite', ['these' => $these->getId()], [], true);
    }

    private function getErrorMessages(): array
    {
        $messages = [];

        // Récupère les messages d'erreur de chaque fieldset
        foreach ($this->getTheseSaisieForm()->getFieldsets() as $fieldset) {
            if ($fieldset instanceof Collection) {
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
            } else if ($fieldset instanceof FieldsetInterface) {
                // Récupère les messages d'erreur de chaque élément du fieldset
                foreach ($fieldset->getElements() as $element) {
                    $elementMessages = $element->getMessages();
                    if (!empty($elementMessages)) {
                        $messages[$fieldset->getLabel()][$element->getLabel()] = $elementMessages;
                    }
                }
                // Récupère les messages d'erreur des possibles fieldsets présents dans le fieldset
                if($fieldsetsInFieldset = $fieldset->getFieldsets()){
                    // Récupère les messages d'erreur de chaque élément du fieldset
                    foreach ($fieldsetsInFieldset as $fieldsetInFieldset) {
                        foreach ($fieldsetInFieldset->getElements() as $element) {
                            $elementMessages = $element->getMessages();
                            if (!empty($elementMessages)) {
                                $messages[$fieldset->getLabel()][$element->getLabel()] = $elementMessages;
                            }
                        }
                    }
                }
            }
        }
        return $messages;
    }

    public function generalitesAction(): Response|ViewModel
    {
        return $this->modifierTheseSaisiePart($this->getGeneralitesForm(), 'generalites');
    }

    public function structuresAction(): Response|ViewModel
    {
        return $this->modifierTheseSaisiePart($this->getStructuresForm(), 'structures');
    }

    public function directionAction(): Response|ViewModel
    {
        return $this->modifierTheseSaisiePart($this->getDirectionForm(), 'direction');
    }

    public function financementsAction(): Response|ViewModel
    {
        return $this->modifierTheseSaisiePart($this->getFinancementsForm(), 'financements');
    }

    private function modifierTheseSaisiePart(Form $form, string $domaine): Response|ViewModel
    {
        $request = $this->getRequest();
        $these = $this->requestedThese();

        $form->setAttribute('action', $this->url()->fromRoute("these/modifier/$domaine", ['these' => $these->getId()], [], true));
        $viewModel = new ViewModel([
            'these' => $these,
            'form' => $form,
            'title' => "Modification de la thèse de ".$these->getDoctorant()
        ]);
        $viewModel->setTemplate("these/these-saisie/partial/$domaine");

        $form->bind($these);

        if (!$request->isPost()) {
            return $viewModel;
        }

        $form->setData($request->getPost());
        if (!$form->isValid()) {
            return $viewModel;
        }

        /** @var These $these */
        $these = $form->getData();
        $this->theseService->saveThese($these);

        $this->flashMessenger()->addSuccessMessage("Thèse modifiée avec succès.");
        return $this->redirect()->toRoute('these/identite', ['these' => $these->getId()], [], true);
    }
}