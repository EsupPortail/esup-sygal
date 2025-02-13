<?php

namespace RapportActivite\Controller\Avis;

use Application\Controller\AbstractController;
use Application\EventRouterReplacerAwareTrait;
use Application\Filter\IdifyFilterAwareTrait;
use Validation\Service\ValidationThese\ValidationTheseServiceAwareTrait;
use Closure;
use Doctrine\ORM\NoResultException;
use Individu\Service\IndividuServiceAwareTrait;
use Laminas\EventManager\EventInterface;
use Laminas\Http\Response;
use RapportActivite\Entity\Db\RapportActivite;
use RapportActivite\Entity\Db\RapportActiviteAvis;
use RapportActivite\Event\Avis\RapportActiviteAvisEvent;
use RapportActivite\Service\Avis\RapportActiviteAvisServiceAwareTrait;
use RapportActivite\Service\RapportActiviteServiceAwareTrait;
use RapportActivite\Service\Validation\RapportActiviteValidationServiceAwareTrait;
use UnicaenApp\Exception\RuntimeException;
use UnicaenAvis\Entity\Db\Avis;
use UnicaenAvis\Entity\Db\AvisTypeValeurComplem;
use UnicaenAvis\Form\AvisForm;
use UnicaenAvis\Service\AvisServiceAwareTrait;

class RapportActiviteAvisController extends AbstractController
{
    use RapportActiviteServiceAwareTrait;
    use RapportActiviteAvisServiceAwareTrait;
    use RapportActiviteValidationServiceAwareTrait;
    use ValidationTheseServiceAwareTrait;
    use IndividuServiceAwareTrait;
    use AvisServiceAwareTrait;

    use IdifyFilterAwareTrait;
    use EventRouterReplacerAwareTrait;

    private AvisForm $form;

    /**
     * @param \UnicaenAvis\Form\AvisForm $form
     */
    public function setForm(AvisForm $form)
    {
        $this->form = $form;
    }

    /**
     * @return array|\Laminas\Http\Response
     */
    public function ajouterAction()
    {
        $rapportActivite = $this->requestedRapport();
        $avisType = $this->avisService->findOneAvisTypeById($this->params('typeAvis'));

        $avis = new Avis();
        $avis->setAvisType($avisType);

        $this->form->setAvisTypeValeurComplemsFilter($this->getAvisTypeValeurComplemsFilter($rapportActivite));
        $this->form->bind($avis);
        $this->form->setAttribute('action', $this->url()->fromRoute(
            'rapport-activite/avis/ajouter', [], ['query' => $this->params()->fromQuery()], true
        ));

        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            $this->form->setData($data);
            if ($this->form->isValid()) {
                /** @var Avis $avis */
                $avis = $this->form->getData();

                $rapportActiviteAvis = $this->rapportActiviteAvisService->newRapportAvis($rapportActivite);
                $rapportActiviteAvis->setAvis($avis);
                $this->rapportActiviteAvisService->saveNewRapportAvis($rapportActiviteAvis);
                $event = $this->rapportActiviteAvisService->triggerEventAvisAjoute($rapportActiviteAvis);

                $this->flashMessenger()->addSuccessMessage("Avis enregistré avec succès.");
                $this->flashMessengerAddMessagesFromEvent($event);
                $this->flashMessengerAddMessageIfRedirect($rapportActiviteAvis);

                if (!$request->isXmlHttpRequest()) {
                    if ($redirectUrl = $this->params()->fromQuery('redirect')) {
                        return $this->redirect()->toUrl($redirectUrl);
                    }
                    return $this->redirect()->toRoute('rapport-activite/consulter', [
                        'these' => $rapportActivite->getThese()->getId(),
                        'rapport' => $rapportActivite->getId(),
                    ]);
                }
            }
        }

