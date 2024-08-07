<?php

namespace RapportActivite\Rule\Creation;

use Application\Entity\AnneeUniv;
use Application\Entity\AnneeUnivInterface;
use Application\Rule\RuleInterface;
use InvalidArgumentException;
use RapportActivite\Entity\Db\RapportActivite;
use RapportActivite\Form\RapportActiviteAbstractForm;
use RapportActivite\Service\RapportActiviteServiceAwareTrait;
use SplObjectStorage;
use Webmozart\Assert\Assert;

/**
 * Application de diverses règles métier concernant la création d'un rapport d'activité.
 */
class RapportActiviteCreationRule implements RuleInterface
{
    use RapportActiviteServiceAwareTrait;

    /**
     * Années univ proposables.
     *
     * @var \Application\Entity\AnneeUnivInterface[]|null
     */
    private ?array $anneesUnivs = null;

    /**
     * @var RapportActivite[]|null
     */
    private ?array $rapportsExistantsAnnuels = null;

    /**
     * @var RapportActivite[]|null
     */
    private ?array $rapportsExistantsFinContrat = null;

    /**
     * @var array [int => bool]
     */
    private array $canCreateRapportAnnuel;

    /**
     * @var array [int => bool]
     */
    private array $canCreateRapportFinContrat;

    private bool $executed = false;

    /**
     * @param \Application\Entity\AnneeUnivInterface[] $anneesUnivs
     */
    public function setAnneesUnivs(array $anneesUnivs): self
    {
        Assert::notEmpty($anneesUnivs, "La liste initiale des années universitaires fournie ne doit pas être vide");
        $this->anneesUnivs = $anneesUnivs;

        $this->executed = false;

        return $this;
    }

