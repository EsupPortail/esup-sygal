<?php

namespace Application\Service\Utilisateur;

use Application\Entity\Db\Repository\UtilisateurRepository;
use Application\Entity\Db\Utilisateur;
use Application\Filter\NomCompletFormatter;
use Application\Service\BaseService;
use Application\Service\Source\SourceServiceAwareTrait;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\OptimisticLockException;
use Exception;
use Individu\Entity\Db\Individu;
use Individu\Service\IndividuServiceAwareTrait;
use Laminas\Crypt\Password\Bcrypt;
use Laminas\Mvc\Controller\AbstractActionController;
use UnicaenApp\Exception\RuntimeException;
use UnicaenUtilisateur\Entity\Db\AbstractUser;
use UnicaenAuthentification\Service\Traits\UserServiceAwareTrait;
use UnicaenLdap\Entity\People;

class UtilisateurService extends BaseService
{
    use SourceServiceAwareTrait;
    use UserServiceAwareTrait;
    use IndividuServiceAwareTrait;

    const SQL_CREATE_APP_USER =
        "INSERT INTO UTILISATEUR (ID, USERNAME, EMAIL, DISPLAY_NAME, PASSWORD) VALUES (1, 'sygal-app', 'noreply@mail.fr', 'Application ESUP-SyGAL', 'ldap');";

    /**
     * @return UtilisateurRepository
     */
    public function getRepository()
    {
        /** @var UtilisateurRepository $repo */
        $repo = $this->entityManager->getRepository(Utilisateur::class);

        return $repo;
    }

    /**
     * Fetche le pseudo-utilisateur représentant l'application.
     */
    public function fetchAppPseudoUtilisateur(): Utilisateur
    {
        $qb = $this->getRepository()->createQueryBuilder('u')
            ->where('u.username = :username')
            ->setParameter('username', $username = Utilisateur::APP_UTILISATEUR_USERNAME);

        try {
            /** @var Utilisateur $utilisateur */
            $utilisateur = $qb->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            throw new RuntimeException("Plusieurs pseudo-utilisateur trouvé avec ce username: " . $username);
        }

        if ($utilisateur === null) {
            throw new RuntimeException(
                "Le pseudo-utilisateur '$username' n'existe pas dans la base de données. " .
                "Vous devez l'ajouter ainsi : " . self::SQL_CREATE_APP_USER);
        }

        return $utilisateur;
    }

    /**
     * @param People $people
     * @return Utilisateur
     */
    public function createFromPeople(People $people)
    {
        $entity = new Utilisateur();
        $entity->setDisplayName($people->getNomComplet(true));
        $entity->setEmail($people->get('mail'));
        $entity->setUsername($people->get('supannAliasLogin'));
        $entity->setPassword('ldap');
        $entity->setState(1);

        $this->getEntityManager()->persist($entity);
        try {
            $this->getEntityManager()->flush($entity);
        } catch (OptimisticLockException $e) {
            throw new RuntimeException("Impossible d'enregistrer l'utilisateur", null, $e);
        }

        return $entity;
    }

    public function createFromFormData(array $data): Utilisateur
    {
        $userName = $data['email'];
        $displayName = $data['prenom'] . " " . $data['nomUsuel'];

        $utilisateur = new Utilisateur();
        $utilisateur->setDisplayName($displayName);
        $utilisateur->setNom($data['nomUsuel']);
        $utilisateur->setPrenom($data['prenom']);
        $utilisateur->setEmail($data['email']);
        $utilisateur->setUsername($userName);
        $utilisateur->setPassword('none');
        $utilisateur->setState(1);

        try {
            $this->getEntityManager()->persist($utilisateur);
            $this->getEntityManager()->flush($utilisateur);
        } catch (ORMException $e) {
            throw new RuntimeException("Impossible d'enregistrer le nouvel utilisateur", null, $e);
        }

        return $utilisateur;
    }

