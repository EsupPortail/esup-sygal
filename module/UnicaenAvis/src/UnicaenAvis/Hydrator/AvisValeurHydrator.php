<?php

namespace UnicaenAvis\Hydrator;

use Doctrine\Laminas\Hydrator\DoctrineObject;
use UnicaenAvis\Service\AvisServiceAwareTrait;

class AvisValeurHydrator extends DoctrineObject
{
    use AvisServiceAwareTrait;

//    /**
//     * @param object|\UnicaenAvis\Entity\Db\Avis $object
//     * @return array
//     */
//    public function extract(object $object): array
//    {
//        $data = [];
//        $data[$object->getAvisType()->getCode()] = $object->getAvisValeur() ? $object->getAvisValeur()->getCode() : null;
//        foreach ($object->getAvisComplems() as $avisComplem) {
//            $data[$avisComplem->getAvisTypeValeurComplem()->getCode()] = $avisComplem->getValeur();
//        }
//
//        return $data;
//    }
//
//    /**
//     * @inheritDoc
//     */
//    public function hydrate(array $data, object $object): object
//    {
//        /** @var \UnicaenAvis\Entity\Db\AvisType $object */
//
//
//
//        return $object;
//    }
}