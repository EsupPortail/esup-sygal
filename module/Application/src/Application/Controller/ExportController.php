<?php

namespace Application\Controller;

use Application\Entity\Db\Acteur;
use Application\Entity\Db\Financement;
use Application\Entity\Db\Role;
use Application\Entity\Db\These;
use Application\Provider\Privilege\FinancementPrivileges;
use Application\Service\FichierThese\FichierTheseServiceAwareTrait;
use Application\Service\These\TheseRechercheServiceAwareTrait;
use Application\Service\These\TheseServiceAwareTrait;
use Application\SourceCodeStringHelperAwareTrait;
use UnicaenApp\Exception\LogicException;
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
            'Civilité'                              => function ($variables) { return $variables['doctorant']->getIndividu()->getCivilite(); },
            'Nom usuel'                             => function ($variables) { return $variables['doctorant']->getIndividu()->getNomUsuel(); },
            'Prenom'                                => function ($variables) { return $variables['doctorant']->getIndividu()->getPrenom(); },
            'Nom patronymique'                      => function ($variables) { return $variables['doctorant']->getIndividu()->getNomPatronymique(); },
            'Date de naissance'                     => function ($variables) { return $variables['doctorant']->getIndividu()->getDateNaissance(); },
            'Nationalité'                           => function ($variables) { return $variables['doctorant']->getIndividu()->getNationalite(); },
            'Adresse électronique'                  => function ($variables) { return $variables['doctorant']->getIndividu()->getEmail(); },
            'Adresse électronique personnelle'      => function ($variables) { return $variables['doctorant']->getIndividu()->getMailContact(); },
            'Numéro étudiant'                       => function ($variables) { return $this->sourceCodeStringHelper->removePrefixFrom($variables['doctorant']->getSourceCode()); },
            'I.N.E.'                                => function ($variables) { return $variables['doctorant']->getIne(); },
            //These
            'Identifiant de la thèse'               => function ($variables) { return $variables['these']->getSourceCode(); },
            'Titre'                                 => function ($variables) { return $variables['these']->getTitre(); },
            'Discipline'                            => function ($variables) { return $variables['these']->getLibelleDiscipline(); },
            //Encadrements
            'Directeurs'                            => function ($variables) {
                $directeurs = $variables['directeurs'];
                $noms = [];
                /** @var Acteur $directeur */
                foreach ($directeurs as $directeur) $noms[] = $directeur->getIndividu()->getNomComplet();
                return implode(",", $noms);
            },
            'Co-directeurs'                            => function ($variables) {
                $directeurs = $variables['co-directeurs'];
                $noms = [];
                /** @var Acteur $directeur */
                foreach ($directeurs as $directeur) $noms[] = $directeur->getIndividu()->getNomComplet();
                return implode(",", $noms);
            },
            'Co-encadrants'                            => function ($variables) {
                $acteurs = $variables['co-encadrants'];
                $noms = [];
                /** @var Acteur $acteurs */
                foreach ($acteurs as $directeur) $noms[] = $directeur->getIndividu()->getNomComplet();
                return implode(",", $noms);
            },
            //Structures
            'Etablissement'                         => function ($variables) { return $variables['etablissement']->getLibelle(); },
            'Ecole Doctorale Code'                  => function ($variables) { return ($variables['ecole doctorale'])?$variables['ecole doctorale']->getStructure()->getCode():null; },
            'Ecole Doctorale'                       => function ($variables) { return ($variables['ecole doctorale'])?$variables['ecole doctorale']->getLibelle():null; },
            'Unité de Recherche Code'               => function ($variables) { return ($variables['unite de recherche'])?$variables['unite de recherche']->getStructure()->getCode():null; },
            'Unité de Recherche'                    => function ($variables) { return ($variables['unite de recherche'])?$variables['unite de recherche']->getLibelle():null; },
            'Etablissement Co-Tutelle'              => function ($variables) { return $variables['these']->getLibelleEtabCotutelle(); },
            'Pays Co-Tutelle'                       => function ($variables) { return $variables['these']->getLibellePaysCotutelle(); },
            //accession
            'Diplôme d\'accession à la thèse'       => function ($variables) { return ($variables['these']->getTitreAcces())?$variables['these']->getTitreAcces()->getLibelleTitreAcces():null; },
            'Établissement d\'accession à la thèse' => function ($variables) { return ($variables['these']->getTitreAcces())?$variables['these']->getTitreAcces()->getLibelleEtabTitreAcces():null; },
            //Financements
            'Origines du financement'                            => function ($variables) {
                $these = $variables['these'];
                $financements = $these->getFinancements();
                $origines = [];
                /** @var Financement $financement */
                foreach ($financements as $financement) {
                    $origine = $financement->getOrigineFinancement();
                    if ($origine->isVisible() || $this->isAllowed($origine, FinancementPrivileges::FINANCEMENT_VOIR_ORIGINE_NON_VISIBLE)) {
                        $origines[] = $origine->getLibelleLong();
                    }
                }
                return implode(",", $origines);
            },
            'Complément sur les financements'                            => function ($variables) {
                $these = $variables['these'];
                $financements = $these->getFinancements();
                $origines = [];
                /** @var Financement $financement */
                foreach ($financements as $financement) $origines[] = ($financement->getComplementFinancement())?:" - ";
                return implode(",", $origines);
            },
            'Type du financement'                            => function ($variables) {
                $these = $variables['these'];
                $financements = $these->getFinancements();
                $types = [];
                /** @var Financement $financement */
                foreach ($financements as $financement) $types[] = $financement->getLibelleTypeFinancement();
                return implode(",", array_filter($types));
            },
            //Domaine
            'Domaines scientifiques'                            => function ($variables) {
                $unite = $variables['unite de recherche'];
                $domaines = ($unite)?$unite->getDomaines():[];
                $liste = [];
                /** @var Financement $financement */
                foreach ($domaines as $domaine) $liste[] = $domaine->getLibelle();
                return implode(",", $liste);
            },
            //Dates
            'Date de première inscription'          => function ($variables) { return $variables['these']->getDatePremiereInscription(); },
            "Date d'abandon"                        => function ($variables) { return $variables['these']->getDateAbandon(); },
            'Date de transfert'                     => function ($variables) { return $variables['these']->getDateTransfert(); },
            'Date de prévisionnel de soutenance'    => function ($variables) { return $variables['these']->getDatePrevisionSoutenance(); },
            'Date de soutenance'                    => function ($variables) { return $variables['these']->getDateSoutenance(); },
            'Date de fin de confientialité'         => function ($variables) { return $variables['these']->getDateFinConfidentialite(); },
            'Date de dépôt version initiale'        => function ($variables) { return ($variables['version_initiale'])?$variables['version_initiale']->getFichier()->getHistoCreation()->format('d/m/Y'):"";},
            'Date de dépôt version corigée'         => function ($variables) { return ($variables['version_corrigee'])?$variables['version_corrigee']->getFichier()->getHistoCreation()->format('d/m/Y'):"";},
            'Durée en mois de la thèse'             => function ($variables) { try { return number_format($variables['these']->getDureeThese(), 2, ',', ''); } catch (LogicException $e) { return ""; } },
            //Flags
            'Etat de la thèse'                      => function ($variables) { return $variables['these']->getEtatTheseToString();},
            'Autorisation à soutenir'               => function ($variables) { return $variables['these']->getSoutenanceAutorisee();},
            'Est confidentielle'                    => function ($variables) { $now = new \DateTime(); $end= $variables['these']->getDateFinConfidentialite(); if ($now > $end) return "N"; else return "O"; },
            'Résultat'                              => function ($variables) { return $variables['these']->getResultat();},
            'Corrections'                           => function ($variables) { return $variables['these']->getCorrectionAutorisee();},
            'Thèse format PDF'                      => function ($variables) { return $variables['version_initiale']?'O':'N'; },
            'Annexe non PDF'                        => function ($variables) { return $variables['annexe']?'O':'N'; },
