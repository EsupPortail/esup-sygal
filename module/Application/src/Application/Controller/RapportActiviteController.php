<?php

namespace Application\Controller;

use Application\Entity\Db\Rapport;
use Application\EventRouterReplacerAwareTrait;
use Application\Filter\IdifyFilter;
use Application\Filter\IdifyFilterAwareTrait;
use Application\Form\RapportActiviteForm;
use Application\Provider\Privilege\RapportPrivileges;
use Application\Service\Fichier\FichierServiceAwareTrait;
use Application\Service\Individu\IndividuServiceAwareTrait;
use Application\Service\Notification\NotifierServiceAwareTrait;
use Application\Service\Rapport\RapportServiceAwareTrait;
use Application\Service\These\TheseServiceAwareTrait;
use Application\Service\Validation\ValidationServiceAwareTrait;
use Application\Service\VersionFichier\VersionFichierServiceAwareTrait;
use Doctrine\ORM\NoResultException;
use DoctrineORMModule\Proxy\__CG__\Application\Entity\Db\These;
use UnicaenApp\Exception\RuntimeException;
use Zend\Http\Response;
use Zend\View\Model\ViewModel;

class RapportActiviteController extends AbstractController
{
    use TheseServiceAwareTrait;
    use FichierServiceAwareTrait;
    use RapportServiceAwareTrait;
    use VersionFichierServiceAwareTrait;
    use IdifyFilterAwareTrait;
    use NotifierServiceAwareTrait;
    use IndividuServiceAwareTrait;
    use ValidationServiceAwareTrait;
    use EventRouterReplacerAwareTrait;

    const EVENT_RAPPORT_ACTIVITE_TELEVERSE = 'RAPPORT_ACTIVITE_TELEVERSE';

    /**
     * @var RapportActiviteForm
     */
    private $form;

    /**
     * @param RapportActiviteForm $form
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
        // gestion d'une éventuelle requête POST d'ajout d'un rapport
        $result = $this->ajouterAction();
        if ($result instanceof Response) {
            return $result;
        }

        $these = $this->requestedThese();
        $rapports = $this->rapportService->findRapportsActiviteForThese($these);
        $theseAnneeUnivsDispo = $this->rapportService->computeAvailableTheseAnneeUniv(
            $these->getAnneesUnivInscription()->toArray(),
            $rapports
        );
        $this->form->setTheseAnneeUnivs($theseAnneeUnivsDispo);
        $tousLesRapportsTeleverses = empty($theseAnneeUnivsDispo);

        return new ViewModel([
            'rapports' => $rapports,
            'these' => $these,
            'form' => $this->form,
            'tousLesRapportsTeleverses' => $tousLesRapportsTeleverses,
        ]);
    }

    /**
     * Ajout d'un nouveau rapport.
     */
    public function ajouterAction()
    {
        $these = $this->requestedThese();

        $request = $this->getRequest();
        if ($request->isPost()) {
            $uploadData = $request->getFiles()->toArray();
            $data = array_merge_recursive(
                $request->getPost()->toArray(),
                $uploadData
            );
            $this->form->setData($data);
            if ($this->form->isValid()) {
                /** @var Rapport $rapport */
                $rapport = $this->form->getData();
                $rapport->setThese($these);
                $this->rapportService->saveRapportActivite($rapport, $uploadData);

                // déclenchement d'un événement "rapport téléversé"
                $this->events->trigger(
                    self::EVENT_RAPPORT_ACTIVITE_TELEVERSE,
                    $rapport, [

                    ]
                );

                return $this->redirect()->toRoute('rapport-activite/consulter', ['these' => IdifyFilter::id($these)]);
            }
        }

        return false; // pas de vue pour cette action
    }

    /**
     * Téléchargement d'un rapport.
     */
    public function telechargerAction()
    {
        $rapport = $this->requestRapportActivite();

        return $this->forward()->dispatch('Application\Controller\Fichier', [
            'action' => 'telecharger',
            'fichier' => IdifyFilter::id($rapport->getFichier()),
        ]);
    }

    /**
     * Suppression d'un rapport.
     */
    public function supprimerAction()
    {
        $rapport = $this->requestRapportActivite();
        $these = $rapport->getThese();

        $this->rapportService->deleteRapport($rapport);

        return $this->redirect()->toRoute('rapport-activite/consulter', ['these' => IdifyFilter::id($these)]);
    }

    /**
     * @return Rapport
     */
    private function requestRapportActivite()
    {
        $id = $this->params()->fromRoute('rapport') ?: $this->params()->fromQuery('rapport');

        try {
            return $this->rapportService->findRapport($id);
        } catch (NoResultException $e) {
            throw new RuntimeException("AUcun rapport trouvé avec cet id", 0, $e);
        }
    }
}
