<?php

namespace These\Controller;

use Application\Controller\AbstractController;
use Application\Entity\Db\Role;
use Application\Service\Role\RoleServiceAwareTrait;
use Application\Service\Source\SourceServiceAwareTrait;
use Application\SourceCodeStringHelperAwareTrait;
use Doctorant\Service\DoctorantServiceAwareTrait;
use Individu\Service\IndividuServiceAwareTrait;
use Laminas\Form\FieldsetInterface;
use Laminas\Form\Form;
use Laminas\View\Model\ViewModel;
use Structure\Service\Etablissement\EtablissementServiceAwareTrait;
use These\Entity\Db\These;
use These\Form\TheseSaisie\TheseSaisieFormAwareTrait;
use These\Service\These\TheseServiceAwareTrait;
use UnicaenApp\Form\Element\Collection;
use UnicaenDbImport\Entity\Db\Traits\SourceAwareTrait;

class TheseSaisieController extends AbstractController
{
    use EtablissementServiceAwareTrait;
    use IndividuServiceAwareTrait;
    use TheseServiceAwareTrait;
    use TheseSaisieFormAwareTrait;
    use SourceAwareTrait;
    use TheseServiceAwareTrait;
    use SourceServiceAwareTrait;
    use SourceCodeStringHelperAwareTrait;
    use DoctorantServiceAwareTrait;
    use RoleServiceAwareTrait;

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

        /** @var These $these */
        $these = $form->getData();
        $individu = $data["generalites"]['doctorant']["id"] ? $this->individuService->getRepository()->find($data["generalites"]['doctorant']["id"]) : null;
        if ($individu) {
            $doctorant = $this->doctorantService->newDoctorant($individu);
            $these->setDoctorant($doctorant);
            //Ajout du rôle doctorant à l'individu
            $role = $this->roleService->getRepository()->findByCode(Role::CODE_DOCTORANT);
            $this->roleService->addRole($individu, $role->getId());
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

        return $this->redirect()->toRoute('these/identite', ['these' => $these->getId()], ['fragment' => $domaine], true);
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
}