//
            //Embargo et refus de diffusion
            'Embargo'                               => function ($variables) {
                $these = $variables['these'];
                $versionInitiale = $variables['version_initiale'];
                $versionCorrigee = $variables['version_corrigee'];
                if ($versionCorrigee !== null) {
                    $diffusionCorrigee = $these->getDiffusionForVersion($versionCorrigee->getFichier()->getVersion());
                    if ($diffusionCorrigee !== null) return $diffusionCorrigee->getAutorisEmbargoDuree();
                }
                if ($versionInitiale !== null) {
                    $diffusionInitiale = $these->getDiffusionForVersion($versionInitiale->getFichier()->getVersion());
                    if ($diffusionInitiale !== null) return $diffusionInitiale->getAutorisEmbargoDuree();
                }
                return null;
            },
            'Refus de diffusion' => function ($variables) {
                $these = $variables['these'];
                $versionInitiale = $variables['version_initiale'];
                $versionCorrigee = $variables['version_corrigee']->getVersion;
                if ($versionCorrigee !== null) {
                    $diffusionCorrigee = $these->getDiffusionForVersion($versionCorrigee->getFichier()->getVersion());
                    if ($diffusionCorrigee !== null) return $diffusionCorrigee->getAutorisMotif();
                }
                if ($versionInitiale !== null) {
                    $diffusionInitiale = $these->getDiffusionForVersion($versionInitiale->getFichier()->getVersion());
                    if ($diffusionInitiale !== null) return $diffusionInitiale->getAutorisMotif();
                }
                return null;
            },
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
            /** @var These $these */
            $these = $theses[$i];
            $record = [];
            foreach($headers as $key => $fct) {
                $variables = [
                    'these' => $these,
                    'doctorant' => $these->getDoctorant(),
                    'directeurs' => $these->getActeursByRoleCode(Role::CODE_DIRECTEUR_THESE),
                    'co-directeurs' => $these->getActeursByRoleCode(Role::CODE_CODIRECTEUR_THESE),
                    'co-encadrants' => $these->getActeursByRoleCode(Role::CODE_CO_ENCADRANT),
                    'etablissement' => $these->getEtablissement(),
                    'ecole doctorale' => $these->getEcoleDoctorale(),
                    'unite de recherche' => $these->getUniteRecherche(),
                    'version_initiale' => $these->hasVersionInitiale(),
                    'version_corrigée' => $these->hasVersionCorrigee(),
                    'annexe' => $these->hasAnnexe(),
                ];
                $record[] = $fct($variables);
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