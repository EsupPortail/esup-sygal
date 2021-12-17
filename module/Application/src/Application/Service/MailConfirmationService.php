<?php

namespace Application\Service;

use Application\Entity\Db\Individu;
use Application\Entity\Db\MailConfirmation;
use Ramsey\Uuid\Uuid;
use UnicaenApp\Service\EntityManagerAwareTrait;

class MailConfirmationService
{
    use EntityManagerAwareTrait;

    public function fetchMailConfirmationById($id): ?MailConfirmation
    {
        /** @var MailConfirmation $mailConfirmation */
        $mailConfirmation = $this->getEntityManager()->getRepository(MailConfirmation::class)
            ->findOneBy(["id" => $id]);

        return $mailConfirmation;
    }

    /**
     * Retourne la demande la plus récente de la part de l'individu spécifié.
     *
     * @param $individu
     * @return null|MailConfirmation
     */
    public function fetchMailConfirmationsForIndividu($individu): ?MailConfirmation
    {
        /** @var MailConfirmation[] $mailConfirmations */
        $mailConfirmations = $this->getEntityManager()->getRepository(MailConfirmation::class)
            ->findBy(["individu" => $individu], ['id' => 'desc']);

        return $mailConfirmations ? current($mailConfirmations) : null;
    }

    /**
     * @return MailConfirmation[]
     */
    public function fetchMailConfirmationsEnvoyes(): array
    {
        $qb = $this->entityManager->getRepository(MailConfirmation::class)->createQueryBuilder("mc")
            ->andWhere("mc.etat = :etat")->setParameter("etat", 'E');

        return $qb->getQuery()->execute();
    }

    /**
     * @return MailConfirmation[]
     */
    public function fetchMailConfirmationConfirmes(): array
    {
        $qb = $this->entityManager->getRepository(MailConfirmation::class)->createQueryBuilder("mc")
            ->andWhere("mc.etat = :etat")->setParameter("etat", 'C');

        return $qb->getQuery()->execute();
    }

    /**
     * @param int $id
     * @return MailConfirmation
     * @throws \Doctrine\ORM\OptimisticLockException|\Doctrine\ORM\ORMException
     */
    public function swapEtat($id): MailConfirmation
    {
        $mailConfirmation = $this->fetchMailConfirmationById($id);

        if ($mailConfirmation !== null) {
            if ($mailConfirmation->getEtat() === MailConfirmation::ENVOYE) {
                $mailConfirmation->setEtat(MailConfirmation::CONFIRME);
            } else {
                $mailConfirmation->setEtat(MailConfirmation::ENVOYE);
            }
            $this->getEntityManager()->flush($mailConfirmation);
        }

        return $mailConfirmation;
    }

    /**
     * @param $id
     * @throws \Doctrine\ORM\OptimisticLockException|\Doctrine\ORM\ORMException
     */
    public function remove($id)
    {
        $mailConfirmation = $this->fetchMailConfirmationById($id);

        if ($mailConfirmation !== null) {
            $this->getEntityManager()->remove($mailConfirmation);
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Supprime toutes les demandes en cours.
     *
     * @param \Application\Entity\Db\Individu $individu
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function purgeForIndividu(Individu $individu)
    {
        $qb = $this->entityManager->getRepository(MailConfirmation::class)->createQueryBuilder('mc')
            ->delete(null, 'mc')
            ->where('mc.individu = :individu')->setParameter('individu', $individu)
            ->andWhere('mc.etat = :etat')->setParameter('etat', MailConfirmation::ENVOYE);

        $qb->getQuery()->execute();

        $this->entityManager->flush();
    }

    /**
     * @param \Application\Entity\Db\MailConfirmation $mailConfirmation
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function confirmEmail(MailConfirmation $mailConfirmation)
    {
        $mailConfirmation->setEtat(MailConfirmation::CONFIRME);

        $this->getEntityManager()->flush($mailConfirmation);
    }

    public function generateCode($id): string
    {
        $code = Uuid::uuid4()->toString();

        $mailConfirmation = $this->fetchMailConfirmationById($id);
        $mailConfirmation->setCode($code);
        $this->getEntityManager()->flush($mailConfirmation);

        return $code;
    }

    public function save(MailConfirmation $mailConfirmation)
    {
        $this->entityManager->persist($mailConfirmation);
        $this->entityManager->flush($mailConfirmation);

        return $mailConfirmation->getId();
    }

    public function delete(MailConfirmation $mailConfirmation)
    {
        $this->entityManager->remove($mailConfirmation);
        $this->entityManager->flush($mailConfirmation);
    }
}
