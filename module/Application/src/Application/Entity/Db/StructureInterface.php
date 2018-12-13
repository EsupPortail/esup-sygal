<?php

namespace Application\Entity\Db;

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
     * @return string
     */
    public function getCode();

    /**
     * @return string
     */
    public function getLibelle();

    /**
     * @param string $cheminLogo
     */
    public function setCheminLogo($cheminLogo);

    /**
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