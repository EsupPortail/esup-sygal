<?php

namespace RapportActivite\Controller;

use Application\Controller\AbstractController;
use Application\Entity\Db\Interfaces\TypeRapportAwareTrait;
use Application\Entity\Db\Interfaces\TypeValidationAwareTrait;
use These\Entity\Db\These;
use Application\Filter\IdifyFilter;
use Fichier\FileUtils;
use Fichier\Service\Fichier\FichierServiceAwareTrait;
use Fichier\Service\Fichier\FichierStorageServiceAwareTrait;
use Laminas\Http\Response;
use Laminas\View\Model\ViewModel;
use RapportActivite\Entity\Db\RapportActivite;
use RapportActivite\Entity\Db\RapportActiviteAvis;
use RapportActivite\Form\RapportActiviteForm;
use RapportActivite\Rule\Televersement\RapportActiviteTeleversementRuleAwareTrait;
use RapportActivite\Service\Avis\RapportActiviteAvisServiceAwareTrait;
use RapportActivite\Service\Fichier\RapportActiviteFichierServiceAwareTrait;
use RapportActivite\Service\RapportActiviteServiceAwareTrait;
use UnicaenApp\Exception\RuntimeException;
use UnicaenAvis\Entity\Db\Avis;

/**
 * @property \RapportActivite\Form\RapportActiviteForm $form
 */
class RapportActiviteController extends AbstractController
{
    use FichierServiceAwareTrait;
    use FichierStorageServiceAwareTrait;
    use RapportActiviteAvisServiceAwareTrait;
    use RapportActiviteServiceAwareTrait;
    use RapportActiviteFichierServiceAwareTrait;
    use RapportActiviteTeleversementRuleAwareTrait;

    use TypeRapportAwareTrait;
    use TypeValidationAwareTrait;

    /**
     * @var \RapportActivite\Form\RapportActiviteForm
     */
    private RapportActiviteForm $form;

    /**
     * @var RapportActivite[]
     */
    private array $rapportsTeleverses = [];

    /**
     * @var \These\Entity\Db\These
     */
    private These $these;

    /**
     * @param \RapportActivite\Form\RapportActiviteForm $form
     */
    public function setForm(RapportActiviteForm $form)
    {
        $this->form = $form;
    }

    /**
     * @return Response|ViewModel
     */
    public function consulterAction()
    {
        $this->these = $this->requestedThese();
        $this->fetchRapportsTeleverses();

        $this->rapportActiviteTeleversementRule->setRapportsTeleverses($this->rapportsTeleverses);
        $this->rapportActiviteTeleversementRule->computeCanTeleverserRapports();
        $isTeleversementPossible = $this->rapportActiviteTeleversementRule->isTeleversementPossible();

        foreach ($this->rapportsTeleverses as $rapport) {
            $avisTypeDispo = $this->rapportActiviteAvisService->findExpectedAvisTypeForRapport($rapport);
            if ($avisTypeDispo === null) {
                $rapport->setRapportAvisPossible(null);
                continue;
            }

            $rapportAvisPossible = new RapportActiviteAvis();
            $rapportAvisPossible
                ->setRapportActivite($rapport)
                ->setAvis((new Avis())->setAvisType($avisTypeDispo));

            $rapport->setRapportAvisPossible($rapportAvisPossible);
        }

        // Gestion du formulaire de dépôt
        $result = $this->ajouterAction();
        if ($result instanceof Response) {
            return $result;
        }

        return new ViewModel([
            'rapports' => $this->rapportsTeleverses,
            'these' => $this->these,
            'form' => $this->form,
            'isTeleversementPossible' => $isTeleversementPossible,

            'typeValidation' => $this->typeValidation,
            'returnUrl' => $this->url()->fromRoute('rapport-activite/consulter', ['these' => $this->these->getId()]),
        ]);
    }

