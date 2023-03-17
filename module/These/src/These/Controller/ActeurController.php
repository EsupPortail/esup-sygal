<?php

namespace These\Controller;

use Application\Controller\AbstractController;
use Application\Service\Role\ApplicationRoleServiceAwareTrait;
use Individu\Service\IndividuServiceAwareTrait;
use Laminas\Http\Response;
use Laminas\View\Model\ViewModel;
use Soutenance\Service\Qualite\QualiteServiceAwareTrait;
use Structure\Service\Etablissement\EtablissementServiceAwareTrait;
use These\Entity\Db\Acteur;
use These\Form\Acteur\ActeurForm;
use These\Form\TheseSaisie\TheseSaisieFormAwareTrait;
use These\Service\Acteur\ActeurServiceAwareTrait;
use These\Service\These\TheseServiceAwareTrait;
use UnicaenDbImport\Entity\Db\Traits\SourceAwareTrait;

class ActeurController extends AbstractController
{
    use ActeurServiceAwareTrait;
    use ApplicationRoleServiceAwareTrait;

    use EtablissementServiceAwareTrait;
    use IndividuServiceAwareTrait;
    use QualiteServiceAwareTrait;
    use TheseServiceAwareTrait;
    use TheseSaisieFormAwareTrait;
    use SourceAwareTrait;

    private ActeurForm $acteurForm;

    public function setActeurForm(ActeurForm $acteurForm): void
    {
        $this->acteurForm = $acteurForm;
    }

    public function modifierAction(): Response|ViewModel
    {
        $acteur = $this->getRequestedActeur();

        $roles = $this->applicationRoleService->getRepository()->findAll();
        /** @var \These\Fieldset\Acteur\ActeurFieldset $acteurFieldset */
        $acteurFieldset = $this->acteurForm->get('acteur');
        $acteurFieldset->setRoles($roles);

        $this->acteurForm->setAttribute('action', $this->url()->fromRoute(null,[],[],true));
        $this->acteurForm->bind($acteur);

        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            $this->acteurForm->setData($data);

            if ($this->acteurForm->isValid()) {
                $this->acteurService->update($acteur);
                $this->flashMessenger()->addSuccessMessage(sprintf("Acteur '%s' modifié avec succès.", $acteur));

                if (!$request->isXmlHttpRequest()) {
                    return $this->redirect()->toRoute('these/identite', ['these' => $acteur->getThese()->getId()], [], true);
                }
            }
        }

        return new ViewModel([
            'form' => $this->acteurForm,
        ]);
    }

    private function getRequestedActeur(): Acteur
    {
        $id = $this->params('acteur');

        return $this->acteurService->getRepository()->find($id);
    }

}