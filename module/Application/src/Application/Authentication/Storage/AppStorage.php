<?php

namespace Application\Authentication\Storage;

use Application\Entity\Db\Doctorant;
use Application\Entity\Db\EcoleDoctoraleIndividu;
use Application\Entity\Db\UniteRechercheIndividu;
use Application\Service\Doctorant\DoctorantServiceAwareTrait;
use Application\Service\EcoleDoctorale\EcoleDoctoraleServiceAwareTrait;
use Application\Service\UniteRecherche\UniteRechercheServiceAwareTrait;
use Doctrine\ORM\NonUniqueResultException;
use UnicaenApp\Exception\RuntimeException;
use UnicaenAuth\Authentication\Storage\ChainableStorage;
use UnicaenAuth\Authentication\Storage\ChainEvent;
use UnicaenAuth\Entity\Ldap\People;
use Zend\Authentication\Exception\ExceptionInterface;

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
class AppStorage implements ChainableStorage
{
    use DoctorantServiceAwareTrait;
    use EcoleDoctoraleServiceAwareTrait;
    use UniteRechercheServiceAwareTrait;

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
     * @throws \Zend\Authentication\Exception\ExceptionInterface
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
        try {
            $e->addContents(self::KEY_DOCTORANT, $this->fetchDoctorant());
        } catch (ExceptionInterface $e) {
            throw new RuntimeException("Erreur imprévue rencontrée.", 0, $e);
        }

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
        $username = $this->people->getSupannAliasLogin();
        try {
            $this->doctorant = $this->doctorantService->getRepository()->findOneByUsername($username);
        } catch (NonUniqueResultException $e) {
            throw new RuntimeException("Plusieurs doctorants ont été trouvés avec le même username: " . $username);
        }

        return $this->doctorant;
    }

    private function addEcoleDoctoraleIndividuContents(ChainEvent $e)
    {
        try {
            $e->addContents(self::KEY_ECOLE_DOCTORALE_INDIVIDU, $this->fetchEcoleDoctoraleIndividu());
        } catch (ExceptionInterface $e) {
            throw new RuntimeException("Erreur imprévue rencontrée.", 0, $e);
        }

        return $this;
    }

    private function fetchEcoleDoctoraleIndividu()
    {
        if (null !== $this->ecoleDoctoraleIndividu) {
            return $this->ecoleDoctoraleIndividu;
        }

        $sourceCodeIndividu = $this->people->getSupannEmpId();

        $this->ecoleDoctoraleIndividu =
            $this->ecoleDoctoraleService->getRepository()->findMembresBySourceCodeIndividu($sourceCodeIndividu);

        return $this->ecoleDoctoraleIndividu;
    }

    private function addUniteRechercheIndividuContents(ChainEvent $e)
    {
        try {
            $e->addContents(self::KEY_UNITE_RECHERCHE_INDIVIDU, $this->fetchUniteRechercheIndividu());
        } catch (ExceptionInterface $e) {
            throw new RuntimeException("Erreur imprévue rencontrée.", 0, $e);
        }

        return $this;
    }

    private function fetchUniteRechercheIndividu()
    {
        if (null !== $this->uniteRechercheIndividu) {
            return $this->uniteRechercheIndividu;
        }

        $sourceCodeIndividu = $this->people->getSupannEmpId();

        $this->uniteRechercheIndividu =
            $this->uniteRechercheService->getRepository()->findMembresBySourceCodeIndividu($sourceCodeIndividu);

        return $this->uniteRechercheIndividu;
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