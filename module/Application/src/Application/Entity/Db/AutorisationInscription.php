<?php

namespace Application\Entity\Db;

use Application\Entity\AnneeUniv;
use Laminas\Permissions\Acl\Resource\ResourceInterface;
use UnicaenUtilisateur\Entity\Db\HistoriqueAwareInterface;
use UnicaenUtilisateur\Entity\Db\HistoriqueAwareTrait;

/**
 * AutorisationInscription
 */
class AutorisationInscription implements ResourceInterface, HistoriqueAwareInterface
{
    use HistoriqueAwareTrait;
    /**
     * @var int
     */
    private $anneeUniv;

    /**
     * @var bool
     */
    private $autorisationInscription;

    /**
     * @var string|null
     */
    private $commentaires;

    /**
     * @var int
     */
    private $id;

    /**
     * @var \Individu\Entity\Db\Individu
     */
    private $individu;

    /**
     * @var \These\Entity\Db\These
     */
    private $these;

    /**
     * @var Rapport
     */
    private $rapport;

    /**
     * Set anneeUniv.
     *
     * @param int $anneeUniv
     *
     * @return AutorisationInscription
     */
    public function setAnneeUniv($anneeUniv)
    {
        $this->anneeUniv = $anneeUniv;

        return $this;
    }

    /**
     * @return AnneeUniv
     */
    public function getAnneeUniv(): AnneeUniv
    {
        return AnneeUniv::fromPremiereAnnee($this->anneeUniv);
    }

    /**
     * Set autorisationInscription.
     *
     * @param bool $autorisationInscription
     *
     * @return AutorisationInscription
     */
    public function setAutorisationInscription(bool $autorisationInscription)
    {
        $this->autorisationInscription = $autorisationInscription;

        return $this;
    }

    /**
     * Get autorisationInscription.
     *
     * @return bool
     */
    public function getAutorisationInscription()
    {
        return $this->autorisationInscription;
    }

    /**
     * Set commentaires.
     *
     * @param string|null $commentaires
     *
     * @return AutorisationInscription
     */
    public function setCommentaires($commentaires = null)
    {
        $this->commentaires = $commentaires;

        return $this;
    }

    /**
     * Get commentaires.
     *
     * @return string|null
     */
    public function getCommentaires()
    {
        return $this->commentaires;
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set individu.
     *
     * @param \Individu\Entity\Db\Individu|null $individu
     *
     * @return AutorisationInscription
     */
    public function setIndividu(\Individu\Entity\Db\Individu $individu = null)
    {
        $this->individu = $individu;

        return $this;
    }

    /**
     * Get individu.
     *
     * @return \Individu\Entity\Db\Individu|null
     */
    public function getIndividu()
    {
        return $this->individu;
    }

    /**
     * Set these.
     *
     * @param \These\Entity\Db\These|null $these
     *
     * @return AutorisationInscription
     */
    public function setThese(\These\Entity\Db\These $these = null)
    {
        $this->these = $these;

        return $this;
    }

    /**
     * Get these.
     *
     * @return \These\Entity\Db\These|null
     */
    public function getThese()
    {
        return $this->these;
    }

    /**
     * Set rapport.
     *
     * @param Rapport|null $rapport
     *
     * @return AutorisationInscription
     */
    public function setRapport(Rapport $rapport = null)
    {
        $this->rapport = $rapport;

        return $this;
    }

    /**
     * Get rapport.
     *
     * @return Rapport|null
     */
    public function getRapport()
    {
        return $this->rapport;
    }

    public function getResourceId()
    {
        return "AutorisationInscription";
    }
}
