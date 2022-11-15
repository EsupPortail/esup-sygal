<?php

namespace ComiteSuiviIndividuel\Controller;

use Application\Controller\AbstractController;
use ComiteSuiviIndividuel\Entity\Db\Membre;
use ComiteSuiviIndividuel\Form\Membre\MembreFromAwareTrait;
use ComiteSuiviIndividuel\Service\Membre\MembreServiceAwareTrait;
use Laminas\View\Model\ViewModel;

class MembreController extends AbstractController
{
    use MembreServiceAwareTrait;
    use MembreFromAwareTrait;

    public function ajouterAction() : ViewModel
    {
        $these = $this->requestedThese();
        $membre = new Membre();

        $form = $this->getMembreForm();
        $form->setAttribute('action', $this->url()->fromRoute('comite-suivi-these/membre/ajouter', ['these' => $these->getId()], [], true));
        $form->bind($membre);

        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            $form->setData($data);
            if ($form->isValid()) {
                $this->getMembreService()->create($membre);
            }
        }

        $vm = new ViewModel([
            'title' => "Ajout d'un membre de comité de suivi individuel",
            'these' => $these,
            'form' => $form,
        ]);
        $vm->setTemplate('comite-suivi-these/membre/modifier');
        return $vm;
    }
}