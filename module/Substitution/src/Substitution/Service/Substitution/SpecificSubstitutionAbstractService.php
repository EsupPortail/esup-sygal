<?php

namespace Substitution\Service\Substitution;

use Application\Service\BaseService;
use BadMethodCallException;
use Closure;
use Doctrine\DBAL\Result;
use Exception;
use Ramsey\Uuid\Uuid;
use Substitution\Entity\Db\SubstitutionAwareEntityInterface;
use Webmozart\Assert\Assert;

abstract class SpecificSubstitutionAbstractService implements SpecificSubstitutionServiceInterface
{
    protected BaseService $entityService;
    protected string $fauxDoublonNpdPrefix = 'faux_doublon_';

    public function setEntityService(BaseService $entityService): void
    {
        $this->entityService = $entityService;
    }

    public function countAllSubstitutions(): int
    {
        return $this->entityService->getEntityManager()->getConnection()->executeQuery(
            'select count(*) nb from (' . $this->generateSqlToFindSubstitutions() . ') tmp'
        )->fetchOne();
    }

    public function findAllSubstitutions(?int $limit = null): Result
    {
        return $this->entityService->getEntityManager()->getConnection()->executeQuery(
            $this->generateSqlToFindSubstitutions() . ($limit !== null ? " limit $limit" : '')
        );
    }

    public function findOneSubstitution(int $substituantId): Result
    {
        return $this->entityService->getEntityManager()->getConnection()->executeQuery(
            $this->generateSqlToFindSubstitutions($substituantId)
        );
    }

    public function findOneEntityById(int $id): SubstitutionAwareEntityInterface
    {
        $entity = $this->entityService->getRepository()->find($id);
        Assert::notNull($entity, sprintf("Enregistrement %s introuvable avec l'id %d",
            $this->entityService->getRepository()->getClassName(), $id));

        return $entity;
    }

    public function addSubstitueToSubstitution(int $substituableId, string $npd): void
    {
        // NB : mettre un NPD forcé égal à celui de la substitution existante provoque l'ajout automatique
        // de l'enregistrement à cette substitution.
        $entity = $this->findOneEntityById($substituableId);
        $entity->setNpdForce($npd);
        $this->saveEntity($entity);
    }

    public function removeSubstitueFromSubstitution(int $substitueId, string $npd): void
    {
        $entity = $this->findOneEntityById($substitueId);
        $estSubstitueManuellement = $entity->getNpdForce() && $entity->getNpdForce() === $npd;

        if ($estSubstitueManuellement) {
            // Cas de retrait d'un substitué ayant été ajouté manuellement :
            // supprimer le NPD forcé provoquera son retrait automatique.
            $npdForce = null;
        } elseif (!$entity->getNpdForce()) {
            // Cas de retrait d'un substitué ayant été ajouté automatiquement :
            // mettre un NPD forcé différent de celui de la substitution provoquera son retrait automatique.
            try {
                $npdForce = Uuid::uuid1();
            } catch (Exception $e) {
                $npdForce = uniqid('', true);
            }
            $npdForce = $this->fauxDoublonNpdPrefix . $npdForce;
        } else {
            throw new BadMethodCallException("Cas de figure inattendu !");
        }

        $entity->setNpdForce($npdForce);
        $this->saveEntity($entity);
    }

    /**
     * @return array[]
     */
    public function findSubstituablesByText(string $text, string $npd): array
    {
        $rows = $this->findEntitiesByText($text, $npd);
//        $rows = array_filter($rows, $this->getSubstituablesFilterClosure($npd));

        return $this->formatSubstituables($rows);
    }

    /**
     * Retourne de quoi filtrer la liste des enregistrements potentiellement substituables.
     */
    protected function getSubstituablesFilterClosure(string $npdSubstitution): Closure
    {
        return function(array $row) use ($npdSubstitution) {
//            $importable = array_key_exists('source_importable', $row) ? $row['source_importable'] : $row['source']['importable'];
            $npdForce = array_key_exists('npd_force', $row) ? $row['npd_force'] : $row['npdForce'];
            $estHistorise = (bool) (array_key_exists('histo_destruction', $row) ? $row['histo_destruction'] : $row['histoDestruction']);

//            // Si l'enregistrement n'est pas issu d'une source importable : il n'est pas substituable.
//            if (!$importable) {
//                return false;
//            }

            // S'il n'est pas historisé et n'a pas de NPD forcé : il est substituable.
            if (!$estHistorise && !$npdForce) {
                return true;
            }

            // S'il est historisé, s'il a un NPD forcé et qu'il diffère du NPD de la substitution,
            // c'est un enregistrement déclaré faux-doublon manuellement : il est substituable.
            if ($npdForce && $npdForce <> $npdSubstitution) {
                return true;
            }

            return false;
        };
    }

    /**
     * Formatte les résultats de la recherche de substituables pour l'élément {@see \UnicaenApp\Form\Element\SearchAndSelect}.
     */
    abstract protected function formatSubstituables(array $substituables): array;

    /**
     * Génère le SQL permettant de sélectionner toutes les substituions,
     * ou alors celle spécifiée par l'id du substituant.
     */
    abstract protected function generateSqlToFindSubstitutions(?int $substituantId = null): string;

    /**
     * Recherche d'enregistrements substituables selon un motif texte.
     */
    abstract protected function findEntitiesByText(string $text, string $npd, int $limit = 0): array;
}