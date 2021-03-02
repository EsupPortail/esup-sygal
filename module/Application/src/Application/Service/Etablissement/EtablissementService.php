<?php

namespace Application\Service\Etablissement;

use Application\Entity\Db\Etablissement;
use Application\Entity\Db\Repository\EtablissementRepository;
use Application\Entity\Db\Structure;
use Application\Entity\Db\TypeStructure;
use Application\Entity\Db\Utilisateur;
use Application\Service\BaseService;
use Application\SourceCodeStringHelperAwareTrait;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\OptimisticLockException;
use UnicaenApp\Exception\RuntimeException;

class EtablissementService extends BaseService
{
    use SourceCodeStringHelperAwareTrait;

    /**
     * @return EtablissementRepository
     */
    public function getRepository()
    {
        /** @var EtablissementRepository $repo */
        $repo = $this->entityManager->getRepository(Etablissement::class);

        return $repo;
    }

    /**
     * Fetch l'éventuel établissement chapeau représentant une communauté d'établissements.
     *
     * @return Etablissement|null
     */
    public function fetchEtablissementComue()
    {
        $qb = $this->getRepository()->createQueryBuilder('e')
            ->addSelect('s')
            ->join('e.structure', 's')
            ->where('e.estComue = 1');

        try {
            $comue = $qb->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            throw new RuntimeException("Anomalie: plusieurs établissements COMUE trouvés.");
        }

        return $comue;
    }

    /**
     * @param Etablissement $structureConcrete
     * @param Utilisateur   $createur
     * @return Etablissement
     */
    public function create(Etablissement $structureConcrete, Utilisateur $createur)
    {
        /** @var TypeStructure $typeStructure */
        $typeStructure = $this->getEntityManager()->getRepository(TypeStructure::class)->findOneBy(['code' => 'etablissement']);

        $sourceCode = $this->sourceCodeStringHelper->addDefaultPrefixTo(uniqid());
        $structureConcrete->setSourceCode($sourceCode);
        $structureConcrete->setHistoCreateur($createur);

        $structure = $structureConcrete->getStructure();
        $structure->setTypeStructure($typeStructure);
        $structure->setSourceCode($sourceCode);

        $this->entityManager->beginTransaction();

        $this->entityManager->persist($structure);
        $this->entityManager->persist($structureConcrete);
        try {
            $this->entityManager->flush($structure);
            $this->entityManager->flush($structureConcrete);
            $this->entityManager->commit();
        } catch (\Exception $e) {
            $this->rollback();
            throw new RuntimeException("Erreur lors de l'enregistrement de l'établissement '$structure'", null, $e);
        }

        return $structureConcrete;
    }

    /**
     * @param Etablissement $etablissement
     * @param Utilisateur   $destructeur
     */
    public function deleteSoftly(Etablissement $etablissement, Utilisateur $destructeur)
    {
        $etablissement->historiser($destructeur);
        $etablissement->getStructure()->historiser($destructeur);

        $this->flush($etablissement);
    }

    /**
     * @param Etablissement $etablissement
     */
    public function undelete(Etablissement $etablissement)
    {
        $etablissement->dehistoriser();
        $etablissement->getStructure()->dehistoriser();

        $this->flush($etablissement);
    }

    /**
     * @param Etablissement $etablissement
     * @return Etablissement
     */
    public function update(Etablissement $etablissement)
    {
        $this->flush($etablissement);

        return $etablissement;
    }


    public function setLogo(Etablissement $etablissement, $cheminLogo)
    {
        $etablissement->setCheminLogo($cheminLogo);
        $this->flush($etablissement);

        return $etablissement;
    }

    public function deleteLogo(Etablissement $etablissement)
    {
        $etablissement->setCheminLogo(null);
        $this->flush($etablissement);

        return $etablissement;
    }

    /**
     * Instancie un pseudo-établissement "Tout établissement confondu" utile dans les vues.
     *
     * @return Etablissement
     */
    public function createToutEtablissementConfondu()
    {
        $structure = new Structure();
        $structure
            ->setCode(ETABLISSEMENT::CODE_TOUT_ETABLISSEMENT_CONFONDU)
            ->setLibelle("Tout établissement confondu");
        $etablissement = new Etablissement();
        $etablissement->setStructure($structure);

        return $etablissement;
    }

    public function getEtablissementAsOptions()
    {
        $qb = $this->getEntityManager()->getRepository(Etablissement::class)->createQueryBuilder('etablissement')
            ->addSelect('structure')->join('etablissement.structure', 'structure')
            ->orderBy('structure.libelle', 'ASC');
        $etablissements = $qb->getQuery()->getResult();
        $result = [];
        /** @var Etablissement $etablissement */
        foreach ($etablissements as $etablissement) $result[$etablissement->getId()] = $etablissement->getLibelle();
        return $result;
    }

    private function persist(Etablissement $etablissement)
    {
        $this->getEntityManager()->persist($etablissement);
        $this->getEntityManager()->persist($etablissement->getStructure());
    }

    private function flush(Etablissement $etablissement)
    {
        try {
            $this->getEntityManager()->flush($etablissement);
            $this->getEntityManager()->flush($etablissement->getStructure());
        } catch (OptimisticLockException $e) {
            throw new RuntimeException("Erreur lors de l'enregistrement de l'Etablissement", null, $e);
        }
    }
}