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
 * Classe représentant un fichier générique.
 */
class Fichier implements HistoriqueAwareInterface, ResourceInterface, UploadedFileInterface
{
    use HistoriqueAwareTrait;

    const RESOURCE_ID = 'Fichier';

    const MIME_TYPE_PDF = 'application/pdf';

    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $uuid;

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
    private $description;

    /**
     * Contenu binaire de ce fichier.
     *
     * NB: utile uniquement pour le plugin Uploader.
     *
     * @var string
     */
    private $contenuFichierData;

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
     */
    public function __construct()
    {
        $this->uuid = Uuid::uuid4()->toString();
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
     * Spécifie le contenu binaire de ce fichier.
     *
     * NB: utile uniquement pour le plugin Uploader.
     *
     * @param string $contenuFichierData
     * @return string
     */
    public function setContenuFichierData($contenuFichierData)
    {
        return $this->contenuFichierData = $contenuFichierData;
    }

    /**
     * Retourne le contenu binaire de ce fichier.
     *
     * NB: utile uniquement pour le plugin Uploader.
     *
     * @return string
     * @see UploadedFileInterface
     */
    public function getContenu()
    {
        return $this->contenuFichierData;
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
     * @return string
     */
    public function getUuid()
    {
        return $this->uuid;
    }

    /**
     * Get first part of uuid4 hash.
     *
     * @return string
     */
    public function getShortUuid()
    {
        return strstr($this->uuid, '-', true);
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
     * Retourne la date de dépôt du fichier.
     *
     * @return DateTime
     */
    public function getDate()
    {
        return $this->getHistoModification();
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
