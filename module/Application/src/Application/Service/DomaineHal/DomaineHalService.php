<?php

namespace Application\Service\DomaineHal;

use Application\Entity\Db\DomaineHal;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\QueryBuilder;
use UnicaenApp\Exception\RuntimeException;
use UnicaenApp\Service\EntityManagerAwareTrait;

class DomaineHalService {
    use EntityManagerAwareTrait;

    /** REQUETAGE *****************************************************************************************************/

    public function createQueryBuilder() : QueryBuilder
    {
        $qb = $this->getEntityManager()->getRepository(DomaineHal::class)->createQueryBuilder('domaineHal');
        return $qb;
    }

    /**
     * @param string|null $code
     * @return DomaineHal|null
     */
    public function getDomaineHal(?string $code) : ?DomaineHal
    {
        $qb = $this->createQueryBuilder()
            ->andWhere('domaineHal.docId = :code')
            ->setParameter('docId', $code);
        try {
            $result = $qb->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            throw new RuntimeException("Plusieurs DomaineHal partagent le même code [".$code."]");
        }
        return $result;
    }

    /**
     * @param string $champ
     * @param string $ordre
     * @return DomaineHal[]
     */
    public function getDomainesHal(string $champ = 'frDomainS', string $ordre = 'ASC') : array
    {
        $qb = $this->createQueryBuilder()
            ->orderBy('domaineHal.'. $champ, $ordre);
        $result = $qb->getQuery()->getResult();
        return $result;
    }

    /**
     * @param string $champ
     * @param string $ordre L'ordre de tri des domaines par le paramètre $champ ('ASC' ou 'DESC') (par défaut: 'ASC')
     * @param string $typeValue Le type de valeur à retourner ('frDomainS' pour le nom en français, 'enDomainS' pour le nom en anglais) (par défaut: 'frDomainS')
     * @return array
     */
    public function getDomainesHalAsOptions(string $champ = 'frDomainS', string $ordre = 'ASC', string $typeValue = 'frDomainS') : array
    {
        $result = $this->getDomainesHal($champ, $ordre);
        $options = [];
        $optGroupId = 0;
        foreach ($result as $item) {
            if ($item->getLevelI() === 0) {
                $optGroupId = $item->getId();
                $options[$optGroupId] = ["label" => $typeValue === 'frDomainS' ? $item->getFrDomainS() : $item->getEnDomainS(), "options" => []];
            } else {
                $options[$optGroupId]["options"][$item->getId()] = $typeValue === 'frDomainS' ? $item->getFrDomainS() : $item->getEnDomainS() ;
            }
        }

        $valueOptions = [];
        foreach ($options as $groupId => $group) {
            $valueOptions[$groupId] = [
                "label" => $group["label"],
                "options" => $group["options"]
            ];
        }

        return $valueOptions;
    }

}