<?php

namespace RapportActivite\Rule\Televersement;

use Application\Entity\AnneeUniv;
use Application\Entity\Db\These;
use Application\Rule\RuleInterface;
use RapportActivite\Entity\Db\RapportActivite;
use RapportActivite\Form\RapportActiviteForm;
use RapportActivite\Service\RapportActiviteServiceAwareTrait;
use SplObjectStorage;

/**
 * Application de diverses règles métier concernant le téléversement d'un rapport d'activité.
 */
class RapportActiviteTeleversementRule implements RuleInterface
{
    use RapportActiviteServiceAwareTrait;

    /**
     * Années univ proposables.
     *
     * @var \Application\Entity\AnneeUniv[]
     */
    private array $anneesUnivs;

    /**
     * @var RapportActivite[]
     */
    private array $rapportsTeleversesAnnuels;

    /**
     * @var RapportActivite[]
     */
    private array $rapportsTeleversesFinContrat;

    /**
     * @var array [int => bool]
     */
    private array $canTeleverserRapportAnnuel;

    /**
     * @var array [int => bool]
     */
    private array $canTeleverserRapportFinContrat;

    /**
     * @param \Application\Entity\AnneeUniv[] $anneesUnivs
     * @return self
     */
    public function setAnneesUnivs(array $anneesUnivs): self
    {
        $this->anneesUnivs = $anneesUnivs;
        return $this;
    }

    /**
     * @param \Application\Entity\Db\These $these
     * @return $this
     */
    public function setThese(These $these): self
    {
        $this->setRapportsTeleverses($this->rapportActiviteService->findRapportsForThese($these));

        return $this;
    }

    /**
     * @param \RapportActivite\Entity\Db\RapportActivite[] $rapportsTeleverses
     * @return self
     */
    public function setRapportsTeleverses(array $rapportsTeleverses): self
    {
        $this->rapportsTeleversesAnnuels = array_filter($rapportsTeleverses, function(RapportActivite $rapport) {
            return $rapport->estFinal() === false;
        });
        $this->rapportsTeleversesFinContrat = array_filter($rapportsTeleverses, function(RapportActivite $rapport) {
            return $rapport->estFinal() === true;
        });

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        // Rien ici, la logique est disséminée dans les différentes méthodes...
    }

    public function computeCanTeleverserRapports()
    {
        $this->canTeleverserRapportAnnuel = [];
        foreach ($this->anneesUnivs as $anneeUniv) {
            $this->canTeleverserRapportAnnuel[$anneeUniv->getPremiereAnnee()] =
                $this->canTeleverserRapportAnnuelForAnneeUniv($anneeUniv);
        }
        $this->canTeleverserRapportFinContrat = [];
        foreach ($this->anneesUnivs as $anneeUniv) {
            $this->canTeleverserRapportFinContrat[$anneeUniv->getPremiereAnnee()] =
                $this->canTeleverserRapportFinContratForAnneeUniv($anneeUniv);
        }
    }

    public function isTeleversementPossible(): bool
    {
        return
            count(array_filter($this->canTeleverserRapportAnnuel)) > 0 ||
            count(array_filter($this->canTeleverserRapportFinContrat)) > 0;
    }

    /**
     * @return int[]
     */
    private function getAnneesPrises(): array
    {
        return array_keys(
            array_intersect_key(
                array_filter($this->canTeleverserRapportAnnuel, fn(bool $can) => $can === false),
                array_filter($this->canTeleverserRapportFinContrat, fn(bool $can) => $can === false)
            )
        );
    }

    /**
     * Retourne les années universitaires disponibles (i.e. n'ayant pas fait l'objet de téléversement de rapport),
     * avec en plus les classes CSS à attribuer à chaque <option> du <select> pour la gestion des années incompatibles
     * avec un type de rapport (annuel ou fin de contrat).
     *
     * @return \SplObjectStorage
     */
    public function getAnneesUnivsDisponibles(): SplObjectStorage
    {
        // Détermination des années universitaires disponibles
        $anneesPrises = $this->getAnneesPrises();
        $anneesUnivsDisponibles = array_filter($this->anneesUnivs, function(AnneeUniv $annee) use ($anneesPrises) {
            $utilisee = in_array($annee->getPremiereAnnee(), $anneesPrises);
            return !$utilisee;
        });

        // Attachement d'infos utiles exploitées dans la vue
        $data = new SplObjectStorage();
        foreach ($anneesUnivsDisponibles as $anneeUniv) {
            // Classes CSS permettant de gérer dans la vue le fait qu'une année universitaire est compatible ou non
            // avec un type de rapport (annuel ou fin de contrat)
            $cssClasses = ['annee-univ'];
            if ($this->canTeleverserRapportAnnuelForAnneeUniv($anneeUniv)) {
                $cssClasses[] = RapportActiviteForm::ANNEE_UNIV__HTML_CLASS_ATTRIB__VALUE_PREFIX .
                    RapportActiviteForm::EST_FINAL__VALUE__ANNUEL;
            }
            if ($this->canTeleverserRapportFinContratForAnneeUniv($anneeUniv)) {
                $cssClasses[] = RapportActiviteForm::ANNEE_UNIV__HTML_CLASS_ATTRIB__VALUE_PREFIX .
                    RapportActiviteForm::EST_FINAL__VALUE__FIN_CONTRAT;
            }
            $info = ['class' => implode(' ', $cssClasses)];

            $data->attach($anneeUniv, $info);
        }

        return $data;
    }

    public function canTeleverserRapport(RapportActivite $rapport): bool
    {
        if ($rapport->estFinal()) {
            return $this->canTeleverserRapportFinContratForAnneeUniv($rapport->getAnneeUniv());
        } else {
            return $this->canTeleverserRapportAnnuelForAnneeUniv($rapport->getAnneeUniv());
        }
    }

    public function canTeleverserRapportAnnuel(): bool
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
        // Peut être téléversé pour une année : 1 rapport annuel.

        $rapportsTeleverses = array_filter(
            $this->rapportsTeleversesAnnuels,
            $this->rapportActiviteService->getFilterRapportsByAnneeUniv($anneeUniv)
        );

        return empty($rapportsTeleverses);
    }

    public function canTeleverserRapportFinContrat(): bool
    {
        // Dépôt d'1 rapport de fin de contrat maxi toutes années univ confondues.

        return count($this->rapportsTeleversesFinContrat) === 0;
    }

    private function canTeleverserRapportFinContratForAnneeUniv(AnneeUniv $anneeUniv): bool
    {
        // Dépôt d'un rapport de fin de contrat seulement sur la dernière année univ.

        if ($anneeUniv !== $this->getAnneeUnivMax()) {
            return false;
        }

        $rapportsTeleverses = array_filter(
            $this->rapportsTeleversesFinContrat,
            $this->rapportActiviteService->getFilterRapportsByAnneeUniv($anneeUniv)
        );

        return empty($rapportsTeleverses);
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
}