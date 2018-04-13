<?php

namespace Application\Service\Structure;

use Application\Entity\Db\EcoleDoctorale;
use Application\Entity\Db\Etablissement;
use Application\Entity\Db\SourceInterface;
use Application\Entity\Db\Structure;
use Application\Entity\Db\StructureEntityInterface;
use Application\Entity\Db\StructureSubstit;
use Application\Entity\Db\TypeStructure;
use Application\Entity\Db\UniteRecherche;
use Application\Service\BaseService;
use Application\Service\Source\SourceServiceAwareTrait;
use Doctrine\ORM\EntityRepository;
use UnicaenApp\Exception\RuntimeException;
use Webmozart\Assert\Assert;

/**
 * @author Unicaen
 */
class StructureService extends BaseService
{
    use SourceServiceAwareTrait;

    /**
     * @return EntityRepository
     */
    public function getRepository()
    {
        /** @var EntityRepository $repo */
        $repo = $this->entityManager->getRepository(Structure::class);

        return $repo;
    }

    /**
     * Enregistre en bdd la substitution de plusieurs structures par une autre structure.
     * NB: la structure de subsitition est créée et sa source est SYGAL.
     *
     * @param StructureEntityInterface[] $structuresSources
     * Structures à substituer (Etablissement|EcoleDoctorale|UniteRecherche
     * @param StructureEntityInterface   $structureCibleDataObject
     * Objet contenant les attributs de la structure de substitution à créer
     * @return StructureSubstit[] Entités créées (une par substitution)
     */
    public function createStructureSubstitutions(array $structuresSources, StructureEntityInterface $structureCibleDataObject)
    {
        // todo: à améliorer si besoin de vérifier que toutes les structures à substituer sont de la même classe
        Assert::allIsInstanceOfAny($structuresSources, [
            Etablissement::class,
            EcoleDoctorale::class,
            UniteRecherche::class,
        ]);

        Assert::null($structureCibleDataObject->getSourceCode(), "Le source code doit être null car il est calculé");

        // le source code d'une structure cible est calculé
        $sourceCode = uniqid(Etablissement::CODE_COMUE . Etablissement::ETAB_PREFIX_SEP);

        // la source d'une structure cible est forcément SYGAL
        $sourceSygal = $this->sourceService->fetchSourceSygal();

        // le type de la structure cible dépend du type des données spécifiées (data object)
        $tsCode = null;
        if ($structureCibleDataObject instanceof Etablissement) {
            $tsCode = TypeStructure::CODE_ETABLISSEMENT;
        } elseif ($structureCibleDataObject instanceof EcoleDoctorale) {
            $tsCode = TypeStructure::CODE_ECOLE_DOCTORALE;
        } elseif ($structureCibleDataObject instanceof UniteRecherche) {
            $tsCode = TypeStructure::CODE_UNITE_RECHERCHE;
        }
        $typeStructure = $this->fetchTypeStructure($tsCode);

        $structureCibleDataObject->setSourceCode($sourceCode);

        // instanciation du couple (Etab|ED|UR ; Structure) cible
        $structureCible = Structure::constructFromDataObject($structureCibleDataObject, $typeStructure, $sourceSygal);
        $structureRattachCible = $structureCible->getStructure(); // StructureSubstitution ne référence que des entités de type Structure

        // instanciations des substitutions
        $substitutions = StructureSubstit::fromStructures($structuresSources, $structureRattachCible);

        // enregistrement en bdd
        $this->getEntityManager()->beginTransaction();
        try {
            $this->getEntityManager()->persist($structureCible);
            $this->getEntityManager()->persist($structureRattachCible);
            array_map(function(StructureSubstit $ss) {
                $this->getEntityManager()->persist($ss);
            }, $substitutions);

            $this->getEntityManager()->flush($structureCible);
            $this->getEntityManager()->flush($structureRattachCible);
            $this->getEntityManager()->flush($substitutions);

            $this->getEntityManager()->commit();
        } catch (\Exception $e) {
            $this->getEntityManager()->rollback();
            throw new RuntimeException("Erreur rencontrée lors de l'enregistrement des substitutions", null, $e);
        }

        return $substitutions;
    }

