<?php

namespace Structure\Service\Etablissement;

use Application\Entity\Db\Utilisateur;
use Application\Service\BaseService;
use Application\SourceCodeStringHelperAwareTrait;
use Doctrine\ORM\Exception\NotSupported;
use Doctrine\ORM\NonUniqueResultException;
use Exception;
use Fichier\Service\Fichier\FichierServiceAwareTrait;
use Structure\Entity\Db\Etablissement;
use Structure\Entity\Db\Repository\EtablissementRepository;
use Structure\Entity\Db\Structure;
use Structure\Entity\Db\TypeStructure;
use Throwable;
use UnicaenApp\Exception\RuntimeException;

class EtablissementService extends BaseService
{
    use SourceCodeStringHelperAwareTrait;
    use FichierServiceAwareTrait;

    /**
     * @return EtablissementRepository
     */
    public function getRepository(): EtablissementRepository
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
    public function fetchEtablissementComue(): ?Etablissement
    {
        $qb = $this->getRepository()->createQueryBuilder('e')
            ->where('e.estComue = true');

        try {
            $comue = $qb->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            throw new RuntimeException("Anomalie: plusieurs établissements COMUE trouvés.");
        }

        return $comue;
    }

    /**
     * Fetch l'éventuel établissement Collège des écoles doctorales (CED).
     *
     * @return Etablissement|null
     */
    public function fetchEtablissementCed(): ?Etablissement
    {
        $qb = $this->getRepository()->createQueryBuilder('e')
            ->where('e.estCed = true');

        try {
            $etab = $qb->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            throw new RuntimeException("Anomalie: plusieurs établissements CED trouvés.");
        }

        return $etab;
    }

    public function create(Etablissement $structureConcrete, Utilisateur $createur): Etablissement
    {
        try {
            /** @var TypeStructure $typeStructure */
            $typeStructure = $this->entityManager->getRepository(TypeStructure::class)->findOneBy(['code' => TypeStructure::CODE_ETABLISSEMENT]);
        } catch (NotSupported $e) {
            throw new RuntimeException("Erreur lors de l'obtention du repository Doctrine", null, $e);
        }

        $sourceCode = $structureConcrete->getSourceCode();
        if ($sourceCode === null) {
            $sourceCode = $this->sourceCodeStringHelper->addDefaultPrefixTo(uniqid());
        }
        $structureConcrete->setSourceCode($sourceCode);
        $structureConcrete->setHistoCreateur($createur);

        $structure = $structureConcrete->getStructure();
        $structure->setTypeStructure($typeStructure);
        $structure->setSourceCode($sourceCode);

        $this->entityManager->beginTransaction();
        try {
            $this->entityManager->persist($structure);
            $this->entityManager->persist($structureConcrete);
            $this->entityManager->flush();
            $this->entityManager->commit();
        } catch (Exception $e) {
            $this->rollback();
            throw new RuntimeException(
                "Erreur lors de l'enregistrement de la structure '$structure' (type : '$typeStructure')", null, $e);
        }

        return $structureConcrete;
    }

    protected function computeSourceCodeForEtablissement(Etablissement $structureConcrete, Utilisateur $createur)
    {
        $sourceCode = $structureConcrete->getSourceCode();
    }

    /**
     * @param Etablissement $etablissement
     * @param Utilisateur   $destructeur
     */
    public function deleteSoftly(Etablissement $etablissement, Utilisateur $destructeur)
    {
        $etablissement->historiser($destructeur);
        $etablissement->getStructure()->historiser($destructeur);

        try {
            $this->entityManager->flush();
        } catch (Throwable $e) {
            throw new RuntimeException("Erreur lors de l'enregistrement de l'Etablissement", null, $e);
        }
    }

    /**
     * @param Etablissement $etablissement
     */
    public function undelete(Etablissement $etablissement)
    {
        $etablissement->dehistoriser();
        $etablissement->getStructure()->dehistoriser();

        try {
            $this->entityManager->flush();
        } catch (Throwable $e) {
            throw new RuntimeException("Erreur lors de l'enregistrement de l'Etablissement", null, $e);
        }
    }

    /**
     * @param Etablissement $etablissement
     * @return Etablissement
     */
    public function update(Etablissement $etablissement)
    {
        try {
            $this->entityManager->flush();
        } catch (Throwable $e) {
            throw new RuntimeException("Erreur lors de l'enregistrement de l'Etablissement", null, $e);
        }

        return $etablissement;
    }


    public function setLogo(Etablissement $etablissement, $cheminLogo)
    {
        $etablissement->getStructure()->setCheminLogo($cheminLogo);
        try {
            $this->entityManager->flush();
        } catch (Throwable $e) {
            throw new RuntimeException("Erreur lors de l'enregistrement de l'Etablissement", null, $e);
        }

        return $etablissement;
    }

    public function deleteLogo(Etablissement $etablissement)
    {
        $etablissement->getStructure()->setCheminLogo(null);
        try {
            $this->entityManager->flush();
        } catch (Throwable $e) {
            throw new RuntimeException("Erreur lors de l'enregistrement de l'Etablissement", null, $e);
        }

        return $etablissement;
    }

    /**
     * Instancie un pseudo-établissement "Tout établissement confondu".
     */
    public function createToutEtablissementConfondu(): Etablissement
    {
        $code = Etablissement::CODE_TOUT_ETABLISSEMENT_CONFONDU;

        $structure = new Structure();
        $structure
            ->setSourceCode($code)
            ->setCode($code)
            ->setLibelle("Tout établissement confondu");

        $etablissement = new Etablissement();
        $etablissement->setSourceCode($code);
        $etablissement->setStructure($structure);

        return $etablissement;
    }

    /**
     * @return array
     */
    public function getEtablissementInscriptionAsOption() : array
    {
        return Etablissement::toValueOptions($this->getRepository()->findAllEtablissementsInscriptions());
    }
}