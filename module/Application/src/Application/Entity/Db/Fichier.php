<?php

namespace Application\Entity\Db;

use Application\Controller\Plugin\Uploader\UploadedFileInterface;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Ramsey\Uuid\Uuid;
use UnicaenApp\Entity\HistoriqueAwareInterface;
use UnicaenApp\Entity\HistoriqueAwareTrait;
use UnicaenApp\Filter\BytesFormatter;
use Zend\Permissions\Acl\Resource\ResourceInterface;

/**
 * Fichier
 */
class Fichier implements HistoriqueAwareInterface, ResourceInterface, UploadedFileInterface
{
    use HistoriqueAwareTrait;

    /**
     * Tag marquant un fichier qui résulte d'un deuxième dépôt (après correction).
     *
     * 'V2' = Deuxième dépôt (après corrections)
     */
    const TAG_DEUXIEME_DEPOT = 'V2';

    const RESOURCE_ID = 'Fichier';

    const MESSAGE_RETRAITEMENT_DUREE = "L'opération de retraitement automatique de fichier peut durer quelques minutes.";
    const MESSAGE_DEPOT_DUREE = "L'opération de téléversement de fichier peut durer quelques minutes.";

    const RETRAITEMENT_AUTO = 'SoDoct';
    const RETRAITEMENT_MANU = 'Inconnu';

    const MIME_TYPE_PDF = 'application/pdf';

    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $nom;

    /**
     * @var string
     */
    private $nomOriginal;

    /**
     * @var float
     */
    private $taille;

    /**
     * @var string
     */
    private $typeMime;

    /**
     * @var string
     */
    private $contenu;

    /**
     * @var string
     */
    private $description;

    /**
     * @var bool
     */
    private $estAnnexe = false;

    /**
     * @var string
     */
    private $retraitement;

    /**
     * @var bool
     */
    private $estExpurge = false;

    /**
     * @var null|int
     */
    private $estConforme = null;

    /**
     * @var These
     */
    private $these;

    /**
     * @var NatureFichier
     */
    private $nature;

    /**
     * @var VersionFichier
     */
    private $version;

    /**
     * @var Collection
     */
    private $validites;

    /**
     * Fichier constructor.
     *
     * Génère un UUID en guise de clé primaire.
     */
    public function __construct()
    {
        $this->id = Uuid::uuid4()->toString();
        $this->validites = new ArrayCollection();
    }

    /**
     * Représentation littérale de cet objet.
     * 
     * @return string
     */
    public function __toString()
    {
        $string = sprintf("%s - Fichier '%s'",
                $this->getTypeMime(),
                $this->getNom());
        
        return $string;
    }

    /**
     *
     *
     * @param      $filePath
     * @param bool $overwrite
     */
    public function exportToFile($filePath, $overwrite = false)
    {
        if (file_exists($filePath) && ! $overwrite) {
            throw new \RuntimeException("Le fichier suivant existe déjà : " . $filePath);
        }

        $contenu = $this->getContenu();
        $content = is_resource($contenu) ? stream_get_contents($contenu) : $contenu;

        file_put_contents($filePath, $content);
    }

    /**
     * Détermine si ce fichier peut faire l'objet d'un test de validité (i.e. compatibilité STAR).
     *
     * @return bool
     */
    public function supporteTestValidite()
    {
        return ! $this->getEstAnnexe() && ! $this->getEstExpurge();
    }

    /**
     * Détermine si ce fichier peut faire l'objet d'un aperçu.
     *
     * @return bool
     */
    public function supporteApercu()
    {
        return $this->getTypeMime() === 'application/pdf';
    }

    /**
     * Set contenu
     *
     * @param string $contenu
     * @return self
     */
    public function setContenu($contenu)
    {
        $this->contenu = $contenu;

        return $this;
    }

    /**
     * Get contenu
     *
     * @return string 
     */
    public function getContenu()
    {
        return $this->contenu;
    }

    /**
     * Set nom
     *
     * @param string $nom
     * @return self
     */
    public function setNom($nom)
    {
        $this->nom = $nom;

        return $this;
    }

    /**
     * Get nom
     *
     * @return string 
     */
    public function getNom()
    {
        return $this->nom;
    }

    /**
     * Set nomOriginal
     *
     * @param string $nomOriginal
     * @return self
     */
    public function setNomOriginal($nomOriginal)
    {
        $this->nomOriginal = $nomOriginal;

        return $this;
    }

