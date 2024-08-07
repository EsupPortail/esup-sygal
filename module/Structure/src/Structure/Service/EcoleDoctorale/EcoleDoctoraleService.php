<?php

namespace Structure\Service\EcoleDoctorale;

use Application\Entity\Db\Utilisateur;
use Application\Service\BaseService;
use Application\SourceCodeStringHelperAwareTrait;
use Doctrine\ORM\Exception\NotSupported;
use Doctrine\ORM\ORMException;
use Exception;
use Laminas\Mvc\Controller\AbstractActionController;
use Structure\Entity\Db\EcoleDoctorale;
use Structure\Entity\Db\Repository\EcoleDoctoraleRepository;
use Structure\Entity\Db\Structure;
use Structure\Entity\Db\TypeStructure;
use UnicaenApp\Exception\RuntimeException;

/**
 * @method EcoleDoctorale|null findOneBy(array $criteria, array $orderBy = null)
 */
class EcoleDoctoraleService extends BaseService
{
    use SourceCodeStringHelperAwareTrait;

    /**
     * @return EcoleDoctoraleRepository
     */
    public function getRepository(): EcoleDoctoraleRepository
    {
        /** @var EcoleDoctoraleRepository $repo */
        $repo = $this->entityManager->getRepository(EcoleDoctorale::class);

        return $repo;
    }

    /**
     * Historise une ED.
     *
     * @param EcoleDoctorale $ecole
     * @param Utilisateur    $destructeur
     */
    public function deleteSoftly(EcoleDoctorale $ecole, Utilisateur $destructeur)
    {
        $ecole->historiser($destructeur);
        $ecole->getStructure()->historiser($destructeur);

        $this->flush($ecole);
    }

    public function undelete(EcoleDoctorale $ecole)
    {
        $ecole->dehistoriser();
        $ecole->getStructure()->dehistoriser();

        $this->flush($ecole);
    }

    public function create(EcoleDoctorale $structureConcrete, Utilisateur $createur): EcoleDoctorale
    {
        try {
            /** @var TypeStructure $typeStructure */
            $typeStructure = $this->entityManager->getRepository(TypeStructure::class)->findOneBy(['code' => TypeStructure::CODE_ECOLE_DOCTORALE]);
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

    public function update(EcoleDoctorale $ecole)
    {
        $this->flush($ecole);

        return $ecole;
    }

    public function setLogo(EcoleDoctorale $ecole, $cheminLogo)
    {
        $ecole->getStructure()->setCheminLogo($cheminLogo);
        $this->flush($ecole);

        return $ecole;
    }

    public function deleteLogo(EcoleDoctorale $ecole)
    {
        $ecole->getStructure()->setCheminLogo(null);
        $this->flush($ecole);

        return $ecole;
    }

    public function getOffre(): array
    {
        $ecoles = $this->getRepository()->createQueryBuilder('ecole')
            ->andWhere('ecole.histoDestruction IS NULL')
            ->andWhere('structure.estFermee = false')
            ->andWhere('ecole.theme IS NOT NULL')
            ->orderBy('ecole.theme', 'asc')
        ;

        /** @var EcoleDoctorale[] $result */
        $result = $ecoles->getQuery()->getResult();
        $array = [];
        foreach ($result as $item) {
            $array[$item->getTheme()] = $item->getOffreThese();
        }
        return $array;
    }

    /**
     * Instancie une pseudo-école doctorale "Toute école doctorale confondue" utile dans les vues.
     *use Structure\Entity\Db\Etablissement;

     * @return EcoleDoctorale
     */
    public function createTouteEcoleDoctoraleConfondue()
    {
        $structure = new Structure();
        $structure
            ->setCode(EcoleDoctorale::CODE_TOUTE_ECOLE_DOCTORALE_CONFONDUE)
            ->setLibelle("Toute école doctorale confondue");
        $ed = new EcoleDoctorale();
        $ed->setStructure($structure);

        return $ed;
    }

    private function flush(EcoleDoctorale $ecole)
    {
        try {
            $this->getEntityManager()->flush($ecole);
            $this->getEntityManager()->flush($ecole->getStructure());
        } catch (ORMException $e) {
            throw new RuntimeException("Erreur lors de l'enregistrement de l'ED", null, $e);
        }
    }

    /**
     * @param AbstractActionController $controller
     * @param string $param
     * @return EcoleDoctorale|null
     */
    public function getRequestedEcoleDoctorale(AbstractActionController $controller, string $param='ecole') : ?EcoleDoctorale
    {
        $id = $controller->params()->fromRoute($param);
        /** @var EcoleDoctorale|null $ecole */
        $ecole = $this->getRepository()->find($id);
        return $ecole;
    }
}