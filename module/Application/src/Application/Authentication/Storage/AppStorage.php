<?php

namespace Application\Authentication\Storage;

use Application\Entity\Db\EcoleDoctoraleIndividu;
use Application\Entity\Db\Doctorant;
use Application\Entity\Db\UniteRechercheIndividu;
use Doctrine\ORM\Query\Expr\Join;
use UnicaenApp\Service\EntityManagerAwareInterface;
use UnicaenApp\Service\EntityManagerAwareTrait;
use UnicaenAuth\Authentication\Storage\ChainableStorage;
use UnicaenAuth\Authentication\Storage\ChainEvent;
use UnicaenAuth\Entity\Ldap\People;

/**
 * Ajout de données utiles concernant l'utilisateur authentifié.
 *
 * Valeur associée à la clé KEY_DOCTORANT :
 * - entité Doctorant si l'utilisateur authentifié est trouvé parmi les thésards,
 * - null sinon.
 *
 * Valeur associée à la clé KEY_ECOLE_DOCTORALE_INDIVIDU :
 * - entités EcoleDoctoraleIndividu si l'utilisateur authentifié est trouvé dans EcoleDoctoraleIndividu,
 * - [] sinon.
 *
 * Valeur associée à la clé KEY_UNITE_RECHERCHE_INDIVIDU :
 * - entités UniteRechercheIndividu si l'utilisateur authentifié est trouvé dans UniteRechercheIndividu,
 * - [] sinon.
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
class AppStorage implements ChainableStorage, EntityManagerAwareInterface
{
    use EntityManagerAwareTrait;

    const KEY_DOCTORANT = 'doctorant';
    const KEY_ECOLE_DOCTORALE_INDIVIDU = 'ecoleDoctoraleIndividu';
    const KEY_UNITE_RECHERCHE_INDIVIDU = 'uniteRechercheIndividu';

    /**
     * @var mixed
     */
    private $contents;

    /**
     * @var People
     */
    private $people;

    /**
     * @var Doctorant
     */
    protected $doctorant;

    /**
     * @var EcoleDoctoraleIndividu[]
     */
    protected $ecoleDoctoraleIndividu;

    /**
     * @var UniteRechercheIndividu[]
     */
    protected $uniteRechercheIndividu;

    /**
     * @param ChainEvent $e
     * @return void
     */
    public function read(ChainEvent $e)
    {
        $this->contents = $e->getContents();
        $this->people = $this->contents['ldap'];

        /**
         * Collecte des données au cas où l'utilisateur connecté est trouvé dans la table Doctorant.
         */
        $this->addDoctorantContents($e);

        /**
         * Collecte des données au cas où l'utilisateur connecté est trouvé dans la table EcoleDoctoraleIndividu.
         */
        $this->addEcoleDoctoraleIndividuContents($e);

        /**
         * Collecte des données au cas où l'utilisateur connecté est trouvé dans la table UniteRechercheIndividu.
         */
        $this->addUniteRechercheIndividuContents($e);
    }

    /**
     * @param ChainEvent $e
     * @return $this
     */
    protected function addDoctorantContents(ChainEvent $e)
    {
        $e->addContents(
            self::KEY_DOCTORANT,
            $this->fetchDoctorant());

        return $this;
    }

    protected function fetchDoctorant()
    {
        if (null !== $this->doctorant) {
            return $this->doctorant;
        }

        /**
         * NB: Un doctorant a la possibilité de s'authentifier :
         * - avec son numéro étudiant (Doctorant::sourceCode),
         * - avec son persopass (DoctorantCompl::persopass), seulement après qu'il l'a saisi sur la page d'identité de la thèse.
         */
        $qb = $this->entityManager->getRepository(Doctorant::class)->createQueryBuilder('t');
        $qb
            ->leftJoin('t.complements', 'c')
            ->andWhere('1 = pasHistorise(t)')
            ->andWhere('t.sourceCode = :login OR c.persopass = :login')
            ->setParameter('login', $this->people->getSupannAliasLogin());

        $result = $qb->getQuery()->getResult();

        $this->doctorant = current($result) ?: null;

        return $this->doctorant;
    }

    private function addEcoleDoctoraleIndividuContents(ChainEvent $e)
    {
        $e->addContents(
            self::KEY_ECOLE_DOCTORALE_INDIVIDU,
            $this->fetchEcoleDoctoraleIndividu());

        return $this;
    }

    private function fetchEcoleDoctoraleIndividu()
    {
        if (null !== $this->ecoleDoctoraleIndividu) {
            return $this->ecoleDoctoraleIndividu;
        }

        $qb = $this->entityManager->getRepository(EcoleDoctoraleIndividu::class)->createQueryBuilder('edi');
        $qb
            ->addSelect('ed, i, r')
            ->join('edi.ecole', 'ed')
            ->join('edi.individu', 'i', Join::WITH, 'i.sourceCode = :personnelId')
            ->join('edi.role', 'r')
            ->setParameter('personnelId', $this->people->getSupannEmpId())
        ;
        $this->ecoleDoctoraleIndividu = $qb->getQuery()->getResult();

        return $this->ecoleDoctoraleIndividu ?: [];
    }

    private function addUniteRechercheIndividuContents(ChainEvent $e)
    {
        $e->addContents(
            self::KEY_UNITE_RECHERCHE_INDIVIDU,
            $this->fetchUniteRechercheIndividu());

        return $this;
    }

    private function fetchUniteRechercheIndividu()
    {
        if (null !== $this->uniteRechercheIndividu) {
            return $this->uniteRechercheIndividu;
        }

        $qb = $this->entityManager->getRepository(UniteRechercheIndividu::class)->createQueryBuilder('uri');
        $qb
            ->addSelect('ur, i, r')
            ->join('uri.uniteRecherche', 'ur')
            ->join('uri.individu', 'i', Join::WITH, 'i.sourceCode = :personnelId')
            ->join('uri.role', 'r')
            ->setParameter('personnelId', $this->people->getSupannEmpId())
        ;
        $this->uniteRechercheIndividu = $qb->getQuery()->getResult();

        return $this->uniteRechercheIndividu ?: [];
    }




    public function write(ChainEvent $e)
    {
        // nop
    }

    public function clear(ChainEvent $e)
    {
        // nop
    }
}