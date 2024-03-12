<?php

namespace RapportActivite\Entity\Db;

use Application\Constants;
use Application\Entity\AnneeUniv;
use Application\Entity\Db\TypeValidation;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Fichier\Entity\Db\Fichier;
use Laminas\Permissions\Acl\Resource\ResourceInterface;
use RapportActivite\Entity\ActionDiffusionCultureScientifique;
use RapportActivite\Entity\AutreActivite;
use RapportActivite\Entity\Formation;
use RapportActivite\Entity\RapportActiviteOperationInterface;
use RuntimeException;
use These\Entity\Db\These;
use UnicaenApp\Entity\HistoriqueAwareInterface;
use UnicaenApp\Entity\HistoriqueAwareTrait;
use UnicaenAvis\Entity\Db\AvisType;
use Webmozart\Assert\Assert;

class RapportActivite implements ResourceInterface, HistoriqueAwareInterface
{
    use HistoriqueAwareTrait;

    const CODE = 'RAPPORT_ACTIVITE';
    const RESOURCE_ID = 'RapportActivite';

    const LIBELLE_ANNUEL = 'Annuel';
    const LIBELLE_FIN_CONTRAT = 'Fin de contrat';

    private ?int $id = null;
    private int $anneeUniv;
    private bool $estFinContrat = false;
    private bool $parDirecteurThese = false; // rapport créé par le directeur de thèse (en cas d'incapacité du doctorant)
    private ?string $parDirecteurTheseMotif = null;
    private ?Fichier $fichier = null;
    private ?string $descriptionProjetRecherche = null;
    private ?string $principauxResultatsObtenus = null;
    private ?string $productionsScientifiques = null;

    /**
     * Le stockage des formations dans le rapport d'activité est très rudimentaire. Comme il n'y a pas de relation
     * 'to-many' entre le {@see \RapportActivite\Entity\Db\RapportActivite} et les formations suivies, on la
     * "simule" en stockant une chaîne de caractères représentant au format JSON la liste des formations suivies
     * (intitulé et durée).
     */
    private ?string $formationsSpecifiques = null;
    /**
     * Le stockage des formations dans le rapport d'activité est très rudimentaire. Comme il n'y a pas de relation
     * 'to-many' entre le {@see \RapportActivite\Entity\Db\RapportActivite} et les formations suivies, on la
     * "simule" en stockant une chaîne de caractères représentant au format JSON la liste des formations suivies
     * (intitulé et durée).
     */
    private ?string $formationsTransversales = null;

    private ?string $actionsDiffusionCultureScientifique = null;
    private ?string $autresActivites = null;
    private ?string $calendrierPrevionnelFinalisation = null;
    private ?string $preparationApresThese = null;
    private ?string $perspectivesApresThese = null;
    private ?string $commentaires = null;

    private ?These $these = null;

    /** @var Collection|RapportActiviteValidation[] */
    private $rapportValidations;

    /** @var Collection|\RapportActivite\Entity\Db\RapportActiviteAvis[] */
    private $rapportAvis;

    /** @var \RapportActivite\Entity\Db\RapportActiviteAvis|\RapportActivite\Entity\Db\RapportActiviteValidation|null */
    private $operationPossible = null;

