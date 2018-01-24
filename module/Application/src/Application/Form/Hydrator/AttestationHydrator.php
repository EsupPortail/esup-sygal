<?php

namespace Application\Form\Hydrator;

use Application\Entity\Db\Attestation;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject;

class AttestationHydrator extends DoctrineObject
{
//    /**
//     * Extract values from an object
//     *
//     * @param  Attestation $diffusion
//     * @return array
//     */
//    public function extract($diffusion)
//    {
//        $data = parent::extract($diffusion);
//
//        $data['confidentielle'] = $diffusion->getThese()->estConfidentielle() ? Attestation::CONFIDENTIELLE_OUI : Attestation::CONFIDENTIELLE_NON;
//        $data['dateFinConfidentialite'] = $diffusion->getThese()->getDateFinConfidentialite();
//
//        return $data;
//    }
//
//    /**
//     * Hydrate $object with the provided $data.
//     *
//     * @param  array       $data
//     * @param  Attestation $attestation
//     * @return Attestation
//     */
//    public function hydrate(array $data, $attestation)
//    {
//        // le champ de saisie de la confidentialité est grisé pour l'instant
//        if (!isset($data['confidentielle'])) {
//            $data['confidentielle'] = $attestation->getThese()->estConfidentielle() ? Attestation::CONFIDENTIELLE_OUI : Attestation::CONFIDENTIELLE_NON;
//        }
//
//        /** @var Attestation $diff */
//        $diff = parent::hydrate($data, $attestation);
//
//        switch ($diff->getAutorisMel()) {
//            case Attestation::AUTORISATION_OUI_IMMEDIAT:
//                $diff->setAutorisEmbargoDuree(null);
//                $diff->setAutorisMotif(null);
//                break;
//            case Attestation::AUTORISATION_NON:
//                $diff->setAutorisEmbargoDuree(null);
//                break;
//        }
//
//        switch ($diff->getConfidentielle()) {
//            case false:
//                $diff->setDateFinConfidentialite(null);
//                break;
//        }
//
//        return $diff;
//    }
}