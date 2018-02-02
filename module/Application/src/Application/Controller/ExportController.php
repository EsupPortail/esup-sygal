<?php

namespace Application\Controller;

use Application\Entity\Db\These;
use Application\Service\These\TheseServiceAwareInterface;
use Application\Service\These\TheseServiceAwareTrait;
use Application\View\Helper\Sortable;
use Doctrine\ORM\QueryBuilder;
use UnicaenApp\View\Model\CsvModel;
use Zend\View\Model\ViewModel;

class ExportController extends AbstractController implements TheseServiceAwareInterface
{
    use TheseServiceAwareTrait;

    public function csvAction()
    {

        //Generer la requete avec les filtres ...
        /**
         * @var \Doctrine\ORM\QueryBuilder      $qb
         * @var \Doctrine\ORM\Query             $query
         * @var These                           $these
         */

        $headers = [
            'Civilité'                              => function ($these) { return $these->getDoctorant()->getIndividu()->getCivilite(); },
            'Nom usuel'                             => function ($these) { return $these->getDoctorant()->getIndividu()->getNomUsuel(); },
            'Prenom'                                => function ($these) { return $these->getDoctorant()->getIndividu()->getPrenom(); },
            'Nom patronymique'                      => function ($these) { return $these->getDoctorant()->getIndividu()->getNomPatronymique(); },
            'Date de naissance'                     => function ($these) { return $these->getDoctorant()->getIndividu()->getDateNaissance(); },
            'Mail'                                  => function ($these) { return $these->getDoctorant()->getIndividu()->getEmail(); },
            'Numéro étudiant'                       => function ($these) { return $these->getDoctorant()->getSourceCode(); },
            'Titre'                                 => function ($these) { return $these->getTitre(); },
            'Identifiant de la thèse'               => function ($these) { return $these->getSourceCode(); },
            'Date de première inscription'          => function ($these) { return $these->getDatePremiereInscription(); },
            'Date de prévisionnel de soutenance'    => function ($these) { return $these->getDatePrevisionSoutenance(); },
            'Date de soutenance'                    => function ($these) { return $these->getDateSoutenance(); },
            //'Date d\'autorisation à soutenir'       => function ($these) { return $these->getDateAutSoutenance(); },

        ];
        $etatThese = $this->params()->fromQuery('etatThese');
        $sort = $this->params()->fromQuery('sort');
        $text = $this->params()->fromQuery('text');
        $dir  = $this->params()->fromQuery('direction', Sortable::ASC);
        var_dump($etatThese);
        var_dump($sort);
        var_dump($text);
        var_dump($dir);

        $repoThese = $this->theseService->getEntityManager()->getRepository(These::class);
        $qb = $repoThese->createQueryBuilder("t")
            ->where("t.ecoleDoctorale = 41")
            ->andWhere("t.etatThese = :etatThese")
            ->orderBy($sort,$dir);
        $qb->setParameter("etatThese", $etatThese);
//        $qb->setParameter("dir", $sort);
        //$qb->setParameter("attrib", $sort);
        $query = $qb->getQuery();
//        var_dump($query);

        //Execution de la requete
        $theses = $query->execute();

        $records = [];
        echo count($theses);
        for ($i = 0 ; $i < 10 ; $i++) {
            $these = $theses[$i];
            $record = [];
            foreach($headers as $key => $fct) {
                $record[] = $fct($these);
            }
            //var_dump($record);

            $records[] = $record;
        }

//        var_dump($result);
//        die("My Job Is Done ...");


        $result = new CsvModel();
        $result->setDelimiter(';');
        $result->setEnclosure('"');
        $result->setHeader(array_keys($headers));
        $result->setData($records);
        $result->setFilename('export_theses.csv');

        return $result;


        $cols = [
            // CSV                                          => SQL
//            'CIVILITE'                                      => 'CIVILITE',
//            'NOM_USUEL'                                     => 'NOM_USUEL',
//            'PRENOM'                                        => 'PRENOM',
//            'NOM_PATRONYMIQUE'                              => 'NOM_PATRONYMIQUE',
//            'DATE_NAISSANCE'                                => "to_char(DATE_NAISSANCE, 'DD/MM/YYYY')",
//            'EMAIL'                                         => 'EMAIL',
//            'NUMERO_ETUDIANT'                               => 'NUMERO_ETUDIANT',
//            'TITRE_APOGEE'                                  => 'TITRE_APOGEE',
//            'NUMERO_APOGEE'                                 => 'NUMERO_APOGEE',
            'DATE_FIN_CONFID'                               => "to_char(DATE_FIN_CONFID, 'DD/MM/YYYY')",
//            'DATE_PREMIERE_INSCR'                           => "to_char(DATE_PREM_INSC, 'DD/MM/YYYY')",
//            'DATE_SOUTENANCE'                               => "to_char(DATE_SOUTENANCE, 'DD/MM/YYYY')",
            'SOUTENANCE_AUTORIS'                            => 'SOUTENANCE_AUTORIS',
            'TEM_AVENANT_COTUT'                             => 'TEM_AVENANT_COTUT',
            'ETAT_THESE'                                    => 'ETAT_THESE',
            'LIB_DISC'                                      => 'LIB_DISC',
            'RESULTAT'                                      => 'RESULTAT',
            'DATE_PREV_SOUTENANCE'                          => "to_char(DATE_PREV_SOUTENANCE, 'DD/MM/YYYY')",
            'CORREC_AUTORISEE'                              => 'CORREC_AUTORISEE',
            'LANGUE'                                        => 'LANGUE',
            'RESUME'                                        => 'RESUME',
            'RESUME_ANGLAIS'                                => 'RESUME_ANGLAIS',
            'MOTS_CLES_LIBRES_FR'                           => 'MOTS_CLES_LIBRES_FR',
            'TITRE_AUTRE_LANGUE'                            => 'TITRE_AUTRE_LANGUE',
            'MOTS_CLES_LIBRES_ANG'                          => 'MOTS_CLES_LIBRES_ANG',
            'AUTORIS_MEL'                                   => 'AUTORIS_MEL',
            'AUTORIS_MEL_MOTIF'                             => 'AUTORIS_MEL_MOTIF',
            'EMBARGO_DUREE'                                 => 'EMBARGO_DUREE',
            'COORD_DOCTORANT'                               => 'COORD_DOCTORANT',
            'DISPO_DOCTORANT'                               => 'DISPO_DOCTORANT',
            'MOTS_CLES_RAMEAU'                              => 'MOTS_CLES_RAMEAU',
            'CONVENTION_MEL_SIGNEE'                         => 'CONVENTION_MEL_SIGNEE',
            'EXEMPL_PAPIER_FOURNI'                          => 'EXEMPL_PAPIER_FOURNI',
            'DIRECTEURS_THESE'                              => 'DIRECTEURS_THESE',
            'MEMBRES_JURY'                                  => 'MEMBRES_JURY',
            'LIBELLE_ED'                                    => 'LIBELLE_ED',
            'SIGLE_ED'                                      => 'SIGLE_ED',
            'CODE_ED'                                       => 'CODE_ED',
            'LIBELLE_UR'                                    => 'LIBELLE_UR',
            'SIGLE_UR'                                      => 'SIGLE_UR',
            'ETAB_SUPPORT_UR'                               => 'ETAB_SUPPORT_UR',
            'AUTRES_ETAB_SUPPORT_UR'                        => 'AUTRES_ETAB_SUPPORT_UR',
            'CODE_UR'                                       => 'CODE_UR',
            'VERSION_ORIGINALE_ARCHIVABLE'                  => 'VERSION_ORIG_ARCHIVABLE',
            'VERSION_ARCHIVAGE_ARCHIVABLE'                  => 'VERSION_ARCHI_ARCHIVABLE',
            'VERSION_ARCHIVAGE_RETRAITEMENT'                => 'VERSION_ARCHI_RETRAIT',
            'VERSION_ORIGINALE_CORRIGEE_ARCHIVABLE'         => 'VERSION_ORIG_CORR_ARCHIVABLE',
            'VERSION_ARCHIVAGE_CORRIGEE_ARCHIVABLE'         => 'VERSION_ARCHI_CORR_ARCHIVABLE',
            'VERSION_ARCHIVAGE_CORRIGEE_RETRAITEMENT'       => 'VERSION_ARCHI_CORR_RETRAIT',
            'VALIDATION_BU'                                 => 'VALIDATION_BU',
            'PV_SOUTENANCE_DEPOSE'                          => 'PV_SOUTENANCE_DEPOSE',
            'RAPPORT_SOUTENANCE_DEPOSE'                     => 'RAPPORT_SOUTENANCE_DEPOSE',

            'DATE_EXTRACTION'                               => 'DATE_EXTRACTION',
        ];

        $sql = "select %s from v_export_all";

        $sql = sprintf($sql, implode(', ', array_values($cols)));

        var_dump($sql);
        /**
         * Filtres et tris.
         */
        $etatThese = $this->params()->fromQuery($name = 'etatThese');
        $sort = $this->params()->fromQuery('sort');
        $text = $this->params()->fromQuery('text');
        $dir  = $this->params()->fromQuery('direction', Sortable::ASC);

        $wheres = $params = [];
        if ($etatThese) {
            $wheres[] = "ETAT_THESE = :etat";
            $params['etat'] = $etatThese;
        }

        $orderBys = [];
        $sortProps = $sort ? explode('+', $sort) : [];
        foreach ($sortProps as $sortProp) {
            switch ($sortProp) {
                case 't.titre':
                    // trim et suppression des guillemets
                    $sortProp = "TRIM(REPLACE(TITRE, CHR(34), ''))"; // CHR(34) <=> "
                    break;
                case 'th.nomUsuel':
                    $sortProp = 'NOM_USUEL';
                    break;
                case 'th.prenom':
                    $sortProp = 'PRENOM';
                    break;
                case 't.codeEcoleDoctorale':
                    $sortProp = 'CODE_ED';
                    break;
                case 't.codeUniteRecherche':
                    $sortProp = 'CODE_UR';
                    break;
                case 't.datePremiereInscription':
                    $sortProp = 'DATE_PREM_INSC';
                    break;
                default:
                    break;
            }
            $orderBys[] = $sortProp . ' ' . $dir;
        }

        /**
         * Recherche textuelle.
         */
        if (strlen($text) > 1) {
            $results = $this->theseService->rechercherThese($text);
            $sourceCodes = array_unique(array_keys($results));
            if ($sourceCodes) {
                $wheres[] = sprintf("NUMERO_APOGEE in (%s)", implode(',', $sourceCodes));
            }
            else {
                $wheres[] = "0 = 1"; // i.e. aucune thèse trouvée
            }
        }

        /**
         * Filtres découlant du rôle de l'utilisateur.
         */
        $this->theseService->decorateSqlQueryFromUserContext($wheres, $params, $this->userContextService);


        if (count($wheres) > 0) {
            $sql .= ' WHERE ' . implode(' AND ', $wheres);
        }
        if (count($orderBys) > 0) {
            $sql .= ' ORDER BY ' . implode(',', $orderBys);
        }

        $statement = $this->theseService->getEntityManager()->getConnection()->executeQuery($sql, $params);
        $records = $statement->fetchAll(\PDO::FETCH_ASSOC);

        $result = new CsvModel();
        $result->setDelimiter(';');
        $result->setEnclosure('"');
        $result->setHeader(array_keys($cols));
        $result->setData($records);
        $result->setFilename('export_theses.csv');

        return $result;
    }
}