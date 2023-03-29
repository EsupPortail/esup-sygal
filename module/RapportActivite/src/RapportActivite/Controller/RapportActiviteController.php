<?php

namespace RapportActivite\Controller;

use Application\Controller\AbstractController;
use Application\Entity\Db\TypeValidation;
use Application\Exporter\ExporterDataException;
use Application\Filter\IdifyFilter;
use Application\Service\Validation\ValidationServiceAwareTrait;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\Query\Expr\Join;
use Fichier\FileUtils;
use Fichier\Service\Fichier\FichierServiceAwareTrait;
use Fichier\Service\Fichier\FichierStorageServiceAwareTrait;
use InvalidArgumentException;
use Laminas\Http\Response;
use Laminas\View\Model\ViewModel;
use RapportActivite\Entity\Db\RapportActivite;
use RapportActivite\Form\RapportActiviteAbstractForm;
use RapportActivite\Form\RapportActiviteAnnuelForm;
use RapportActivite\Form\RapportActiviteFinContratForm;
use RapportActivite\Rule\Creation\RapportActiviteCreationRuleAwareTrait;
use RapportActivite\Rule\Operation\RapportActiviteOperationRuleAwareTrait;
use RapportActivite\Service\Fichier\RapportActiviteFichierServiceAwareTrait;
use RapportActivite\Service\RapportActiviteServiceAwareTrait;
use SplObjectStorage;
use These\Entity\Db\These;
use UnicaenApp\Exception\RuntimeException;

class RapportActiviteController extends AbstractController
{
    use FichierServiceAwareTrait;
    use FichierStorageServiceAwareTrait;
    use RapportActiviteServiceAwareTrait;
    use RapportActiviteFichierServiceAwareTrait;
    use RapportActiviteCreationRuleAwareTrait;
    use RapportActiviteOperationRuleAwareTrait;
    use ValidationServiceAwareTrait;

    private RapportActiviteAnnuelForm $annuelForm;
    private RapportActiviteFinContratForm $finContratForm;

    /**
     * @var RapportActivite[]
     */
    private array $rapports = [];

    private These $these;

    private ?RapportActivite $rapport = null;

    public function setAnnuelForm(RapportActiviteAnnuelForm $annuelForm)
    {
        $this->annuelForm = $annuelForm;
    }

    public function setFinContratForm(RapportActiviteFinContratForm $finContratForm)
    {
        $this->finContratForm = $finContratForm;
    }

    public function listerAction(): ViewModel
    {
        $this->these = $this->requestedThese();
        $this->fetchRapports();

        $this->rapportActiviteCreationRule->setRapportsExistants($this->rapports);
        $this->rapportActiviteCreationRule->execute();

        $typesRapportPossiblesData = [];
        if ($this->rapportActiviteCreationRule->canCreateRapportAnnuel()) {
            $typesRapportPossiblesData[] = ['label' => RapportActivite::LIBELLE_ANNUEL, 'value' => 0];
        }
        if ($this->rapportActiviteCreationRule->canCreateRapportFinContrat()) {
            $typesRapportPossiblesData[] = ['label' => RapportActivite::LIBELLE_FIN_CONTRAT, 'value' => 1];
        }

        $operationss = [];
        foreach ($this->rapports as $rapport) {
            $this->rapportActiviteOperationRule->injectOperationPossible($rapport);
            $operationss[$rapport->getId()] = $this->rapportActiviteOperationRule->getOperationsForRapport($rapport);
        }

        return new ViewModel([
            'rapports' => $this->rapports,
            'these' => $this->these,
            'typesRapportPossiblesData' => $typesRapportPossiblesData,
            'operationss' => $operationss,
            'campagneDepotDates' => $this->rapportActiviteService->fetchParametresCampagneDepotDates(),

            'returnUrl' => $this->url()->fromRoute('rapport-activite/lister', ['these' => $this->these->getId()]),
        ]);
    }

    public function consulterAction(): ViewModel
    {
        $rapport = $this->requestedRapportAndCo();

        $this->rapportActiviteOperationRule->injectOperationPossible($rapport);
        $operations = $this->rapportActiviteOperationRule->getOperationsForRapport($rapport);

        return new ViewModel([
            'rapport' => $rapport,
            'operations' => $operations,
        ]);
    }

