<?php

namespace Admission\Controller\Avis;

use Application\Controller\AbstractController;
use Application\EventRouterReplacerAwareTrait;
use Application\Filter\IdifyFilterAwareTrait;
use Application\Service\Validation\ValidationServiceAwareTrait;
use Doctrine\ORM\NoResultException;
use Individu\Service\IndividuServiceAwareTrait;
use Laminas\EventManager\EventInterface;
use Laminas\Http\Response;
use Admission\Entity\Db\AdmissionAvis;
use Admission\Event\Avis\AdmissionAvisEvent;
use Admission\Service\Avis\AdmissionAvisServiceAwareTrait;
use Admission\Service\Admission\AdmissionServiceAwareTrait;
use Admission\Service\Validation\AdmissionValidationServiceAwareTrait;
use UnicaenApp\Exception\RuntimeException;
use UnicaenAvis\Entity\Db\Avis;
use UnicaenAvis\Form\AvisForm;
use UnicaenAvis\Service\AvisServiceAwareTrait;

class AdmissionAvisController extends AbstractController
{
    use AdmissionServiceAwareTrait;
    use AdmissionAvisServiceAwareTrait;
    use AdmissionValidationServiceAwareTrait;
    use ValidationServiceAwareTrait;
    use IndividuServiceAwareTrait;
    use AvisServiceAwareTrait;

    use IdifyFilterAwareTrait;
    use EventRouterReplacerAwareTrait;

    private AvisForm $form;

    /**
     * @param AvisForm $form
     */
    public function setForm(AvisForm $form): void
    {
        $this->form = $form;
    }

    /**
     * @return array|Response
     */
    public function aviserAction(): Response|array
    {
        $admission = $this->admissionService->getRepository()->findRequestedAdmission($this);
        $avisType = $this->avisService->findOneAvisTypeById($this->params('typeAvis'));

        $avis = new Avis();
        $avis->setAvisType($avisType);

        $this->form->bind($avis);
        $this->form->setAttribute('action', $this->url()->fromRoute(
            'admission/aviser', [], ['query' => $this->params()->fromQuery()], true
        ));

        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            $this->form->setData($data);
            if ($this->form->isValid()) {
                /** @var Avis $avis */
                $avis = $this->form->getData();

                $admissionAvis = $this->admissionAvisService->newAdmissionAvis($admission);
                $admissionAvis->setAvis($avis);
                $this->admissionAvisService->saveNewAdmissionAvis($admissionAvis);
                $event = $this->admissionAvisService->triggerEventAvisAjoute($admissionAvis);
                $this->admissionService->changeEtatAdmission($admissionAvis, "aviser");

                $this->flashMessenger()->addSuccessMessage("Avis enregistré avec succès.");
                $this->flashMessengerAddMessagesFromEvent($event);

                if (!$request->isXmlHttpRequest()) {
                    if ($redirectUrl = $this->params()->fromQuery('redirect')) {
                        return $this->redirect()->toUrl($redirectUrl);
                    }

                    $individu = $admission->getIndividu()->getId();
                    return $this->redirect()->toRoute('admission/document', ['individu' => $individu]);
                }
            }
        }

        return [
            'admission' => $admission,
            'form' => $this->form,
            'title' => "Nouvel avis à propos du dossier d'admission de ".$admission->getIndividu()->getNomComplet(),
        ];
    }

    public function modifierAction(): Response|array
    {
        $admissionAvis = $this->requestedAdmissionAvis();
        $admission = $admissionAvis->getAdmission();
        $individu = $admission->getIndividu();

        $this->form->bind($admissionAvis->getAvis());
        $this->form->setAttribute('action', $this->url()->fromRoute(
            'admission/modifierAvis', [], ['query' => $this->params()->fromQuery()], true
        ));

        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            $this->form->setData($data);
            if ($this->form->isValid()) {
                $this->admissionAvisService->updateAdmissionAvis($admissionAvis);
                $event = $this->admissionAvisService->triggerEventAvisModifie($admissionAvis);
                $this->admissionService->changeEtatAdmission($admissionAvis, "modifier");

                $this->flashMessenger()->addSuccessMessage("Avis modifié avec succès.");
                $this->flashMessengerAddMessagesFromEvent($event);

                if (!$request->isXmlHttpRequest()) {
                    if ($redirectUrl = $this->params()->fromQuery('redirect')) {
                        return $this->redirect()->toUrl($redirectUrl);
                    }

                    return $this->redirect()->toRoute('admission/document', ['individu' => $individu->getId()]);
                }
            }
        }

        return [
            'admission' => $admission,
            'form' => $this->form,
            'title' => "Modification d'un avis à propos du dossier d'admission de ".$individu->getNomComplet(),
        ];
    }

    public function desaviserAction(): Response
    {
        $admissionAvis = $this->requestedAdmissionAvis();
        $admission = $admissionAvis->getAdmission();
        $individu = $admission->getIndividu();

        $this->admissionAvisService->deleteAdmissionAvis($admissionAvis);
        $event = $this->admissionAvisService->triggerEventAvisSupprime($admissionAvis);
        $this->admissionService->changeEtatAdmission($admissionAvis, "desaviser");

        $this->flashMessenger()->addSuccessMessage("Avis supprimé avec succès.");
        $this->flashMessengerAddMessagesFromEvent($event);

        if ($redirectUrl = $this->params()->fromQuery('redirect')) {
            return $this->redirect()->toUrl($redirectUrl);
        }

        return $this->redirect()->toRoute('admission/document', ['individu' => $individu->getId()]);
    }

    /**
     * @param AdmissionAvisEvent $event
     * @param string $paramName
     */
    protected function flashMessengerAddMessagesFromEvent(EventInterface $event, string $paramName = 'logs'): void
    {
        if ($messages = $event->getMessages()) {
            foreach ($messages as $namespace => $message) {
                $this->flashMessenger()->addMessage($message, $namespace);
            }
        }
    }

    /**
     * @return AdmissionAvis
     */
    private function requestedAdmissionAvis(): AdmissionAvis
    {
        $id = $this->params()->fromRoute('admissionAvis') ?: $this->params()->fromQuery('admissionAvis');
        try {
            $admissionAvis = $this->admissionAvisService->getRepository()->findAdmissionAvisById($id);
        } catch (NoResultException $e) {
            throw new RuntimeException("Aucun avis trouvé avec cet id : $id", 0, $e);
        }

        return $admissionAvis;
    }
}
