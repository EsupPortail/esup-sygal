<?php

namespace RapportActivite\Controller;

use Application\Controller\AbstractController;
use Application\Entity\AnneeUniv;
use Application\Entity\Db\Interfaces\TypeRapportAwareTrait;
use Application\Entity\Db\Interfaces\TypeValidationAwareTrait;
use Application\Entity\Db\These;
use Application\Filter\IdifyFilter;
use Application\Service\Fichier\FichierServiceAwareTrait;
use Application\Service\File\FileServiceAwareTrait;
use Application\Service\TheseAnneeUniv\TheseAnneeUnivService;
use Application\Service\TheseAnneeUniv\TheseAnneeUnivServiceAwareTrait;
use Doctrine\ORM\NoResultException;
use Laminas\Http\Response;
use Laminas\View\Model\ViewModel;
use RapportActivite\Entity\Db\RapportActivite;
use RapportActivite\Entity\Db\RapportActiviteAvis;
use RapportActivite\Form\RapportActiviteForm;
use RapportActivite\Provider\Privilege\RapportActivitePrivileges;
use RapportActivite\Service\Avis\RapportActiviteAvisServiceAwareTrait;
use RapportActivite\Service\Fichier\RapportActiviteFichierServiceAwareTrait;
use RapportActivite\Service\RapportActiviteServiceAwareTrait;
use UnicaenApp\Exception\RuntimeException;
use UnicaenAvis\Entity\Db\Avis;

/**
 * @property \RapportActivite\Form\RapportActiviteForm $form
 */
class RapportActiviteController extends AbstractController
{
    use FichierServiceAwareTrait;
    use FileServiceAwareTrait;
    use TheseAnneeUnivServiceAwareTrait;
    use RapportActiviteAvisServiceAwareTrait;
    use RapportActiviteServiceAwareTrait;
    use RapportActiviteFichierServiceAwareTrait;

    use TypeRapportAwareTrait;
    use TypeValidationAwareTrait;
    
    private string $routeName = 'rapport-activite';

    private string $privilege_LISTER_TOUT;
    private string $privilege_LISTER_SIEN;
    private string $privilege_TELEVERSER_TOUT = RapportActivitePrivileges::RAPPORT_ACTIVITE_TELEVERSER_TOUT;
    private string $privilege_TELEVERSER_SIEN = RapportActivitePrivileges::RAPPORT_ACTIVITE_TELEVERSER_SIEN;
    private string $privilege_SUPPRIMER_TOUT = RapportActivitePrivileges::RAPPORT_ACTIVITE_SUPPRIMER_TOUT;
    private string $privilege_SUPPRIMER_SIEN = RapportActivitePrivileges::RAPPORT_ACTIVITE_SUPPRIMER_SIEN;
    private string $privilege_RECHERCHER_TOUT;
    private string $privilege_RECHERCHER_SIEN;
    private string $privilege_TELECHARGER_TOUT = RapportActivitePrivileges::RAPPORT_ACTIVITE_TELECHARGER_TOUT;
    private string $privilege_TELECHARGER_SIEN = RapportActivitePrivileges::RAPPORT_ACTIVITE_TELECHARGER_SIEN;
    private string $privilege_TELECHARGER_ZIP;
    private string $privilege_VALIDER_TOUT = RapportActivitePrivileges::RAPPORT_ACTIVITE_VALIDER_TOUT;
    private string $privilege_VALIDER_SIEN = RapportActivitePrivileges::RAPPORT_ACTIVITE_VALIDER_SIEN;
    private string $privilege_DEVALIDER_TOUT = RapportActivitePrivileges::RAPPORT_ACTIVITE_DEVALIDER_TOUT;
    private string $privilege_DEVALIDER_SIEN = RapportActivitePrivileges::RAPPORT_ACTIVITE_DEVALIDER_SIEN;

    /**
     * @var string
     */
    private string $rapportTeleverseEventName = 'RAPPORT_TELEVERSE';

    /**
     * @var \RapportActivite\Form\RapportActiviteForm
     */
    private RapportActiviteForm $form;

    /**
     * @var RapportActivite[]
     */
    private array $rapportsTeleverses = [];

    /**
     * @var \Application\Entity\Db\These
     */
    private These $these;

    /**
     * Années univ proposables.
     *
     * @var \Application\Entity\AnneeUniv[]
     */
    private array $anneesUnivs;

    /**
     * @var RapportActivite[]
     */
    private array $rapportsTeleversesAnnuels = [];

    /**
     * @var RapportActivite[]
     */
    private array $rapportsTeleversesFintheses = [];

    /**
     * @var array [int => bool]
     */
    private array $canTeleverserRapportAnnuel;

    /**
     * @var array [int => bool]
     */
    private array $canTeleverserRapportFinthese;

