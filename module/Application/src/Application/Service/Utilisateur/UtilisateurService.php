<?php

namespace Application\Service\Utilisateur;

use Application\Entity\Db\Individu;
use Application\Entity\Db\Repository\UtilisateurRepository;
use Application\Entity\Db\Source;
use Application\Entity\Db\Utilisateur;
use Application\Filter\NomCompletFormatter;
use Application\Service\BaseService;
use Application\Service\Source\SourceServiceAwareTrait;
use Application\SourceCodeStringHelperAwareTrait;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use UnicaenApp\Exception\RuntimeException;
use UnicaenAuth\Entity\Db\AbstractUser;
use UnicaenAuth\Service\Traits\UserServiceAwareTrait;
use UnicaenLdap\Entity\People;
use Zend\Crypt\Password\Bcrypt;
use Zend\Mvc\Controller\AbstractActionController;

class UtilisateurService extends BaseService
{
    use SourceServiceAwareTrait;
    use SourceCodeStringHelperAwareTrait;
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

    /**
     * @param Individu $individu
     * @return Utilisateur
     */
    public function createFromIndividuForUsurpationShib(Individu $individu)
    {
        if ($individu->getSupannId() == null) {
            throw new RuntimeException("Le supannId de l'individu $individu (id={$individu->getId()}) est null.");
        }

        /** @var Source $source */
        $source = $individu->getSource();
        $etablissementSource = $source->getEtablissement();
        $username = $individu->getSupannId() . '@' . $etablissementSource->getDomaine();

        $password = 'shib';

        return $this->createFromIndividu($individu, $username, $password);
    }

    public function createFromFormData(array $data)
    {
        $userName = $data['email'];
        $displayName = $data['prenom'] . " " . $data['nomUsuel'];
        $email = $data['email'];

        $utilisateur = new Utilisateur();
        $utilisateur->setDisplayName($displayName);
        $utilisateur->setEmail($email);
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
        $utilisateur->setEmail($individu->getEmail());
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
     * @param array    $formData
     * @return Utilisateur
     */
    public function createFromIndividuAndFormData(Individu $individu, array $formData)
    {
        if (! $individu->getEmail()) {
            throw new RuntimeException("Impossible de créer un utilisateur à partir d'un individu n'ayant pas d'email");
        }

        $username = $individu->getEmail(); // NB: username = email

        $bcrypt = new Bcrypt();
        $password = $bcrypt->create($formData['password']);

        return $this->createFromIndividu($individu, $username, $password);
    }

    /**
     * Crée un utilisateur ainsi que l'individu associé, à partir des données du formulaire de création d'utilisateur.
     *
     * @param array $formData
     * @return Individu
     */
    public function createIndividuFromFormData(array $formData)
    {
        $source = $this->sourceService->fetchApplicationSource();

        /** @var Individu $individu */
        $individu = new Individu();
        $individu->setCivilite($formData['civilite']);
        $individu->setNomUsuel($formData['nomUsuel']);
        $individu->setNomPatronymique($formData['nomPatronymique']);
        $individu->setPrenom1($formData['prenom']);
        $individu->setEmail($formData['email']);
        $individu->setSource($source);
        $individu->setSourceCode(uniqid()); // NB: sera remplacé par "COMUE::{INDIVIDU.ID}"

        try {
            $this->getEntityManager()->persist($individu);
            $this->getEntityManager()->flush($individu);
        } catch (ORMException $e) {
            throw new RuntimeException("Erreur lors de l'enregistrement du nouvel individu", null, $e);
        }

        // source code définitif, ex : "COMUE::{INDIVIDU.ID}"
        $sourceCodeIndividu = $this->sourceCodeStringHelper->addDefaultPrefixTo($individu->getId());
        $individu->setSourceCode($sourceCodeIndividu);

        try {
            $this->getEntityManager()->flush($individu);
        } catch (ORMException $e) {
            throw new RuntimeException("Erreur lors de l'enregistrement de l'individu", null, $e);
        }

        return $individu;
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
        /** @var Utilisateur $result */
        $result = $this->getRepository()->find($id);
        return $result;
    }


}