    /**
     * Met à jour en bdd la substitution existante de plusieurs structures par une autre structure.
     *
     * @param StructureEntityInterface[] $structuresSources
     * Structures à substituer (Etablissement|EcoleDoctorale|UniteRecherche
     * @param Structure                  $structureCible Structure de subsitution existante
     */
    public function updateStructureSubstitutions(array $structuresSources, Structure $structureCible)
    {
        Assert::notEmpty($structuresSources, "La liste des structures à substituer ne peut être vide");

        Assert::notNull($structureCible->getId(), "La structure de substitution doit exister en bdd");
        Assert::eq(
            $code = SourceInterface::CODE_SYGAL,
            $structureCible->getSource()->getCode(),
            "La source de la structure de substitution doit être $code");

        // todo: à améliorer si besoin de vérifier que toutes les structures à substituer sont de la même classe
        Assert::allIsInstanceOfAny($structuresSources, [
            Etablissement::class,
            EcoleDoctorale::class,
            UniteRecherche::class,
        ]);

        // recherche des substitutions existantes
        $structureSubstitsExistantes =
            $this->getEntityManager()->getRepository(StructureSubstit::class)->findBy(['toStructure' => $structureCible]);

        // détermination des substitutions à créer et à supprimer
        $structureSubstitsExistantesParStructure = [];
        $structuresSourcesExistantes = [];
        /** @var StructureSubstit[] $structureSubstitsExistantes */
        foreach ($structureSubstitsExistantes as $ss) {
            $structureSource = $ss->getFromStructure();
            $structuresSourcesExistantes[] = $structureSource;
            $structureSubstitsExistantesParStructure[$structureSource->getId()] = $ss;
        }
        /** @var Structure[] $structuresSourcesToAdd */
        $structuresSourcesToAdd = array_diff($structuresSources, $structuresSourcesExistantes);
        /** @var Structure[] $structuresSourcesToRem */
        $structuresSourcesToRem = array_diff($structuresSourcesExistantes, $structuresSources);

        // enregistrement en bdd
        $structureSubstits = [];
        $this->getEntityManager()->beginTransaction();
        try {
            foreach ($structuresSourcesToAdd as $structureSource) {
                $ss = StructureSubstit::fromStructures([$structureSource], $structureCible)[0];
                $this->getEntityManager()->persist($ss);
                $structureSubstits[] = $ss;
            }
            foreach ($structuresSourcesToRem as $structureSource) {
                $ss = $structureSubstitsExistantesParStructure[$structureSource->getId()];
                $this->getEntityManager()->remove($ss);
                $structureSubstits[] = $ss;
            }
            $this->getEntityManager()->flush($structureSubstits);
            $this->getEntityManager()->commit();
        } catch(\Exception $e) {
            $this->getEntityManager()->rollback();
            throw new RuntimeException("Erreur rencontrée lors de l'enregistrement des substitutions", null, $e);
        }
    }

    /**
     * Suppression en bdd des substitutions pointant vers la structure spécifiée.
     *
     * @param Structure $structureCible Structure de substitution (cible)
     * @return StructureSubstit[] Substitutions supprimées
     */
    public function deleteStructureSubstitutions(Structure $structureCible)
    {
        Assert::notNull($structureCible->getId(), "La structure de substitution doit exister en bdd");
        Assert::eq(
            $code = SourceInterface::CODE_SYGAL,
            $structureCible->getSource()->getCode(),
            "La source de la structure de substitution doit être $code");

        // recherche des substitutions existantes
        $structureSubstits =
            $this->getEntityManager()->getRepository(StructureSubstit::class)->findBy(['toStructure' => $structureCible]);

        Assert::notEmpty($structureSubstits, "Aucune substitution trouvée pour la structure cible '$structureCible'");

        // enregistrement en bdd
        $this->getEntityManager()->beginTransaction();
        try {
            foreach ($structureSubstits as $ss) {
                $this->getEntityManager()->remove($ss);
            }
            $this->getEntityManager()->flush($structureSubstits);
            $this->getEntityManager()->commit();
        } catch(\Exception $e) {
            $this->getEntityManager()->rollback();
            throw new RuntimeException("Erreur rencontrée lors de la supression des substitutions", null, $e);
        }

        return $structureSubstits;
    }

    /**
     * Fetch un type de structure à partir de son code.
     *
     * @param string $code Ex: TypeStructure::CODE_ECOLE_DOCTORALE
     * @return TypeStructure
     */
    public function fetchTypeStructure($code)
    {
        /** @var TypeStructure $typeStructure */
        $typeStructure = $this->getEntityManager()->getRepository(TypeStructure::class)->findOneBy(['code' => $code]);

        return $typeStructure;
    }
}