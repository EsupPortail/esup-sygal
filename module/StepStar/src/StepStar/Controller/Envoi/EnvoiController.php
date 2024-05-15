<?php

namespace StepStar\Controller\Envoi;

use Application\Controller\AbstractController;
use Laminas\Http\Response;
use StepStar\Facade\Envoi\EnvoiFacadeAwareTrait;
use StepStar\Form\Envoi\EnvoiFichiersForm;
use StepStar\Form\Envoi\EnvoiThesesForm;
use StepStar\Service\Fetch\FetchServiceAwareTrait;
use StepStar\Service\Log\LogServiceAwareTrait;

class EnvoiController extends AbstractController
{
    use EnvoiFacadeAwareTrait;

    use FetchServiceAwareTrait;
    use LogServiceAwareTrait;

    private EnvoiFichiersForm $envoiFichiersForm;
    private EnvoiThesesForm $envoiThesesForm;

    public function setEnvoiFichiersForm(EnvoiFichiersForm $envoiFichiersForm): void
    {
        $this->envoiFichiersForm = $envoiFichiersForm;
    }

    public function setEnvoiThesesForm(EnvoiThesesForm $envoiThesesForm): void
    {
        $this->envoiThesesForm = $envoiThesesForm;
    }

    /**
     * Action pour envoyer des fichiers XML TEF vers STEP/STAR.
     */
    public function envoyerFichiersAction(): Response|array
    {
        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            $this->envoiFichiersForm->setData($data);

            if ($this->envoiFichiersForm->isValid()) {
                $path = $this->envoiFichiersForm->getData()['path'];

                $this->envoiFacade->setSaveLogs(true);
                $logs = $this->envoiFacade->envoyerFichiers($path);

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
            'form' => $this->envoiFichiersForm,
            'help' => "Le motif de recherche des fichiers TEF dans le répertoire est le suivant : " .
                '<code>' . $this->envoiFacade->getTefFilesGlobPattern() . '</code>',
        ];
    }

    /**
     * Action pour envoyer plusieurs theses vers STEP/STAR.
     */
    public function envoyerThesesAction(): Response|array
    {
        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            $this->envoiThesesForm->setData($data);

            if ($this->envoiThesesForm->isValid()) {
                $command = $this->getRequest()->getUriString();

                $these = $this->envoiThesesForm->getData()['these']; // ex : '12345' ou '12345,12346'
                $force = (bool) $this->envoiThesesForm->getData()['force'];
                $tag = $this->envoiThesesForm->getData()['tag'] ?? null;
                $clean = $this->envoiThesesForm->getData()['clean'] ?? false;

                $criteria = array_filter(compact('these'));

                $theses = $this->fetchService->fetchThesesByCriteria($criteria);
                if (empty($theses)) {
                    $this->flashMessenger()->addErrorMessage("Aucune these trouvee avec les criteres specifies");
                }

                $this->envoiFacade->setSaveLogs(true);
                $this->envoiFacade->setCleanAfterWork($clean);
                $logs = $this->envoiFacade->envoyerTheses($theses, $force, $command, $tag);

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
            'form' => $this->envoiThesesForm,
        ];
    }
}