    /**
     * @inheritDoc
     */
    public function setTheseAnneeUnivService(TheseAnneeUnivService $service)
    {
        $this->theseAnneeUnivService = $service;

        $this->anneesUnivs = [
            $this->theseAnneeUnivService->anneeUnivCourante(),
            $this->theseAnneeUnivService->anneeUnivPrecedente(),
        ];
    }

    /**
     * @param \RapportActivite\Form\RapportActiviteForm $form
     */
    public function setForm(RapportActiviteForm $form)
    {
        $this->form = $form;
    }

    /**
     * @return AnneeUniv
     */
    private function getAnneeUnivMax(): AnneeUniv
    {
        $annees = array_map(function(AnneeUniv $anneeUniv) {
            return $anneeUniv->getPremiereAnnee();
        }, $this->anneesUnivs);

        return AnneeUniv::fromPremiereAnnee(max($annees));
    }

    /**
     * @return Response|ViewModel
     */
    public function consulterAction()
    {
        $this->these = $this->requestedThese();
        $this->fetchRapportsTeleverses();

        foreach ($this->rapportsTeleverses as $rapport) {
            $avisTypeDispo = $this->rapportActiviteAvisService->findNextExpectedAvisTypeForRapport($rapport);
            if ($avisTypeDispo === null) {
                $rapport->setRapportAvisPossible(null);
                continue;
            }

            $rapportAvisPossible = new RapportActiviteAvis();
            $rapportAvisPossible
                ->setRapportActivite($rapport)
                ->setAvis((new Avis())->setAvisType($avisTypeDispo));

            $rapport->setRapportAvisPossible($rapportAvisPossible);
        }

        // gestion d'une éventuelle requête POST d'ajout d'un rapport
        $result = $this->ajouterAction();
        if ($result instanceof Response) {
            return $result;
        }

        return new ViewModel([
            'rapports' => $this->rapportsTeleverses,
            'these' => $this->these,
            'form' => $this->form,
            'isTeleversementPossible' => $this->isTeleversementPossible(),

            'typeValidation' => $this->typeValidation,
            'returnUrl' => $this->url()->fromRoute($this->routeName . '/consulter', ['these' => $this->these->getId()]),
            'routeName' => $this->routeName,
        ]);
    }

    /**
     * Ajout d'un nouveau rapport.
     */
    public function ajouterAction()
    {
        $this->these = $this->requestedThese();

        $this->initForm();

        $request = $this->getRequest();
        if ($request->isPost()) {
            $uploadData = $request->getFiles()->toArray();
            $data = array_merge_recursive(
                $request->getPost()->toArray(),
                $uploadData
            );
            $this->form->setData($data);
            if ($this->form->isValid()) {
                /** @var RapportActivite $rapport */
                $rapport = $this->form->getData();
                $rapport->setTypeRapport($this->typeRapport);
                $rapport->setThese($this->these);
                if ($this->canTeleverserRapport($rapport)) {
                    $this->rapportActiviteService->saveRapport($rapport, $uploadData);

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
                }

                return $this->redirect()->toRoute($this->routeName . '/consulter', ['these' => IdifyFilter::id($this->these)]);
            }
        }

        return false; // pas de vue pour cette action
    }

    /**
     * Suppression d'un rapport.
     */
    public function supprimerAction(): Response
    {
        $rapport = $this->requestedRapport();
        $these = $rapport->getThese();

        $this->rapportActiviteService->deleteRapport($rapport);

        $this->flashMessenger()->addSuccessMessage("Rapport supprimé avec succès.");

        return $this->redirect()->toRoute($this->routeName . '/consulter', ['these' => IdifyFilter::id($these)]);
    }

    public function telechargerAction()
    {
        $rapport = $this->requestedRapport();

        // s'il s'agit d'un rapport validé, on ajoute à la volée la page de validation
        if ($rapport->getRapportValidation() !== null) {
            // l'ajout de la page de validation n'est pas forcément possible
            if ($rapport->supporteAjoutPageValidation()) {
                $exportData = $this->rapportActiviteService->createPageValidationData($rapport);
                $outputFilePath = $this->rapportActiviteFichierService->ajouterPageValidation($rapport, $exportData);
                $this->fileService->downloadFile($outputFilePath);
                exit;
            }
        }

        return $this->forward()->dispatch('Application\Controller\Fichier', [
            'action' => 'telecharger',
            'fichier' => IdifyFilter::id($rapport->getFichier()),
        ]);
    }

