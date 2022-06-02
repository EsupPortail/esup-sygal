<?php

namespace UnicaenAvis\Hydrator;

use Doctrine\Laminas\Hydrator\Strategy\AllowRemoveByValue;
use InvalidArgumentException;
use Laminas\Hydrator\AbstractHydrator;
use UnicaenAvis\Entity\Db\AvisComplem;
use UnicaenAvis\Entity\Db\AvisTypeValeurComplem;
use UnicaenAvis\Service\AvisServiceAwareTrait;

class AvisHydrator extends AbstractHydrator
{
    use AvisServiceAwareTrait;

    /**
     * @param object|\UnicaenAvis\Entity\Db\Avis $object
     * @return array
     */
    public function extract(object $object): array
    {
        $data = [];
        $data[$object->getAvisType()->getCode()] = $object->getAvisValeur() ? $object->getAvisValeur()->getCode() : null;
        foreach ($object->getAvisComplems() as $avisComplem) {
            $data[$avisComplem->getAvisTypeValeurComplem()->getCode()] = $avisComplem->getValeur();
        }

        return $data;
    }

    /**
     * @inheritDoc
     */
    public function hydrate(array $data, object $object): object
    {
        /** @var \UnicaenAvis\Entity\Db\Avis $object */

        $avisType = $object->getAvisType();

        $name = $avisType->getCode();
        $avisValeur = $this->avisService->findOneAvisValeurByCode($data[$name]);
        $object->setAvisValeur($avisValeur);

        $avisComplems = [];
        $avisTypeValeur = $this->avisService->findOneAvisTypeValeur($avisType, $avisValeur);
        $avisTypeValeurComplems = $avisTypeValeur->getAvisTypeValeurComplems();
        foreach ($avisTypeValeurComplems as $avisTypeValeurComplem) {
            if ($this->shouldCreateAvisComplem($data, $avisTypeValeurComplem)) {
                $name = $avisTypeValeurComplem->getCode();
                $complemValue = $data[$name];

                $avisComplem = new AvisComplem();
                $avisComplem
                    ->setAvis($object)
                    ->setValeur($complemValue)
                    ->setAvisTypeValeurComplem($avisTypeValeurComplem);

                $avisComplems[] = $avisComplem;
            }
        }
        $strategy = new AllowRemoveByValue();
        $strategy->setObject($object)->setCollectionName('avisComplems');
        $strategy->hydrate($avisComplems, []);

        return $object;
    }

    protected function shouldCreateAvisComplem(array $data, AvisTypeValeurComplem $avisTypeValeurComplem): bool
    {
        $name = $avisTypeValeurComplem->getCode();

        // un complément enfant n'est pas pris en compte si son parent n'est pas renseigné
        if ($avisTypeValeurComplemParent = $avisTypeValeurComplem->getAvisTypeValeurComplemParent()) {
            $avisTypeValeurComplemParentValue = $data[$avisTypeValeurComplemParent->getCode()] ?? null;
            if (!$avisTypeValeurComplemParentValue) {
                return false;
            }
        }

        if (!array_key_exists($name, $data)) {
            return false;
        }

        switch ($type = $avisTypeValeurComplem->getType()) {
            case AvisTypeValeurComplem::TYPE_COMPLEMENT_CHECKBOX:
                return $data[$name] === '1';

            case AvisTypeValeurComplem::TYPE_COMPLEMENT_TEXTAREA:
                return (bool) trim($data[$name]);

            case AvisTypeValeurComplem::TYPE_COMPLEMENT_INFORMATION:
                return false;

            default:
                throw new InvalidArgumentException("Type de complément rencontré inattendu : " . $type);
        }
    }
}