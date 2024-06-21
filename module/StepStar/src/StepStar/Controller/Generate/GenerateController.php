<?php

namespace StepStar\Controller\Generate;

use Application\Controller\AbstractController;
use Fichier\FileUtils;
use Laminas\Http\Response;
use Laminas\Session\Container as SessionContainer;
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
    private SessionContainer $sessionContainer;

    public function __construct()
    {
        $this->sessionContainer = new SessionContainer(__CLASS__);
    }

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

                $log = $this->generateFacade->generateFilesForTheses($theses, $command);

                // pour l'action de téléchargement ci-après, on met à disposition dans la session le chemin du fichier généré
                $resulFilePath = $log->getZipFilePath() ?: $log->getTefFilePath();
                $this->sessionContainer->offsetSet('resulFilePath', $resulFilePath);

                if ($log->isSuccess()) {
                    $downloadUrl = $this->url()->fromRoute('step-star/generation/telecharger-tef');
                    $this->flashMessenger()->addSuccessMessage($log->getLogToHtml() .
                        "<br><a href='$downloadUrl'>Télécharger le ou les fichiers TEF générés</a>");
                } else {
                    $this->flashMessenger()->addErrorMessage($log->getLogToHtml());
                }

                return $this->redirect()->refresh();
            }
        }

        return [
            'form' => $this->generateForm,
            'downloadEnabled' => $this->sessionContainer->offsetExists('resulFilePath'),
        ];
    }

    private function isDownloadResultEnabled(): bool
    {
        return $this->sessionContainer->offsetExists('resulFilePath');
    }

    /**
     * Action pour envoyer au client web le fichier TEF généré ou un zip en cas de fichiers TEF multiples.
     * Le chemin du fichier sur le serveur est transmis à cette action via la session.
     */
    public function telechargerTefAction(): Response
    {
        if (!$this->sessionContainer->offsetExists('resulFilePath')) {
            return $this->redirect()->toRoute('step-star/generation');
        }

        $resulFilePath = $this->sessionContainer->offsetGet('resulFilePath');

        FileUtils::downloadFile($resulFilePath);
    }
}