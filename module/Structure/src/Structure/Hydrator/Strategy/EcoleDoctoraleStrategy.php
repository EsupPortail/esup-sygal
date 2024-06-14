<?php

namespace Structure\Hydrator\Strategy;

use Laminas\Hydrator\Strategy\StrategyInterface;
use Structure\Entity\Db\EcoleDoctorale;
use Structure\Service\EcoleDoctorale\EcoleDoctoraleServiceAwareTrait;

class EcoleDoctoraleStrategy implements StrategyInterface
{
    use EcoleDoctoraleServiceAwareTrait;

    /**
     * @param \Structure\Entity\Db\EcoleDoctorale $value
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
     * @return \Structure\Entity\Db\EcoleDoctorale
     */
    public function hydrate($value, ?array $data): EcoleDoctorale
    {
        /** @var EcoleDoctorale $ed */
        $ed = $this->ecoleDoctoraleService->getRepository()->find($value);

        return $ed;
    }
}