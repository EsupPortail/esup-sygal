<?php

namespace Structure\Hydrator\Strategy;

use Laminas\Hydrator\Strategy\StrategyInterface;
use Structure\Entity\Db\Etablissement;
use Structure\Service\Etablissement\EtablissementServiceAwareTrait;

class EtablissementStrategy implements StrategyInterface
{
    use EtablissementServiceAwareTrait;

    /**
     * @param \Structure\Entity\Db\Etablissement $value
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
     * @return \Structure\Entity\Db\Etablissement
     */
    public function hydrate($value, ?array $data): Etablissement
    {
        /** @var Etablissement $etablissement */
        $etablissement = $this->etablissementService->getRepository()->find($value);

        return $etablissement;
    }
}