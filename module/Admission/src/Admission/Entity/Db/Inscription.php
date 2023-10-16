<?php
namespace Admission\Entity\Db;

use Structure\Entity\Db\EcoleDoctorale;
use Structure\Entity\Db\Structure;
use Structure\Entity\Db\UniteRecherche;
use UnicaenApp\Entity\HistoriqueAwareInterface;
use UnicaenApp\Entity\HistoriqueAwareTrait;

class Inscription implements HistoriqueAwareInterface{

    use HistoriqueAwareTrait;

    /**
     * @var string|null
     */
    private $disciplineDoctorat;

    /**
     * @var string|null
     */
    private $nomDirecteurThese;

    /**
     * @var string|null
     */
    private $nomCodirecteurThese;

    /**
     * @var string|null
     */
    private $titreThese;

    /**
     * @var \DateTime|null
     */
    private $dateConfidentialite;

    /**
     * @var bool|null
     */
    private $coTutelle;

    /**
     * @var string|null
     */
    private $paysCoTutelle;

    /**
     * @var bool|null
     */
    private $coEncadrement;

    /**
     * @var bool|null
     */
    private $coDirection;

    /**
     * @var Structure
     */
    private $composanteDoctorat;

    /**
     * @var EcoleDoctorale
     */
    private $ecoleDoctorale;

    /**
     * @var UniteRecherche
     */
    private $uniteRecherche;

    /**
     * @var bool|null
     */
    private $confidentialite;

    /**
     * @var int
     */
    private ?int $id = null;

    /**
     * @var Admission
     */
    private $admission;

    /**
     * Set disciplineDoctorat.
     *
     * @param string|null $disciplineDoctorat
     *
     * @return Inscription
     */
    public function setDisciplineDoctorat($disciplineDoctorat = null)
    {
        $this->disciplineDoctorat = $disciplineDoctorat;

        return $this;
    }

    /**
     * Get disciplineDoctorat.
     *
     * @return string|null
     */
    public function getDisciplineDoctorat()
    {
        return $this->disciplineDoctorat;
    }

    /**
     * Set nomDirecteurThese.
     *
     * @param string|null $nomDirecteurThese
     *
     * @return Diplome
     */
    public function setNomDirecteurThese($nomDirecteurThese = null)
    {
        $this->nomDirecteurThese = $nomDirecteurThese;

        return $this;
    }

    /**
     * Get nomDirecteurThese.
     *
     * @return string|null
     */
    public function getNomDirecteurThese()
    {
        return $this->nomDirecteurThese;
    }

    /**
     * Set nomCodirecteurThese.
     *
     * @param string|null $nomCodirecteurThese
     *
     * @return Diplome
     */
    public function setNomCodirecteurThese($nomCodirecteurThese = null)
    {
        $this->nomCodirecteurThese = $nomCodirecteurThese;

        return $this;
    }

    /**
     * Get nomCodirecteurThese.
     *
     * @return string|null
     */
    public function getNomCodirecteurThese()
    {
        return $this->nomCodirecteurThese;
    }

    /**
     * Set titreThese.
     *
     * @param string|null $titreThese
     *
     * @return Inscription
     */
    public function setTitreThese($titreThese = null)
    {
        $this->titreThese = $titreThese;

        return $this;
    }

    /**
     * Get titreThese.
     *
     * @return string|null
     */
    public function getTitreThese()
    {
        return $this->titreThese;
    }

    /**
     * Set confidentialite.
     *
     * @param bool|null $confidentialite
     *
     * @return Inscription
     */
    public function setConfidentialite($confidentialite = null)
    {
        $this->confidentialite = $confidentialite;

        return $this;
    }

    /**
     * Get confidentialite.
     *
     * @return bool|null
     */
    public function getConfidentialite()
    {
        return $this->confidentialite;
    }

