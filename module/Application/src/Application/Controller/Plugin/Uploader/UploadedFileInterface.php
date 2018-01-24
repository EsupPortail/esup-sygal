<?php

namespace Application\Controller\Plugin\Uploader;

use DateTime;

/**
 * Interface décrivant les caractéristiques obligatoires d'un fichier 
 * ayant fait l'objet d'un dépôt (upload).
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
interface UploadedFileInterface extends \UnicaenApp\Controller\Plugin\Upload\UploadedFileInterface
{
    /**
     * Retourne le contenu du fichier.
     *
     * @return string 
     */
    public function getContenu();

    /**
     * Retourne le nom du fichier, ex: "image.png".
     *
     * @return string 
     */
    public function getNom();

    /**
     * Retourne la taille exacte du fichier au format numérique.
     *
     * @return float 
     */
    public function getTaille();

    /**
     * Retourne la taille du fichier dans un format lisible, ex: "1,12 Mo".
     *
     * @return string 
     */
    public function getTailleToString();

    /**
     * Retourne l'identifiant unique du fichier.
     *
     * @return mixed 
     */
    public function getId();

    /**
     * Retourne le type MIME du fichier, ex: "image/png".
     *
     * @return string
     */
    public function getTypeMime();

    /**
     * Retourne la date de dépôt du fichier.
     *
     * @return DateTime
     */
    public function getDate();
}