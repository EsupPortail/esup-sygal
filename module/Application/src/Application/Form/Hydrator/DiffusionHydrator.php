<?php

namespace Application\Form\Hydrator;

use Application\Entity\Db\Diffusion;
use Doctrine\Laminas\Hydrator\DoctrineObject;
use UnicaenApp\Service\EntityManagerAwareInterface;
use UnicaenApp\Service\EntityManagerAwareTrait;

class DiffusionHydrator extends DoctrineObject implements EntityManagerAwareInterface
{
    use EntityManagerAwareTrait;

    /**
     * Extract values from an object
     *
     * @param  Diffusion $diffusion
     * @return array
     */
    public function extract($diffusion): array
    {
        $data = parent::extract($diffusion);

        $data['confidentielle'] = $diffusion->getThese()->getDateFinConfidentialite() !== null ? Diffusion::CONFIDENTIELLE_OUI : Diffusion::CONFIDENTIELLE_NON;
        $data['dateFinConfidentialite'] = $diffusion->getThese()->getDateFinConfidentialite();

        return $data;
    }

    /**
     * Hydrate $object with the provided $data.
     *
     * @param  array     $data
     * @param  Diffusion $attestation
     * @return Diffusion
     */
    public function hydrate(array $data, $attestation): Diffusion
    {
        // le champ de saisie de la confidentialité est grisé pour l'instant
        if (!isset($data['confidentielle'])) {
            $data['confidentielle'] = $attestation->getThese()->getDateFinConfidentialite() !== null ? Diffusion::CONFIDENTIELLE_OUI : Diffusion::CONFIDENTIELLE_NON;
        }

        if (!isset($data['orcid'])) {
            $data['orcid'] = null;
        }
        if (!isset($data['halId'])) {
            $data['halId'] = null;
        }

        /** @var Diffusion $diff */
        $diff = parent::hydrate($data, $attestation);

        switch ($diff->getAutorisMel()) {
            case Diffusion::AUTORISATION_OUI_IMMEDIAT:
                $diff->setAutorisEmbargoDuree(null);
                $diff->setAutorisMotif(null);
                break;
            case Diffusion::AUTORISATION_NON:
                $diff->setAutorisEmbargoDuree(null);
                break;
        }

        switch ($diff->getConfidentielle()) {
            case false:
                $diff->setDateFinConfidentialite(null);
                break;
        }

        return $diff;
    }
}