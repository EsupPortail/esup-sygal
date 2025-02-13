<?php

namespace Acteur\Controller;

use Acteur\Entity\Db\AbstractActeur;
use Acteur\Form\AbstractActeurForm;
use Application\Controller\AbstractController;
use Application\Service\Role\ApplicationRoleServiceAwareTrait;
use Laminas\Http\Response;
use Laminas\View\Model\ViewModel;
use UnicaenDbImport\Entity\Db\Traits\SourceAwareTrait;

abstract class AbstractActeurController extends AbstractController
{
    use ApplicationRoleServiceAwareTrait;
    use SourceAwareTrait;

    protected AbstractActeurForm $acteurForm;
    protected AbstractActeur $acteur;

    public function setActeurForm(AbstractActeurForm $acteurForm): void
    {
        $this->acteurForm = $acteurForm;
    }

    public function modifierAction(): Response|ViewModel
    {
        $this->acteur = $this->getRequestedActeur();

        $roles = $this->applicationRoleService->getRepository()->findAll();
        /** @var \Acteur\Fieldset\AbstractActeurFieldset $acteurFieldset */
        $acteurFieldset = $this->acteurForm->get('acteur');
        $acteurFieldset->setRoles($roles);

        $this->acteurForm->setAttribute('action', $this->url()->fromRoute(null,[],[],true));
        $this->acteurForm->bind($this->acteur);

        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            $this->acteurForm->setData($data);

            if ($this->acteurForm->isValid()) {
                $this->saveActeur();
                $this->flashMessenger()->addSuccessMessage(sprintf("l'acteur '%s' modifié avec succès.", $this->acteur));

                if (!$request->isXmlHttpRequest()) {
                    return $this->redirect()->toUrl($this->urlIdentite());
                }
            }else{
                $this->flashMessenger()->addErrorMessage(sprintf("L'acteur '%s' n'a pas pu être modifié.", $this->acteur));
            }
        }
        $vm = new ViewModel();
        $vm->setVariables([
            'form' => $this->acteurForm,
            'title' => $this->acteur->getId() === null ? "Ajout d'un acteur" : "Modification de l'acteur ".$this->acteur->getDenomination()
        ]);
        $vm->setTemplate("acteur/acteur/modifier");
        return $vm;
    }

    abstract protected function getRequestedActeur(): AbstractActeur;
    abstract protected function saveActeur(): void;
    abstract protected function urlIdentite(): string;
}