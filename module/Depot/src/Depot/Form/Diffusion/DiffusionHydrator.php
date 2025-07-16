<?php

namespace Depot\Form\Diffusion;

use Depot\Entity\Db\Diffusion;
use Doctrine\Laminas\Hydrator\DoctrineObject;
use UnicaenApp\Service\EntityManagerAwareInterface;
use UnicaenApp\Service\EntityManagerAwareTrait;

class DiffusionHydrator extends DoctrineObject implements EntityManagerAwareInterface
{
    use EntityManagerAwareTrait;

    /**
     * @param Diffusion $object
     */
    public function extract($object): array
    {
        $data = parent::extract($object);

        $data['confidentielle'] = $object->getThese()->getDateFinConfidentialite() !== null ? Diffusion::CONFIDENTIELLE_OUI : Diffusion::CONFIDENTIELLE_NON;
        $data['dateFinConfidentialite'] = $object->getThese()->getDateFinConfidentialite();

        return $data;
    }

    /**
     * @param array $data
     * @param Diffusion $object
     * @return \Depot\Entity\Db\Diffusion
     */
    public function hydrate(array $data, $object): Diffusion
    {
        // NB : le champ de saisie de la confidentialité est grisé pour l'instant
        if (!isset($data['confidentielle'])) {
            $data['confidentielle'] = $object->getThese()->getDateFinConfidentialite() !== null ? Diffusion::CONFIDENTIELLE_OUI : Diffusion::CONFIDENTIELLE_NON;
        }
        if (!isset($data['dateFinConfidentialite'])) {
            $data['dateFinConfidentialite'] = $object->getThese()->getDateFinConfidentialite();
        }

        if (!isset($data['orcid'])) {
            $data['orcid'] = null;
        }
        if (!isset($data['halId'])) {
            $data['halId'] = null;
        }

        /** @var Diffusion $diff */
        $diff = parent::hydrate($data, $object);

        switch ($diff->getAutorisMel()) {
            case Diffusion::AUTORISATION_OUI_IMMEDIAT:
                $diff->setAutorisEmbargoDuree(null);
                $diff->setAutorisMotif(null);
                $diff->setAutorisPretPapier(null);
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