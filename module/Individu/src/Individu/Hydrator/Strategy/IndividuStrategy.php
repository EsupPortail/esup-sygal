<?php

namespace Individu\Hydrator\Strategy;

use Individu\Entity\Db\Individu;
use Individu\Service\IndividuServiceAwareTrait;
use Laminas\Hydrator\Strategy\StrategyInterface;

class IndividuStrategy implements StrategyInterface
{
    use IndividuServiceAwareTrait;

    /**
     * @param \Individu\Entity\Db\Individu $value
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
     * @return \Individu\Entity\Db\Individu
     */
    public function hydrate($value, ?array $data): Individu
    {
        /** @var Individu $individu */
        $individu = $this->individuService->getRepository()->find($value);

        return $individu;
    }
}