    /**
     * Ajout d'un nouveau rapport.
     */
    public function ajouterAction()
    {
        $this->these = $this->requestedThese();
        $estFinContrat = (bool)$this->params('estFinContrat');

        $rapport = $this->rapportActiviteService->newRapportActivite($this->these);
        $rapport->setEstFinContrat($estFinContrat);

        // Si ce n'est pas le doctorant qui est connecté, on actionne le témoin "rapport créé par le dir de thèse".
        $rapport->setParDirecteurThese($this->userContextService->getSelectedRoleDoctorant() === null);

        $form = $rapport->estFinContrat() ? $this->finContratForm : $this->annuelForm;
        $this->initForm($form, $rapport);
        $form->bind($rapport);

        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost()->toArray();
            $form->setData($data);
            if ($form->isValid()) {
                /** @var RapportActivite $rapport */
                $rapport = $form->getData();
                if ($this->rapportActiviteCreationRule->canCreateRapport($rapport)) {
                    $event = $this->rapportActiviteService->saveRapport($rapport);

                    $this->flashMessenger()->addSuccessMessage($rapport . " enregistré avec succès.");

                    if ($messages = $event->getMessages()) {
                        foreach ($messages as $namespace => $message) {
                            $this->flashMessenger()->addMessage($message, $namespace);
                        }
                    }
                } else {
                    $this->flashMessenger()->addErrorMessage(
                        "L'enregistrement n'est pas possible. Vérifiez la cohérence entre le type de rapport et l'année universitaire, svp."
                    );
                }

                return $this->redirect()->toRoute('rapport-activite/consulter', [
                    'these' => $rapport->getThese()->getId(),
                    'rapport' => $rapport->getId(),
                ]);
            }
        }

