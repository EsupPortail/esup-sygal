<?php

namespace Application\Controller\Rapport;

use Application\Controller\AbstractController;
use Application\Entity\Db\Rapport;
use Application\Entity\Db\RapportAvis;
use Application\EventRouterReplacerAwareTrait;
use Application\Filter\IdifyFilter;
use Application\Filter\IdifyFilterAwareTrait;
use Application\Form\Rapport\RapportAvisForm;
use Individu\Service\IndividuServiceAwareTrait;
use Application\Service\Rapport\Avis\RapportAvisServiceAwareTrait;
use Application\Service\Rapport\RapportServiceAwareTrait;
use Doctrine\ORM\NoResultException;
use UnicaenApp\Exception\RuntimeException;
use Laminas\Http\Response;

class RapportAvisController extends AbstractController
{
    use RapportServiceAwareTrait;
    use RapportAvisServiceAwareTrait;
    use IdifyFilterAwareTrait;
    use IndividuServiceAwareTrait;
    use EventRouterReplacerAwareTrait;

    /**
     * @var string À redéfinir par les sous-classes.
     */
    protected $routeName;

    /**
     * @var string
     */
    protected $rapportAvisAjouteEventName = 'RAPPORT_AVIS_AJOUTE';
    protected $rapportAvisModifieEventName = 'RAPPORT_AVIS_MODIFIE';

    /**
     * @var RapportAvisForm
     */
    protected $form;

    /**
     * @param RapportAvisForm $form
     */
    public function setForm(RapportAvisForm $form)
    {
        $this->form = $form;
    }

    /**
     * @return array|\Laminas\Http\Response
     */
    public function ajouterAction()
    {
        $rapport = $this->requestedRapport();

        $this->initForm();

        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            $this->form->setData($data);
            if ($this->form->isValid()) {
                /** @var RapportAvis $rapportAvis */
                $rapportAvis = $this->form->getData();
                $rapportAvis->setRapport($rapport);
                $this->rapportAvisService->saveRapportAvis($rapportAvis);

                // déclenchement d'un événement "avis ajouté"
                $this->events->trigger(
                    $this->rapportAvisAjouteEventName,
                    $rapportAvis,
                    []
                );

                $this->flashMessenger()->addSuccessMessage("Avis enregistré avec succès.");

                if ($redirectUrl = $this->params()->fromQuery('redirect')) {
                    return $this->redirect()->toUrl($redirectUrl);
                }

                return $this->redirect()->toRoute('these/identite', ['these' => $rapport->getThese()->getId()]);
            }
        }

        return [
            'rapport' => $rapport,
            'form' => $this->form,
        ];
    }

    public function modifierAction()
    {
        $rapportAvis = $this->requestedRapportAvis();
        $these = $rapportAvis->getRapport()->getThese();

        $this->initForm();
        $this->form->bind($rapportAvis);

        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            $this->form->setData($data);
            if ($this->form->isValid()) {
                $this->rapportAvisService->saveRapportAvis($rapportAvis);

                // déclenchement d'un événement "avis modifié"
                $this->events->trigger(
                    $this->rapportAvisModifieEventName,
                    $rapportAvis,
                    []
                );

                $this->flashMessenger()->addSuccessMessage("Avis modifié avec succès.");

                if ($redirectUrl = $this->params()->fromQuery('redirect')) {
                    return $this->redirect()->toUrl($redirectUrl);
                }

                return $this->redirect()->toRoute('these/identite', ['these' => $these->getId()]);
            }
        }

        return [
            'rapport' => $rapportAvis->getRapport(),
            'form' => $this->form,
        ];
    }

    public function supprimerAction(): Response
    {
        $rapportAvis = $this->requestedRapportAvis();
        $these = $rapportAvis->getRapport()->getThese();

        $this->rapportAvisService->deleteRapportAvis($rapportAvis);

        $this->flashMessenger()->addSuccessMessage("Avis supprimé avec succès.");

        if ($redirectUrl = $this->params()->fromQuery('redirect')) {
            return $this->redirect()->toUrl($redirectUrl);
        }

        return $this->redirect()->toRoute('these/identite', ['these' => $these->getId()]);
    }

    /**
     * @return Rapport
     */
    private function requestedRapport(): Rapport
    {
        $id = $this->params()->fromRoute('rapport') ?: $this->params()->fromQuery('rapport');
        try {
            $rapport = $this->rapportService->findRapportById($id);
        } catch (NoResultException $e) {
            throw new RuntimeException("Aucun rapport trouvé avec cet id", 0, $e);
        }

        return $rapport;
    }

    /**
     * @return RapportAvis
     */
    private function requestedRapportAvis(): RapportAvis
    {
        $id = $this->params()->fromRoute('rapportAvis') ?: $this->params()->fromQuery('rapportAvis');
        try {
            $rapportAvis = $this->rapportAvisService->findRapportAvisById($id);
        } catch (NoResultException $e) {
            throw new RuntimeException("Aucun avis trouvé avec cet id : $id", 0, $e);
        }

        return $rapportAvis;
    }

    protected function initForm()
    {
        $this->form->setAvisPossibles(RapportAvis::AVIS);
    }
}