    /**
     * Set dateConfidentialite.
     *
     * @param \DateTime|null $dateConfidentialite
     *
     * @return Inscription
     */
    public function setDateConfidentialite($dateConfidentialite = null)
    {
        $this->dateConfidentialite = $dateConfidentialite;

        return $this;
    }

    /**
     * Get dateConfidentialite.
     *
     * @return \DateTime|null
     */
    public function getDateConfidentialite()
    {
        return $this->dateConfidentialite;
    }

    /**
     * Set coTutelle.
     *
     * @param bool|null $coTutelle
     *
     * @return Inscription
     */
    public function setCoTutelle($coTutelle = null)
    {
        $this->coTutelle = $coTutelle;

        return $this;
    }

    /**
     * Get coTutelle.
     *
     * @return bool|null
     */
    public function getCoTutelle()
    {
        return $this->coTutelle;
    }

    /**
     * Set paysCoTutelle.
     *
     * @param string|null $paysCoTutelle
     *
     * @return Inscription
     */
    public function setPaysCoTutelle($paysCoTutelle = null)
    {
        $this->paysCoTutelle = $paysCoTutelle;

        return $this;
    }

    /**
     * Get paysCoTutelle.
     *
     * @return string|null
     */
    public function getPaysCoTutelle()
    {
        return $this->paysCoTutelle;
    }

    /**
     * Set coEncadrement.
     *
     * @param bool|null $coEncadrement
     *
     * @return Inscription
     */
    public function setCoEncadrement($coEncadrement = null)
    {
        $this->coEncadrement = $coEncadrement;

        return $this;
    }

    /**
     * Get coEncadrement.
     *
     * @return bool|null
     */
    public function getCoEncadrement()
    {
        return $this->coEncadrement;
    }

    /**
     * Set coDirection.
     *
     * @param bool|null $coDirection
     *
     * @return Inscription
     */
    public function setCoDirection($coDirection = null)
    {
        $this->coDirection = $coDirection;

        return $this;
    }

    /**
     * Get coDirection.
     *
     * @return bool|null
     */
    public function getCoDirection()
    {
        return $this->coDirection;
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
     * Set composanteDoctorat.
     *
     * @param Structure|null $composanteDoctorat
     *
     * @return Inscription
     */
    public function setComposanteDoctorat(Structure $composanteDoctorat = null)
    {
        $this->composanteDoctorat = $composanteDoctorat;

        return $this;
    }

    /**
     * Get composanteDoctorat.
     *
     * @return Structure|null
     */
    public function getComposanteDoctorat()
    {
        return $this->composanteDoctorat;
    }

    /**
     * Set ecoleDoctorale.
     *
     * @param EcoleDoctorale|null $ecoleDoctorale
     *
     * @return Inscription
     */
    public function setEcoleDoctorale(EcoleDoctorale $ecoleDoctorale = null)
    {
        $this->ecoleDoctorale = $ecoleDoctorale;

        return $this;
    }

    /**
     * Get ecoleDoctorale.
     *
     * @return EcoleDoctorale|null
     */
    public function getEcoleDoctorale()
    {
        return $this->ecoleDoctorale;
    }

    /**
     * Set uniteRecherche.
     *
     * @param UniteRecherche|null $uniteRecherche
     *
     * @return Inscription
     */
    public function setUniteRecherche(UniteRecherche $uniteRecherche = null)
    {
        $this->uniteRecherche = $uniteRecherche;

        return $this;
    }

    /**
     * Get uniteRecherche.
     *
     * @return UniteRecherche|null
     */
    public function getUniteRecherche()
    {
        return $this->uniteRecherche;
    }

    /**
     * Set admission.
     *
     * @param \Admission\Entity\Db\Admission|null $admission
     *
     * @return Inscription
     */
    public function setAdmission(\Admission\Entity\Db\Admission $admission = null)
    {
        $this->admission = $admission;

        return $this;
    }

    /**
     * Get admission.
     *
     * @return \Admission\Entity\Db\Admission|null
     */
    public function getAdmission()
    {
        return $this->admission;
    }
}
