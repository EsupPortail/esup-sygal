<?php

namespace Application\Entity\Db;

use Application\Entity\Db\Source;

interface StructureConcreteInterface
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
     * @return string
     */
    public function getCheminLogo();

    /**
     * @return Source
     */
    public function getSource();

    /**
     * @return Structure
     */
    public function getStructure();

    /**
     * @return string
     */
    public function __toString();




}