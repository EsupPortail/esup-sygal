<?php

namespace Structure\Hydrator\Strategy;

use Laminas\Hydrator\Strategy\StrategyInterface;
use Structure\Entity\Db\UniteRecherche;
use Structure\Service\UniteRecherche\UniteRechercheServiceAwareTrait;

class UniteRechercheStrategy implements StrategyInterface
{
    use UniteRechercheServiceAwareTrait;

    /**
     * @param \Structure\Entity\Db\UniteRecherche $value
     * @param object|null $object
     * @return int
     */
    public function extract($value, ?object $object = null): int
    {
        return $value->getId();
    }

    /**
     * @param int $value
     * @param array|null $data
     * @return \Structure\Entity\Db\UniteRecherche
     */
    public function hydrate($value, ?array $data): UniteRecherche
    {
        /** @var UniteRecherche $ur */
        $ur = $this->uniteRechercheService->getRepository()->find($value);

        return $ur;
    }
}