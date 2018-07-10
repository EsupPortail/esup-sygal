<?php

namespace Import\Model;

/**
 * TmpThese
 */
class TmpThese
{
    /**
     * @var string
     */
    private $sourceId;

    /**
     * @var string
     */
    private $etablissementId;

    /**
     * @var string
     */
    private $doctorantId;

    /**
     * @var string
     */
    private $ecoleDoctId;

    /**
     * @var string
     */
    private $uniteRechId;

    /**
     * @var string
     */
    private $title;

    /**
     * @var \DateTime
     */
    private $dateSoutenanceAutorisee;

    /**
     * @var \DateTime
     */
    private $dateConfidFin;

    /**
     * @var \DateTime
     */
    private $datePremiereInsc;

    /**
     * @var \DateTime
     */
    private $dateSoutenancePrev;

    /**
     * @var \DateTime
     */
    private $dateSoutenance;

    /**
     * @var string
     */
    private $etatThese;

    /**
     * @var string
     */
    private $libDiscipline;

    /**
     * @var string
     */
    private $libEtabCotut;

    /**
     * @var string
     */
    private $libPaysCotut;

    /**
     * @var string
     */
    private $correctionAutorisee;

    /**
     * @var string
     */
    private $resultat;

    /**
     * @var string
     */
    private $temAvenant;

    /**
     * @var string
     */
    private $temSoutenanceAutorisee;

    /**
     * @var string
     */
    private $sourceCode;

    /**
     * @var string
     */
    private $id;

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getDoctorantId()
    {
        return $this->doctorantId;
    }

    /**
     * @return string
     */
    public function getEcoleDoctId()
    {
        return $this->ecoleDoctId;
    }

    /**
     * @return string
     */
    public function getUniteRechId()
    {
        return $this->uniteRechId;
    }
}

