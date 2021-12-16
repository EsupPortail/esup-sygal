<?php

namespace Application\Controller\Rapport;

use Application\Controller\AbstractController;
use Application\Entity\Db\Interfaces\TypeRapportAwareTrait;
use Application\Entity\Db\Interfaces\TypeValidationAwareTrait;
use Application\Entity\Db\Rapport;
use Application\Entity\Db\TypeRapport;
use Application\EventRouterReplacerAwareTrait;
use Application\Filter\IdifyFilter;
use Application\Filter\IdifyFilterAwareTrait;
use Application\Form\Rapport\RapportForm;
use Application\Service\Fichier\FichierServiceAwareTrait;
use Application\Service\File\FileServiceAwareTrait;
use Application\Service\Individu\IndividuServiceAwareTrait;
use Application\Service\Notification\NotifierServiceAwareTrait;
use Application\Service\Rapport\RapportServiceAwareTrait;
use Application\Service\These\TheseServiceAwareTrait;
use Application\Service\TheseAnneeUniv\TheseAnneeUnivServiceAwareTrait;
use Application\Service\Validation\ValidationServiceAwareTrait;
use Application\Service\VersionFichier\VersionFichierServiceAwareTrait;
use Doctrine\ORM\NoResultException;
use UnicaenApp\Exception\RuntimeException;
use Zend\Http\Response;
use Zend\View\Model\ViewModel;

abstract class RapportController extends AbstractController
{
    use TheseServiceAwareTrait;
    use FileServiceAwareTrait;
    use FichierServiceAwareTrait;
    use RapportServiceAwareTrait;
    use VersionFichierServiceAwareTrait;
    use IdifyFilterAwareTrait;
    use NotifierServiceAwareTrait;
    use IndividuServiceAwareTrait;
    use ValidationServiceAwareTrait;
    use EventRouterReplacerAwareTrait;
    use TheseAnneeUnivServiceAwareTrait;
    use TypeRapportAwareTrait;
    use TypeValidationAwareTrait;

    /**
     * @var string À redéfinir par les sous-classes.
     */
    protected $routeName;

    /**
     * @var string À redéfinir par les sous-classes.
     */
    protected $privilege_LISTER_TOUT;
    protected $privilege_LISTER_SIEN;
    protected $privilege_TELEVERSER_TOUT;
    protected $privilege_TELEVERSER_SIEN;
    protected $privilege_SUPPRIMER_TOUT;
    protected $privilege_SUPPRIMER_SIEN;
    protected $privilege_RECHERCHER_TOUT;
    protected $privilege_RECHERCHER_SIEN;
    protected $privilege_TELECHARGER_TOUT;
    protected $privilege_TELECHARGER_SIEN;
    protected $privilege_TELECHARGER_ZIP;
    protected $privilege_VALIDER_TOUT;
    protected $privilege_VALIDER_SIEN;
    protected $privilege_DEVALIDER_TOUT;
    protected $privilege_DEVALIDER_SIEN;

    /**
     * @var string
     */
    protected $rapportTeleverseEventName = 'RAPPORT_TELEVERSE';

    /**
     * @var RapportForm
     */
    protected $form;

    /**
     * @var Rapport[]
     */
    protected $rapportsTeleverses = [];

    /**
     * @var \Application\Entity\Db\These
     */
    protected $these;

    /**
     * @var \Application\Entity\AnneeUniv
     */
    protected $anneeUnivCourante;

    /**
     * @param RapportForm $form
     */
    public function setForm(RapportForm $form)
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

        $this->anneeUnivCourante = $this->theseAnneeUnivService->anneeUnivCourante();
        $this->these = $this->requestedThese();
        $this->loadRapportsTeleverses();

        $this->initForm();

