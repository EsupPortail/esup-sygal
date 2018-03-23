<?php

namespace Application\Service;

use Application\Entity\Db\MailConfirmation;
use Doctrine\ORM\QueryBuilder;
use UnicaenApp\Controller\Plugin\Mail;
use UnicaenApp\Service\EntityManagerAwareTrait;

class MailConfirmationService {
    use EntityManagerAwareTrait;


    public function getDemandeById($id)
    {
        $mailConfirmation = $this->getEntityManager()->getRepository(MailConfirmation::class)->findOneBy(["id"=>$id]);
        return $mailConfirmation;
    }

    /**
     * @return MailConfirmation[] $confirmations
     */
    public function getDemandeEnCours()
    {
        /** @var QueryBuilder $qb */
        $qb = $this->entityManager->getRepository(MailConfirmation::class)->createQueryBuilder("mc")
            ->andWhere("mc.etat = :etat")->setParameter("etat", 'E');
        $confirmations = $qb->getQuery()->execute();

        return $confirmations;
    }

    /**
     * @return MailConfirmation[] $confirmations
     */
    public function getDemandeConfirmees()
    {
        /** @var QueryBuilder $qb */
        $qb = $this->entityManager->getRepository(MailConfirmation::class)->createQueryBuilder("mc")
            ->andWhere("mc.etat = :etat")->setParameter("etat", 'C');
        $confirmations = $qb->getQuery()->execute();

        return $confirmations;
    }

    /**
     * @param int $id
     * @return MailConfirmation
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function swapEtat($id)
    {
        /** @var MailConfirmation $mailConfirmation */
        $mailConfirmation = $this->getDemandeById($id);
        if ($mailConfirmation !== null) {
            if ($mailConfirmation->getEtat() === MailConfirmation::ENVOYER) {
                $mailConfirmation->setEtat(MailConfirmation::CONFIRMER);
            } else {
                $mailConfirmation->setEtat(MailConfirmation::ENVOYER);
            }
            $this->getEntityManager()->flush($mailConfirmation);
        }
        return $mailConfirmation;
    }

    /**
     * @param $id
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function remove($id)
    {
        /** @var MailConfirmation $mailConfirmation */
        $mailConfirmation = $this->getDemandeById($id);
        if ($mailConfirmation !== null) {
            $this->getEntityManager()->remove($mailConfirmation);
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @param $id
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function confirmEmail($id)
    {
         /** @var MailConfirmation $mailConfirmation */
        $mailConfirmation = $this->getDemandeById($id);
        $mailConfirmation->setEtat(MailConfirmation::CONFIRMER);
        $this->getEntityManager()->flush($mailConfirmation);

    }

    public function generateCode($id) {

        $code ="";

        //do {
            for ($groupe = 0; $groupe < 4; $groupe++) {
                if ($groupe !== 0) $code .= "-";
                for ($lettre = 0; $lettre < 4; $lettre++) {
                    $l = "";
                    $casse = rand(1, 2);
                    if ($casse === 1) {
                        $l = chr(rand(97, 122));
                    } else {
                        $l = chr(rand(65, 90));
                    }
                    $code .= $l;
                }

            }
        //} while (false);
        $mailConfirmation = $this->getDemandeById($id);
        $mailConfirmation->setCode($code);
        $this->getEntityManager()->flush($mailConfirmation);

        return $code;
    }

    public function save($mailConfirmation)
    {
        $this->entityManager->persist($mailConfirmation);
        $this->entityManager->flush($mailConfirmation);
        return $mailConfirmation->getId();
    }
}
