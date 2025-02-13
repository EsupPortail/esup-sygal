<?php

namespace HDR\Controller;

use Application\Controller\AbstractController;
use Application\Service\Role\ApplicationRoleServiceAwareTrait;
use Candidat\Service\CandidatServiceAwareTrait;
use HDR\Entity\Db\HDR;
use HDR\Form\Direction\DirectionFormAwareTrait;
use HDR\Form\Generalites\GeneralitesFormAwareTrait;
use HDR\Form\HDRSaisie\HDRSaisieFormAwareTrait;
use HDR\Service\HDRServiceAwareTrait;
use Individu\Service\IndividuServiceAwareTrait;
use Laminas\Form\FieldsetInterface;
use Laminas\Form\Form;
use Laminas\Http\Response;
use Laminas\View\Model\ViewModel;
use HDR\Form\Structures\StructuresFormAwareTrait;
use UnicaenApp\Form\Element\Collection;

class HDRSaisieController extends AbstractController
{
    use IndividuServiceAwareTrait;
    use HDRServiceAwareTrait;
    use HDRSaisieFormAwareTrait;
    use CandidatServiceAwareTrait;
    use ApplicationRoleServiceAwareTrait;
    use GeneralitesFormAwareTrait;
    use StructuresFormAwareTrait;
    use DirectionFormAwareTrait;

    public function ajouterAction()
    {
        $request = $this->getRequest();
        $form = $this->getHDRSaisieForm();
        $form->bind($this->hdrService->newHDR());

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

        /** @var HDR $hdr */
        $hdr = $form->getData();
        $individu = $data["generalites"]['candidat']["id"] ? $this->individuService->getRepository()->find($data["generalites"]['candidat']["id"]) : null;
        if ($individu) {
            $etablissement = $hdr->getEtablissement();
            $candidat = $this->candidatService->newCandidat($individu, $etablissement);
            $hdr->setCandidat($candidat);
        }
        $this->hdrService->saveHDR($hdr);

        $this->flashMessenger()->addSuccessMessage("HDR créée avec succès.");

        return $this->redirect()->toRoute('hdr/identite', ['hdr' => $hdr->getId()], [], true);
    }

    public function modifierAction()
    {
        $form = $this->getHDRSaisieForm();
        $request = $this->getRequest();
        $hdr = $this->requestedHDR();

        $form->setAttribute('action', $this->url()->fromRoute('hdr/modifier', ['hdr' => $hdr->getId()], [], true));
        $viewModel = new ViewModel([
            'hdr' => $hdr,
            'form' => $form,
        ]);
        $viewModel->setTemplate('hdr/hdr-saisie/modifier');

        $form->bind($hdr);

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

        $this->hdrService->saveHDR($hdr);

        $this->flashMessenger()->addSuccessMessage("HDR modifiée avec succès.");

        return $this->redirect()->toRoute('hdr/identite', ['hdr' => $hdr->getId()], [], true);
    }

    private function getErrorMessages(): array
    {
        $messages = [];

        // Récupère les messages d'erreur de chaque fieldset
        foreach ($this->getHDRSaisieForm()->getFieldsets() as $fieldset) {
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
        return $this->modifierHDRSaisiePart($this->getGeneralitesForm(), 'generalites');
    }

    public function structuresAction(): Response|ViewModel
    {
        return $this->modifierHDRSaisiePart($this->getStructuresForm(), 'structures');
    }

    public function directionAction(): Response|ViewModel
    {
        return $this->modifierHDRSaisiePart($this->getDirectionForm(), 'direction');
    }

    private function modifierHDRSaisiePart(Form $form, string $domaine): Response|ViewModel
    {
        $request = $this->getRequest();
        $hdr = $this->requestedHDR();

        $form->setAttribute('action', $this->url()->fromRoute("hdr/modifier/$domaine", ['hdr' => $hdr->getId()], [], true));
        $viewModel = new ViewModel([
            'hdr' => $hdr,
            'form' => $form,
            'title' => "Modification de la HDR de ".$hdr->getCandidat()
        ]);
        $viewModel->setTemplate("hdr/hdr-saisie/partial/$domaine");

        $form->bind($hdr);

        if (!$request->isPost()) {
            return $viewModel;
        }

        $form->setData($request->getPost());
        if (!$form->isValid()) {
            return $viewModel;
        }

        /** @var HDR $hdr */
        $hdr = $form->getData();
        $this->hdrService->saveHDR($hdr);

        $this->flashMessenger()->addSuccessMessage("HDR modifiée avec succès.");
        return $this->redirect()->toRoute('hdr/identite', ['hdr' => $hdr->getId()], [], true);
    }
}