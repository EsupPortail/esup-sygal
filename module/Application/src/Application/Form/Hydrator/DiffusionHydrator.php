<?php

namespace Application\Form\Hydrator;

use Application\Entity\Db\Diffusion;
use Application\Entity\Db\RecapBu;
use Doctrine\ORM\OptimisticLockException;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject;
use UnicaenApp\Service\EntityManagerAwareInterface;
use UnicaenApp\Service\EntityManagerAwareTrait;

/**
 * Created by PhpStorm.
 * User: gauthierb
 * Date: 20/05/16
 * Time: 17:08
 */
class DiffusionHydrator extends DoctrineObject implements EntityManagerAwareInterface
{
    use EntityManagerAwareTrait;

    /**
     * Extract values from an object
     *
     * @param  Diffusion $diffusion
     * @return array
     */
    public function extract($diffusion)
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
     * @throws OptimisticLockException
     */
    public function hydrate(array $data, $attestation)
    {
        // le champ de saisie de la confidentialité est grisé pour l'instant
        if (!isset($data['confidentielle'])) {
            $data['confidentielle'] = $attestation->getThese()->getDateFinConfidentialite() !== null ? Diffusion::CONFIDENTIELLE_OUI : Diffusion::CONFIDENTIELLE_NON;
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

        /** @var RecapBu $recap */
        $repoRecapBu = $this->entityManager->getRepository(RecapBu::class);
        $recap = $repoRecapBu->findOneBy(["these" => $diff->getThese()]);
        if ($recap !== null) {
            $recap->setIdOrcid($diff->getIdOrcid());
            $this->entityManager->flush($recap);
        }

        return $diff;
    }
}