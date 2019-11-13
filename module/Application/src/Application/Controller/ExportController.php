<?php

namespace Application\Controller;

use Application\Entity\Db\Acteur;
use Application\Entity\Db\Financement;
use Application\Entity\Db\Role;
use Application\Entity\Db\These;
use Application\Service\FichierThese\FichierTheseServiceAwareTrait;
use Application\Service\These\TheseRechercheServiceAwareTrait;
use Application\Service\These\TheseServiceAwareTrait;
use Application\SourceCodeStringHelperAwareTrait;
use UnicaenApp\View\Model\CsvModel;

class ExportController extends AbstractController
{
    use TheseServiceAwareTrait;
    use TheseRechercheServiceAwareTrait;
    use FichierTheseServiceAwareTrait;
    use SourceCodeStringHelperAwareTrait;

    public function csvAction()
    {
        $headers = [
            // Doctorant
            'Civilité'                              => function (These $these) { return $these->getDoctorant()->getIndividu()->getCivilite(); },
            'Nom usuel'                             => function (These $these) { return $these->getDoctorant()->getIndividu()->getNomUsuel(); },
            'Prenom'                                => function (These $these) { return $these->getDoctorant()->getIndividu()->getPrenom(); },
            'Nom patronymique'                      => function (These $these) { return $these->getDoctorant()->getIndividu()->getNomPatronymique(); },
            'Date de naissance'                     => function (These $these) { return $these->getDoctorant()->getIndividu()->getDateNaissance(); },
            'Nationalité'                           => function (These $these) { return $these->getDoctorant()->getIndividu()->getNationalite(); },
            'Adresse électronique'                  => function (These $these) { return $these->getDoctorant()->getIndividu()->getEmail(); },
            'Adresse électronique personnelle'      => function (These $these) { return $these->getDoctorant()->getIndividu()->getMailContact(); },
            'Numéro étudiant'                       => function (These $these) { return $this->sourceCodeStringHelper->removePrefixFrom($these->getDoctorant()->getSourceCode()); },
            'I.N.E.'                                => function (These $these) { return $these->getDoctorant()->getIne(); },
            //These
            'Identifiant de la thèse'               => function (These $these) { return $these->getSourceCode(); },
            'Titre'                                 => function (These $these) { return $these->getTitre(); },
            'Discipline'                            => function (These $these) { return $these->getLibelleDiscipline(); },
            //Encadrements
            'Directeurs'                            => function (These $these) {
                $directeurs = $these->getActeursByRoleCode(Role::CODE_DIRECTEUR_THESE);
                $noms = [];
                /** @var Acteur $directeur */
                foreach ($directeurs as $directeur) $noms[] = $directeur->getIndividu()->getNomComplet();
                return implode(",", $noms);
            },
            'Co-directeurs'                            => function (These $these) {
                $directeurs = $these->getActeursByRoleCode(Role::CODE_CODIRECTEUR_THESE);
                $noms = [];
                /** @var Acteur $directeur */
                foreach ($directeurs as $directeur) $noms[] = $directeur->getIndividu()->getNomComplet();
                return implode(",", $noms);
            },
            //Structures
            'Etablissement'                         => function (These $these) { return $these->getEtablissement()->getLibelle(); },
            'Ecole Doctorale Code'                  => function (These $these) { if($these->getEcoleDoctorale() !== null) return $these->getEcoleDoctorale()->getStructure()->getCode(); else return null; },
            'Ecole Doctorale'                       => function (These $these) { if($these->getEcoleDoctorale() !== null)return $these->getEcoleDoctorale()->getLibelle(); else return null; },
            'Unité de Recherche Code'               => function (These $these) { if($these->getUniteRecherche() !== null) return $these->getUniteRecherche()->getStructure()->getCode(); else return null; },
            'Unité de Recherche'                    => function (These $these) { if($these->getUniteRecherche() !== null) return $these->getUniteRecherche()->getLibelle(); else return null; },
            'Etablissement Co-Tutelle'              => function (These $these) { return $these->getLibelleEtabCotutelle(); },
            'Pays Co-Tutelle'                       => function (These $these) { return $these->getLibellePaysCotutelle(); },
            //accession
            'Diplôme d\'accession à la thèse'       => function (These $these) { if($these->getTitreAcces() !== null) return $these->getTitreAcces()->getLibelleTitreAcces(); },
            'Établissement d\'accession à la thèse' => function (These $these) { if($these->getTitreAcces() !== null) return $these->getTitreAcces()->getLibelleEtabTitreAcces(); },
            //Financements
            'Origines du financement'                            => function (These $these) {
                $financements = $these->getFinancements();
                $origines = [];
                /** @var Financement $financement */
                foreach ($financements as $financement) $origines[] = $financement->getOrigineFinancement()->getLibelleLong();
                return implode(",", $origines);
            },
            //Domaine
            'Domaines scientifiques'                            => function (These $these) {
                $domaines = ($these->getUniteRecherche())?($these->getUniteRecherche())->getDomaines():[];
                $liste = [];
                /** @var Financement $financement */
                foreach ($domaines as $domaine) $liste[] = $domaine->getLibelle();
                return implode(",", $liste);
            },
            //Dates
            'Date de première inscription'          => function (These $these) { return $these->getDatePremiereInscription(); },
            'Date de prévisionnel de soutenance'    => function (These $these) { return $these->getDatePrevisionSoutenance(); },
            'Date de soutenance'                    => function (These $these) { return $these->getDateSoutenance(); },
            'Date de fin de confientialité'         => function (These $these) { return $these->getDateFinConfidentialite(); },
            'Date de dépôt version initiale'        => function (These $these) { $file = $these->hasVersionInitiale(); if ($file) return $file->getFichier()->getHistoCreation()->format('d/m/Y'); },
            'Date de dépôt version corigée'         => function (These $these) { $file = $these->hasVersionCorrigee(); if ($file) return $file->getFichier()->getHistoCreation()->format('d/m/Y'); },
            //Flags
            'Etat de la thèse'                      => function (These $these) { return $these->getEtatTheseToString();},
            'Autorisation à soutenir'               => function (These $these) { return $these->getSoutenanceAutorisee();},
            'Est confidentielle'                    => function (These $these) { $now = new \DateTime(); $end= $these->getDateFinConfidentialite(); if ($now > $end) return "N"; else return "O"; },
            'Résultat'                              => function (These $these) { return $these->getResultat();},
            'Corrections'                           => function (These $these) { return $these->getCorrectionAutorisee();},
            'Thèse format PDF'                      => function (These $these) { if ($these->hasMemoire())  return 'O'; else return 'N'; },
            'Annexe non PDF'                        => function (These $these) { if ($these->hasAnnexe())   return 'O'; else return 'N'; },

        ];

        $queryParams = $this->params()->fromQuery();

        $this->theseRechercheService
            ->createFilters()
            ->createSorters()
            ->processQueryParams($queryParams);

        $qb = $this->theseRechercheService->createQueryBuilder();
        $theses = $qb->getQuery()->getResult();

        $records = [];
        for ($i = 0 ; $i < count($theses) ; $i++) {
            $these = $theses[$i];
            $record = [];
            foreach($headers as $key => $fct) {
                $record[] = $fct($these);
            }
            $records[] = $record;
        }

        $result = new CsvModel();
        $result->setDelimiter(';');
        $result->setEnclosure('"');
        $result->setHeader(array_keys($headers));
        $result->setData($records);
        $result->setFilename('export_theses.csv');

        return $result;
    }
}