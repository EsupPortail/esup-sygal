<?php

namespace Application\Entity\Db;

use UnicaenApp\Util;

/**
 * Etablissement
 */
class Etablissement extends Structure
{
    const CODE_COMUE = 'COMUE';

    protected $id;
    protected $code;
    protected $theses;
    protected $doctorants;
    protected $roles;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param mixed $code
     */
    public function setCode($code)
    {
        $this->code = $code;
    }

    /**
     * @return mixed
     */
    public function getTheses()
    {
        return $this->theses;
    }

    /**
     * @param mixed $theses
     */
    public function setTheses($theses)
    {
        $this->theses = $theses;
    }

    /**
     * @return mixed
     */
    public function getDoctorants()
    {
        return $this->doctorants;
    }

    /**
     * @param mixed $doctorants
     */
    public function setDoctorants($doctorants)
    {
        $this->doctorants = $doctorants;
    }

    /**
     * @return mixed
     */
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * @param mixed $roles
     */
    public function setRoles($roles)
    {
        $this->roles = $roles;
    }

    public function getLogoContent()
    {
        if ($this->cheminLogo === null) {
            $image = Util::createImageWithText("Aucun logo pour l'Etab|" . $this->getCode() . "|" . $this->getLibelle(), 200, 200);
            return $image;
        }
        return file_get_contents(APPLICATION_DIR . $this->cheminLogo);

    }

}