    public function __construct()
    {
        $this->anneeUniv = (int) (new DateTime('today'))->format('Y');
        $this->rapportValidations = new ArrayCollection();
        $this->rapportAvis = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->getTypeRapportLibelle() . ' ' . $this->getAnneeUniv()->toString();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAnneeUniv(): AnneeUniv
    {
        return AnneeUniv::fromPremiereAnnee($this->anneeUniv);
    }

    /**
     * @param int|AnneeUniv $anneeUniv
     */
    public function setAnneeUniv($anneeUniv): self
    {
        if ($anneeUniv instanceof AnneeUniv) {
            $anneeUniv = $anneeUniv->getPremiereAnnee();
        }

        $this->anneeUniv = $anneeUniv;
        return $this;
    }

    public function estFinContrat(): bool
    {
        return $this->estFinContrat;
    }

    public function getEstFinContratToString(): string
    {
        return $this->estFinContrat ? self::LIBELLE_FIN_CONTRAT : self::LIBELLE_ANNUEL;
    }

    public function setEstFinContrat(bool $estFinContrat = true): self
    {
        $this->estFinContrat = $estFinContrat;

        return $this;
    }

    public function getParDirecteurThese(): bool
    {
        return $this->parDirecteurThese;
    }

    public function setParDirecteurThese(bool $parDirecteurThese): self
    {
        $this->parDirecteurThese = $parDirecteurThese;
        return $this;
    }

    public function getParDirecteurTheseMotif(): ?string
    {
        return $this->parDirecteurTheseMotif;
    }

    public function setParDirecteurTheseMotif(?string $parDirecteurTheseMotif): self
    {
        $this->parDirecteurTheseMotif = $parDirecteurTheseMotif;
        return $this;
    }

    public function getTypeRapportLibelle(): string
    {
        return "Rapport d'activité " . (
            $this->estFinContrat ?
                'de ' . lcfirst(self::LIBELLE_FIN_CONTRAT) :
                lcfirst(self::LIBELLE_ANNUEL)
            );
    }

    public function getDescriptionProjetRecherche(): ?string
    {
        return $this->descriptionProjetRecherche;
    }

    public function setDescriptionProjetRecherche(?string $descriptionProjetRecherche): self
    {
        $this->descriptionProjetRecherche = $descriptionProjetRecherche;
        return $this;
    }

    public function getPrincipauxResultatsObtenus(): ?string
    {
        return $this->principauxResultatsObtenus;
    }

    public function setPrincipauxResultatsObtenus(?string $principauxResultatsObtenus): self
    {
        $this->principauxResultatsObtenus = $principauxResultatsObtenus;
        return $this;
    }

    public function getProductionsScientifiques(): ?string
    {
        return $this->productionsScientifiques;
    }

    public function setProductionsScientifiques(?string $productionsScientifiques): self
    {
        $this->productionsScientifiques = $productionsScientifiques;
        return $this;
    }

    /**
     * Initialisation des formations transversales et/ou spécifiques suivies par le doctorant à partir des
     * {@see \Formation\Entity\Db\Inscription} renseignées dans le module Formation.
     *
     * Le stockage des formations dans le rapport d'activité est très rudimentaire. Comme il n'y a pas de relation
     * 'to-many' entre le {@see \RapportActivite\Entity\Db\RapportActivite} et les formations suivies, on la
     * "simule" en stockant une chaîne de caractères représentant au format JSON la liste des formations suivies
     * (intitulé et durée).
     *
     * @param \Formation\Entity\Db\Inscription[] $formationInscriptions
     */
    public function setFormationsFromInscriptions(array $formationInscriptions): void
    {
        // parcours des inscriptions à des sessions pour cumul des durées par intitulé de formation
        $infosFormations = [];
        foreach ($formationInscriptions as $inscription) {
            $session = $inscription->getSession();
            $dateDebut = $session->getDateDebut();
            if ($dateDebut > new DateTime()) {
                continue; // on écarte les sessions situées dans le futur
            }
            if ($inscription->computeDureePresence() === 0.0) {
                continue; // on écarte les inscription dont la durée de présence est nulle
            }
            $formation = $session->getFormation();
            $type = $formation->getType();
            $intitule = sprintf('%s (%s)', $formation->getLibelle(), $dateDebut->format(Constants::DATE_FORMAT));
            $infosFormations[$type][$session->getId()] = $infosFormations[$type][$session->getId()] ?? ['intitule' => null, 'temps' => 0.0];
            $infosFormations[$type][$session->getId()]['intitule'] = $intitule;
            $infosFormations[$type][$session->getId()]['temps'] += $inscription->computeDureePresence();
        }
        foreach ($infosFormations as $type => $data) {
            $formations = array_map(fn($d) => Formation::fromArray($d), $data);
            if ($formations) {
                if ($type === \Formation\Entity\Db\Formation::TYPE_CODE_SPECIFIQUE) {
                    $this->setFormationsSpecifiques($formations);
                } elseif ($type === \Formation\Entity\Db\Formation::TYPE_CODE_TRAVERSAL) {
                    $this->setFormationsTransversales($formations);
                }
            }
        }
    }

    /**
     * @return \RapportActivite\Entity\Formation[]
     */
    public function getFormationsSpecifiques(): array
    {
        $toArray = [];
        if ($formations = $this->formationsSpecifiques) {
            foreach (json_decode($formations, true) as $array) {
                $toArray[] = Formation::fromArray($array);
            }
        }

        return $toArray;
    }

    public function getFormationsSpecifiquesTempsTotal(): int
    {
        return array_reduce($this->getFormationsSpecifiques(), fn(int $sum, $e) => $sum + $e->getTemps(), 0);
    }

    /**
     * @param \RapportActivite\Entity\Formation[] $formations
     */
    public function setFormationsSpecifiques(array $formations = []): self
    {
        Assert::allIsInstanceOf($formations, Formation::class);

        usort($formations, fn($a, $b) => $b->getTemps() <=> $a->getTemps());
        $formations = array_map(fn($entity) => $entity->toArray(), $formations);
        $formationsAsString = json_encode($formations);

        $this->formationsSpecifiques = $formationsAsString;

        return $this;
    }

    /**
     * @return \RapportActivite\Entity\Formation[]
     */
    public function getFormationsTransversales(): array
    {
        $toArray = [];
        if ($actions = $this->formationsTransversales) {
            foreach (json_decode($actions, true) as $array) {
                $toArray[] = Formation::fromArray($array);
            }
        }

        return $toArray;
    }

    public function getFormationsTransversalesTempsTotal(): int
    {
        return array_reduce($this->getFormationsTransversales(), fn(int $sum, $e) => $sum + $e->getTemps(), 0);
    }

    /**
     * @param \RapportActivite\Entity\Formation[] $formations
     */
    public function setFormationsTransversales(array $formations = []): self
    {
        Assert::allIsInstanceOf($formations, Formation::class);

        usort($formations, fn($a, $b) => $b->getTemps() <=> $a->getTemps());
        $formations = array_map(fn($entity) => $entity->toArray(), $formations);
        $formationsAsString = $formations ? json_encode($formations) : null;

        $this->formationsTransversales = $formationsAsString;

        return $this;
    }

    /**
     * @return \RapportActivite\Entity\ActionDiffusionCultureScientifique[]
     */
    public function getActionsDiffusionCultureScientifique(): array
    {
        $toArray = [];
        if ($actions = $this->actionsDiffusionCultureScientifique) {
            foreach (json_decode($actions, true) as $array) {
                $toArray[] = ActionDiffusionCultureScientifique::fromArray($array);
            }
        }

        return $toArray;
    }

    public function getActionsDiffusionCultureScientifiqueTempsTotal(): int
    {
        return array_reduce($this->getActionsDiffusionCultureScientifique(), fn(int $sum, $e) => $sum + $e->getTemps(), 0);
    }

    /**
     * @param \RapportActivite\Entity\ActionDiffusionCultureScientifique[] $actions
     */
    public function setActionsDiffusionCultureScientifique(array $actions = []): self
    {
        Assert::allIsInstanceOf($actions, ActionDiffusionCultureScientifique::class);

        usort($actions, fn($a, $b) => $a->getDate() <=> $b->getDate());
        $actions = array_map(fn($action) => $action->toArray(), $actions);
        $actionsAsString = json_encode($actions);

        $this->actionsDiffusionCultureScientifique = $actionsAsString;

        return $this;
    }

    /**
     * @return \RapportActivite\Entity\AutreActivite[]
     */
    public function getAutresActivites(): array
    {
        $toArray = [];
        if ($activites = $this->autresActivites) {
            foreach (json_decode($activites, true) as $array) {
                $toArray[] = AutreActivite::fromArray($array);
            }
        }

        return $toArray;
    }

    public function getAutresActivitesTempsTotal(): int
    {
        return array_reduce($this->getAutresActivites(), fn(int $sum, $e) => $sum + $e->getTemps(), 0);
    }

    /**
     * @param \RapportActivite\Entity\AutreActivite[] $autresActivites
     */
    public function setAutresActivites(array $autresActivites = []): self
    {
        usort($autresActivites, fn($a, $b) => $a->getDate() <=> $b->getDate());
        $autresActivites = array_map(fn($action) => $action->toArray(), $autresActivites);
        $autresActivitesAsString = json_encode($autresActivites);

        $this->autresActivites = $autresActivitesAsString;

        return $this;
    }

    public function getCalendrierPrevionnelFinalisationEnabled(AnneeUniv $anneeUnivCourante): bool
    {
        return
            !$this->estFinContrat() &&
            $this->getThese()->getAnneesUnivInscriptionCount($anneeUnivCourante) >= 3; // à partir de la 3ème année d'inscription
    }

    public function getCalendrierPrevionnelFinalisation(): ?string
    {
        return $this->calendrierPrevionnelFinalisation;
    }

    public function setCalendrierPrevionnelFinalisation(?string $calendrierPrevionnelFinalisation): self
    {
        $this->calendrierPrevionnelFinalisation = $calendrierPrevionnelFinalisation;
        return $this;
    }

    public function getPreparationApresTheseEnabled(): bool
    {
        return !$this->estFinContrat();
    }

    public function getPreparationApresThese(): ?string
    {
        return $this->preparationApresThese;
    }

    public function setPreparationApresThese(?string $preparationApresThese): self
    {
        $this->preparationApresThese = $preparationApresThese;
        return $this;
    }

    public function getPerspectivesApresTheseEnabled(): bool
    {
        return $this->estFinContrat();
    }

    public function getPerspectivesApresThese(): ?string
    {
        return $this->perspectivesApresThese;
    }

    /**
     * @param string|null $perspectivesApresThese
     * @return self
     */
    public function setPerspectivesApresThese(?string $perspectivesApresThese): self
    {
        $this->perspectivesApresThese = $perspectivesApresThese;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCommentaires(): ?string
    {
        return $this->commentaires;
    }

    /**
     * @param string|null $commentaires
     * @return self
     */
    public function setCommentaires(?string $commentaires): self
    {
        $this->commentaires = $commentaires;
        return $this;
    }

    /**
     * Détache le fichier téléversé pour ce rapport non dématérialisé (ancien mode opératoire).
     */
    public function removeFichier(): self
    {
        $this->fichier = null;

        return $this;
    }

    /**
     * Retourne le fichier téléversé pour ce rapport non dématérialisé (ancien mode opératoire).
     */
    public function getFichier(): ?Fichier
    {
        return $this->fichier;
    }

    public function getThese(): ?These
    {
        return $this->these;
    }

    public function setThese(These $these): self
    {
        $this->these = $these;

        return $this;
    }

    /**
     * Retourne l'éventuelle validation de ce rapport, du type spécifié.
     */
    public function getRapportValidationOfType(TypeValidation $typeValidation): ?RapportActiviteValidation
    {
        $validations = $this->getRapportValidations()->filter(function(RapportActiviteValidation $v) use ($typeValidation) {
            return $v->getTypeValidation() === $typeValidation;
        });

        if (count($validations) > 1) {
            throw new RuntimeException("Anomalie : plusieurs validations de rapport du même type trouvées");
        }

        return $validations->first() ?: null;
    }

    /**
     * @return \RapportActivite\Entity\Db\RapportActiviteValidation[]|Collection
     */
    public function getRapportValidations(bool $includeHistorises = false): Collection
    {
        if ($includeHistorises) {
            return $this->rapportValidations;
        }

        return $this->rapportValidations->filter(function(RapportActiviteValidation $v) {
            return $v->estNonHistorise();
        });
    }

    public function addRapportValidation(RapportActiviteValidation $validation): self
    {
        $this->rapportValidations->add($validation);

        return $this;
    }

    public function removeRapportValidation(RapportActiviteValidation $validation): self
    {
        $this->rapportValidations->removeElement($validation);

        return $this;
    }

    /**
     * @deprecated
     */
    public function getFirstRapportAvis(): ?RapportActiviteAvis
    {
        $rapportsAvis = $this->rapportAvis->filter(function(RapportActiviteAvis $rapportAvis) {
            return $rapportAvis->estNonHistorise();
        });

        return $rapportsAvis->first() ?: null;
    }

    /**
     * Injecte l'opération éventuelle qu'il est possible de réaliser sur ce rapport.
     */
    public function setOperationPossible(?RapportActiviteOperationInterface $operation = null): self
    {
        $this->operationPossible = $operation;
        if ($this->operationPossible) {
            $this->operationPossible->setRapportActivite($this);
        }

        return $this;
    }

    public function getOperationPossible()
    {
        return $this->operationPossible;
    }

    /**
     * @return \RapportActivite\Entity\Db\RapportActiviteAvis[]|Collection
     */
    public function getRapportAvis(bool $includeHistorises = false): Collection
    {
        if ($includeHistorises) {
            return $this->rapportAvis;
        }

        return $this->rapportAvis->filter(function(RapportActiviteAvis $rapportAvis) {
            return $rapportAvis->estNonHistorise();
        });
    }

    /**
     * Retourne l'éventuel avis sur ce rapport, du type spécifié.
     */
    public function getRapportAvisOfType(AvisType $avisType): ?RapportActiviteAvis
    {
        $aviss = $this->getRapportAvis()->filter(function(RapportActiviteAvis $avis) use ($avisType) {
            return $avis->getAvis()->getAvisType() === $avisType;
        });

        if (count($aviss) > 1) {
            throw new RuntimeException("Anomalie : plusieurs aviss de rapport du même type trouvées");
        }

        return $aviss->first() ?: null;
    }

    public function addRapportAvis(RapportActiviteAvis $rapportAvis): self
    {
        $this->rapportAvis->add($rapportAvis);

        return $this;
    }

    public function removeRapportAvis(RapportActiviteAvis $rapportAvis): self
    {
        $this->rapportAvis->removeElement($rapportAvis);

        return $this;
    }

    public function getResourceId(): string
    {
        return self::RESOURCE_ID;
    }

    public function generateInternalPathForZipArchive(): string
    {
        $these = $this->getThese();

        return sprintf('%s/%s/%s/%s',
            $these->getEtablissement()->getStructure()->getSourceCode(),
            ($ed = $these->getEcoleDoctorale()) ? $ed->getStructure()->getCode() : "ED_inconnue",
            ($ur = $these->getUniteRecherche()) ? $ur->getStructure()->getCode() : "UR_inconnue",
            $this->getFichier()->getNom()
        );
    }

    /**
     * Teste si le fichier du rapport supporte l'ajout de la page de validation.
     */
    public function supporteAjoutPageValidation(): bool
    {
        return $this->getFichier()->isTypeMimePdf();
    }
}
