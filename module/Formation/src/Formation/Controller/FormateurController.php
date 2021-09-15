<?php

namespace Formation\Controller;

use Application\Controller\AbstractController;
use Application\Entity\Db\Individu;
use Application\Service\Individu\IndividuServiceAwareTrait;
use Formation\Entity\Db\Formateur;
use Formation\Entity\Db\Session;
use Formation\Service\Formateur\FormateurServiceAwareTrait;
use UnicaenApp\Service\EntityManagerAwareTrait;
use Zend\View\Model\ViewModel;

class FormateurController extends AbstractController {
    use EntityManagerAwareTrait;
    use FormateurServiceAwareTrait;
    use IndividuServiceAwareTrait;

    public function ajouterAction()
    {
        /** @var Session $session */
        $session = $this->getEntityManager()->getRepository(Session::class)->getRequestedSession($this);

        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            /** @var Individu $individu */
            $individu = $this->getIndividuService()->getRepository()->find($data["individu"]["id"]);
            if ($individu !== null) {
                $formateur = new Formateur();
                $formateur->setSession($session);
                $formateur->setIndividu($individu);
                $this->getFormateurService()->create($formateur);
            }
        }

        return new ViewModel([
            'title' => "Ajout d'un formateur pour la session " . $session->getFormation()->getLibelle() . " #" . $session->getIndex(),
            'session' => $session,
        ]);
    }

    public function retirerAction() {
        /** @var Formateur $formateur */
        $formateur = $this->getEntityManager()->getRepository(Formateur::class)->getRequestedFormateur($this);

        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            if ($data["reponse"] === "oui") $this->getFormateurService()->delete($formateur);
            exit();
        }

        $vm = new ViewModel();
        if ($formateur !== null) {
            $vm->setTemplate('formation/default/confirmation');
            $vm->setVariables([
                'title' => "Retrait du formateur&middot;trice " . $formateur->getIndividu()->getNomComplet(),
                'text' => "La suppression est définitive êtes-vous sûr&middot;e de vouloir continuer ?",
                'action' => $this->url()->fromRoute('formation/formateur/retirer', ["formateur" => $formateur->getId()], [], true),
            ]);
        }
        return $vm;

    }
}