<?php

namespace Fichier\Controller\Plugin\Uploader;

/**
 * Interface décrivant les caractéristiques obligatoires d'un fichier 
 * ayant fait l'objet d'un dépôt (upload).
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
interface UploadedFileInterface extends \UnicaenApp\Controller\Plugin\Upload\UploadedFileInterface
{
    /**
     * Retourne la date de dépôt du fichier au format lisible.
     */
    public function getDateToString(): string;
}