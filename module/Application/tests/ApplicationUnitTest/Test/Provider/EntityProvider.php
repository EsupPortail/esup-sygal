<?php

namespace ApplicationUnitTest\Test\Provider;

use Application\Entity\Db\Doctorant;
use Application\Entity\Db\Fichier;
use Application\Entity\Db\FichierThese;
use Application\Entity\Db\Individu;
use Application\Entity\Db\NatureFichier;
use Application\Entity\Db\Parametre;
use Application\Entity\Db\Role;
use Application\Entity\Db\These;
use Application\Entity\Db\TypeValidation;
use Application\Entity\Db\Utilisateur;
use Application\Entity\Db\Validation;
use Application\Entity\Db\VersionFichier;
use ApplicationUnitTest\Test\Asset\EntityAsset;
use Doctrine\ORM\EntityManager;
use InvalidArgumentException;
use UnicaenApp\Exception\RuntimeException;
use UnicaenApp\ORM\Event\Listeners\HistoriqueListener;
use Application\Entity\Db\Source;
use UnicaenTest\Entity\Db\AbstractEntityProvider;

/**
 * Description of EntityProvider
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
class EntityProvider extends AbstractEntityProvider
{
    /**
     * @var Utilisateur
     */
    protected $utilisateur;

    /**
     * @param EntityManager $entityManager
     * @param array         $config
     */
    public function __construct(EntityManager $entityManager, array $config = [])
    {
        parent::__construct($entityManager, $config);

        if (!isset($config[$key = 'numero_etudiant_test'])) {
            throw new InvalidArgumentException(
                "Le numéro de l'étudiant de test doit être spécifié dans la config à l'aide de la clé '%$key'.");
        }
        if (!isset($config[$key = 'source_id_test'])) {
            throw new InvalidArgumentException(
                "Le numéro de l'étudiant de test doit être spécifié dans la config à l'aide de la clé '%$key'.");
        }

        $utilisateur = $this->getUtilisateurApp();

        // recherche du listener de gestion de l'historique pour lui transmettre le pseudo-utilisateur correspondant à l'application
        foreach ($this->getEntityManager()->getEventManager()->getListeners() as $listeners) {
            foreach ($listeners as $listener) {
                if ($listener instanceof HistoriqueListener) {
                    $listener->setIdentity(['db' => $utilisateur]);
                }
            }
        }
    }

    /**
     * Retourne le pseudo-utilisateur correspondant à l'application.
     *
     * @return Utilisateur
     */
    public function getUtilisateurApp()
    {
        if (null === $this->utilisateur) {
            if (!($param = $this->getEntityManager()->find(Parametre::class, $id = Parametre::APP_UTILISATEUR_ID))) {
                throw new RuntimeException("Le paramètre '$id' est introuvable dans la table.");
            }
            if (!($this->utilisateur = $this->getEntityManager()->find(Utilisateur::class, $id = $param->getValeur()))) {
                throw new RuntimeException("L'id $id spécifié pour le pseudo-utilisateur correspondant à l'application est introuvable.");
            }
        }

        return $this->utilisateur;
    }

    /**
     * @var Source
     */
    protected $source;

    /**
     * @return Source
     */
    public function getSource()
    {
        if ($this->source === null) {
            $this->source = $this->getEntityManager()->find(Source::class, $this->config['source_id_test']);
        }

        return $this->source;
    }

    /**
     * @var Doctorant
     */
    protected $doctorant;

    /**
     * @return Doctorant
     */
    public function getDoctorant()
    {
        if ($this->doctorant === null) {
            $numeroEtudiant = $this->config['numero_etudiant_test'];
            $this->doctorant = $this->getEntityManager()->getRepository(Doctorant::class)->findOneBy(['sourceCode' => $numeroEtudiant]);
        }

        return $this->doctorant;
    }

    /**
     * @return These
     */
    public function these()
    {
        $entity = EntityAsset::newThese($this->getDoctorant(), $this->getSource());
        $this->getEntityManager()->persist($entity);

        $this->newEntities->push($entity);

        return $entity;
    }

    public function fichierThese(These $these, $nature = NatureFichier::CODE_THESE_PDF, $version = VersionFichier::CODE_ORIG)
    {
        if (!$nature instanceof NatureFichier) {
            $nature = $this->getEntityManager()->getRepository(NatureFichier::class)
                ->findOneBy(['code' => $nature]);
        }
        if (!$version instanceof VersionFichier) {
            $version = $this->getEntityManager()->getRepository(VersionFichier::class)
                ->findOneBy(['code' => $version]);
        }

        $fichier = EntityAsset::newFichier($nature, $version);
        $this->getEntityManager()->persist($fichier);

        $entity = EntityAsset::newFichierThese($these, $fichier);
        $this->getEntityManager()->persist($entity);

        // $this->newEntities->push() inutile grâce au "delete cascade"

        return $entity;
    }

    public function attestation(These $these)
    {
        $entity = EntityAsset::newAttestation($these);
        $this->getEntityManager()->persist($entity);

        // $this->newEntities->push() inutile grâce au "delete cascade"

        return $entity;
    }

    public function diffusion(These $these)
    {
        $entity = EntityAsset::newDiffusion($these);
        $this->getEntityManager()->persist($entity);

        // $this->newEntities->push() inutile grâce au "delete cascade"

        return $entity;
    }

    public function signalement(These $these)
    {
        $entity = EntityAsset::newSignalement($these);
        $this->getEntityManager()->persist($entity);

        // $this->newEntities->push() inutile grâce au "delete cascade"

        return $entity;
    }

    public function validiteFichier(Fichier $fichier, $estValide = null)
    {
        $entity = EntityAsset::newValiditeFichierThese($fichier, $estValide);
        $this->getEntityManager()->persist($entity);

        // $this->newEntities->push() inutile grâce au "delete cascade"

        return $entity;
    }

    public function rdvBu(These $these)
    {
        $entity = EntityAsset::newRdvBu($these);
        $this->getEntityManager()->persist($entity);

        // $this->newEntities->push() inutile grâce au "delete cascade"

        return $entity;
    }

    public function individu()
    {
        $individu = EntityAsset::newIndividu($this->getSource());
        $this->getEntityManager()->persist($individu);

        $this->newEntities->push($individu);

        return $individu;
    }

    /**
     * Retourne à chaque appel une nouvelle instance de Validation persistée.
     *
     * @param These                 $these
     * @param TypeValidation|string $typeValidation
     * @param Individu              $individu
     * @return Validation
     */
    public function validation(These $these, $typeValidation, Individu $individu = null)
    {
        if (!$typeValidation instanceof TypeValidation) {
            $typeValidation = $this->getEntityManager()->getRepository(TypeValidation::class)
                ->findOneBy(['code' => $typeValidation]);
        }

        $entity = EntityAsset::newValidation($these, $typeValidation, $individu);
        $this->getEntityManager()->persist($entity);

        // $this->newEntities->push() inutile grâce au "delete cascade"

        return $entity;
    }

    public function acteur(These $these, Role $role, Individu $individu)
    {
        $acteur = EntityAsset::newActeur($these, $this->getSource(), $role, $individu);
        $this->getEntityManager()->persist($acteur);

        // $this->newEntities->push() inutile grâce au "delete cascade"

        return $acteur;
    }

    public function directeurThese(These $these, Individu $individu)
    {
        /** @var Role $role */
        $role = $this->getEntityManager()->getRepository(Role::class)
            ->findOneBy(['sourceCode' => 'D']);

        return $this->acteur($these, $role, $individu);
    }

    protected $typesValidation = [];

    /**
     * Recherche et retourne le TypeValidation correspondant au code spécifié.
     *
     * @param string $sourceCode Code du TypeValidation, ex: TypeValidation::CODE_PIECE_JOINTE
     * @return TypeValidation
     */
    public function getTypeValidationByCode($sourceCode)
    {
        if (!isset($this->typesValidation[$sourceCode])) {
            $this->typesValidation[$sourceCode] = $this->getEntityManager()->getRepository(TypeValidation::class)
                ->findOneByCode($sourceCode);
            if (!$this->typesValidation[$sourceCode]) {
                throw new RuntimeException("TypeValidation introuvable avec le code '$sourceCode'.");
            }
        }

        return $this->typesValidation[$sourceCode];
    }
}