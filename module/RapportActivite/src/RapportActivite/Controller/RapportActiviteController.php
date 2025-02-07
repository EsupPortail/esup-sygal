<?php

namespace RapportActivite\Controller;

use Application\Controller\AbstractController;
use Application\Entity\Db\TypeValidation;
use Application\Exporter\ExporterDataException;
use Application\Filter\IdifyFilter;
use Application\Service\AnneeUniv\AnneeUnivServiceAwareTrait;
use Application\Service\Validation\ValidationServiceAwareTrait;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\Query\Expr\Join;
use Fichier\FileUtils;
use Fichier\Service\Fichier\FichierServiceAwareTrait;
use Fichier\Service\Fichier\FichierStorageServiceAwareTrait;
use Formation\Service\Inscription\InscriptionServiceAwareTrait as FormationInscriptionServiceAwareTrait;
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
use These\Service\TheseAnneeUniv\TheseAnneeUnivServiceAwareTrait;
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
    use TheseAnneeUnivServiceAwareTrait;
    use AnneeUnivServiceAwareTrait;
    use FormationInscriptionServiceAwareTrait;

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

        $anneesUnivs = $this->these->getAnneesUnivInscription();
        $anneesUnivsPossiblesPourRapportAnnuel = [];
        $anneesUnivsPossiblesPourRapportFinContrat = [];
        $typesRapportPossiblesData = [];
        if (!$anneesUnivs->isEmpty()) {
            $this->rapportActiviteCreationRule->setAnneesUnivs($anneesUnivs->toArray());
            $anneesUnivsPossiblesPourRapportAnnuel = $this->rapportActiviteCreationRule->getAnneesUnivsDisponiblesPourRapportAnnuel();
            sort($anneesUnivsPossiblesPourRapportAnnuel);
            $anneesUnivsPossiblesPourRapportFinContrat = $this->rapportActiviteCreationRule->getAnneesUnivsDisponiblesPourRapportFinContrat();
            sort($anneesUnivsPossiblesPourRapportFinContrat);

            if ($this->rapportActiviteCreationRule->canCreateRapportAnnuel()) {
                $typesRapportPossiblesData[] = ['label' => RapportActivite::LIBELLE_ANNUEL, 'value' => 0];
            }
            if ($this->rapportActiviteCreationRule->canCreateRapportFinContrat()) {
                $typesRapportPossiblesData[] = ['label' => RapportActivite::LIBELLE_FIN_CONTRAT, 'value' => 1];
            }
        }

        $typeRapportPossiblesOptions = [];
        foreach ($typesRapportPossiblesData as $typeRapportPossible) {
            $typeRapport = [];
            $label = $typeRapportPossible["label"];
            if ($label === RapportActivite::LIBELLE_ANNUEL || $label === RapportActivite::LIBELLE_FIN_CONTRAT) {
                $typeRapport['label'] = $label;
                $typeRapport['value'] =  $typeRapportPossible["value"];
                $typeRapport['options'] = $label === RapportActivite::LIBELLE_ANNUEL ? $anneesUnivsPossiblesPourRapportAnnuel : $anneesUnivsPossiblesPourRapportFinContrat;
                $typeRapportPossiblesOptions[] = $typeRapport;
            }
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
            'typeRapportPossiblesOptions' => $typeRapportPossiblesOptions,
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
            'anneeUnivCourante' => $this->anneeUnivService->courante(),
        ]);
    }

    /**
     * Ajout d'un nouveau rapport.
     */
    public function ajouterAction(): Response|ViewModel
    {
        $this->these = $this->requestedThese();
        $estFinContrat = (bool)$this->params('estFinContrat');
        $anneeUniv = $this->params('anneeUniv');

        $rapport = $this->rapportActiviteService->newRapportActivite($this->these);
        $rapport->setEstFinContrat($estFinContrat);
        $rapport->setAnneeUniv($anneeUniv);

        // Si ce n'est pas le doctorant qui est connecté, on actionne le témoin "rapport créé par le dir de thèse".
        $rapport->setParDirecteurThese($this->userContextService->getSelectedRoleDoctorant() === null);

        $form = $rapport->estFinContrat() ? $this->finContratForm : $this->annuelForm;
        $this->initForm($form, $rapport);

        $form->bind($rapport);
        $form->get("anneeUniv")->setValue($anneeUniv);
        $form->setAnneesUnivsReadonly();

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
                $this->redirect()->toRoute('rapport-activite/consulter', [
                    'these' => IdifyFilter::id($rapport->getThese()),
                    'rapport' => IdifyFilter::id($rapport),
                ]);
        }

        exit;
    }

    private function fetchRapports()
    {
        $this->rapports = $this->rapportActiviteService->findRapportsForThese($this->these);
    }

    private function initForm(RapportActiviteAbstractForm $form, RapportActivite $rapportActivite): void
    {
        if ($rapportActivite->getId() === null) {
            $anneesUnivs = $rapportActivite->getThese()->getAnneesUnivInscription();
            $this->rapportActiviteCreationRule->setAnneesUnivs($anneesUnivs->toArray());
            $anneesUnivsPossibles = $this->rapportActiviteCreationRule
                ->setRapportsExistants($this->rapportActiviteService->findRapportsForThese($rapportActivite->getThese()))
                ->getAnneesUnivsDisponibles();
            $form->setAnneesUnivs($anneesUnivsPossibles);

            // TODO : Modifier le process/formulaire pour avoir l'année avant et pouvoir ainsi filtrer les formations
            //
            // 2 idées :
            //   - soit générer dans le bouton autant de liens de création de RA qu'il y a de couples (type de RA ; année) possibles
            //     (ex : 'Annuel 2022', 'Annuel 2023', 'Fin de contrat 2023'). On pourra utiliser ça :
            //       $anneesUnivsPossiblesPourRapportAnnuel = $this->rapportActiviteCreationRule->getAnneesUnivsDisponiblesPourRapportAnnuel();
            //       $anneesUnivsPossiblesPourRapportFinContrat = $this->rapportActiviteCreationRule->getAnneesUnivsDisponiblesPourRapportFinContrat();
            //     L'avantage est que ça évite aussi l'exception "Interdit de créer ce type de rapport" déclénchée ci-après.
            //
            //   - soit modifier le comportement du formulaire pour faire saisir d'abord l'année seule (.e. validation group)
            //     et ensuite le formulaire entier (avec année grisée) initialisé avec les formations de l'année choisie.
            //     Cf. AnneeUnivService pour les bornes de début et de fin d'une année univ.
            $formationInscriptions = $this->inscriptionService->getInscriptionByDoctorantAndAnnee($rapportActivite->getThese()->getDoctorant());
            $rapportActivite->setFormationsFromInscriptions($formationInscriptions);

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
