<?php

namespace Application;

use Application\Entity\Db\EcoleDoctorale;
use Application\Entity\Db\Etablissement;
use Application\Entity\Db\Fichier;
use Application\Entity\Db\Rapport;
use Application\Entity\Db\Role;
use Application\Entity\Db\Structure;
use Application\Entity\Db\These;
use Application\Entity\Db\UniteRecherche;
use Application\Entity\Db\Utilisateur;
use Doctorant\Entity\Db\Doctorant;
use Doctrine\ORM\EntityRepository;
use UnicaenApp\Service\EntityManagerAwareTrait;
use Zend\Router\RouteMatch as BaseRouteMatch;

/**
 * Ce RouteMatch fournit des accesseurs métiers traduisant en entités ses paramètres éventuels
 * (exemple : 'these', 'fichier').
 *
 * @package Application
 */
class RouteMatch extends BaseRouteMatch
{
    use EntityManagerAwareTrait;

    /**
     * @var array
     */
    private $entityClassNamesMapping = [
        'these' => These::class,
        'doctorant' => Doctorant::class,
        'fichier' => Fichier::class,
        'utilisateur' => Utilisateur::class,
        'role' => Role::class,
        'ecoleDoctorale' => EcoleDoctorale::class,
        'uniteRecherche' => UniteRecherche::class,
        'etablissement' => Etablissement::class,
        'structure' => Structure::class,
        'rapport' => Rapport::class,
    ];

    /**
     * @var array
     */
    private $repositories = [];

    /**
     * @param string $name Nom du paramètre
     * @return object|null
     */
    private function fetchEntityParam(string $name): ?object
    {
        $repository = $this->getRepository($name);
        if ($repository === null) {
            return null;
        }

        // c'est un identifiant unique qui doit être spécifié
        $id = $this->getParam($name);

        if (! $id) {
            return null;
        }

        // NB : certaines entités peuvent être recherchées par autre chose que par leur id.
        switch ($name) {
            case 'role':
                $criteria = is_numeric($id) ? ['id' => $id] : ['roleId' => $id];
                break;
            case 'fichier':
                $criteria = ['uuid' => $id];
                break;
            default:
                $criteria = ['id' => $id];
        }

        return $repository->findOneBy($criteria);
    }

    /**
     * @param string $name
     * @return EntityRepository
     */
    private function getRepository(string $name): EntityRepository
    {
        if (! isset($this->repositories[$name])) {
            $entityClassName = $this->entityClassNamesMapping[$name];
            $this->repositories[$name] = $this->getEntityManager()->getRepository($entityClassName);
        }

        return $this->repositories[$name];
    }

    /**
     * @var These
     */
    private $these;

    /**
     * @return These|null
     */
    public function getThese(): ?These
    {
        if (null === $this->these) {
            $this->these = $this->fetchEntityParam('these');
        }

        return $this->these;
    }

    /**
     * @var Doctorant
     */
    private $doctorant;

    /**
     * @return Doctorant|null
     */
    public function getDoctorant(): ?Doctorant
    {
        if (null === $this->doctorant) {
            $this->doctorant = $this->fetchEntityParam('doctorant');
        }

        return $this->doctorant;
    }

    /**
     * @var Fichier
     */
    private $fichier;

    /**
     * @return Fichier|null
     */
    public function getFichier(): ?Fichier
    {
        if (null === $this->fichier) {
            $this->fichier = $this->fetchEntityParam('fichier');
        }

        return $this->fichier;
    }

    /**
     * @var Utilisateur
     */
    private $utilisateur;

    /**
     * @return Utilisateur|null
     */
    public function getUtilisateur(): ?Utilisateur
    {
        if (null === $this->utilisateur) {
            $this->utilisateur = $this->fetchEntityParam('utilisateur');
        }

        return $this->utilisateur;
    }

    /**
     * @var Role
     */
    private $role;

    /**
     * @return Role|null
     */
    public function getRole(): ?Role
    {
        if (null === $this->role) {
            $this->role = $this->fetchEntityParam('role');
        }

        return $this->role;
    }

    /**
     * @var EcoleDoctorale
     */
    private $ecoleDoctorale;

    /**
     * @return EcoleDoctorale|null
     */
    public function getEcoleDoctorale(): ?EcoleDoctorale
    {
        if (null === $this->ecoleDoctorale) {
            $this->ecoleDoctorale = $this->fetchEntityParam('ecoleDoctorale');
        }

        return $this->ecoleDoctorale;
    }

    /**
     * @var UniteRecherche
     */
    private $uniteRecherche;

    /**
     * @return UniteRecherche|null
     */
    public function getUniteRecherche(): ?UniteRecherche
    {
        if (null === $this->uniteRecherche) {
            $this->uniteRecherche = $this->fetchEntityParam('uniteRecherche');
        }

        return $this->uniteRecherche;
    }

    /**
     * @var Etablissement
     */
    private $etablissement;

    /**
     * @return Etablissement|null
     */
    public function getEtablissement(): ?Etablissement
    {
        if (null === $this->etablissement) {
            $this->etablissement = $this->fetchEntityParam('etablissement');
        }

        return $this->etablissement;
    }

    /** @var Structure */
    private $structure;

    /**
     * @return Structure|null
     */
    public function getStructure(): ?Structure
    {
        if (null === $this->structure) {
            $this->structure = $this->fetchEntityParam('structure');
        }

        return $this->structure;
    }

    /** @var Rapport */
    private $rapport;

    /**
     * @return Rapport|null
     */
    public function getRapport(): ?Rapport
    {
        if (null === $this->rapport) {
            $this->rapport = $this->fetchEntityParam('rapport');
        }

        return $this->rapport;
    }
}