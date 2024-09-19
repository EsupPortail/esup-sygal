<?php

namespace InscriptionAdministrative\Controller;

use Admission\Service\Admission\AdmissionServiceAwareTrait;
use Application\Controller\AbstractController;
use Application\Service\AnneeUniv\AnneeUnivServiceAwareTrait;
use InscriptionAdministrative\Entity\Db\InscriptionAdministrative;
use InscriptionAdministrative\Process\Reception\ReceptionProcessAwareTrait;
use InscriptionAdministrative\Process\Reception\ReceptionProcessResult;
use InscriptionAdministrative\Rule\Reception\ReceptionRuleAwareTrait;
use InscriptionAdministrative\Service\InscriptionAdministrativeServiceAwareTrait;
use Laminas\Http\Response;
use Laminas\View\Model\ViewModel;
use RuntimeException;
use UnicaenApp\Traits\MessageAwareInterface;

class InscriptionAdministrativeController extends AbstractController
{
    use ReceptionRuleAwareTrait;
    use InscriptionAdministrativeServiceAwareTrait;
    use AdmissionServiceAwareTrait;
    use AnneeUnivServiceAwareTrait;

    use ReceptionProcessAwareTrait;

    private InscriptionAdministrative $inscriptionAdministrative;

    public function indexAction(): Response
    {
        return $this->redirect()->toRoute('inscription-administrative/recherche/index');
    }

    public function voirAction(): ViewModel
    {
        $id = $this->params('id');

        /** @var \InscriptionAdministrative\Entity\Db\InscriptionAdministrative $inscriptionAdministrative */
        $this->inscriptionAdministrative = $this->inscriptionAdministrativeService->getRepository()->findOneById($id);

        $admission = $this->admissionService->getRepository()->findOneByNumeroCandidature(
            $this->inscriptionAdministrative->getNoCandidat()
        );

        $vm = new ViewModel([
            'inscriptionAdministrative' => $this->inscriptionAdministrative,
            'admission' => $admission,
            'anneeUnivService' => $this->anneeUnivService,
        ]);

        $childModel = $this->createReceptionProcessChildModel();
        $vm->addChild($childModel, 'reception-process');

        return $vm;
    }

    private function createReceptionProcessChildModel(): ViewModel
    {
        // Lancement de la Rule pour savoir si les conditions pour créer la thèse et le doctorant sont rassemblées.
        $this->receptionRule->setInscriptionAdministrative($this->inscriptionAdministrative);
        $this->receptionRule->execute();

        $childModel = new ViewModel();
        $childModel->setVariables([
            'inscriptionAdministrative' => $this->inscriptionAdministrative,
            'receptionRule' => $this->receptionRule,
        ]);
        $childModel->setTemplate('inscription-administrative/inscription-administrative/partial/reception-process');

        return $childModel;
    }

    /**
     * Réalisation des opérations faisables.
     */
    public function processAction(): Response
    {
        $this->inscriptionAdministrative = $this->getRequestedInscriptionAdministrative();

        // Lancement du process pour créer la thèse et/ou le doctorant.
        $this->receptionProcess->setInscriptionAdministrative($this->inscriptionAdministrative);
        $this->receptionProcess->run();
        $result = $this->receptionProcess->getResult();
        if (!$result->isSuccess()) {
            $e = $result->getException();
            $this->flashMessenger()->addErrorMessage("Erreur survenue pendant l'opération ! " . $e?->getMessage());

            return $this->redirect()->toRoute('inscription-administrative/voir', ['id' => $this->inscriptionAdministrative->getId()]);
        }

        $this->flashMessenger()->addSuccessMessage(
            "Opération réalisée avec succès !" .
            '<ul><li>'.$result->getMessage('</li><li>', MessageAwareInterface::SUCCESS).'</li></ul>');

//        return $this->redirect()->toRoute('these/identite', ['these' => $result->getCreatedThese()->getId()]);
        return $this->redirect()->toRoute('inscription-administrative/voir', ['id' => $this->inscriptionAdministrative->getId()]);
    }

    private function runReceptionProcess(bool $dryRun = false): ReceptionProcessResult
    {
        $this->receptionProcess->setInscriptionAdministrative($this->inscriptionAdministrative);
        $this->receptionProcess->setDryRun($dryRun);
        $this->receptionProcess->run();

        return $this->receptionProcess->getResult();
    }

    private function getRequestedInscriptionAdministrative(): ?InscriptionAdministrative
    {
        /** @var InscriptionAdministrative $inscriptionAdministrative */
        $inscriptionAdministrative = $this->inscriptionAdministrativeService->getRepository()->find($this->params('id'));

        return $inscriptionAdministrative;
    }
}