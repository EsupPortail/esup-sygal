<?php

namespace StepStar\Controller\Envoi;

use Application\Controller\AbstractController;
use StepStar\Facade\EnvoiFacadeAwareTrait;
use StepStar\Form\EnvoiForm;
use StepStar\Service\Fetch\FetchServiceAwareTrait;
use StepStar\Service\Log\LogServiceAwareTrait;

class EnvoiController extends AbstractController
{
    use EnvoiFacadeAwareTrait;

    use FetchServiceAwareTrait;
    use LogServiceAwareTrait;

    private EnvoiForm $envoiForm;

    public function setEnvoiForm(EnvoiForm $envoiForm): void
    {
        $this->envoiForm = $envoiForm;
    }

    /**
     * Action pour envoyer plusieurs theses vers STEP/STAR.
     */
    public function envoyerThesesAction()
    {
        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            $this->envoiForm->setData($data);

            if ($this->envoiForm->isValid()) {
                $command = $this->getRequest()->getUriString();

                $these = $this->envoiForm->getData()['these']; // ex : '12345' ou '12345,12346'
                $force = (bool) $this->envoiForm->getData()['force'];

                $criteria = array_filter(compact('these'));

                $theses = $this->fetchService->fetchThesesByCriteria($criteria);
                if (empty($theses)) {
                    $this->flashMessenger()->addErrorMessage("Aucune these trouvee avec les criteres specifies");
                }

                $this->envoiFacade->setSaveLogs(true);
                $logs = $this->envoiFacade->envoyerTheses($theses, $force, $command);

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
            'form' => $this->envoiForm,
        ];
    }
}