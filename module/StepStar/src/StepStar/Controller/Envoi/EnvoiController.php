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

    /**
     * @param \StepStar\Form\EnvoiForm $envoiForm
     */
    public function setEnvoiForm(EnvoiForm $envoiForm): void
    {
        $this->envoiForm = $envoiForm;
    }

    /**
     * Action pour envoyer plusieurs theses vers STEP/STAR.
     */
    public function envoyerThesesAction(): array
    {
        if ($this->getRequest()->isPost()) {
            $this->envoiForm->setData($this->getRequest()->getPost());

            if ($this->envoiForm->isValid()) {
                $command = $this->getRequest()->getUriString();

                $these = $this->envoiForm->getData()['these']; // ex : '12345' ou '12345,12346'
                $force = (bool) $this->envoiForm->getData()['force'];

                $criteria = array_filter(compact('these'));

                $theses = $this->fetchService->fetchThesesByCriteria($criteria);
                if (empty($theses)) {
                    $this->flashMessenger()->addErrorMessage("Aucune these trouvee avec les criteres specifies");
                }

                /**
                 * 04/08/2022
                 *
                 * Tant que le bug #4371 n'est pas corrigé dans la lib Saxon/C, l'envoi depuis l'IHM ne peut pas fonctionner.
                 *
                 * @see https://www.saxonica.com/saxon-c/documentation1.2/index.html#!starting/installingphp :
                 * "There is currently an outstanding bug in the Saxon/C 1.2 PHP extension (See bug issue #4371)
                 * which causes the browser to hang wen running a PHP script with Saxon/C code.
                 * Please replace the file php7_saxon.cpp with the this patched verson: php7_saxon.cpp"
                 *
                 * @see https://saxonica.plan.io/issues/4371
                 */
                $this->envoiFacade->setSaveLog(false);
                $logs = $this->envoiFacade->envoyerTheses($theses, $force, $command);

                /** @var \StepStar\Entity\Db\Log $log */
                foreach ($logs as $log) {
                    if ($log->isSuccess()) {
                        $this->flashMessenger()->addSuccessMessage($log->getLog());
                    } else {
                        $this->flashMessenger()->addErrorMessage($log->getLog());
                    }
                }
            }
        }

        $this->flashMessenger()->addErrorMessage(
            ":-( Tant que le bug <a href='https://saxonica.plan.io/issues/4371'>#4371</a> n'est pas corrigé dans la lib 
            Saxon/C, l'envoi d'une thèse depuis cette page est impossible, désolé."
        );
        $this->envoiForm->get('submit')->setAttribute('disabled', true);

        return [
            'form' => $this->envoiForm,
        ];
    }
}