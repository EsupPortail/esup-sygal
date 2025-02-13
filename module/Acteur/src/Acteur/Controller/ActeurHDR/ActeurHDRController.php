<?php

namespace Acteur\Controller\ActeurHDR;

use Acteur\Controller\AbstractActeurController;
use Acteur\Entity\Db\ActeurHDR;
use Acteur\Form\AbstractActeurForm;
use Acteur\Form\ActeurHDR\ActeurHDRForm;
use Acteur\Service\ActeurHDR\ActeurHDRServiceAwareTrait;
use HDR\Service\HDRServiceAwareTrait;
use Webmozart\Assert\Assert;

/**
 * @property \Acteur\Form\ActeurHDR\ActeurHDRForm $acteurForm
 * @property \Acteur\Entity\Db\ActeurHDR $acteur
 */
class ActeurHDRController extends AbstractActeurController
{
    use ActeurHDRServiceAwareTrait;
    use HDRServiceAwareTrait;

    public function setActeurForm(AbstractActeurForm $acteurForm): void
    {
        Assert::isInstanceOf($acteurForm, ActeurHDRForm::class);
        $this->acteurForm = $acteurForm;
    }

    protected function saveActeur(): void
    {
        $this->acteurHDRService->save($this->acteur);
    }

    protected function urlIdentite(): string
    {
        return $this->url()->fromRoute('hdr/identite', ['hdr' => $this->acteur->getHDR()->getId()], [], true);
    }

    protected function getRequestedActeur(): ActeurHDR
    {
        $id = $this->params('acteur');

        return $this->acteurHDRService->getRepository()->find($id);
    }

}