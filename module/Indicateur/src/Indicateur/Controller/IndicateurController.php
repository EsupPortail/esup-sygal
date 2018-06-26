<?php

namespace Indicateur\Controller;

use Application\Entity\Db\Acteur;
use Application\Entity\Db\These;
use Application\Service\AnomalieServiceAwareTrait;
use Application\Service\Etablissement\EtablissementServiceAwareTrait;
use Application\Service\Individu\IndividuServiceAwareTrait;
use Application\Service\These\TheseServiceAwareTrait;
use UnicaenApp\View\Model\CsvModel;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class IndicateurController extends AbstractActionController {
    use IndividuServiceAwareTrait;
    use TheseServiceAwareTrait;
    use EtablissementServiceAwareTrait;
    use AnomalieServiceAwareTrait;

    public function indexAction()
    {
        $acteursSansMail = $this->getIndividuService()->getActeurSansMail();
        $theses = $this->getTheseService()->getTheseEnCoursPostSoutenance();
        $anomalies = $this->getAnomalieService()->getAnomalies();

        $etablissements = [];
        $etablissements[] = $this->getEtablissementService()->getEtablissementById(2);
        $etablissements[] = $this->getEtablissementService()->getEtablissementById(3);
        $etablissements[] = $this->getEtablissementService()->getEtablissementById(4);
        $etablissements[] = $this->getEtablissementService()->getEtablissementById(5);

        $effectifs = [];
        $effectifs["COMUE"] = $this->getTheseService()->getEffectifs();
        foreach($etablissements as $etablissement) {
            $result = $this->getTheseService()->getEffectifs($etablissement);
            $effectifs[$etablissement->getStructure()->getCode()] = $result;
        }

        return new ViewModel([
                "effectifs" => $effectifs,
                "theses" => $theses,
                "sansMail" => $acteursSansMail,
                "anomalies" => $anomalies,
            ]
        );
    }

    /**
     * faire remonter les thèses ayant en cours ayant une date de soutenance dépassée
     * @return ViewModel
     */
    public function soutenanceDepasseeAction()
    {
        $theses = $this->getTheseService()->getTheseEnCoursPostSoutenance();
        return new ViewModel([
                'theses' => $theses,
            ]
        );
    }

    public function exportSoutenanceDepasseeAction()
    {
        $data = $this->getTheseService()->getTheseEnCoursPostSoutenance();
        $headers = [
            'Identitfiant'                    => function(These $these) {return $these->getId();},
            'SourceCode'                      => function(These $these) {return $these->getSourceCode();},
            'Titre'                           => function(These $these) {return $these->getTitre();},
            'Doctorant'                       => function(These $these) {return $these->getDoctorant()->getIndividu()->getNomComplet();},
            'État'                            => function(These $these) {return $these->getEtatThese();},
            'Date de soutenace'               => function(These $these) {return $these->getDateSoutenance()->format("d//m/Y");},
            'Établissement'                   => function(These $these) {return ($these->getEtablissement())?$these->getEtablissement()->getStructure()->getCode():"";},
            'École doctorale'                 => function(These $these) {return ($these->getEcoleDoctorale())?$these->getEcoleDoctorale()->getStructure()->getCode():"";},
            'Unité de recherche'              => function(These $these) {return ($these->getUniteRecherche())?$these->getUniteRecherche()->getStructure()->getCode():"";},

        ];

        $records = [];
        foreach ($data as $entry) {
            $record = [];
            foreach($headers as $key => $fct) {
                $record[] = $fct($entry);
            }
            $records[] = $record;
        }

        $CSV = new CsvModel();
        $CSV->setDelimiter(';');
        $CSV->setEnclosure('"');
        $CSV->setHeader(array_keys($headers));
        $CSV->setData($records);
        $CSV->setFilename('export_soutenanceDepassee.csv');

        return $CSV;
    }

    public function acteursSansMailAction()
    {
        $acteursSansMail = $this->getIndividuService()->getActeurSansMail();

        return new ViewModel([
                "sansMail" => $acteursSansMail,
            ]
        );
    }

    public function exportActeursSansMailAction()
    {
        $acteursSansMail = $this->getIndividuService()->getActeurSansMail();

        $headers = [
            'Thèse Identitfiant'                    => function(Acteur $acteur) {return $acteur->getThese()->getId();},
            'Thèse SourceCode'                      => function(Acteur $acteur) {return $acteur->getThese()->getSourceCode();},
            'Thèse Titre'                           => function(Acteur $acteur) {return $acteur->getThese()->getTitre();},
            'Thèse Établissement'                   => function(Acteur $acteur) {return ($acteur->getThese()->getEtablissement())?$acteur->getThese()->getEtablissement()->getStructure()->getCode():"";},
            'Thèse École doctorale'                 => function(Acteur $acteur) {return ($acteur->getThese()->getEcoleDoctorale())?$acteur->getThese()->getEcoleDoctorale()->getStructure()->getCode():"";},
            'Thèse Unité de recherche'              => function(Acteur $acteur) {return ($acteur->getThese()->getUniteRecherche())?$acteur->getThese()->getUniteRecherche()->getStructure()->getCode():"";},
            'Acteur Identifiant'                    => function(Acteur $acteur) {return $acteur->getId();},
            'Acteur Nom'                            => function(Acteur $acteur) {return $acteur->getIndividu()->getNomComplet();},
            'Acteur Role'                           => function(Acteur $acteur) {return $acteur->getRole()->getLibelle();},
        ];

        $records = [];
        foreach ($acteursSansMail as $acteur) {
            $record = [];
            foreach($headers as $key => $fct) {
                $record[] = $fct($acteur);
            }
            $records[] = $record;
        }

        $CSV = new CsvModel();
        $CSV->setDelimiter(';');
        $CSV->setEnclosure('"');
        $CSV->setHeader(array_keys($headers));
        $CSV->setData($records);
        $CSV->setFilename('export_sansMail.csv');

        return $CSV;
    }
}

