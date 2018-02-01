<?php

namespace Import\Model;

class TmpThese {
    protected $id;
    protected $sourceId;
    protected $etatThese;
    protected $doctorantId;
    protected $codeDiscipline;
    protected $libDiscipline;
    protected $title;
    protected $codeLNG;
    protected $datePremiereInsc;
    protected $uniteRechId;
    protected $ecoleDoctId;
    protected $libEtabCotut;
    protected $libPaysCotut;
    protected $temAvenant;
    protected $dateSoutenancePrev;
    protected $temSoutenanceAutorisee;
    protected $dateSoutenanceAutorisee;
    protected $dateSoutenance;
    protected $dateConfidFin;
    protected $resultat;
    protected $etatReporduction;
    protected $correctionAutorisee;
    protected $etablissementId;
    protected $sourceCode;

    public function __construct($json) {
        $this->id                           = $json->{'id'};
        $this->sourceId                     = $json->{'sourceId'};
        $this->etatThese                    = $json->{'etatThese'};
        $this->doctorantId                  = $json->{'doctorantId'};
        $this->codeDiscipline               = $json->{'codeDiscipline'};
        $this->libDiscipline                = $json->{'libDiscipline'};
        $this->title                        = $json->{'title'};
        $this->codeLNG                      = $json->{'codeLNG'};
        $this->datePremiereInsc             = ($json->{'datePremiereInsc'} !== null && $json->{'datePremiereInsc'}->{'date'} !== null) ? new \DateTime($json->{'datePremiereInsc'}->{'date'}) : null ;
        $this->uniteRechId                  = $json->{'uniteRechId'};
        $this->ecoleDoctId                  = $json->{'ecoleDoctId'};
        $this->libEtabCotut                 = $json->{'libEtabCotut'};
        $this->libPaysCotut                 = $json->{'libPaysCotut'};
        $this->temAvenant                   = $json->{'temAvenant'};
        $this->dateSoutenancePrev           = ($json->{'dateSoutenancePrev'} !== null && $json->{'dateSoutenancePrev'}->{'date'} !== null) ? new \DateTime($json->{'dateSoutenancePrev'}->{'date'}) : null ;
        $this->temSoutenanceAutorisee       = $json->{'temSoutenanceAutorisee'};
        $this->dateSoutenanceAutorisee      = ($json->{'dateSoutenanceAutorisee'} !== null && $json->{'dateSoutenanceAutorisee'}->{'date'} !== null) ? new \DateTime($json->{'dateSoutenanceAutorisee'}->{'date'}) : null ;
        $this->dateSoutenance               = ($json->{'dateSoutenance'} !== null && $json->{'dateSoutenance'}->{'date'} !== null) ? new \DateTime($json->{'dateSoutenance'}->{'date'}) : null ;
        $this->dateConfidFin                = ($json->{'dateConfidFin'} !== null && $json->{'dateConfidFin'}->{'date'} !== null) ? new \DateTime($json->{'dateConfidFin'}->{'date'}) : null ;
        $this->resultat                     = $json->{'resultat'};
        $this->etatReporduction             = $json->{'etatReporduction'};
        $this->correctionAutorisee          = $json->{'correctionAutorisee'};
    }

}