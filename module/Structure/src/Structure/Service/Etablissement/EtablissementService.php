<?php

namespace Structure\Service\Etablissement;

use Application\Entity\Db\Utilisateur;
use Application\Service\BaseService;
use Application\SourceCodeStringHelperAwareTrait;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\ORMException;
use Fichier\Service\Fichier\FichierServiceAwareTrait;
use Structure\Entity\Db\Etablissement;
use Structure\Entity\Db\Repository\EtablissementRepository;
use Structure\Entity\Db\Structure;
use Structure\Entity\Db\TypeStructure;
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

        $this->persist($structureConcrete);
        $this->flush($structureConcrete);

        $this->entityManager->commit();

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
        $etablissement->getStructure()->setCheminLogo($cheminLogo);
        $this->flush($etablissement);

        return $etablissement;
    }

    public function deleteLogo(Etablissement $etablissement)
    {
        $etablissement->getStructure()->setCheminLogo(null);
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

    public function getEtablissementsInscriptionsAsOptions() : array
    {
        $etablissements = $this->getRepository()->findAllEtablissementsInscriptions(true);
        $result = [];
        foreach ($etablissements as $etablissement) $result[$etablissement->getId()] = $etablissement->getStructure()->getLibelle();
        return $result;
    }

    private function persist(Etablissement $etablissement)
    {
        try {
            $this->getEntityManager()->persist($etablissement);
            $this->getEntityManager()->persist($etablissement->getStructure());
        } catch(ORMException $e) {
            throw new RuntimeException("Un problème est survenu lors de lors de l'enregistrement de l'Etablissement [".$etablissement->getId()."]",0,$e);
        }
    }

    private function flush(Etablissement $etablissement)
    {
        try {
            $this->getEntityManager()->flush($etablissement);
            $this->getEntityManager()->flush($etablissement->getStructure());
        } catch (ORMException $e) {
            throw new RuntimeException("Erreur lors de l'enregistrement de l'Etablissement", null, $e);
        }
    }

    /**
     * @return array
     */
    public function getEtablissementInscriptionAsOption() : array
    {
        $etablissements = $this->getRepository()->findAllEtablissementsInscriptions();
        $array = [];
        foreach ($etablissements as $etablissement) {
            $array[$etablissement->getId()] = $etablissement->getStructure()->getLibelle();
        }
        return $array;
    }

    //todo faire les filtrage et considerer que les UR internes
    public function getEtablissementsAsOptions() : array
    {
        $etablissements = $this->getRepository()->findAll();

        $options = [];
        foreach ($etablissements as $etablissement) {
            $options[$etablissement->getId()] = $etablissement->getStructure()->getLibelle() . " " ."<span class='badge'>".$etablissement->getStructure()->getSigle()."</span>";
        }
        return $options;
    }
}