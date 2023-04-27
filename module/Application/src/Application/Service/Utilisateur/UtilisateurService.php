<?php

namespace Application\Service\Utilisateur;

use Individu\Entity\Db\Individu;
use Application\Entity\Db\Repository\UtilisateurRepository;
use Application\Entity\Db\Utilisateur;
use Application\Filter\NomCompletFormatter;
use Application\Service\BaseService;
use Application\Service\Source\SourceServiceAwareTrait;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use UnicaenApp\Exception\RuntimeException;
use UnicaenAuth\Entity\Db\AbstractUser;
use UnicaenAuth\Service\Traits\UserServiceAwareTrait;
use UnicaenLdap\Entity\People;
use Laminas\Crypt\Password\Bcrypt;
use Laminas\Mvc\Controller\AbstractActionController;

class UtilisateurService extends BaseService
{
    use SourceServiceAwareTrait;
    use UserServiceAwareTrait;

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
     * @return Utilisateur
     */
    public function fetchAppPseudoUtilisateur()
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
            $this->getEntityManager()->persist($utilisateur);
            $this->getEntityManager()->flush($utilisateur);
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
    public function createFromIndividuAndFormData(Individu $individu, array $formData): Utilisateur
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
     * Fonction utilisée lors de la déassociation d'un utilisateur/individu et un membre d'un jury de thèse
     * @param AbstractUser $utilisateur
     */
    public function supprimerUtilisateur(AbstractUser $utilisateur) {
        try {
            $this->getEntityManager()->remove($utilisateur);
            $this->getEntityManager()->flush($utilisateur);
        } catch (OptimisticLockException $e) {
            throw new RuntimeException("Impossible d'enregistrer l'utilisateur", null, $e);
        }
    }

    /**
     * @param Utilisateur $utilisateur
     * @param string $password
     * @return Utilisateur
     */
    public function changePassword($utilisateur, $password)
    {

        $bcrypt = new Bcrypt();
        $bcrypt->setCost($this->userService->getZfcUserOptions()->getPasswordCost());
        $password = $bcrypt->create($password);

        $utilisateur->setPassword($password);
        $utilisateur->setPasswordResetToken();

        try {
            $this->getEntityManager()->flush($utilisateur);
        } catch (OptimisticLockException $e) {
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