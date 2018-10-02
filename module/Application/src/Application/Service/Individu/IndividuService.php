<?php

namespace Application\Service\Individu;

use Application\Entity\Db\Acteur;
use Application\Entity\Db\Doctorant;
use Application\Entity\Db\Etablissement;
use Application\Entity\Db\Individu;
use Application\Entity\Db\IndividuRole;
use Application\Entity\Db\Repository\IndividuRepository;
use Application\Entity\Db\Role;
use Application\Entity\Db\SourceInterface;
use Application\Entity\Db\These;
use Application\Entity\Db\Utilisateur;
use Application\Entity\UserWrapper;
use Application\Service\BaseService;
use Doctrine\ORM\OptimisticLockException;
use UnicaenApp\Exception\RuntimeException;
use UnicaenImport\Entity\Db\Source;
use UnicaenLdap\Entity\People;

class IndividuService extends BaseService
{
    /**
     * @return IndividuRepository
     */
    public function getRepository()
    {
        /** @var IndividuRepository $repo */
        $repo = $this->entityManager->getRepository(Individu::class);

        return $repo;
    }

    /**
     * @param People        $people
     * @param Etablissement $etablissement
     * @return Individu
     */
    public function createFromPeopleAndEtab(People $people, Etablissement $etablissement)
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

        $entity->setSourceCode($etablissement->prependPrefixTo($people->get('supannEmpId')));

        $this->getEntityManager()->persist($entity);
        try {
            $this->getEntityManager()->flush($entity);
        } catch (OptimisticLockException $e) {
            throw new RuntimeException("Impossible d'enregistrer l'Individu", null, $e);
        }

        return $entity;
    }

    /**
     * @param UserWrapper   $userWrapper
     * @param Etablissement $etablissement
     * @param Utilisateur   $utilisateur   Auteur éventuel de la création
     * @return Individu
     */
    public function createFromUserWrapperAndEtab(UserWrapper $userWrapper,
                                                 Etablissement $etablissement,
                                                 Utilisateur $utilisateur = null)
    {
        $sourceCode = $etablissement->prependPrefixTo($userWrapper->getSupannId());

        $entity = new Individu();
        $entity->setNomUsuel($userWrapper->getNom());
        $entity->setNomPatronymique($userWrapper->getNom());
        $entity->setPrenom($userWrapper->getPrenom());
        $entity->setCivilite($userWrapper->getCivilite());
        $entity->setEmail($userWrapper->getEmail());
        $entity->setSourceCode($sourceCode);
        $entity->setHistoCreateur($utilisateur);

        $this->getEntityManager()->persist($entity);
        try {
            $this->getEntityManager()->flush($entity);
        } catch (OptimisticLockException $e) {
            throw new RuntimeException("Impossible d'enregistrer l'Individu", null, $e);
        }

        return $entity;
    }

    /**
     * @param Individu $individu
     * @param Utilisateur $utilisateur
     * @throws OptimisticLockException
     */
    public function createFromForm(Individu $individu, Utilisateur $utilisateur)
    {
        $source = $this->getEntityManager()->getRepository(Source::class)->findOneBy(["code" => SourceInterface::CODE_SYGAL]);
        $user = $this->getEntityManager()->getRepository(Utilisateur::class)->findOneBy(["username" => 'sygal-app']);
        $individu->setSource($source); //COMUE::SyGAL
        $individu->setHistoCreateur($user); //sygal-app
        $individu->setHistoModificateur($user); //sygal-app

        $this->getEntityManager()->persist($individu);
        $this->getEntityManager()->flush($individu);
        $this->getEntityManager()->persist($utilisateur);
        $this->getEntityManager()->flush($utilisateur);

        $individu->setSourceCode("COMUE::" . $individu->getId());
        $this->getEntityManager()->flush($individu);
    }

    public function existIndividuUtilisateurByEmail($email) {
        $exist_individu = $this->getEntityManager()->getRepository(Individu::class)->findOneBy(["email" => $email]);
        $exist_utilisateur = $this->getEntityManager()->getRepository(Utilisateur::class)->findOneBy(["email" => $email]);

        return ($exist_individu !== null || $exist_utilisateur !== null);
    }

    public function getActeurSansMail()
    {
        $qb = $this->getEntityManager()->getRepository(Acteur::class)->createQueryBuilder("acteur")
            ->addSelect("these")
            ->addSelect("individu")
            ->addSelect("etablissement")
            ->addSelect("ecoleDoctorale")
            ->addSelect("uniteRecherche")
            ->addSelect("structure_etab")
            ->addSelect("structure_ed")
            ->addSelect("structure_ur")
            ->leftJoin("acteur.individu", "individu")
            ->leftJoin("acteur.these", "these")
            ->leftJoin("these.etablissement", "etablissement")
            ->leftJoin("etablissement.structure", "structure_etab")
            ->leftJoin("these.ecoleDoctorale", "ecoleDoctorale")
            ->leftJoin("ecoleDoctorale.structure", "structure_ed")
            ->leftJoin("these.uniteRecherche", "uniteRecherche")
            ->leftJoin("uniteRecherche.structure", "structure_ur")
            ->andWhere("individu.email is NULL")
            ->andWhere("these.etatThese = 'E'")
            ->orderBy("these.sourceCode")
            ;

        $result = $qb->getQuery()->getResult();
        return $result;
    }

    public function getDoctorantSansMail()
    {
        $qb = $this->getEntityManager()->getRepository(These::class)->createQueryBuilder("these")
            ->addSelect("doctorant")
            ->addSelect("individu")
            ->leftJoin("these.doctorant", "doctorant")
            ->leftJoin("doctorant.individu", "individu")
            ->addSelect("etablissement")
            ->addSelect("ecoleDoctorale")
            ->addSelect("uniteRecherche")
            ->addSelect("structure_etab")
            ->addSelect("structure_ed")
            ->addSelect("structure_ur")
            ->leftJoin("these.etablissement", "etablissement")
            ->leftJoin("etablissement.structure", "structure_etab")
            ->leftJoin("these.ecoleDoctorale", "ecoleDoctorale")
            ->leftJoin("ecoleDoctorale.structure", "structure_ed")
            ->leftJoin("these.uniteRecherche", "uniteRecherche")
            ->leftJoin("uniteRecherche.structure", "structure_ur")
            ->andWhere("individu.email is NULL")
            ->andWhere("these.etatThese = 'E'")
            ->orderBy("these.sourceCode")
        ;

        $result = $qb->getQuery()->getResult();
        return $result;
    }
}