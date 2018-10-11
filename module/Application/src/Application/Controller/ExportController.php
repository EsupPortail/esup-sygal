<?php

namespace Application\Controller;

use Application\Entity\Db\Acteur;
use Application\Entity\Db\NatureFichier;
use Application\Entity\Db\Role;
use Application\Entity\Db\These;
use Application\Service\Fichier\FichierServiceAwareTrait;
use Application\Service\These\TheseRechercheServiceAwareTrait;
use Application\Service\These\TheseServiceAwareTrait;
use UnicaenApp\View\Model\CsvModel;

class ExportController extends AbstractController
{
    use TheseServiceAwareTrait;
    use TheseRechercheServiceAwareTrait;
    use FichierServiceAwareTrait;

    public function csvAction()
    {
        $headers = [
            // Doctorant
            'Civilité'                              => function (These $these) { return $these->getDoctorant()->getIndividu()->getCivilite(); },
            'Nom usuel'                             => function (These $these) { return $these->getDoctorant()->getIndividu()->getNomUsuel(); },
            'Prenom'                                => function (These $these) { return $these->getDoctorant()->getIndividu()->getPrenom(); },
            'Nom patronymique'                      => function (These $these) { return $these->getDoctorant()->getIndividu()->getNomPatronymique(); },
            'Date de naissance'                     => function (These $these) { return $these->getDoctorant()->getIndividu()->getDateNaissance(); },
            'Mail'                                  => function (These $these) { return $these->getDoctorant()->getIndividu()->getEmail(); },
            'Numéro étudiant'                       => function (These $these) { return $these->getDoctorant()->getSourceCode(); },
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
            'Ecole Doctorale Code'                  => function (These $these) { if($these->getEcoleDoctorale() !== null)return $these->getEcoleDoctorale()->getStructure()->getCode(); else return null; },
            'Ecole Doctorale'                       => function (These $these) { if($these->getEcoleDoctorale() !== null)return $these->getEcoleDoctorale()->getLibelle(); else return null; },
            'Unité de Recherche Code'               => function (These $these) { if($these->getUniteRecherche() !== null) return $these->getUniteRecherche()->getStructure()->getCode(); else return null; },
            'Unité de Recherche'                    => function (These $these) { if($these->getUniteRecherche() !== null) return $these->getUniteRecherche()->getLibelle(); else return null; },
            'Etablissement Co-Tutelle'              => function (These $these) { return $these->getLibelleEtabCotutelle(); },
            'Pays Co-Tutelle'                       => function (These $these) { return $these->getLibellePaysCotutelle(); },
            //Dates
            'Date de première inscription'          => function (These $these) { return $these->getDatePremiereInscription(); },
            'Date de prévisionnel de soutenance'    => function (These $these) { return $these->getDatePrevisionSoutenance(); },
            'Date de soutenance'                    => function (These $these) { return $these->getDateSoutenance(); },
            'Date de fin de confientialité'         => function (These $these) { return $these->getDateFinConfidentialite(); },
            'Date de dépôt'                         => function (These $these) { $file = $this->fichierService->getRepository()->fetchFichiers($these, NatureFichier::CODE_THESE_PDF)[0]; if ($file !== null) return $file->getHistoCreation()->format('d/m/Y'); },
            //Flags
            'Etat de la thèse'                      => function (These $these) { return $these->getEtatTheseToString();},
            'Autorisation à soutenir'               => function (These $these) { return $these->getSoutenanceAutorisee();},
            'Est confidentielle'                    => function (These $these) { $now = new \DateTime(); $end= $these->getDateFinConfidentialite(); if ($now > $end) return "N"; else return "O"; },
            'Résultat'                              => function (These $these) { return $these->getResultat();},
            'Corrections'                           => function (These $these) { return $these->getCorrectionAutorisee();},
            'Thèse format PDF'                      => function (These $these) { if (!empty($this->fichierService->getRepository()->fetchFichiers($these, NatureFichier::CODE_THESE_PDF))) return 'O'; else return 'N'; },
            'Annexe non PDF'                        => function (These $these) { if (!empty($this->fichierService->getRepository()->fetchFichiers($these, NatureFichier::CODE_FICHIER_NON_PDF))) return 'O'; else return 'N'; },

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