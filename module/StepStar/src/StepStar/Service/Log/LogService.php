<?php

namespace StepStar\Service\Log;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\ORMException;
use DoctrineModule\Persistence\ProvidesObjectManager;
use StepStar\Entity\Db\Log;
use These\Service\These\TheseServiceAwareTrait;
use UnicaenApp\Exception\RuntimeException;

/**
 * @property \Doctrine\ORM\EntityManager $objectManager
 */
class LogService
{
    use TheseServiceAwareTrait;
    use ProvidesObjectManager;

    public function getRepository(): EntityRepository
    {
        return $this->objectManager->getRepository(Log::class);
    }

    /**
     * Instancie un nouveau Log, qui devient le Log courant.
     */
    public function newLog(?string $operation = null, ?string $command = null, ?string $tag = null): Log
    {
        $log = new Log();
        if ($operation !== null) {
            $log->setOperation($operation);
        }
        if ($command !== null) {
            $log->setCommand($command);
        }
        if ($tag !== null) {
            $log->setTag($tag);
        }
        $log->setStartedOn();

        return $log;
    }

    /**
     * Instancie un nouveau Log concernant une thèse, qui devient le Log courant.
     */
    public function newLogForThese(int $theseId, ?string $operation = null, ?string $command = null, ?string $tag = null): Log
    {
        $log = $this->newLog($operation, $command, $tag);
        $log->setTheseId($theseId);

        return $log;
    }

    public function findLastLogForOperation(string $operation): ?Log
    {
        $qb = $this->getRepository()->createQueryBuilder('l')
            ->andWhere('l.operation = :operation')->setParameter('operation', $operation)
            ->orderBy('l.id', 'desc')
            ->setMaxResults(1);

        $result = $qb->getQuery()->getResult();

        return $result[0] ?? null;
    }

    public function findLastLogForTheseAndOperation(int $theseId, string $operation): ?Log
    {
        $qb = $this->getRepository()->createQueryBuilder('l')
            ->andWhere('l.these = :theseId')->setParameter('theseId', $theseId)
            ->andWhere('l.operation = :operation')->setParameter('operation', $operation)
            ->orderBy('l.id', 'desc')
            ->setMaxResults(1);

        $result = $qb->getQuery()->getResult();

        return $result[0] ?? null;
    }

    /**
     * Enregistre le Log spécifié en bdd.
     */
    public function saveLog(Log $log): void
    {
        if ($log->getThese() === null) {
            if ($log->getTheseId() !== null) {
                /** @var \These\Entity\Db\These $these */
                $these = $this->theseService->getRepository()->find($log->getTheseId());
                $log->setThese($these);
            }
        }

        $log->setEndedOn();

        try {
            $this->objectManager->persist($log);
            $this->objectManager->flush($log);
        } catch (ORMException $e) {
            throw new RuntimeException("Erreur rencontrée pendant l'enregistrement du log", null, $e);
        }
    }
}