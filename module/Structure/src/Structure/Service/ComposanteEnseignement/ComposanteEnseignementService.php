<?php

namespace Structure\Service\ComposanteEnseignement;

use Application\Entity\Db\Utilisateur;
use Application\QueryBuilder\DefaultQueryBuilder;
use Application\Service\BaseService;
use Application\SourceCodeStringHelperAwareTrait;
use Doctrine\ORM\Exception\NotSupported;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\ORMException;
use Exception;
use Structure\Entity\Db\Etablissement;
use Structure\Entity\Db\EtablissementRattachement;
use Structure\Entity\Db\Repository\ComposanteEnseignementRepository;
use Structure\Entity\Db\TypeStructure;
use Structure\Entity\Db\ComposanteEnseignement;
use UnicaenApp\Exception\RuntimeException;

/**
 * @method ComposanteEnseignement|null findOneBy(array $criteria, array $orderBy = null)
 */
class ComposanteEnseignementService extends BaseService
{
    use SourceCodeStringHelperAwareTrait;

    /**
     * @return ComposanteEnseignementRepository
     */
    public function getRepository(): ComposanteEnseignementRepository
    {
        /** @var ComposanteEnseignementRepository $repo */
        $repo = $this->entityManager->getRepository(ComposanteEnseignement::class);

        return $repo;
    }

    /**
     * Historise une ED.
     *
     * @param ComposanteEnseignement $ce
     * @param Utilisateur    $destructeur
     */
    public function deleteSoftly(ComposanteEnseignement $ce, Utilisateur $destructeur)
    {
        $ce->historiser($destructeur);
        $ce->getStructure()->historiser($destructeur);

        $this->flush($ce);
    }

    public function undelete(ComposanteEnseignement $ce)
    {
        $ce->dehistoriser();
        $ce->getStructure()->dehistoriser();

        $this->flush($ce);
    }

    public function create(ComposanteEnseignement $structureConcrete, Utilisateur $createur): ComposanteEnseignement
    {
        try {
            /** @var TypeStructure $typeStructure */
            $typeStructure = $this->entityManager->getRepository(TypeStructure::class)->findOneBy(['code' => TypeStructure::CODE_COMPOSANTE_ENSEIGNEMENT]);
        } catch (NotSupported $e) {
            throw new RuntimeException("Erreur lors de l'obtention du repository Doctrine", null, $e);
        }

        $sourceCode = $this->sourceCodeStringHelper->addDefaultPrefixTo(uniqid());
        $structureConcrete->setSourceCode($sourceCode);
        $structureConcrete->setHistoCreateur($createur);

        $structure = $structureConcrete->getStructure();
        $structure->setTypeStructure($typeStructure);
        $structure->setSourceCode($sourceCode);

        $this->entityManager->beginTransaction();
        try {
            $this->entityManager->persist($structure);
            $this->entityManager->persist($structureConcrete);
            $this->entityManager->flush($structure);
            $this->entityManager->flush($structureConcrete);
            $this->entityManager->commit();
        } catch (Exception $e) {
            $this->rollback();
            throw new RuntimeException(
                "Erreur lors de l'enregistrement de la structure '$structure' (type : '$typeStructure')", null, $e);
        }

        return $structureConcrete;
    }

    public function update(ComposanteEnseignement $ce)
    {
        $this->flush($ce);

        return $ce;
    }

    public function setLogo(ComposanteEnseignement $composanteEnseignement, $cheminLogo)
    {
        $composanteEnseignement->getStructure()->setCheminLogo($cheminLogo);
        $this->flush($composanteEnseignement);

        return $composanteEnseignement;
    }

    public function deleteLogo(ComposanteEnseignement $composanteEnseignement)
    {
        $composanteEnseignement->getStructure()->setCheminLogo(null);
        $this->flush($composanteEnseignement);

        return $composanteEnseignement;
    }

    private function flush(ComposanteEnseignement $ce)
    {
        try {
            $this->getEntityManager()->flush($ce);
            $this->getEntityManager()->flush($ce->getStructure());
        } catch (ORMException $e) {
            throw new RuntimeException("Erreur lors de l'enregistrement de l'UR", null, $e);
        }
    }

    //todo faire les filtrage et considerer que les UR internes
    public function getUnitesRecherchesAsOptions() : array
    {
        $composantes = $this->getRepository()->findAll();

        $options = [];
        foreach ($composantes as $composante) {
            $options[$composante->getId()] = $composante->getStructure()->getLibelle() . " " ."<span class='badge'>".$composante->getStructure()->getSigle()."</span>";
        }
        return $options;
    }
}