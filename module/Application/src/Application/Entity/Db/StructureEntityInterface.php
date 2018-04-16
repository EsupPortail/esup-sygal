<?php

namespace Application\Entity\Db;

interface StructureEntityInterface
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
    public function getLibelle();

    /**
     * @return string
     */
    public function getCheminLogo();

    /**
     * @return string
     */
    public function __toString();
}