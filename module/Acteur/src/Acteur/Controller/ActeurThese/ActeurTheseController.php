<?php

namespace Acteur\Controller\ActeurThese;

use Acteur\Controller\AbstractActeurController;
use Acteur\Entity\Db\ActeurThese;
use Acteur\Form\AbstractActeurForm;
use Acteur\Form\ActeurThese\ActeurTheseForm;
use Acteur\Service\ActeurThese\ActeurTheseServiceAwareTrait;
use These\Service\These\TheseServiceAwareTrait;
use Webmozart\Assert\Assert;

/**
 * @property \Acteur\Form\ActeurThese\ActeurTheseForm $acteurForm
 * @property \Acteur\Entity\Db\ActeurThese $acteur
 */
class ActeurTheseController extends AbstractActeurController
{
    use ActeurTheseServiceAwareTrait;
    use TheseServiceAwareTrait;

    public function setActeurForm(AbstractActeurForm $acteurForm): void
    {
        Assert::isInstanceOf($acteurForm, ActeurTheseForm::class);
        $this->acteurForm = $acteurForm;
    }

    protected function saveActeur(): void
    {
        $this->acteurTheseService->save($this->acteur);
    }

    protected function urlIdentite(): string
    {
        return $this->url()->fromRoute('these/identite', ['these' => $this->acteur->getThese()->getId()], [], true);
    }

    protected function getRequestedActeur(): ActeurThese
    {
        $id = $this->params('acteur');

        return $this->acteurTheseService->getRepository()->find($id);
    }

}