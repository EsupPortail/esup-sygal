<?php

namespace Application\Controller;

use Application\Entity\Db\OrigineFinancement;
use Application\Provider\Privilege\FinancementPrivileges;
use Application\SourceCodeStringHelperAwareTrait;
use Depot\Service\FichierThese\FichierTheseServiceAwareTrait;
use Doctrine\DBAL\Exception;
use RuntimeException;
use These\Service\These\TheseSearchServiceAwareTrait;
use These\Service\These\TheseServiceAwareTrait;
use UnicaenApp\View\Model\CsvModel;

class ExportController extends AbstractController
{
    use TheseServiceAwareTrait;
    use TheseSearchServiceAwareTrait;
    use FichierTheseServiceAwareTrait;
    use SourceCodeStringHelperAwareTrait;

    public function csvAction(): CsvModel
    {
        $originesFinancementsFilter = function ($r) {
            if (empty($r['financ_origs'])) {
                return null;
            }
            // certaines origines ne sont pas visibles par tout le monde (ex : handicap)
            $origines = array_map('trim', explode(';', $r['financ_origs']));
            $visibles = array_map('trim', explode(';', $r['financ_origs_visibles']));
            $filteredOrigines = [];
            foreach ($origines as $i => $orig) {
                $isVisible = ['O'=>true,'N'=>false][$visibles[$i]];
                if ($isVisible || $this->isAllowed(new OrigineFinancement(), FinancementPrivileges::FINANCEMENT_VOIR_ORIGINE_NON_VISIBLE)) {
                    $filteredOrigines[] = $orig;
                }
            }
            return implode(" ; ", $filteredOrigines);
        };

        $headers = [
            'id'                                            => fn($r) => $r['id'],
            // Doctorant
            'Civilité'                                      => fn($r) => $r['civilite'],
            'Nom usuel'                                     => fn($r) => $r['nom_usuel'],
            'Prenom'                                        => fn($r) => $r['prenom1'],
            'Nom patronymique'                              => fn($r) => $r['nom_patronymique'],
            'Date de naissance'                             => fn($r) => $r['date_naissance'],
            'Nationalité'                                   => fn($r) => $r['nationalite'],
            'Adresse électronique'                          => fn($r) => $r['email_pro'],
            'Adresse électronique personnelle'              => fn($r) => $r['email_contact'],
            'Numéro étudiant'                               => fn($r) => $r['num_etudiant'],
            'I.N.E.'                                        => fn($r) => $r['ine'],
            //These
            'Identifiant de la thèse'                       => fn($r) => $r['num_these'],
            'Titre'                                         => fn($r) => $r['titre'],
            'Discipline Code SISE'                          => fn($r) => $r['code_sise_disc'],
            'Discipline'                                    => fn($r) => $r['lib_disc'],
            //Encadrements
            'Directeurs'                                    => fn($r) => $r['dirs'],
            'Co-directeurs'                                 => fn($r) => $r['codirs'],
            'Co-encadrants'                                 => fn($r) => $r['coencs'],
            //Structures
            'Etablissement'                                 => fn($r) => $r['etab_lib'],
            'Ecole Doctorale Code'                          => fn($r) => $r['ed_code'],
            'Ecole Doctorale'                               => fn($r) => $r['ed_lib'],
            'Unité de Recherche Code'                       => fn($r) => $r['ur_code'],
            'Unité de Recherche'                            => fn($r) => $r['ur_lib'],
            'Etablissement Co-Tutelle'                      => fn($r) => $r['lib_etab_cotut'],
            'Pays Co-Tutelle'                               => fn($r) => $r['lib_pays_cotut'],
            //accession
            "Diplôme d'accession à la thèse"                => fn($r) => $r['libelle_titre_acces'],
            "Établissement d'accession à la thèse"          => fn($r) => $r['libelle_etb_titre_acces'],
            //Financements
            'Années financées'                              => fn($r) => $r['financ_annees'],
            'Origines du financement'                       => $originesFinancementsFilter,
            'Complément sur les financements'               => fn($r) => $r['financ_compls'],
            'Type du financement'                           => fn($r) => $r['financ_types'],
            //Domaine
            'Domaines scientifiques'                        => fn($r) => $r['domaines'],
            //Dates
            'Date de première inscription'                  => fn($r) => $r['date_prem_insc'],
            "Date d'abandon"                                => fn($r) => $r['date_abandon'],
            'Date de transfert'                             => fn($r) => $r['date_transfert'],
            'Date de soutenance'                            => fn($r) => $r['date_soutenance'],
            'Date de fin de confientialité'                 => fn($r) => $r['date_fin_confid'],
            'Date de dépôt version initiale'                => fn($r) => $r['date_depot_vo'],
            'Date de dépôt version corrigée'                => fn($r) => $r['date_depot_voc'],
            'Durée en mois de la thèse'                     => fn($r) => $r['duree_these_mois'],
            //Flags
            'Etat de la thèse'                              => fn($r) => $r['etat_these'],
            'Autorisation à soutenir'                       => fn($r) => $r['soutenance_autoris'],
            'Est confidentielle'                            => fn($r) => $r['confidentielle'],
            'Résultat'                                      => fn($r) => $r['resultat'],
            'Corrections'                                   => fn($r) => $r['correc_autorisee'],
            'Thèse format PDF'                              => fn($r) => $r['depot_pdf'],
            'Annexe non PDF'                                => fn($r) => $r['depot_annexe'],
            //Diffusion
            'Autorisation de MEL'                           => fn($r) => $r['autoris_mel'],
            'Embargo'                                       => fn($r) => $r['autoris_embargo_duree'],
            'Refus de diffusion'                            => fn($r) => $r['autoris_motif'],
            //Rapports
            "Dernier rapport d'activité"                    => fn($r) => $r['dernier_rapport_activite'],
            "Dernier rapport CSI"                           => fn($r) => $r['dernier_rapport_csi'],
        ];

        $queryParams = $this->params()->fromQuery();

        $this->theseSearchService->init();
        $this->theseSearchService->processQueryParams($queryParams);
        $qb = $this->theseSearchService->getQueryBuilder();
        $qb->select('these'); // pas besoin de tout sélectionner, seuls les ids nous intéressent
        $theses = $qb->getQuery()->getArrayResult();
        $thesesIds = array_map(fn(array $t) => $t['id'], $theses); // extraction des 'id'

        // on fragmente la liste des id pour éviter de dépasser le nombre maxi de termes autorisés dans un IN()
        $wheres = array_map(
            fn(string $ids) => "id in ($ids)",
            array_map(
                fn(array $ids) => implode(',', $ids),
                array_chunk($thesesIds, 3000)
            )
        );
        $sql = sprintf('select * from v_extract_theses where %s', implode(' or ', $wheres));
        try {
            $records = $qb->getEntityManager()->getConnection()->executeQuery($sql)->fetchAllAssociative();
        } catch (Exception $e) {
            throw new RuntimeException("Erreur rencontrée lors de l'exécution de la requête SQL", null, $e);
        }

        $data = [];
        foreach ($records as $r) {
            $row = [];
            /** @var callable $fct */
            foreach ($headers as $fct) {
                $row[] = $fct($r);
            }
            $data[] = $row;
        }

        $result = new CsvModel();
        $result->setDelimiter(';');
        $result->setEnclosure('"'); // indispensable car il peut y avoir des ; dans les données
        $result->setHeader(array_keys($headers));
        $result->setData($data);
        $result->setFilename('export_theses.csv');

        return $result;
    }
}