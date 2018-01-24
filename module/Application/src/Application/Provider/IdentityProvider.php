<?php

namespace Application\Provider;

use Application\Entity\Db\Acteur;
use Application\Entity\Db\EcoleDoctoraleIndividu;
use Application\Entity\Db\Role;
use Application\Entity\Db\Doctorant;
use Application\Entity\Db\UniteRechercheIndividu;
use BjyAuthorize\Provider\Identity\ProviderInterface;
use Doctrine\ORM\Query\Expr\Join;
use UnicaenApp\Service\EntityManagerAwareInterface;
use UnicaenApp\Service\EntityManagerAwareTrait;
use UnicaenAuth\Entity\Ldap\People;
use UnicaenAuth\Provider\Identity\ChainableProvider;
use UnicaenAuth\Provider\Identity\ChainEvent;
use Zend\Authentication\AuthenticationService;
use Zend\Http\PhpEnvironment\Request;
use Zend\Http\PhpEnvironment\Response;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

/**
 *
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
class IdentityProvider implements ProviderInterface, ChainableProvider, EntityManagerAwareInterface, ServiceLocatorAwareInterface
{
    use EntityManagerAwareTrait;
    use ServiceLocatorAwareTrait;

    private $roles;

    /**
     * @var AuthenticationService
     */
    private $authenticationService;

    /**
     * IdentityProvider constructor.
     *
     * @param AuthenticationService $authenticationService
     */
    public function __construct(AuthenticationService $authenticationService)
    {
        $this->authenticationService = $authenticationService;
    }

    /**
     * {@inheritDoc}
     */
    public function injectIdentityRoles(ChainEvent $event)
    {
        $event->addRoles($this->getIdentityRoles());
    }

    /**
     * {@inheritDoc}
     */
    public function getIdentityRoles()
    {
        if (! ($identity = $this->authenticationService->getIdentity())) {
            return [];
        }

        if ($this->roles !== null) {
            return $this->roles;
        }

        /** @var People $people */
        $people = $identity['ldap'];

        $roles = array_merge([],
            $this->getRolesFromActeur($people),
            $this->getRolesFromEcoleDoctoraleIndividu($people),
            $this->getRolesFromUniteRechercheIndividu($people),
            $this->getRolesFromDoctorant($people));

        $this->roles = array_unique($roles);

        return $this->roles;
    }

    /**
     * Rôles découlant de la présence de l'utilisateur dans la table Acteur.
     *
     * @param People $people
     * @return array
     */
    private function getRolesFromActeur(People $people)
    {
        $qb = $this->entityManager->getRepository(Acteur::class)->createQueryBuilder('a');
        $qb
            ->addSelect('r')
            ->join('a.individu', 'i', Join::WITH, 'i.sourceCode = :pid')
            ->join('a.role', 'r')
            ->setParameter('pid', $people->getSupannEmpId())
        ;

        // pour l'instant on ne considère pas tous les types d'acteur
        $qb->andWhere($qb->expr()->in('r.sourceCode', [
            Role::SOURCE_CODE_DIRECTEUR_THESE,
        ]));

        $acteurs = $qb->getQuery()->getResult();

        return array_unique(array_map(function(Acteur $a) {
            return $a->getRole()->getRoleId();
        }, $acteurs));
    }

    /**
     * Rôle découlant de la présence de l'utilisateur dans EcoleDoctoraleIndividu.
     *
     * @param People $people
     * @return array
     */
    private function getRolesFromEcoleDoctoraleIndividu(People $people)
    {
        $qb = $this->entityManager->getRepository(EcoleDoctoraleIndividu::class)->createQueryBuilder('edi');
        $qb
            ->addSelect('r')
            ->join('edi.individu', 'i', Join::WITH, 'i.sourceCode = :codePer')
            ->join('edi.role', 'r')
            ->setParameter('codePer', $people->getSupannEmpId())
        ;
        $result = $qb->getQuery()->getResult();

        return array_unique(array_map(function(EcoleDoctoraleIndividu $edi) {
            return $edi->getRole()->getRoleId();
        }, $result));
    }

    /**
     * Rôle découlant de la présence de l'utilisateur dans UniteRechercheIndividu.
     *
     * @param People $people
     * @return array
     */
    private function getRolesFromUniteRechercheIndividu(People $people)
    {
        $qb = $this->entityManager->getRepository(UniteRechercheIndividu::class)->createQueryBuilder('uri');
        $qb
            ->addSelect('r')
            ->join('uri.individu', 'i', Join::WITH, 'i.sourceCode = :codePer')
            ->join('uri.role', 'r')
            ->setParameter('codePer', $people->getSupannEmpId())
        ;
        $result = $qb->getQuery()->getResult();

        return array_unique(array_map(function(UniteRechercheIndividu $uri) {
            return $uri->getRole()->getRoleId();
        }, $result));
    }

    /**
     * Rôle découlant de la présence de l'utilisateur dans la table Doctorant.
     *
     * @param People $people
     * @return array
     */
    private function getRolesFromDoctorant(People $people)
    {
        /**
         * NB: Un doctorant a la possibilité de s'authentifier :
         * - avec son numéro étudiant (Doctorant::sourceCode),
         * - avec son persopass (DoctorantCompl::persopass), seulement après qu'il l'a saisi sur la page d'identité de la thèse.
         */
        $qb = $this->entityManager->getRepository(Doctorant::class)->createQueryBuilder('t');
        $qb
            ->select('COUNT(t)')
            ->andWhere('1 = pasHistorise(t)')
            ->leftJoin('t.complements', 'c')
            ->andWhere('t.sourceCode = :login OR c.persopass = :login')
            ->setParameter('login', $people->getSupannAliasLogin());

        $isDoctorant = (bool) (int) $qb->getQuery()->getSingleScalarResult();

        return $isDoctorant ? [Role::ROLE_ID_DOCTORANT] : [];
    }
}