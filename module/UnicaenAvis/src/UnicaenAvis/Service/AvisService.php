<?php

namespace UnicaenAvis\Service;

use Doctrine\DBAL\Exception;
use Doctrine\Persistence\ObjectRepository;
use DoctrineModule\Persistence\ProvidesObjectManager;
use RuntimeException;
use UnicaenAvis\Entity\Db\Avis;
use UnicaenAvis\Entity\Db\AvisType;
use UnicaenAvis\Entity\Db\AvisTypeValeurComplem;
use UnicaenAvis\Entity\Db\AvisTypeValeur;
use UnicaenAvis\Entity\Db\AvisValeur;

class AvisService
{
    use ProvidesObjectManager;

    protected function getAvisTypeRepository(): ObjectRepository
    {
        return $this->objectManager->getRepository(AvisType::class);
    }

    protected function getAvisValeurRepository(): ObjectRepository
    {
        return $this->objectManager->getRepository(AvisValeur::class);
    }

    protected function getAvisTypeValeurRepository(): ObjectRepository
    {
        return $this->objectManager->getRepository(AvisTypeValeur::class);
    }

    protected function getAvisTypeValeurComplemRepository(): ObjectRepository
    {
        return $this->objectManager->getRepository(AvisTypeValeurComplem::class);
    }

    public function findOneAvisTypeByCode(string $code): AvisType
    {
        /** @var \UnicaenAvis\Entity\Db\AvisType $avisType */
        $avisType = $this->getAvisTypeRepository()->findOneBy(['code' => $code]);

        return $avisType;
    }

    public function findOneAvisValeurByCode(string $code): AvisValeur
    {
        /** @var \UnicaenAvis\Entity\Db\AvisValeur $avisValeur */
        $avisValeur = $this->getAvisValeurRepository()->findOneBy(['code' => $code]);

        return $avisValeur;
    }

    public function findOneAvisTypeValeur(AvisType $avisType, AvisValeur $avisValeur): AvisTypeValeur
    {
        /** @var \UnicaenAvis\Entity\Db\AvisTypeValeur $avisTypeValeur */
        $avisTypeValeur = $this->getAvisTypeValeurRepository()->findOneBy([
            'avisType' => $avisType,
            'avisValeur' => $avisValeur,
        ]);

        return $avisTypeValeur;
    }

    /**
     * @param \UnicaenAvis\Entity\Db\Avis $avis
     */
    public function saveAvis(Avis $avis)
    {
        $this->objectManager->beginTransaction();
        try {
            $this->objectManager->persist($avis);
            foreach ($avis->getAvisComplems() as $avisComplem) {
                $this->objectManager->persist($avisComplem);
                $this->objectManager->flush($avisComplem);
            }
            $this->objectManager->flush($avis);
            $this->objectManager->commit();
        } catch (Exception $e) {
            $this->objectManager->rollback();
            throw new RuntimeException("Erreur survenue lors de l'enregistrement de l'avis, rollback!", null, $e);
        }
    }

    /**
     * @param Avis $avis
     */
    public function deleteAvis(Avis $avis)
    {
        $this->objectManager->beginTransaction();
        try {
            $this->objectManager->remove($avis);
            $this->objectManager->flush($avis);
            $this->objectManager->commit();
        } catch (Exception $e) {
            $this->objectManager->rollback();
            throw new RuntimeException("Erreur survenue lors de la suppression de l'avis, rollback!", 0, $e);
        }
    }
}