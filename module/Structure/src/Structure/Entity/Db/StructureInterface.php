<?php

namespace Structure\Entity\Db;

use Application\Entity\Db\Source;

interface StructureInterface
{
    /**
     * @return string
     */
    public function getId();

    /**
     * @return string
     */
    public function getSigle();

    /**
     * @return string
     */
    public function getSourceCode();

    /**
     * @return string|null
     */
    public function getCode(): ?string;

    /**
     * @return string
     */
    public function getLibelle();

    /**
     * SPécifie le Nom du fichier (pas le chemin!)
     *
     * @param string $cheminLogo
     */
    public function setCheminLogo($cheminLogo);

    /**
     * Retourne le Nom du fichier (pas le chemin!)
     *
     * @return string
     */
    public function getCheminLogo();

    /**
     * @return Source
     */
    public function getSource();

    /**
     * @return string
     */
    public function __toString();
}