        return [
            'rapportActivite' => $rapportActivite,
            'form' => $this->form,
            'title' => "Nouvel avis à propos d'un rapport",
        ];
    }

    public function getAvisTypeValeurComplemsFilter(RapportActivite $rapportActivite): ?Closure
    {
        if (! $rapportActivite->estFinContrat()) {
            return null;
        }

        // Rapports de fin de contrat : aucun avis Dir/UR attendu donc on écarte les compléments qui génèreraient une
        // case à cocher permettant de signaler une absence d'avis.
        return function (AvisTypeValeurComplem $avisTypeValeurComplem) {
            return !in_array($avisTypeValeurComplem->getCode(), [
                RapportActiviteAvis::AVIS_RAPPORT_ACTIVITE_GEST__AVIS_RAPPORT_ACTIVITE_VALEUR_INCOMPLET__MANQUE_AVIS_DIRECTION_THESE,
                RapportActiviteAvis::AVIS_RAPPORT_ACTIVITE_GEST__AVIS_RAPPORT_ACTIVITE_VALEUR_INCOMPLET__MANQUE_AVIS_DIRECTION_UR,
            ]);
        };
    }

    public function modifierAction()
    {
        $rapportActiviteAvis = $this->requestedRapportAvis();
        $rapportActivite = $rapportActiviteAvis->getRapportActivite();
        $these = $rapportActivite->getThese();

        $this->form->setAvisTypeValeurComplemsFilter($this->getAvisTypeValeurComplemsFilter($rapportActivite));
        $this->form->bind($rapportActiviteAvis->getAvis());
        $this->form->setAttribute('action', $this->url()->fromRoute(
            'rapport-activite/avis/modifier', [], ['query' => $this->params()->fromQuery()], true
        ));

        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            $this->form->setData($data);
            if ($this->form->isValid()) {
                $this->rapportActiviteAvisService->updateRapportAvis($rapportActiviteAvis);
                $event = $this->rapportActiviteAvisService->triggerEventAvisModifie($rapportActiviteAvis);

                $this->flashMessenger()->addSuccessMessage("Avis modifié avec succès.");
                $this->flashMessengerAddMessagesFromEvent($event);
                $this->flashMessengerAddMessageIfRedirect($rapportActiviteAvis);

                if (!$request->isXmlHttpRequest()) {
                    if ($redirectUrl = $this->params()->fromQuery('redirect')) {
                        return $this->redirect()->toUrl($redirectUrl);
                    }
                    return $this->redirect()->toRoute('rapport-activite/consulter', [
                        'these' => $rapportActivite->getThese()->getId(),
                        'rapport' => $rapportActivite->getId(),
                    ]);
                }
            }
        }

        return [
            'rapport' => $rapportActivite,
            'form' => $this->form,
            'title' => "Modification d'un avis à propos d'un rapport",
        ];
    }

    public function supprimerAction(): Response
    {
        $rapportActiviteAvis = $this->requestedRapportAvis();
        $rapportActivite = $rapportActiviteAvis->getRapportActivite();

        $this->rapportActiviteAvisService->deleteRapportAvis($rapportActiviteAvis);
        $event = $this->rapportActiviteAvisService->triggerEventAvisSupprime($rapportActiviteAvis);

        $this->flashMessenger()->addSuccessMessage("Avis supprimé avec succès.");
        $this->flashMessengerAddMessagesFromEvent($event);
        $this->flashMessengerAddMessageIfRedirect($rapportActiviteAvis);

        if ($redirectUrl = $this->params()->fromQuery('redirect')) {
            return $this->redirect()->toUrl($redirectUrl);
        }

        return $this->redirect()->toRoute('rapport-activite/consulter', [
            'these' => $rapportActivite->getThese()->getId(),
            'rapport' => $rapportActivite->getId(),
        ]);
    }

    /**
     * @param RapportActiviteAvisEvent $event
     * @param string $paramName
     */
    protected function flashMessengerAddMessagesFromEvent(EventInterface $event, string $paramName = 'logs')
    {
        if ($messages = $event->getMessages()) {
            foreach ($messages as $namespace => $message) {
                $this->flashMessenger()->addMessage($message, $namespace);
            }
        }
    }

    private function flashMessengerAddMessageIfRedirect(RapportActiviteAvis $rapportActiviteAvis)
    {
        if ($redirectUrl = $this->params()->fromQuery('redirect')) {
            // On ajoute un message contenant un lien vers la page des rapports du doctorant,
            // si ce n'est pas redondant avec la page de retour demandée.
            $theseRapportActivitePageUrl = $this->url()->fromRoute(
                'rapport-activite/lister',
                ['these' => $rapportActiviteAvis->getRapportActivite()->getThese()->getId()],
            );
            if ($redirectUrl !== $theseRapportActivitePageUrl) {
                $this->flashMessenger()->addInfoMessage(sprintf(
                        'Si besoin, vous pouvez aller sur la <a href="%s">page des rapports d\'activité de %s.</a>',
                        $theseRapportActivitePageUrl,
                        $rapportActiviteAvis->getRapportActivite()->getThese()->getDoctorant())
                );
            }
        }
    }

    /**
     * @return RapportActivite
     */
    private function requestedRapport(): RapportActivite
    {
        $id = $this->params()->fromRoute('rapport') ?: $this->params()->fromQuery('rapport');

        $rapport = $this->rapportActiviteService->fetchRapportById($id);
        if ($rapport === null) {
            throw new RuntimeException("Aucun rapport trouvé avec l'id spécifié");
        }

        return $rapport;
    }

    /**
     * @return RapportActiviteAvis
     */
    private function requestedRapportAvis(): RapportActiviteAvis
    {
        $id = $this->params()->fromRoute('rapportAvis') ?: $this->params()->fromQuery('rapportAvis');
        try {
            $rapportAvis = $this->rapportActiviteAvisService->findRapportAvisById($id);
        } catch (NoResultException $e) {
            throw new RuntimeException("Aucun avis trouvé avec cet id : $id", 0, $e);
        }

        return $rapportAvis;
    }
}
