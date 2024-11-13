<?php

namespace Application\Controller;

use Application\Entity\Db\AutorisationInscription;
use Application\Entity\Db\Rapport;
use Application\Form\AutorisationInscriptionFormAwareTrait;
use Application\Service\AutorisationInscription\AutorisationInscriptionServiceAwareTrait;
use Application\Service\Rapport\RapportServiceAwareTrait;
use Doctrine\ORM\NoResultException;
use Laminas\Http\Response;
use Laminas\View\Model\ViewModel;
use These\Service\These\TheseServiceAwareTrait;
use These\Service\TheseAnneeUniv\TheseAnneeUnivServiceAwareTrait;
use UnicaenApp\Exception\RuntimeException;

class AutorisationInscriptionController extends AbstractController
{
    use TheseServiceAwareTrait;
    use RapportServiceAwareTrait;
    use AutorisationInscriptionFormAwareTrait;
    use AutorisationInscriptionServiceAwareTrait;
    use TheseAnneeUnivServiceAwareTrait;

    public function ajouterAction(): Response|ViewModel
    {
        $form = $this->getAutorisationInscriptionForm();
        $rapport = $this->requestedRapport();
        $these = $rapport->getThese();
        $autorisationInscription = $this->autorisationInscriptionService->initAutorisationInscriptionFromRapport($rapport);

        $form->bind($autorisationInscription);
        $form->setAttribute('action', $this->url()->fromRoute('autoriser-inscription/ajouter', ['rapport' => $rapport->getId()], [], true));
        $viewModel = new ViewModel([
            'title' => "Autorisation d'inscription pour l'année ".$autorisationInscription->getAnneeUniv()->getAnneeUnivToString()." de ".$these->getDoctorant()->getIndividu(),
            'form' => $form,
        ]);

        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            $form->setData($data);
            if ($form->isValid()) {
                /** @var AutorisationInscription $autorisationInscription */
                $autorisationInscription = $form->getData();

                if($autorisationInscription->getThese() && $autorisationInscription->getAutorisationInscription() === true){
                    $theseAnneeUnivInscription = $this->theseAnneeUnivService->initFromAutorisationInscription($autorisationInscription);
                    $these->addAnneesUnivInscription($theseAnneeUnivInscription);
                    $this->theseService->saveThese($these);
                }

                $this->autorisationInscriptionService->create($autorisationInscription);
                $this->flashMessenger()->addSuccessMessage("Avis concernant l'autorisation de réinscription pour l'année {$autorisationInscription->getAnneeUniv()->getAnneeUnivToString()} effectuée avec succès.");

                if ($redirectUrl = $this->params()->fromQuery('redirect')) {
                    return $this->redirect()->toUrl($redirectUrl);
                }
            }
        }
        return $viewModel;
    }

    /**
     * @return Rapport
     */
    protected function requestedRapport(): Rapport
    {
        $id = $this->params()->fromRoute('rapport') ?: $this->params()->fromQuery('rapport');
        try {
            $rapport = $this->rapportService->findRapportById($id);
        } catch (NoResultException $e) {
            throw new RuntimeException("Aucun rapport trouvé avec cet id", 0, $e);
        }

        return $rapport;
    }
}
