<?php

namespace StepStar\Controller\Generate;

use Application\Controller\AbstractController;
use Laminas\Http\Response;
use StepStar\Facade\Generate\GenerateFacadeAwareTrait;
use StepStar\Form\Generate\GenerateForm;
use StepStar\Service\Fetch\FetchServiceAwareTrait;
use StepStar\Service\Log\LogServiceAwareTrait;

class GenerateController extends AbstractController
{
    use GenerateFacadeAwareTrait;

    use FetchServiceAwareTrait;
    use LogServiceAwareTrait;

    private GenerateForm $generateForm;

    public function setGenerateForm(GenerateForm $generateForm): void
    {
        $this->generateForm = $generateForm;
    }

    /**
     * Action pour générer les fichiers XML nécessaires à l'envoi de plusieurs theses vers STEP/STAR.
     */
    public function genererThesesAction(): Response|array
    {
        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            $this->generateForm->setData($data);

            if ($this->generateForm->isValid()) {
                $command = $this->getRequest()->getUriString();

                $these = $this->generateForm->getData()['these']; // ex : '12345' ou '12345,12346'
                $criteria = array_filter(compact('these'));

                $theses = $this->fetchService->fetchThesesByCriteria($criteria);
                if (empty($theses)) {
                    $this->flashMessenger()->addErrorMessage("Aucune these trouvee avec les criteres specifies");
                }

                $logs = $this->generateFacade->generateFilesForTheses($theses, $command);

                /** @var \StepStar\Entity\Db\Log $log */
                foreach ($logs as $log) {
                    if ($log->isSuccess()) {
                        $this->flashMessenger()->addSuccessMessage($log->getLogToHtml());
                    } else {
                        $this->flashMessenger()->addErrorMessage($log->getLogToHtml());
                    }
                }

                return $this->redirect()->refresh();
            }
        }

        return [
            'form' => $this->generateForm,
        ];
    }
}