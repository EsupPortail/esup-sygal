<?php /** @noinspection PhpUnusedAliasInspection */

namespace Formation\Controller;

use Application\Controller\AbstractController;
use Formation\Service\Session\SessionServiceAwareTrait;
use Individu\Entity\Db\Individu;
use Individu\Service\IndividuServiceAwareTrait;
use Formation\Entity\Db\Formateur;
use Formation\Entity\Db\Session;
use Formation\Service\Formateur\FormateurServiceAwareTrait;
use UnicaenApp\Service\EntityManagerAwareTrait;
use Laminas\View\Model\ViewModel;

class FormateurController extends AbstractController {
    use EntityManagerAwareTrait;
    use FormateurServiceAwareTrait;
    use SessionServiceAwareTrait;
    use IndividuServiceAwareTrait;

    public function ajouterAction() : ViewModel
    {
        $session = $this->getSessionService()->getRepository()->getRequestedSession($this);

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

    public function retirerAction() : ViewModel
    {
        $formateur = $this->getFormateurService()->getRepository()->getRequestedFormateur($this);

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