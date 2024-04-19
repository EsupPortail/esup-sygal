<?php

namespace Formation\Controller;

use Application\Controller\AbstractController;
use Formation\Service\Module\ModuleServiceAwareTrait;
use Formation\Service\Notification\FormationNotificationFactoryAwareTrait;
use Formation\Service\Session\SessionServiceAwareTrait;
use Laminas\Http\Response;
use Notification\Exception\RuntimeException;
use Notification\Service\NotifierServiceAwareTrait;
use Structure\Service\Etablissement\EtablissementServiceAwareTrait;
use Formation\Entity\Db\Formation;
use Formation\Form\Formation\FormationFormAwareTrait;
use Formation\Service\Formation\FormationServiceAwareTrait;
use UnicaenApp\Service\EntityManagerAwareTrait;
use Laminas\View\Model\ViewModel;

class FormationController extends AbstractController
{
    use EntityManagerAwareTrait;
    use FormationServiceAwareTrait;
    use ModuleServiceAwareTrait;
    use SessionServiceAwareTrait;
    use FormationFormAwareTrait;
    use NotifierServiceAwareTrait;
    use FormationNotificationFactoryAwareTrait;

    use EtablissementServiceAwareTrait;

    public function afficherAction() : ViewModel
    {
        $formation = $this->getFormationService()->getRepository()->getRequestedFormation($this);
        $sessions = $this->getSessionService()->getRepository()->fetchSessionsByFormation($formation, 'id', 'asc', true);

        return new ViewModel([
            'formation' => $formation,
            'sessions' => $sessions,
        ]);
    }

    public function ajouterAction() : ViewModel
    {
        $module = $this->getModuleService()->getRepository()->getRequestedModule($this);
        $formation = new Formation();

        if ($module !== null) $formation->setModule($module);

        $form = $this->getFormationForm();
        $form->setAttribute('action', $this->url()->fromRoute('formation/formation/ajouter', ['module' => ($module)?$module->getId():null], [], true));
        $form->bind($formation);

        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            $form->setData($data);
            if ($form->isValid()) {
                $this->getFormationService()->create($formation);
            }
        }

        $vm = new ViewModel([
            'title' => "Ajout d'une formation",
            'form' => $form,
        ]);
        $vm->setTemplate('formation/formation/modifier');
        return $vm;
    }

    public function modifierAction() : ViewModel
    {
        $formation = $this->getFormationService()->getRepository()->getRequestedFormation($this);

        $form = $this->getFormationForm();
        $form->setAttribute('action', $this->url()->fromRoute('formation/formation/modifier', [], [], true));
        $form->bind($formation);

        $oldSite = $formation->getSite();
        $oldStructure = $formation->getTypeStructure();
        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            $form->setData($data);
            if ($form->isValid()) {
                // Envoi d'un mail lors de la création d'une formation spécifique à la liste de diffusion du site déclaré
                // seulement si le site et la structure ont été renseignés
                // et des informations ont été modifiées
                if ($formation->getType() === Formation::TYPE_CODE_SPECIFIQUE && ($formation->getSite() && $formation->getTypeStructure()) && ($formation->getSite() !== $oldSite || $formation->getTypeStructure() !== $oldStructure)) {
                    try {
                        $notif = $this->formationNotificationFactory->createNotificationFormationSpecifiqueAjoutee($formation);
                        $this->notifierService->trigger($notif);
                        $destinataires = implode(', ', array_reduce(array_keys($notif->getTo()), function(array $accu, string $key) use ($notif) {
                            $accu[] = sprintf('%s', $notif->getTo()[$key]);
                            return $accu;
                        }, []));
                        $this->flashMessenger()->addInfoMessage("Le site organisateur et/ou la structure associée ont été modifiés. <br> Une notification par mail a donc été envoyée aux doctorants appartenant à la liste de diffusion suivante : ".$destinataires);
                    } catch (RuntimeException $e) {
                        $ed = $formation->getTypeStructure()->getEcoleDoctorale();
                        $this->flashMessenger()->addErrorMessage("La session a bien été modifiée, cependant aucune liste de diffusion trouvée pour l'ED {$ed} (afin de notifier les doctorants).");
                    }
                }
                $this->getFormationService()->update($formation);
            }
        }

        $vm = new ViewModel([
            'title' => "Modification d'une formation",
            'form' => $form,
        ]);
        $vm->setTemplate('formation/formation/modifier');
        return $vm;
    }

    public function historiserAction() : Response
    {
        $formation = $this->getFormationService()->getRepository()->getRequestedFormation($this);
        $this->getFormationService()->historise($formation);

        $retour = $this->params()->fromQuery('retour');
        if ($retour !== null) return $this->redirect()->toUrl($retour);
        return $this->redirect()->toRoute('formation/formation');

    }

    public function restaurerAction() : Response
    {
        $formation = $this->getFormationService()->getRepository()->getRequestedFormation($this);
        $this->getFormationService()->restore($formation);

        $retour = $this->params()->fromQuery('retour');
        if ($retour !== null) return $this->redirect()->toUrl($retour);
        return $this->redirect()->toRoute('formation/formation');
    }

    public function supprimerAction()
    {
        $formation = $this->getFormationService()->getRepository()->getRequestedFormation($this);

        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            if ($data["reponse"] === "oui") $this->getFormationService()->delete($formation);
            exit();
        }

        $vm = new ViewModel();
        if ($formation !== null) {
            $vm->setTemplate('formation/default/confirmation');
            $vm->setVariables([
                'title' => "Suppression d'une formation #" . $formation->getId(),
                'text' => "La suppression est définitive êtes-vous sûr&middot;e de vouloir continuer ?",
                'action' => $this->url()->fromRoute('formation/formation/supprimer', ["formation" => $formation->getId()], [], true),
            ]);
        }
        return $vm;
    }
}