    /**
     * Ajout d'un nouveau rapport.
     */
    public function ajouterAction()
    {
        $this->these = $this->requestedThese();

        $this->initForm();

        $rapport = $this->rapportActiviteService->newRapportActivite($this->these);
        $rapport->setTypeRapport($this->typeRapport);
        $this->form->bind($rapport);

        $request = $this->getRequest();
        if ($request->isPost()) {
            $uploadData = $request->getFiles()->toArray();
            $data = array_merge_recursive(
                $request->getPost()->toArray(),
                $uploadData
            );
            $this->form->setData($data);
            if ($this->form->isValid()) {
                /** @var RapportActivite $rapport */
                $rapport = $this->form->getData();
                if ($this->rapportActiviteTeleversementRule->canTeleverserRapport($rapport)) {
                    $event = $this->rapportActiviteService->saveRapport($rapport, $uploadData);

                    $this->flashMessenger()->addSuccessMessage(sprintf(
                        "Rapport téléversé avec succès sous le nom suivant :<br>'%s'.",
                        $rapport->getFichier()->getNom()
                    ));

                    if ($messages = $event->getMessages()) {
                        foreach ($messages as $namespace => $message) {
                            $this->flashMessenger()->addMessage($message, $namespace);
                        }
                    }
                } else {
                    $this->flashMessenger()->addErrorMessage(
                        "Ce téléversement n'est pas possible. Vérifiez la cohérence entre le type de rapport et l'année universitaire, svp."
                    );
                }

                return $this->redirect()->toRoute('rapport-activite/consulter', ['these' => IdifyFilter::id($this->these)]);
            }
        }

        return false; // pas de vue pour cette action
    }

    /**
     * Suppression d'un rapport.
     */
    public function supprimerAction(): Response
    {
        $rapport = $this->requestedRapport();
        $these = $rapport->getThese();

        $event = $this->rapportActiviteService->deleteRapport($rapport);

        $this->flashMessenger()->addSuccessMessage("Rapport supprimé avec succès.");

        if ($messages = $event->getMessages()) {
            foreach ($messages as $namespace => $message) {
                $this->flashMessenger()->addMessage($message, $namespace);
            }
        }

        return $this->redirect()->toRoute('rapport-activite/consulter', ['these' => IdifyFilter::id($these)]);
    }

    public function telechargerAction()
    {
        $rapport = $this->requestedRapport();

        // s'il s'agit d'un rapport validé, on ajoute à la volée la page de validation
        if ($rapport->estValide()) {
            // l'ajout de la page de validation n'est pas forcément possible
            if ($rapport->supporteAjoutPageValidation()) {
                $exportData = $this->rapportActiviteService->createPageValidationData($rapport);
                $outputFilePath = $this->rapportActiviteFichierService->createFileWithPageValidation($rapport, $exportData);
                FileUtils::downloadFile($outputFilePath);
                exit;
            }
        }

        return $this->forward()->dispatch('Application\Controller\Fichier', [
            'action' => 'telecharger',
            'fichier' => IdifyFilter::id($rapport->getFichier()),
        ]);
    }

    private function fetchRapportsTeleverses()
    {
        $this->rapportsTeleverses = $this->rapportActiviteService->findRapportsForThese($this->these);
    }

    private function initForm()
    {
        $this->form->setAnneesUnivs($this->rapportActiviteTeleversementRule->getAnneesUnivsDisponibles());

        if ($this->rapportActiviteTeleversementRule->canTeleverserRapportAnnuel()) {
            $this->form->addRapportAnnuelSelectOption();
        }
        if ($this->rapportActiviteTeleversementRule->canTeleverserRapportFinContrat()) {
            $this->form->addRapportFinContratSelectOption();
        }
    }

    /**
     * @return RapportActivite
     */
    private function requestedRapport(): RapportActivite
    {
        $id = $this->params()->fromRoute('rapport') ?: $this->params()->fromQuery('rapport');

        $rapport = $this->rapportActiviteService->findRapportById($id);
        if ($rapport === null) {
            throw new RuntimeException("Aucun rapport trouvé avec l'id spécifié");
        }

        if ($rapport->getTypeRapport() !== $this->typeRapport) {
            throw new RuntimeException("Type de rapport attendu : " . $this->typeRapport->getCode());
        }

        return $rapport;
    }
}