        return new ViewModel([
            'rapports' => $this->rapportsTeleverses,
            'these' => $this->these,
            'form' => $this->form,
            'isTeleversementPossible' => $this->isTeleversementPossible(),

            'typeValidation' => $this->typeValidation,
            'routeName' => $this->routeName,
            'privilege_LISTER_TOUT' => $this->privilege_LISTER_TOUT,
            'privilege_LISTER_SIEN' => $this->privilege_LISTER_SIEN,
            'privilege_TELEVERSER_TOUT' => $this->privilege_TELEVERSER_TOUT,
            'privilege_TELEVERSER_SIEN' => $this->privilege_TELEVERSER_SIEN,
            'privilege_SUPPRIMER_TOUT' => $this->privilege_SUPPRIMER_TOUT,
            'privilege_SUPPRIMER_SIEN' => $this->privilege_SUPPRIMER_SIEN,
            'privilege_RECHERCHER_TOUT' => $this->privilege_RECHERCHER_TOUT,
            'privilege_RECHERCHER_SIEN' => $this->privilege_RECHERCHER_SIEN,
            'privilege_TELECHARGER_TOUT' => $this->privilege_TELECHARGER_TOUT,
            'privilege_TELECHARGER_SIEN' => $this->privilege_TELECHARGER_SIEN,
            'privilege_TELECHARGER_ZIP' => $this->privilege_TELECHARGER_ZIP,
            'privilege_VALIDER_TOUT' => $this->privilege_VALIDER_TOUT,
            'privilege_VALIDER_SIEN' => $this->privilege_VALIDER_SIEN,
            'privilege_DEVALIDER_TOUT' => $this->privilege_DEVALIDER_TOUT,
            'privilege_DEVALIDER_SIEN' => $this->privilege_DEVALIDER_SIEN,
        ]);
    }

    protected function loadRapportsTeleverses()
    {
        $this->rapportsTeleverses = $this->rapportService->findRapportsForThese($this->these, $this->typeRapport);
    }

    protected function isTeleversementPossible(): bool
    {
        return true;
    }

    protected function initForm()
    {
        $this->form->setAnneesUnivs([$this->anneeUnivCourante]);
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
                $rapport->setTypeRapport($this->typeRapport);
                $rapport->setThese($these);
                $this->rapportService->saveRapport($rapport, $uploadData);

                // déclenchement d'un événement "rapport téléversé"
                $this->events->trigger(
                    $this->rapportTeleverseEventName,
                    $rapport, [

                    ]
                );

                $this->flashMessenger()->addSuccessMessage(sprintf(
                    "Rapport téléversé avec succès sous le nom suivant :<br>'%s'.",
                    $rapport->getFichier()->getNom()
                ));

                return $this->redirect()->toRoute($this->routeName . '/consulter', ['these' => IdifyFilter::id($these)]);
            }
        }

        return false; // pas de vue pour cette action
    }

    /**
     * Téléchargement d'un rapport.
     */
    public function telechargerAction()
    {
        $rapport = $this->requestedRapport();

        return $this->forward()->dispatch('Application\Controller\Fichier', [
            'action' => 'telecharger',
            'fichier' => IdifyFilter::id($rapport->getFichier()),
        ]);
    }

    /**
     * Suppression d'un rapport.
     */
    public function supprimerAction(): Response
    {
        $rapport = $this->requestedRapport();
        $these = $rapport->getThese();

        $this->rapportService->deleteRapport($rapport);

        $this->flashMessenger()->addSuccessMessage("Rapport supprimé avec succès.");

        return $this->redirect()->toRoute($this->routeName . '/consulter', ['these' => IdifyFilter::id($these)]);
    }

    /**
     * Retourne le type de rapport spécifié dans la requête courante.
     *
     * @return TypeRapport
     */
    protected function requestedTypeRapport(): TypeRapport
    {
        $code = $this->params()->fromRoute('type') ?: $this->params()->fromQuery('type');
        $this->typeRapport = $this->rapportService->findTypeRapportByCode($code);
        if ($this->typeRapport === null) {
            throw new RuntimeException("Type spécifié invalide");
        }

        return $this->typeRapport;
    }

    /**
     * @return Rapport
     */
    protected function requestedRapport(): Rapport
    {
        $id = $this->params()->fromRoute('rapport') ?: $this->params()->fromQuery('rapport');
        try {
            $rapport = $this->rapportService->findRapportById($id);
        } catch (NoResultException $e) {
            throw new RuntimeException("Aucun rapport trouvé avec cet id", 0, $e);
        }

        if ($rapport->getTypeRapport() !== $this->typeRapport) {
            throw new RuntimeException("Type de rapport attendu : " . $this->typeRapport->getCode());
        }

        return $rapport;
    }
}