        return (new ViewModel([
            'rapport' => $rapport,
            'form' => $form,
        ]))->setTemplate('rapport-activite/rapport-activite/modifier');
    }

    /**
     * Modification d'un rapport.
     */
    public function modifierAction()
    {
        $rapport = $this->requestedRapport();

        // Si ce n'est pas le doctorant qui est connecté, on actionne le témoin "rapport créé par le dir de thèse".
        $rapport->setParDirecteurThese($this->userContextService->getSelectedRoleDoctorant() === null);

        $form = $rapport->estFinContrat() ? $this->finContratForm : $this->annuelForm;
        $this->initForm($form, $rapport);
        $form->setAnneesUnivsReadonly();
        $form->bind($rapport);

        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost()->toArray();
            $form->setData($data);
            if ($form->isValid()) {
                /** @var RapportActivite $rapport */
                $rapport = $form->getData();
                $event = $this->rapportActiviteService->saveRapport($rapport);

                $this->flashMessenger()->addSuccessMessage($rapport . " modifié avec succès.");

                if ($messages = $event->getMessages()) {
                    foreach ($messages as $namespace => $message) {
                        $this->flashMessenger()->addMessage($message, $namespace);
                    }
                }

                return $this->redirect()->toRoute('rapport-activite/consulter', [
                    'these' => $rapport->getThese()->getId(),
                    'rapport' => $rapport->getId(),
                ]);
            }
        }

        return [
            'rapport' => $rapport,
            'form' => $form,
        ];
    }

    /**
     * Suppression d'un rapport.
     */
    public function supprimerAction(): Response
    {
        $rapport = $this->requestedRapport();
        $these = $rapport->getThese();

        $event = $this->rapportActiviteService->deleteRapport($rapport);

        $this->flashMessenger()->addSuccessMessage("Rapport supprimé avec succès.");

        if ($messages = $event->getMessages()) {
            foreach ($messages as $namespace => $message) {
                $this->flashMessenger()->addMessage($message, $namespace);
            }
        }

        return $this->redirect()->toRoute('rapport-activite/lister', ['these' => IdifyFilter::id($these)]);
    }

    public function telechargerAction()
    {
        $rapport = $this->requestedRapport();

        // s'il s'agit d'un rapport validé, on ajoute à la volée la page de validation
        $typeValidation = $this->validationService->findTypeValidationByCode(TypeValidation::CODE_RAPPORT_ACTIVITE_AUTO);
        if ($rapport->getRapportValidationOfType($typeValidation) !== null) {
            // l'ajout de la page de validation n'est pas forcément possible
            if ($rapport->supporteAjoutPageValidation()) {
                try {
                    $exportData = $this->rapportActiviteService->createPageValidationDataForRapport($rapport);
                } catch (ExporterDataException $e) {
                    $redirect = $this->params()->fromQuery('redirect');
                    $this->flashMessenger()->addErrorMessage(sprintf(
                        "Impossible de générer la page de validation du rapport '%s'. " . $e->getMessage(),
                        $rapport->getFichier()->getNom()
                    ));
                    return $redirect ?
                        $this->redirect()->toUrl($redirect) :
                        $this->redirect()->toRoute('rapport-activite/lister', ['these' => IdifyFilter::id($rapport->getThese())]);
                }
                $outputFilePath = $this->rapportActiviteFichierService->createFileWithPageValidation($rapport, $exportData);
                FileUtils::downloadFile($outputFilePath);
                exit;
            }
        }

        return $this->forward()->dispatch('Application\Controller\Fichier', [
            'action' => 'telecharger',
            'fichier' => IdifyFilter::id($rapport->getFichier()),
        ]);
    }

    public function genererAction()
    {
        $rapport = $this->requestedRapport();

        // L'ancienne version du module Rapport d'activité invitait à téléverser le rapport.
        if ($rapport->getFichier() !== null) {
            throw new InvalidArgumentException("Seuls les rapports non dématérialisés peuvent être générés au format PDF");
        }

        try {
            $this->rapportActiviteService->genererRapportActivitePdf($rapport);
        } catch (ExporterDataException $e) {
            $redirect = $this->params()->fromQuery('redirect');
            $this->flashMessenger()->addErrorMessage(sprintf(
                "Impossible de générer le %s au format PDF. " . $e->getMessage(),
                lcfirst($rapport)
            ));
            return $redirect ?
                $this->redirect()->toUrl($redirect) :
                $this->redirect()->toRoute('rapport-activite/consulter', ['rapport' => IdifyFilter::id($rapport)]);
        }

        exit;
    }

    private function fetchRapports()
    {
        $this->rapports = $this->rapportActiviteService->findRapportsForThese($this->these);
    }

    private function initForm(RapportActiviteAbstractForm $form, RapportActivite $rapportActivite)
    {
        if ($rapportActivite->getId() === null) {
            $anneesUnivsPossibles = $this->rapportActiviteCreationRule
                ->setRapportsExistants($this->rapportActiviteService->findRapportsForThese($rapportActivite->getThese()))
                ->getAnneesUnivsDisponibles();
            $form->setAnneesUnivs($anneesUnivsPossibles);

            if ($rapportActivite->estFinContrat() && !$this->rapportActiviteCreationRule->canCreateRapportFinContrat() ||
                !$rapportActivite->estFinContrat() && !$this->rapportActiviteCreationRule->canCreateRapportAnnuel()) {
                throw new InvalidArgumentException("Interdit de créer ce type de rapport");
            }
        } else {
            $anneesUnivs = new SplObjectStorage();
            $anneesUnivs->attach($rapportActivite->getAnneeUniv(), []);
            $form->setAnneesUnivs($anneesUnivs);
            $form->setAnneesUnivsReadonly();
        }
    }

    /**
     * Fetch du rapport d'activité spécifié dans l'URL.
     */
    private function requestedRapport(): RapportActivite
    {
        $id = $this->params()->fromRoute('rapport') ?: $this->params()->fromQuery('rapport');

        $rapport = $this->rapportActiviteService->fetchRapportById($id);
        if ($rapport === null) {
            throw new RuntimeException("Aucun rapport trouvé avec l'id spécifié");
        }

        return $rapport;
    }

    /**
     * Fetch du rapport d'activité spécifié dans l'URL, AVEC LES ENTITÉS LIÉES.
     */
    private function requestedRapportAndCo(): RapportActivite
    {
        $id = $this->params()->fromRoute('rapport') ?: $this->params()->fromQuery('rapport');

        $qb = $this->rapportActiviteService->getRepository()->createQueryBuilder('r');
        $qb
            ->join('r.these', 't')->addSelect('t')
            ->join('t.doctorant', 'd')->addSelect('d')
            ->join('d.individu', 'di')->addSelect('di')
            ->leftJoin('r.fichier', 'f')->addSelect('f')
            ->leftJoin('r.rapportValidations', 'rv', Join::WITH, '1 = pasHistorise(rv)')->addSelect('rv')
            ->leftJoin('rv.typeValidation', 'tv')->addSelect('tv')
            ->leftJoin('r.rapportAvis', 'ra', Join::WITH, '1 = pasHistorise(ra)')->addSelect('ra')
            ->leftJoin('ra.avis', 'a')->addSelect('a')
            ->leftJoin('a.avisType', 'at')->addSelect('at')
            ->where('r = :id')->setParameter('id', $id);

        try {
            $rapport = $qb->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            throw new RuntimeException("Plusieurs rapports trouvés avec l'id spécifié");
        }
        if ($rapport === null) {
            throw new RuntimeException("Aucun rapport trouvé avec l'id spécifié");
        }

        return $rapport;
    }
}
