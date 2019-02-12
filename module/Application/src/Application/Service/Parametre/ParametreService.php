<?php

namespace Application\Service\Parametre;

use Application\Entity\Db\Parametre;
use Application\Entity\Db\Repository\DefaultEntityRepository;
use Application\Service\BaseService;
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
        /** @var DefaultEntityRepository $repo */
        $repo = $this->entityManager->getRepository(Parametre::class);

        return $repo;
    }

    /**
     * Retourne le Code de l'éventuel établissement chapeau représentant une communauté d'établissements.
     * NULL <=> pas d'établissement chapeau.
     *
     * @return string|null
     */
    public function getSourceCodeEtablissementCommunaute()
    {
        return $this->fetch(Parametre::ID__SOURCE_CODE_ETAB_COMMUNAUTE)->getValeur();
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
        /** @var Parametre $p */
        $p = $this->getRepository()->findOneBy(['id' => $id]);
        if (null === $p) {
            throw new RuntimeException("Paramètre '$id' introuvable");
        }

        return $p;
    }
}