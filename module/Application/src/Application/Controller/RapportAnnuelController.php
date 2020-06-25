<?php

namespace Application\Controller;

use Application\Entity\Db\RapportAnnuel;
use Application\EventRouterReplacerAwareTrait;
use Application\Filter\IdifyFilter;
use Application\Filter\IdifyFilterAwareTrait;
use Application\Form\RapportAnnuelForm;
use Application\Service\Fichier\FichierServiceAwareTrait;
use Application\Service\FichierThese\Exception\DepotImpossibleException;
use Application\Service\Individu\IndividuServiceAwareTrait;
use Application\Service\Notification\NotifierServiceAwareTrait;
use Application\Service\RapportAnnuel\RapportAnnuelServiceAwareTrait;
use Application\Service\These\TheseServiceAwareTrait;
use Application\Service\Validation\ValidationServiceAwareTrait;
use Application\Service\VersionFichier\VersionFichierServiceAwareTrait;
use Doctrine\ORM\NoResultException;
use UnicaenApp\Exception\RuntimeException;
use Zend\Form\Element\Hidden;
use Zend\Http\Response;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;

class RapportAnnuelController extends AbstractController
{
    use TheseServiceAwareTrait;
    use FichierServiceAwareTrait;
    use RapportAnnuelServiceAwareTrait;
    use VersionFichierServiceAwareTrait;
    use IdifyFilterAwareTrait;
    use NotifierServiceAwareTrait;
    use IndividuServiceAwareTrait;
    use ValidationServiceAwareTrait;
    use EventRouterReplacerAwareTrait;

    const EVENT_RAPPORT_ANNUEL_TELEVERSE = 'RAPPORT_ANNUEL_TELEVERSE';

    /**
     * @var RapportAnnuelForm
     */
    private $form;

    /**
     * @param RapportAnnuelForm $form
     */
    public function setForm(RapportAnnuelForm $form)
    {
        $this->form = $form;
    }

    /**
     * @return ViewModel
     */
    public function rechercherAction()
    {


    }

    /**
     * @return Response|ViewModel
     */
    public function listerAction()
    {
        $these = $this->requestedThese();
        $rapportsAnnuels = $this->rapportAnnuelService->findRapportsAnnuelsForThese($these);
        $theseAnneeUnivsDispo = $this->rapportAnnuelService->computeAvailableTheseAnneeUniv(
            $these->getAnneesUnivInscription()->toArray(),
            $rapportsAnnuels
        );
        $this->form->setTheseAnneeUnivs($theseAnneeUnivsDispo);
        $this->form->setAttribute('action', $this->url()->fromRoute('rapport-annuel/ajouter', ['these' => IdifyFilter::id($these)]));
        $tousLesRapportsTeleverses = empty($theseAnneeUnivsDispo);

        return new ViewModel([
            'rapportsAnnuels' => $rapportsAnnuels,
            'these' => $these,
            'form' => $this->form,
            'tousLesRapportsTeleverses' => $tousLesRapportsTeleverses,
        ]);
    }

    /**
     * Création d'un rapport annuel.
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
                /** @var RapportAnnuel $rapportAnnuel */
                $rapportAnnuel = $this->form->getData();
                $rapportAnnuel->setThese($these);
                $this->rapportAnnuelService->saveRapportAnnuel($rapportAnnuel, $uploadData);

                // déclenchement d'un événement "rapport annuel téléversé"
                $this->events->trigger(
                    self::EVENT_RAPPORT_ANNUEL_TELEVERSE,
                    $rapportAnnuel, [

                    ]
                );

//                // on notifie de BdD
//                // todo: déplacer ceci dans un service écoutant l'événement "fichier de thèse téléversé" déclenché ci-dessus
//                $notif = $this->notifierService->getNotificationFactory()->createNotificationForFichierTeleverse($these);
//                $notif
//                    ->setSubject("Dépôt d'un rapport annuel")
//                    ->setTemplatePath('application/these/mail/notif-depot-rapport-annuel');
//                $this->notifierService->trigger($notif);
            }
        }

        return $this->redirect()->toRoute('rapport-annuel/lister', ['these' => IdifyFilter::id($these)]);
    }

    /**
     * Suppression d'un rapport annuel.
     */
    public function supprimerAction()
    {
        $rapportAnnuel = $this->requestRapportAnnuel();
        $these = $rapportAnnuel->getThese();

        $this->rapportAnnuelService->deleteRapportAnnuel($rapportAnnuel);

        return $this->redirect()->toRoute('rapport-annuel/lister', ['these' => IdifyFilter::id($these)]);
    }

    /**
     * @return RapportAnnuel
     */
    private function requestRapportAnnuel()
    {
        $id = $this->params()->fromRoute('rapportAnnuel') ?: $this->params()->fromQuery('rapportAnnuel');

        try {
            return $this->rapportAnnuelService->findRapportAnnuel($id);
        } catch (NoResultException $e) {
            throw new RuntimeException("AUcun rapport annuel trouvé avec cet id", 0, $e);
        }
    }
}