    private function fetchRapportsTeleverses()
    {
        $this->rapportsTeleverses = $this->rapportActiviteService->findRapportsForThese($this->these);

        $this->rapportsTeleversesAnnuels = array_filter($this->rapportsTeleverses, function(RapportActivite $rapport) {
            return $rapport->estFinal() === false;
        });
        $this->rapportsTeleversesFintheses = array_filter($this->rapportsTeleverses, function(RapportActivite $rapport) {
            return $rapport->estFinal() === true;
        });

        $this->canTeleverserRapportAnnuel = [];
        foreach ($this->anneesUnivs as $anneeUniv) {
            $this->canTeleverserRapportAnnuel[$anneeUniv->getPremiereAnnee()] =
                $this->canTeleverserRapportAnnuelForAnneeUniv($anneeUniv);
        }
        $this->canTeleverserRapportFinthese = [];
        foreach ($this->anneesUnivs as $anneeUniv) {
            $this->canTeleverserRapportFinthese[$anneeUniv->getPremiereAnnee()] =
                $this->canTeleverserRapportFintheseForAnneeUniv($anneeUniv);
        }
    }

    private function isTeleversementPossible(): bool
    {
        return
            count(array_filter($this->canTeleverserRapportAnnuel)) > 0 ||
            count(array_filter($this->canTeleverserRapportFinthese)) > 0;
    }

    private function getAnneesPrises(): array
    {
        return array_keys(
            array_intersect_key(
                array_filter($this->canTeleverserRapportAnnuel, function(bool $can) { return $can === false; }),
                array_filter($this->canTeleverserRapportFinthese, function(bool $can) { return $can === false; })
            )
        );
    }

    /**
     * @return AnneeUniv[]
     */
    private function getAnneesUnivsDisponibles(): array
    {
        $anneesPrises = $this->getAnneesPrises();

        return array_filter($this->anneesUnivs, function(AnneeUniv $annee) use ($anneesPrises) {
            $utilisee = in_array($annee->getPremiereAnnee(), $anneesPrises);
            return !$utilisee;
        });
    }

    private function canTeleverserRapport(RapportActivite $rapport): bool
    {
        if ($rapport->estFinal()) {
            return $this->canTeleverserRapportFintheseForAnneeUniv($rapport->getAnneeUniv());
        } else {
            return $this->canTeleverserRapportAnnuelForAnneeUniv($rapport->getAnneeUniv());
        }
    }

    private function canTeleverserRapportAnnuel(): bool
    {
        // Peut être téléversé : 1 rapport annuel par année universitaire.

        foreach ($this->anneesUnivs as $anneeUniv) {
            $rapportsTeleverses = array_filter(
                $this->rapportsTeleversesAnnuels,
                $this->rapportActiviteService->getFilterRapportsByAnneeUniv($anneeUniv)
            );
            if (empty($rapportsTeleverses)) {
                return true;
            }
        }

        return false;
    }

    private function canTeleverserRapportAnnuelForAnneeUniv(AnneeUniv $anneeUniv): bool
    {
        // Peut être téléversé : 1 rapport annuel.

        $rapportsTeleverses = array_filter(
            $this->rapportsTeleversesAnnuels,
            $this->rapportActiviteService->getFilterRapportsByAnneeUniv($anneeUniv)
        );

        return empty($rapportsTeleverses);
    }

    private function canTeleverserRapportFinthese(): bool
    {
        // Dépôt d'1 rapport de fin de contrat maxi toutes années univ confondues.

        return count($this->rapportsTeleversesFintheses) === 0;
    }

    private function canTeleverserRapportFintheseForAnneeUniv(AnneeUniv $anneeUniv): bool
    {
        // Dépôt d'un rapport de fin de contrat seulement sur la dernière année univ.

        if ($anneeUniv !== $this->getAnneeUnivMax()) {
            return false;
        }

        $rapportsTeleverses = array_filter(
            $this->rapportsTeleversesFintheses,
            $this->rapportActiviteService->getFilterRapportsByAnneeUniv($anneeUniv)
        );

        return empty($rapportsTeleverses);
    }

    private function initForm()
    {
        $this->form->setAnneesUnivs($this->getAnneesUnivsDisponibles());

        $estFinalValueOptions = [];
        if ($this->canTeleverserRapportAnnuel()) {
            $estFinalValueOptions['0'] = "Rapport d'activité annuel";
        }
        if ($this->canTeleverserRapportFinthese()) {
            $estFinalValueOptions['1'] = "Rapport d'activité de fin de contrat";
        }
        $this->form->setEstFinalValueOptions($estFinalValueOptions);
    }

    /**
     * @return RapportActivite
     */
    private function requestedRapport(): RapportActivite
    {
        $id = $this->params()->fromRoute('rapport') ?: $this->params()->fromQuery('rapport');
        try {
            $rapport = $this->rapportActiviteService->findRapportById($id);
        } catch (NoResultException $e) {
            throw new RuntimeException("Aucun rapport trouvé avec cet id", 0, $e);
        }

        if ($rapport->getTypeRapport() !== $this->typeRapport) {
            throw new RuntimeException("Type de rapport attendu : " . $this->typeRapport->getCode());
        }

        return $rapport;
    }
}
