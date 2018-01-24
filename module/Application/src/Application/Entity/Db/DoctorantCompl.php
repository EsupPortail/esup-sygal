<?php

namespace Application\Entity\Db;

use UnicaenApp\Entity\HistoriqueAwareInterface;
use UnicaenApp\Entity\HistoriqueAwareTrait;

/**
 * DoctorantCompl
 */
class DoctorantCompl implements HistoriqueAwareInterface
{
    use HistoriqueAwareTrait;

    /**
     * @var string
     */
    private $persopass;

    /**
     * @var string
     */
    private $emailPro;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var Doctorant
     */
    private $doctorant;


    /**
     * Set persopass
     *
     * @param string $persopass
     *
     * @return DoctorantCompl
     */
    public function setPersopass($persopass)
    {
        $this->persopass = $persopass;

        return $this;
    }

    /**
     * Get persopass
     *
     * @return string
     */
    public function getPersopass()
    {
        return $this->persopass;
    }

    /**
     * @return string
     */
    public function getEmailPro()
    {
        return $this->emailPro;
    }

    /**
     * @param string $emailPro
     * @return DoctorantCompl
     */
    public function setEmailPro($emailPro)
    {
        $this->emailPro = $emailPro;

        return $this;
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set doctorant
     *
     * @param Doctorant $doctorant
     *
     * @return DoctorantCompl
     */
    public function setDoctorant(Doctorant $doctorant = null)
    {
        $this->doctorant = $doctorant;

        return $this;
    }

    /**
     * Get doctorant
     *
     * @return Doctorant
     */
    public function getDoctorant()
    {
        return $this->doctorant;
    }
}

