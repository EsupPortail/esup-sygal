<?php

namespace Application\Service\Individu;

use Application\Entity\Db\Etablissement;
use Application\Entity\Db\Individu;
use Application\Entity\Db\Repository\IndividuRepository;
use Application\Entity\Db\Utilisateur;
use Application\Service\BaseService;
use Application\SourceCodeStringHelperAwareTrait;
use Doctrine\ORM\OptimisticLockException;
use Laminas\Mvc\Controller\AbstractActionController;
use UnicaenApp\Exception\RuntimeException;
use UnicaenLdap\Entity\People;

class IndividuService extends BaseService
{
    use SourceCodeStringHelperAwareTrait;

    /**
     * @return IndividuRepository
     */
    public function getRepository()
    {
        /** @var IndividuRepository $repo */
        $repo = $this->entityManager->getRepository(Individu::class);

        $repo->setSourceCodeStringHelper($this->sourceCodeStringHelper);

        return $repo;
    }

    /**
     * @param People        $people
     * @param Etablissement $etablissement
     * @return Individu
     * @deprecated À supprimer car non utilisée
     */
    public function createIndividuFromPeopleAndEtab(People $people, Etablissement $etablissement)
    {
        $sns = (array)$people->get('sn');
        $usuel = array_pop($sns);
        $patro = array_pop($sns);
        if ($patro === null) $patro = $usuel;

        $entity = new Individu();
        $entity->setNomUsuel($usuel);
        $entity->setNomPatronymique($patro);
        $entity->setPrenom($people->get('givenName'));
        $entity->setCivilite($people->get('supannCivilite'));
        $entity->setEmail($people->get('mail'));

        $sourceCode = $this->sourceCodeStringHelper->addEtablissementPrefixTo($people->get('supannEmpId'), $etablissement);
        $entity->setSourceCode($sourceCode);

        $this->getEntityManager()->persist($entity);
        try {
            $this->getEntityManager()->flush($entity);
        } catch (OptimisticLockException $e) {
            throw new RuntimeException("Impossible d'enregistrer l'Individu", null, $e);
        }

        return $entity;
    }

    /**
     * Met à jour le SOURCE_CODE d'un individu à partir de l'établissement spécifié.
     *
     * @param Individu      $entity
     * @param Etablissement $etablissement
     * @param Utilisateur   $modificateur
     */
    public function updateIndividuSourceCodeFromEtab(Individu $entity,
                                                     Etablissement $etablissement,
                                                     Utilisateur $modificateur)
    {
        $sourceCode = $this->sourceCodeStringHelper->addEtablissementPrefixTo($entity->getSupannId(), $etablissement);

        $entity->setSourceCode($sourceCode);
        $entity->setHistoModificateur($modificateur);

        try {
            $this->getEntityManager()->flush($entity);
        } catch (OptimisticLockException $e) {
            throw new RuntimeException("Impossible d'enregistrer l'Individu", null, $e);
        }
    }

    public function existIndividuUtilisateurByEmail($email) {
        $exist_individu = $this->getEntityManager()->getRepository(Individu::class)->findOneBy(["email" => $email]);
        $exist_utilisateur = $this->getEntityManager()->getRepository(Utilisateur::class)->findOneBy(["email" => $email]);

        return ($exist_individu !== null || $exist_utilisateur !== null);
    }

    /**
     * @param AbstractActionController $controller
     * @param string $param
     * @return Individu
     */
    public function getRequestedIndividu($controller, $param = 'individu')
    {
        $id = $controller->params()->fromRoute($param);
        /** @var Individu $individu */
        if ($id !== null) {
            $individu = $this->getRepository()->find($id);
            return $individu;
        }
        return null;

    }
}