    /**
     * @param Individu $individu
     * @param string   $username
     * @param string   $password
     * @return Utilisateur
     */
    public function createFromIndividu(Individu $individu, string $username, string $password) : Utilisateur
    {
        if (! $username) {
            throw new RuntimeException("Impossible de créer un utilisateur sans username");
        }
        if (! $password) {
            throw new RuntimeException("Impossible de créer un utilisateur sans password");
        }

        $nomFormatter = new NomCompletFormatter();
        $displayName = $nomFormatter->filter($individu);

        $utilisateur = new Utilisateur();
        $utilisateur->setDisplayName($displayName);
        $utilisateur->setEmail($individu->getEmailPro());
        $utilisateur->setUsername($username);
        $utilisateur->setPassword($password);
        $utilisateur->setState(1);
        $utilisateur->setIndividu($individu);

        try {
            $this->entityManager->persist($utilisateur);
            $this->entityManager->flush($utilisateur);
        } catch (ORMException $e) {
            throw new RuntimeException("Impossible d'enregistrer le nouvel utilisateur", null, $e);
        }

        return $utilisateur;
    }

    /**
     * @param Individu $individu
     * @param array $formData
     * @return Utilisateur
     */
    public function createUtilisateurFromIndividuAndFormData(Individu $individu, array $formData): Utilisateur
    {
        if (!$individu->getEmailPro()) {
            throw new RuntimeException("Impossible de créer un utilisateur à partir d'un individu n'ayant pas d'email");
        }

        $username = $individu->getEmailPro(); // NB: username = email

        $bcrypt = new Bcrypt();
        $password = $bcrypt->create($formData['password']);

        return $this->createFromIndividu($individu, $username, $password);
    }

    /**
     * Renseigne l'individu correspondant à un utilisateur en bdd.
     *
     * @param Individu    $individu
     * @param Utilisateur $utilisateur
     */
    public function setIndividuForUtilisateur(Individu $individu, Utilisateur $utilisateur)
    {
        $utilisateur->setIndividu($individu);

        try {
            $this->getEntityManager()->flush($utilisateur);
        } catch (OptimisticLockException $e) {
            throw new RuntimeException("Impossible d'enregistrer l'utilisateur", null, $e);
        }
    }

    /**
     * @throws \Exception
     */
    public function supprimerUtilisateur(AbstractUser $utilisateur): void
    {
        try {
            $this->entityManager->remove($utilisateur);
            $this->entityManager->flush();
        } catch (Exception $e) {
            $precision = '';
            if ($e instanceof ForeignKeyConstraintViolationException) {
                $precision = " car il est référencé dans d'autres tables de données";
            }
            throw new Exception(sprintf("Impossible de supprimer ce compte utilisateur%s.", $precision), null, $e);
        }
    }

    /**
     * @param Utilisateur $utilisateur
     * @param string $password
     * @return Utilisateur
     */
    public function changePassword(Utilisateur $utilisateur, string $password): Utilisateur
    {
        $bcrypt = new Bcrypt();
        $bcrypt->setCost($this->userService->getZfcUserOptions()->getPasswordCost());
        $password = $bcrypt->create($password);

        $utilisateur->setPassword($password);
        $utilisateur->setPasswordResetToken();

        try {
            $this->entityManager->flush();
        } catch (ORMException $e) {
            throw new RuntimeException("Impossible d'enregistrer l'utilisateur", null, $e);
        }

        return $utilisateur;
    }

    /**
     * @param AbstractActionController $controller
     * @param string $param
     * @return Utilisateur|null
     */
    public function getRequestedUtilisateur(AbstractActionController $controller, string $param = "utilisateur") : ?Utilisateur
    {
        $id = $controller->params()->fromRoute($param);
        if ($id === null) {
            return null;
        }

        /** @var Utilisateur $result */
        $result = $this->getRepository()->find($id);

        return $result;
    }


}