    /**
     * @param \RapportActivite\Entity\Db\RapportActivite[] $rapportsExistants
     * @return self
     */
    public function setRapportsExistants(array $rapportsExistants): self
    {
        $this->rapportsExistantsAnnuels = array_filter($rapportsExistants, function(RapportActivite $rapport) {
            return $rapport->estFinContrat() === false;
        });
        $this->rapportsExistantsFinContrat = array_filter($rapportsExistants, function(RapportActivite $rapport) {
            return $rapport->estFinContrat() === true;
        });

        $this->executed = false;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function execute(): void
    {
        if ($this->anneesUnivs === null) {
            throw new InvalidArgumentException("La liste initiale des années universitaires n'a pas été fournie");
        }

        $this->canCreateRapportAnnuel = [];
        foreach ($this->anneesUnivs as $anneeUniv) {
            $this->canCreateRapportAnnuel[$anneeUniv->getPremiereAnnee()] =
                $this->canCreateRapportAnnuelForAnneeUniv($anneeUniv);
        }
        $this->canCreateRapportFinContrat = [];
        foreach ($this->anneesUnivs as $anneeUniv) {
            $this->canCreateRapportFinContrat[$anneeUniv->getPremiereAnnee()] =
                $this->canCreateRapportFinContratForAnneeUniv($anneeUniv);
        }

        $this->executed = true;
    }

    public function getAnneesUnivsDisponiblesPourRapportAnnuel(): array
    {
        if (!$this->executed) {
            $this->execute();
        }

        return array_keys(array_filter($this->canCreateRapportAnnuel, fn($can) => $can));
    }

    public function getAnneesUnivsDisponiblesPourRapportFinContrat(): array
    {
        if (!$this->executed) {
            $this->execute();
        }

        return array_keys(array_filter($this->canCreateRapportFinContrat, fn($can) => $can));
    }

    public function isCreationPossible(int $anneeUniv, bool $estFinContrat): bool
    {
        if (!$this->executed) {
            $this->execute();
        }

        if ($estFinContrat) {
            return isset($this->canCreateRapportFinContrat[$anneeUniv]) && $this->canCreateRapportFinContrat[$anneeUniv];
        } else {
            return isset($this->canCreateRapportAnnuel[$anneeUniv]) && $this->canCreateRapportAnnuel[$anneeUniv];
        }
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
        if (!$this->executed) {
            $this->execute();
        }

        // Détermination des années universitaires disponibles
        $anneesPrises = $this->getAnneesPrises();
        $anneesUnivsDisponibles = array_filter($this->anneesUnivs, function(AnneeUnivInterface $annee) use ($anneesPrises) {
            $utilisee = in_array($annee->getPremiereAnnee(), $anneesPrises);
            return !$utilisee;
        });

        // Attachement d'infos utiles exploitées dans la vue
        $data = new SplObjectStorage();
        foreach ($anneesUnivsDisponibles as $anneeUniv) {
            // Classes CSS permettant de gérer dans la vue le fait qu'une année universitaire est compatible ou non
            // avec un type de rapport (annuel ou fin de contrat)
            $cssClasses = ['annee-univ'];
            if ($this->canCreateRapportAnnuelForAnneeUniv($anneeUniv)) {
                $cssClasses[] = RapportActiviteAbstractForm::ANNEE_UNIV__HTML_CLASS_ATTRIB__VALUE_PREFIX .
                    RapportActiviteAbstractForm::EST_FINAL__VALUE__ANNUEL;
            }
            if ($this->canCreateRapportFinContratForAnneeUniv($anneeUniv)) {
                $cssClasses[] = RapportActiviteAbstractForm::ANNEE_UNIV__HTML_CLASS_ATTRIB__VALUE_PREFIX .
                    RapportActiviteAbstractForm::EST_FINAL__VALUE__FIN_CONTRAT;
            }
            $info = ['class' => implode(' ', $cssClasses)];

            $data->attach($anneeUniv, $info);
        }

        return $data;
    }

    public function canCreateRapport(RapportActivite $rapport): bool
    {
        if (!$this->executed) {
            $this->execute();
        }

        if ($rapport->estFinContrat()) {
            return $this->canCreateRapportFinContratForAnneeUniv($rapport->getAnneeUniv());
        } else {
            return $this->canCreateRapportAnnuelForAnneeUniv($rapport->getAnneeUniv());
        }
    }

    public function canCreateRapportAnnuel(): bool
    {
        if (!$this->executed) {
            $this->execute();
        }

        // Peut être téléversé : 1 rapport annuel par année universitaire.

        foreach ($this->anneesUnivs as $anneeUniv) {
            $rapportsExistants = array_filter(
                $this->rapportsExistantsAnnuels,
                $this->rapportActiviteService->getFilterRapportsByAnneeUniv($anneeUniv)
            );
            if (empty($rapportsExistants)) {
                return true;
            }
        }

        return false;
    }

    public function canCreateRapportFinContrat(): bool
    {
        if (!$this->executed) {
            $this->execute();
        }

        // Dépôt d'1 rapport de fin de contrat maxi toutes années univ confondues.

        return count($this->rapportsExistantsFinContrat) === 0;
    }

    /**
     * @return int[]
     */
    private function getAnneesPrises(): array
    {
        return array_keys(
            array_intersect_key(
                array_filter($this->canCreateRapportAnnuel, fn(bool $can) => $can === false),
                array_filter($this->canCreateRapportFinContrat, fn(bool $can) => $can === false)
            )
        );
    }

    private function canCreateRapportAnnuelForAnneeUniv(AnneeUnivInterface $anneeUniv): bool
    {
        if ($this->rapportsExistantsAnnuels === null) {
            throw new InvalidArgumentException("La liste des rapports annuels existants n'a pas été fournie");
        }

        // Peut être téléversé pour une année : 1 rapport annuel.

        $rapportsExistants = array_filter(
            $this->rapportsExistantsAnnuels,
            $this->rapportActiviteService->getFilterRapportsByAnneeUniv($anneeUniv)
        );

        return empty($rapportsExistants);
    }

    private function canCreateRapportFinContratForAnneeUniv(AnneeUnivInterface $anneeUniv): bool
    {
        if ($this->rapportsExistantsAnnuels === null) {
            throw new InvalidArgumentException("La liste des rapports de fin de contrat existants n'a pas été fournie");
        }

        // Dépôt d'un rapport de fin de contrat seulement sur la dernière année univ.

        if ($anneeUniv->getPremiereAnnee() !== $this->getAnneeUnivMax()->getPremiereAnnee()) {
            return false;
        }

        $rapportsExistants = array_filter(
            $this->rapportsExistantsFinContrat,
            $this->rapportActiviteService->getFilterRapportsByAnneeUniv($anneeUniv)
        );

        return empty($rapportsExistants);
    }

    private function getAnneeUnivMax(): AnneeUnivInterface
    {
        /** @var int[] $annees */
        $annees = array_map(function(AnneeUnivInterface $anneeUniv) {
            return $anneeUniv->getPremiereAnnee();
        }, $this->anneesUnivs);

        return AnneeUniv::fromPremiereAnnee(max($annees));
    }
}