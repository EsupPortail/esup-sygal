<?php

namespace Indicateur\Controller;

use Application\Entity\Db\Acteur;
use Application\Entity\Db\These;
use Application\Service\AnomalieServiceAwareTrait;
use Application\Service\Etablissement\EtablissementServiceAwareTrait;
use Application\Service\Individu\IndividuServiceAwareTrait;
use Application\Service\These\TheseServiceAwareTrait;
use Indicateur\Service\IndicateurServiceAwareTrait;
use UnicaenApp\View\Model\CsvModel;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class IndicateurController extends AbstractActionController {
    use IndividuServiceAwareTrait;
    use TheseServiceAwareTrait;
    use EtablissementServiceAwareTrait;
    use AnomalieServiceAwareTrait;
    use IndicateurServiceAwareTrait;

    /**
     * @return array|ViewModel
     * @throws \Exception
     */
    public function indexAction()
    {
//        $acteursSansMail = $this->getIndividuService()->getActeurSansMail();
//        $doctorantsSansMail = $this->getIndividuService()->getDoctorantSansMail();
//        $theses = $this->getTheseService()->getTheseEnCoursPostSoutenance();
//        $thesesAnciennes = $this->getTheseService()->getThesesAnciennes(6);
//        $thesesASoutenir = $this->getTheseService()->getTheseASoutenir();
//        $pasPDC = $this->getTheseService()->getTheseSansCouverture(2);
//        $pasDepot = $this->getTheseService()->getTheseSansDepot(1);
//
//        $anomalies = $this->getAnomalieService()->getAnomalies();
//
//
//        $etablissements = [];
//        $etablissements[] = $this->getEtablissementService()->getRepository()->find(2);
//        $etablissements[] = $this->getEtablissementService()->getRepository()->find(3);
//        $etablissements[] = $this->getEtablissementService()->getRepository()->find(4);
//        $etablissements[] = $this->getEtablissementService()->getRepository()->find(5);
//
//        $effectifs = [];
//        $effectifs["COMUE"] = $this->getTheseService()->getEffectifs();
//        foreach($etablissements as $etablissement) {
//            $result = $this->getTheseService()->getEffectifs($etablissement);
//            $effectifs[$etablissement->getStructure()->getCode()] = $result;
//        }

        $indicateurs = $this->getIndicateurService()->findAll();
        $result_ind0 = $this->getIndicateurService()->fetch(1);
        $result_ind1 = $this->getIndicateurService()->fetch(2);
        $result_ind2 = $this->getIndicateurService()->fetch(3);
        $result_ind3 = $this->getIndicateurService()->fetch(4);
        $result_ind4 = $this->getIndicateurService()->fetch(5);


        return new ViewModel([
                'indicateurs' => $indicateurs,
                'result_ind0' => $result_ind0,
                'result_ind1' => $result_ind1,
                'result_ind2' => $result_ind2,
                'result_ind3' => $result_ind3,
                'result_ind4' => $result_ind4,


//                "effectifs" => $effectifs,

//                "thesesASoutenir" => $thesesASoutenir,
//                "acteursSansMail" => $acteursSansMail,
//                "doctorantsSansMail" => $doctorantsSansMail,
//                "anomalies" => $anomalies,
//                "pasPDC" => $pasPDC,
//                "pasDepot" => $pasDepot,
            ]
        );
    }

    /**
     * faire remonter les thèses ayant en cours ayant une date de soutenance dépassée
     * @return ViewModel
     */
    public function thesesASoutenirAction()
    {
        $theses = $this->getTheseService()->getTheseASoutenir();
        return new ViewModel([
                'theses' => $theses,
            ]
        );
    }

    public function exportThesesASoutenirAction()
    {
        $data = $this->getTheseService()->getTheseASoutenir();
        $headers = [
            'Identitfiant'                    => function(These $these) {return $these->getId();},
            'SourceCode'                      => function(These $these) {return $these->getSourceCode();},
            'Titre'                           => function(These $these) {return $these->getTitre();},
            'Doctorant'                       => function(These $these) {return $these->getDoctorant()->getIndividu()->getNomComplet();},
            'État'                            => function(These $these) {return $these->getEtatThese();},
            'Date de soutenace'               => function(These $these) {return $these->getDateSoutenance()->format("d/m/Y");},
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

    public function thesesSansPdcAction()
    {
        $theses = $this->getTheseService()->getTheseSansDepot(2);
        return new ViewModel([
                'theses' => $theses,
            ]
        );
    }

    public function exportThesesSansPdcAction()
    {
        $data = $this->getTheseService()->getTheseASoutenir();
        $headers = [
            'Identitfiant'                    => function(These $these) {return $these->getId();},
            'SourceCode'                      => function(These $these) {return $these->getSourceCode();},
            'Titre'                           => function(These $these) {return $these->getTitre();},
            'Doctorant'                       => function(These $these) {return $these->getDoctorant()->getIndividu()->getNomComplet();},
            'État'                            => function(These $these) {return $these->getEtatThese();},
            'Date de soutenace'               => function(These $these) {return $these->getDateSoutenance()->format("d/m/Y");},
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
        $CSV->setFilename('export_pasPDC.csv');

        return $CSV;
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
            'Date de soutenace'               => function(These $these) {return $these->getDateSoutenance()->format("d/m/Y");},
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

    /**
     * faire remonter les thèses ayant en cours ayant une date de soutenance dépassée
     * @return ViewModel
     */
    public function thesesSansDepotAction()
    {
        $theses = $this->getTheseService()->getTheseSansDepot(1);
        return new ViewModel([
                'theses' => $theses,
            ]
        );
    }

    public function exportThesesSansDepotAction()
    {
        $data = $this->getTheseService()->getTheseSansDepot(1);
        $headers = [
            'Identitfiant'                    => function(These $these) {return $these->getId();},
            'SourceCode'                      => function(These $these) {return $these->getSourceCode();},
            'Titre'                           => function(These $these) {return $these->getTitre();},
            'Doctorant'                       => function(These $these) {return $these->getDoctorant()->getIndividu()->getNomComplet();},
            'État'                            => function(These $these) {return $these->getEtatThese();},
            'Date de soutenace'               => function(These $these) {return $these->getDateSoutenance()->format("d/m/Y");},
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
        $CSV->setFilename('export_sansDepot.csv');

        return $CSV;
    }


    public function doctorantsSansMailAction()
    {
        $doctorantsSansMail = $this->getIndividuService()->getDoctorantSansMail();

        return new ViewModel([
                "sansMail" => $doctorantsSansMail,
            ]
        );
    }

    public function exportDoctorantsSansMailAction()
    {
        $doctorantsSansMail = $this->getIndividuService()->getDoctorantSansMail();

        $headers = [
            'Thèse Identitfiant'                    => function(These $these) {return $these->getId();},
            'Thèse SourceCode'                      => function(These $these) {return $these->getSourceCode();},
            'Thèse Titre'                           => function(These $these) {return $these->getTitre();},
            'Thèse Établissement'                   => function(These $these) {return ($these->getEtablissement())?$these->getEtablissement()->getStructure()->getCode():"";},
            'Thèse École doctorale'                 => function(These $these) {return ($these->getEcoleDoctorale())?$these->getEcoleDoctorale()->getStructure()->getCode():"";},
            'Thèse Unité de recherche'              => function(These $these) {return ($these->getUniteRecherche())?$these->getUniteRecherche()->getStructure()->getCode():"";},
            'Doctorant Identifiant'                 => function(These $these) {return $these->getDoctorant()->getId();},
            'Doctorant Nom'                         => function(These $these) {return $these->getDoctorant()->getIndividu()->getNomComplet();},
            'Première inscription'                  => function(These $these) {return ($these->getDatePremiereInscription())?$these->getDatePremiereInscription()->format("d/m/Y"):"";},
        ];

        $records = [];
        foreach ($doctorantsSansMail as $these) {
            $record = [];
            foreach($headers as $key => $fct) {
                $record[] = $fct($these);
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

    /**
     * faire remonter les thèses ayant plus de 6 ans
     * @return ViewModel
     */
    public function thesesAnciennesAction()
    {
        $theses = $this->getTheseService()->getThesesAnciennes(6);
        return new ViewModel([
                'theses' => $theses,
            ]
        );
    }

    public function exportThesesAnciennesAction()
    {
        $data = $this->getTheseService()->getTheseEnCoursPostSoutenance();
        $headers = [
            'Identitfiant'                    => function(These $these) {return $these->getId();},
            'SourceCode'                      => function(These $these) {return $these->getSourceCode();},
            'Titre'                           => function(These $these) {return $these->getTitre();},
            'Doctorant'                       => function(These $these) {return $these->getDoctorant()->getIndividu()->getNomComplet();},
            'État'                            => function(These $these) {return $these->getEtatThese();},
            'Date de première inscription'    => function(These $these) {return ($these->getDatePremiereInscription())?$these->getDatePremiereInscription()->format("d/m/Y"):"";},
            'Date de soutenace'               => function(These $these) {return ($these->getDateSoutenance())?$these->getDateSoutenance()->format("d/m/Y"):"";},
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
        $CSV->setFilename('export_thesesAnciennes.csv');

        return $CSV;
    }
}

