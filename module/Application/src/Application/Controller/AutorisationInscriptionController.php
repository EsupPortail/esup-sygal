<?php

namespace Application\Controller;

use Application\Entity\Db\AutorisationInscription;
use Application\Entity\Db\Rapport;
use Application\Form\AutorisationInscriptionFormAwareTrait;
use Application\Service\AnneeUniv\AnneeUnivServiceAwareTrait;
use Application\Service\AutorisationInscription\AutorisationInscriptionServiceAwareTrait;
use Application\Service\Rapport\RapportServiceAwareTrait;
use Application\Service\Source\SourceServiceAwareTrait;
use ComiteSuiviIndividuel\Service\Membre\MembreServiceAwareTrait;
use Doctrine\ORM\NoResultException;
use Individu\Service\IndividuServiceAwareTrait;
use Laminas\Http\Response;
use Laminas\View\Model\ViewModel;
use These\Entity\Db\TheseAnneeUniv;
use These\Service\These\TheseServiceAwareTrait;
use These\Service\TheseAnneeUniv\TheseAnneeUnivServiceAwareTrait;
use UnicaenApp\Exception\RuntimeException;

class AutorisationInscriptionController extends AbstractController
{
    use MembreServiceAwareTrait;
    use AnneeUnivServiceAwareTrait;
    use SourceServiceAwareTrait;
    use TheseServiceAwareTrait;
    use RapportServiceAwareTrait;
    use IndividuServiceAwareTrait;
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
                    $theseAnneeUnivPremiereInscription = $this->theseAnneeUnivService->initFromAutorisationInscription($autorisationInscription);
                    $these->addAnneesUniv1ereInscription($theseAnneeUnivPremiereInscription);
                    foreach ($these->getAnneesUnivInscription() as $anneeUniv) {
                        /** @var TheseAnneeUniv $anneeUniv */
                        if ($anneeUniv->getPremiereAnnee() === $theseAnneeUnivPremiereInscription->getPremiereAnnee()) {
                            $this->flashMessenger()->addErrorMessage("L'année universitaire {$theseAnneeUnivPremiereInscription->getAnneeUnivToString()} a déjà reçu une autorisation pour une réinscription.");
                            return $this->redirect()->toRoute('rapport-csi/consulter', ['these' => $these->getId()]);
                        }
                    }
                    if (!$these->getSource()->getImportable()) $this->theseService->saveThese($these);
                }

                if (!$these->getSource()->getImportable()) {
                    $this->autorisationInscriptionService->create($autorisationInscription);
                    $this->flashMessenger()->addSuccessMessage("Autorisation de réinscription pour l'année {$autorisationInscription->getAnneeUniv()->getAnneeUnivToString()} effectuée avec succès.");
                }

                if ($redirectUrl = $this->params()->fromQuery('redirect')) {
                    return $this->redirect()->toUrl($redirectUrl);
                }

                return $this->redirect()->toRoute('rapport-csi/consulter', ['these' => $these->getId()]);
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

//        if ($rapport->getTypeRapport() !== $this->typeRapport) {
//            throw new RuntimeException("Type de rapport attendu : " . $this->typeRapport->getCode());
//        }

        return $rapport;
    }
}
