<?php

namespace Depot\Rule;

use Application\Rule\RuleInterface;
use Depot\Entity\Db\Diffusion;
use Doctrine\ORM\EntityManager;

/**
 * Règle métier concernant la remise d'une version papier de la thèse.
 *
 * @author Unicaen
 */
class AutorisationDiffusionRule implements RuleInterface
{
    /**
     * @var Diffusion
     */
    private $diffusion;

    /**
     * @param Diffusion $diffusion
     * @return self
     */
    public function setDiffusion(Diffusion $diffusion): self
    {
        $this->diffusion = $diffusion;

        return $this;
    }

    /**
     * @return self
     */
    public function execute(): self
    {
        // rien n'est fait ici

        return $this;
    }

    /**
     * Retourne un booléen indiquant si la remise d'un exemplaire papier est requise.
     *
     * @return bool
     */
    public function computeRemiseExemplairePapierEstRequise(): bool
    {
        return $this->autorisMelEntraineRemiseExemplairePapier($this->diffusion->getAutorisMel());
    }

    /**
     * Détermine si la réponse à l'autorisation de diffusion (NON ENCORE ENREGISTRÉE) a changé de manière "importante",
     * càd si elle est passée
     * de "Oui" à "Oui+Embargo" ou "Non",
     * ou
     * de "Oui+Embargo" ou "Non" à "Oui".
     *
     * ATTENTION: cette méthode ne peut être appelée qu'avant le flush de l'entity manager.
     *
     * @param EntityManager $entityManager
     * @return bool
     */
    public function computeChangementDeReponseImportant(EntityManager $entityManager): bool
    {
        $metadata = $entityManager->getClassMetadata(Diffusion::class);
        $uow = $entityManager->getUnitOfWork();
        $uow->recomputeSingleEntityChangeSet($metadata, $this->diffusion);
        $changeset = $uow->getEntityChangeSet($this->diffusion);

        if (isset($changeset['autorisMel'])) {
            $oldValue = $changeset['autorisMel'][0];
            $newValue = $changeset['autorisMel'][1];
            return
                $this->autorisMelEntraineRemiseExemplairePapier($oldValue) && ! $this->autorisMelEntraineRemiseExemplairePapier($newValue)
                ||
                $this->autorisMelEntraineRemiseExemplairePapier($newValue) && ! $this->autorisMelEntraineRemiseExemplairePapier($oldValue);
        }

        return false;
    }

    /**
     * Teste si la réponse spécifiée à l'autorisation de diffusion entraîne ou non la nécessité de remettre
     * une version imprimée de la thèse.
     *
     * @param int $autorisMel
     * @return bool
     */
    private function autorisMelEntraineRemiseExemplairePapier($autorisMel): bool
    {
        if ($autorisMel === Diffusion::AUTORISATION_OUI_EMBARGO || $autorisMel === Diffusion::AUTORISATION_NON) {
            return true;
        } else {
            return false;
        }
    }
}