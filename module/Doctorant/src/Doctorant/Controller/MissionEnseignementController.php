<?php

namespace Doctorant\Controller;

use Application\Controller\AbstractController;
use Doctorant\Entity\Db\Doctorant;
use Doctorant\Entity\Db\MissionEnseignement;
use Doctorant\Form\MissionEnseignement\MissionEnseignementFormAwareTrait;
use Doctorant\Service\DoctorantServiceAwareTrait;
use Doctorant\Service\MissionEnseignement\MissionEnseignementServiceAwareTrait;
use Laminas\View\Model\ViewModel;

class MissionEnseignementController extends AbstractController {
    use DoctorantServiceAwareTrait;
    use MissionEnseignementServiceAwareTrait;

    use MissionEnseignementFormAwareTrait;

    public function ajouterAction() : ViewModel
    {
        /** @var Doctorant $doctorant */
        $id = $this->params()->fromRoute('doctorant');
        $doctorant = $this->doctorantService->getRepository()->find($id);

        $mission = new MissionEnseignement();
        $mission->setDoctorant($doctorant);

        $form = $this->getMissionEnseignementForm();
        $form->setAttribute('action', $this->url()->fromRoute('doctorant/mission-enseignement/ajouter', ['doctorant' => $doctorant->getId()], [], true));
        $form->bind($mission);

        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            $form->setData($data);
            if ($form->isValid()) {
                $this->getMissionEnseignementService()->create($mission);
                exit();
            }
        }

        $vm = new ViewModel([
            'title' => "Ajouter une mission d'enseignement pour ".$doctorant->getIndividu()->getNomComplet(),
            'form' => $form
        ]);
        $vm->setTemplate('default/default-form');
        return $vm;
    }

    public function retirerAction() : ViewModel
    {
        /** @var MissionEnseignement $mission */
        $id = $this->params()->fromRoute('mission');
        $mission = $this->getMissionEnseignementService()->getRepository()->find($id);

        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            if ($data["reponse"] === "oui") $this->getMissionEnseignementService()->delete($mission);
            exit();
        }

        $vm = new ViewModel();
        if ($mission !== null) {
            $vm->setTemplate('default/confirmation');
            $vm->setVariables([
                'title' => "Suppression de la mission d'enseignement",
                'text' => "La suppression est définitive êtes-vous sûr&middot;e de vouloir continuer ?",
                'action' => $this->url()->fromRoute('doctorant/mission-enseignement/retirer', ["mission" => $mission->getId()], [], true),
            ]);
        }
        return $vm;
    }
}