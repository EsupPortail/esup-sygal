<?php

namespace Application\Controller;

use Application\Entity\Db\NatureFichier;
use Application\Entity\Db\These;
use Application\Service\Fichier\FichierServiceAwareInterface;
use Application\Service\Fichier\FichierServiceAwareTrait;
use Application\Service\These\TheseServiceAwareInterface;
use Application\Service\These\TheseServiceAwareTrait;
use Application\View\Helper\Sortable;
use UnicaenApp\View\Model\CsvModel;

class ExportController extends AbstractController implements TheseServiceAwareInterface, FichierServiceAwareInterface
{
    use TheseServiceAwareTrait;
    use FichierServiceAwareTrait;

    public function csvAction()
    {

        //Generer la requete avec les filtres ...
        /**
         * @var \Doctrine\ORM\QueryBuilder      $qb
         * @var \Doctrine\ORM\Query             $query
         * @var These                           $these
         */

        //TODO compléter avec les infos demandées
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
            //Structures
            'Etablissement'                         => function (These $these) { return $these->getEtablissement()->getLibelle(); },
            'Ecole Doctorale Code'                  => function (These $these) { if($these->getEcoleDoctorale() !== null)return $these->getEcoleDoctorale()->getSourceCode(); else return null; },
            'Ecole Doctorale'                       => function (These $these) { if($these->getEcoleDoctorale() !== null)return $these->getEcoleDoctorale()->getLibelle(); else return null; },
            'Unité de Recherche Code'               => function (These $these) { if($these->getUniteRecherche() !== null) return $these->getUniteRecherche()->getSourceCode(); else return null; },
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
        $etatThese = $this->params()->fromQuery('etatThese');
        //$sort = $this->params()->fromQuery('sort');
        $text = $this->params()->fromQuery('text');
        $dir  = $this->params()->fromQuery('direction', Sortable::ASC);
        $sort = $this->params()->fromQuery('sort');

//        var_dump($etatThese);
//        var_dump($sort);
//        var_dump($text);
//        var_dump($dir);

//        $repoThese = $this->theseService->getEntityManager()->getRepository(These::class);
//        $qb = $repoThese->createQueryBuilder("t")
//            ->where("t.ecoleDoctorale = 3")
//            ->andWhere("t.etatThese = :etatThese")
//        ;
//        $qb->setParameter("etatThese", $etatThese);
//        $query = $qb->getQuery();
        $query = $this->createQueryBuilder()->getQuery();

            //Execution de la requete
        $theses = $query->execute();

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

    //TODO FACTORISER AVEC INDEX DES THESES ...
    private function createQueryBuilder() {
        $etatThese = $this->params()->fromQuery($name = 'etatThese');
        $sort = $this->params()->fromQuery('sort');
        $text = $this->params()->fromQuery('text');
        $dir  = $this->params()->fromQuery('direction', Sortable::ASC);

        $qb = $this->theseService->getRepository()->createQueryBuilder('t')
            ->addSelect('di')->leftJoin('th.individu', 'di')
            ->addSelect('ed')->leftJoin('t.ecoleDoctorale', 'ed')
            ->addSelect('ur')->leftJoin('t.uniteRecherche', 'ur')
            ->addSelect('a')->leftJoin('t.acteurs', 'a')
            ->addSelect('i')->leftJoin('a.individu', 'i')
            ->addSelect('r')->leftJoin('a.role', 'r')
            ->andWhere('1 = pasHistorise(t)');

        if ($etatThese) {
            $qb->andWhere('t.etatThese = :etat')->setParameter('etat', $etatThese);
        }
        $sortProps = $sort ? explode('+', $sort) : [];
        foreach ($sortProps as $sortProp) {
            if ($sortProp === 't.titre') {
                // trim et suppression des guillemets
                $sortProp = "TRIM(REPLACE($sortProp, CHR(34), ''))"; // CHR(34) <=> "
            }
            $qb->addOrderBy($sortProp, $dir);
        }

        /**
         * Filtres découlant du rôle de l'utilisateur.
         */
        $this->theseService->decorateQbFromUserContext($qb, $this->userContextService);

        /**
         * Prise en compte du champ de recherche textuelle.
         */
        if (strlen($text) > 1) {
            $results = $this->theseService->rechercherThese($text);
            $sourceCodes = array_unique(array_keys($results));
            if ($sourceCodes) {
                $qb
                    ->andWhere($qb->expr()->in('t.sourceCode', ':sourceCodes'))
                    ->setParameter('sourceCodes', $sourceCodes);
            }
            else {
                $qb->andWhere("0 = 1"); // i.e. aucune thèse trouvée
            }
        }

        return $qb;
    }
}