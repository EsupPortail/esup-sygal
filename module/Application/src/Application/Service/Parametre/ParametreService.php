<?php

namespace Application\Service\Parametre;

use Application\Entity\Db\Parametre;
use Application\Service\BaseService;
use Application\Entity\Db\Repository\DefaultEntityRepository;
use RuntimeException;

/**
 * @method Parametre|null findOneBy(array $criteria, array $orderBy = null)
 */
class ParametreService extends BaseService
{
    /**
     * @return DefaultEntityRepository
     */
    public function getRepository()
    {
        return $this->entityManager->getRepository(Parametre::class);
    }

    /**
     * Retourne la valeur d'un paramètre spécifié par son code.
     *
     * @param $id
     * @return string
     */
    public function get($id)
    {
        return $this->fetch($id)->getValeur();
    }

    /**
     * @param string $id
     * @return Parametre
     * @throws RuntimeException Si paramètre introuvable
     */
    private function fetch($id)
    {
        $p = $this->getRepository()->findOneBy(['id' => $id]);
        if (null === $p) {
            throw new RuntimeException("Paramètre '$id'' introuvable");
        }
        return $p;
    }
}