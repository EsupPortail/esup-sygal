<?php

namespace InscriptionAdministrative\Controller;

use Admission\Service\Admission\AdmissionServiceAwareTrait;
use Application\Controller\AbstractController;
use Application\Service\AnneeUniv\AnneeUnivServiceAwareTrait;
use Doctorant\Service\DoctorantServiceAwareTrait;
use InscriptionAdministrative\Entity\Db\InscriptionAdministrative;
use InscriptionAdministrative\Process\Reception\InscriptionAdministrativeReceptionProcessAwareTrait;
use InscriptionAdministrative\Rule\Reception\ReceptionRulePluginManagerAwareTrait;
use InscriptionAdministrative\Service\InscriptionAdministrativeServiceAwareTrait;
use Laminas\Http\Response;
use Laminas\View\Model\ViewModel;
use UnicaenApp\Traits\MessageAwareInterface;

class InscriptionAdministrativeController extends AbstractController
{
    use InscriptionAdministrativeServiceAwareTrait;
    use AdmissionServiceAwareTrait;
    use DoctorantServiceAwareTrait;
    use AnneeUnivServiceAwareTrait;

    use ReceptionRulePluginManagerAwareTrait;
    use InscriptionAdministrativeReceptionProcessAwareTrait;

    private InscriptionAdministrative $inscriptionAdministrative;

    public function indexAction(): Response
    {
        return $this->redirect()->toRoute('inscription-administrative/recherche/index');
    }

    public function voirAction(): ViewModel
    {
        $id = $this->params('id');

        $this->inscriptionAdministrative = $this->inscriptionAdministrativeService->getRepository()->findOneById($id);
        $inscriptionAdministrativeApprenant = $this->inscriptionAdministrative->getInscriptionAdministrativeApprenant();

        $admission = $this->admissionService->getRepository()->findOneByNumeroCandidature($this->inscriptionAdministrative->getNoCandidat());
        $doctorantTrouveAvecCode = $this->doctorantService->getRepository()->findOneByCodeApprenant($inscriptionAdministrativeApprenant->getCode());
        $doctorantTrouveAvecINE = $this->doctorantService->getRepository()->findOneByIne($inscriptionAdministrativeApprenant->getINE());

        $vm = new ViewModel([
            'inscriptionAdministrative' => $this->inscriptionAdministrative,
            'inscriptionAdministrativeApprenant' => $inscriptionAdministrativeApprenant,
            'admission' => $admission,
            'doctorantTrouveAvecCode' => $doctorantTrouveAvecCode,
            'doctorantTrouveAvecINE' => $doctorantTrouveAvecINE,
            'anneeUnivService' => $this->anneeUnivService,
        ]);

        $childModel = $this->createReceptionProcessChildModel();
        $vm->addChild($childModel, 'reception-process');

        return $vm;
    }

    private function createReceptionProcessChildModel(): ViewModel
    {
        // Lancement de la Rule pour savoir si les conditions pour créer la thèse et le doctorant sont rassemblées.
        $receptionRule = $this->receptionRulePluginManager->getRuleForInscriptionAdministrative($this->inscriptionAdministrative);
        $receptionRule->execute();

        $childModel = new ViewModel();
        $childModel->setVariables([
            'inscriptionAdministrative' => $this->inscriptionAdministrative,
            'receptionRule' => $receptionRule,
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
        $this->inscriptionAdministrativeReceptionProcess->setInscriptionAdministrative($this->inscriptionAdministrative);
        $this->inscriptionAdministrativeReceptionProcess->run();
        $result = $this->inscriptionAdministrativeReceptionProcess->getResult();
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

    private function getRequestedInscriptionAdministrative(): ?InscriptionAdministrative
    {
        /** @var InscriptionAdministrative $inscriptionAdministrative */
        $inscriptionAdministrative = $this->inscriptionAdministrativeService->getRepository()->find($this->params('id'));

        return $inscriptionAdministrative;
    }
}