    /**
     * Get nomOriginal
     *
     * @return string
     */
    public function getNomOriginal()
    {
        return $this->nomOriginal;
    }

    /**
     * Set taille
     *
     * @param float $taille
     * @return self
     */
    public function setTaille($taille)
    {
        $this->taille = $taille;

        return $this;
    }

    /**
     * Get taille
     *
     * @return float 
     */
    public function getTaille()
    {
        return $this->taille;
    }

    /**
     * Get taille
     *
     * @return string 
     */
    public function getTailleToString()
    {
        $f = new BytesFormatter();
        
        return $f->filter($this->getTaille());
    }

    /**
     * Get id
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set type
     *
     * @param string $typeMime
     * @return self
     */
    public function setTypeMime($typeMime = null)
    {
        $this->typeMime = $typeMime;

        return $this;
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getTypeMime()
    {
        return $this->typeMime;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return self
     */
    public function setDescription($description = null)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return boolean
     * @deprecated Exploiter la NatureFichier
     */
    public function getEstAnnexe()
    {
        return $this->estAnnexe;
    }

    /**
     * @param boolean $estAnnexe
     * @return self
     * @deprecated Exploiter la NatureFichier
     */
    public function setEstAnnexe($estAnnexe = true)
    {
        $this->estAnnexe = $estAnnexe;

        return $this;
    }

    /**
     * @return bool
     */
    public function isRetraitementAuto()
    {
        return $this->getRetraitement() === self::RETRAITEMENT_AUTO;
    }

    /**
     * @return bool
     */
    public function isRetraitementManuel()
    {
        return $this->getRetraitement() === self::RETRAITEMENT_MANU;
    }

    /**
     * @return string
     */
    public function getRetraitement()
    {
        return $this->retraitement;
    }

    /**
     * @param string $retraitement
     * @return self
     */
    public function setRetraitement($retraitement)
    {
        $this->retraitement = $retraitement;

        return $this;
    }

    /**
     * @return boolean
     * @deprecated Redondant avec la version du fichier (version de diffusion)
     */
    public function getEstExpurge()
    {
        return $this->estExpurge;
    }

    /**
     * @param boolean $estExpurge
     * @return self
     * @deprecated Redondant avec la version du fichier (version de diffusion)
     */
    public function setEstExpurge($estExpurge = true)
    {
        $this->estExpurge = $estExpurge;

        return $this;
    }

    /**
     * Retourne :
     * - <code>1</code> si le thésard a certifié que le fichier de thèse était conforme ;
     * - <code>0</code> si le thésard a certifié que le fichier de thèse n'était PAS conforme ;
     * - <code>null</code> si le thésard n'a pas encore répondu à la question.
     *
     * @return null|int
     */
    public function getEstConforme()
    {
        return $this->estConforme;
    }

    /**
     * @return bool|null
     */
    public function getEstConformeToString()
    {
        if ($this->getEstConforme() === null) {
            return "";
        }

        return $this->getEstConforme() ? "Oui" : "Non";
    }

    /**
     * @param null|int $estConforme
     * @return self
     */
    public function setEstConforme($estConforme = null)
    {
        $this->estConforme = $estConforme;

        return $this;
    }

    /**
     * @return These
     */
    public function getThese()
    {
        return $this->these;
    }

    /**
     * @param These $these
     * @return self
     */
    public function setThese($these)
    {
        $this->these = $these;

        return $this;
    }

    /**
     * @return NatureFichier
     */
    public function getNature()
    {
        return $this->nature;
    }

    /**
     * @param NatureFichier $nature
     * @return static
     */
    public function setNature(NatureFichier $nature)
    {
        $this->nature = $nature;

        $this->setEstAnnexe($nature->estFichierNonPdf());

        return $this;
    }

    /**
     * @return VersionFichier
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @param VersionFichier $version
     * @return self
     */
    public function setVersion(VersionFichier $version)
    {
        $this->version = $version;

        return $this;
    }

    /**
     * @return ValiditeFichier|null
     */
    public function getValidite()
    {
        return $this->validites->first() ?: null; // NB: relation 'validites' triée par histoModification DESC
    }

    /**
     * @return Collection
     */
    public function getValidites()
    {
        return $this->validites;
    }

    /**
     * Ce fichier est-il archivable ?
     *
     * @return bool|null
     * <code>true</code>  : fichier jugé archivable ;
     * <code>false</code> : fichier jugé non archivable ;
     * <code>null</code>  : archivabilité indéterminée (test d'archivabilité non effectué, ou plantage lors du test).
     */
    public function estArchivable()
    {
        if ($validite = $this->getValidite()) {
            return $validite->getEstValide();
        }

        return null;
    }

    /**
     * @return bool|null
     */
    public function estArchivableToString()
    {
        if ($this->estArchivable() === null) {
            return "";
        }

        return $this->estArchivable() ? "Oui" : "Non";
    }

    /**
     * @param ValiditeFichier $validiteFichier
     * @return $this
     */
    public function addValidite(ValiditeFichier $validiteFichier)
    {
        $this->validites->add($validiteFichier);
        return $this;
    }

    /**
     * @param ValiditeFichier $validiteFichier
     * @return $this
     */
    public function removeValidite(ValiditeFichier $validiteFichier)
    {
        $this->validites->removeElement($validiteFichier);
        return $this;
    }




    /**
     * Retourne la date de dépôt du fichier.
     *
     * @return DateTime
     */
    public function getDate()
    {
        return $this->getHistoModification();
    }




    /**
     * Ecriture (du contenu) de ce fichier sur le disque.
     *
     * @param string  $filePath Eventuel chemin du fichier à créer
     * @return string Chemin vers le fichier créé
     */
    public function writeFichierToDisk($filePath = null)
    {
        // création du fichier temporaire sur le disque à partir de la bdd
        $contenu = $this->getContenu();
        $content = is_resource($contenu) ? stream_get_contents($contenu) : $contenu;

        $tmpDir = sys_get_temp_dir();
        if (! $filePath) {
            $filePath = $tmpDir . '/' . uniqid('sodoct-') . '-' . $this->getNom();
        }
        file_put_contents($filePath, $content);

        return $filePath;
    }



    /**
     * Returns the string identifier of the Resource
     *
     * @return string
     */
    public function getResourceId()
    {
        return self::RESOURCE_ID;
    }
}




class FichierFiltering
{
    /**
     * @param int|bool $estExpurge
     * @return \Closure
     */
    static public function getFilterByExpurge($estExpurge = true)
    {
        return function(Fichier $fichier = null) use ($estExpurge) {
            if ($estExpurge === null) {
                return $fichier;
            }
            if ($fichier === null) {
                return null;
            }

            $actual = $fichier->getEstExpurge();
            $expected = (bool) $estExpurge;

            return $actual === $expected ? $fichier : null;
        };
    }

    /**
     * @param int|bool $estAnnexe
     * @return \Closure
     */
    static public function getFilterByAnnexe($estAnnexe = true)
    {
        return function(Fichier $fichier = null) use ($estAnnexe) {
            if ($estAnnexe === null) {
                return $fichier;
            }
            if ($fichier === null) {
                return null;
            }

            $actual = $fichier->getEstAnnexe();
            $expected = (bool) $estAnnexe;

            return $actual === $expected ? $fichier : null;
        };
    }

    /**
     * @param int|bool|string $estRetraite '0', '1', booléen ou code du retraitement
     * @return \Closure
     */
    static public function getFilterByRetraitement($estRetraite = true)
    {
        return function(Fichier $fichier = null) use ($estRetraite) {
            if ($estRetraite === null) {
                return $fichier;
            }
            if ($fichier === null) {
                return null;
            }

            $actual = $fichier->getRetraitement();
            $expected = $estRetraite;

            if (is_numeric($expected) || is_bool($expected)) {
                $expected = (bool) $estRetraite;
                $actual = (bool) $actual;
            }

            return $actual === $expected ? $fichier : null;
        };
    }

    /**
     * @param NatureFichier|string $nature
     * @return \Closure
     */
    static public function getFilterByNature($nature = null)
    {
        return function(Fichier $fichier = null) use ($nature) {
            if ($nature === null) {
                return $fichier;
            }
            if ($fichier === null) {
                return null;
            }

            if ($nature instanceof NatureFichier) {
                $nature = $nature->getCode();
            }

            return $nature === $fichier->getNature()->getCode() ? $fichier : null;
        };
    }

    /**
     * @param VersionFichier|string $version
     * @return \Closure
     */
    static public function getFilterByVersion($version = null)
    {
        return function(Fichier $fichier = null) use ($version) {
            if ($version === null) {
                return $fichier;
            }
            if ($fichier === null) {
                return null;
            }

            if ($version instanceof VersionFichier) {
                $version = $version->getCode();
            }

            return $version === $fichier->getVersion()->getCode() ? $fichier : null;
        };
    }
}