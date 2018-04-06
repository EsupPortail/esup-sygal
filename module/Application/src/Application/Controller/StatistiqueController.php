<?php

namespace Application\Controller;

use Application\Service\EcoleDoctorale\EcoleDoctoraleServiceAwareTrait;
use Application\Service\Etablissement\EtablissementServiceAwareTrait;
use Application\Service\These\TheseServiceAwareTrait;
use Application\Service\UniteRecherche\UniteRechercheServiceAwareTrait;
use DateTime;
use Doctrine\ORM\QueryBuilder;
use Zend\View\Model\ViewModel;

class StatistiqueController extends AbstractController
{
    use TheseServiceAwareTrait;
    use EcoleDoctoraleServiceAwareTrait;
    use UniteRechercheServiceAwareTrait;
    use EtablissementServiceAwareTrait;

    public function indexAction()
    {

        /**
         * Certaines statistiques exploites le genre de la personne et nécessite de récupérer
         * les données présentent dans la table Individu. Afin d'évite moultes requêtes, il
         * faut faire les jointures qui vont biens (et aussi le select)
         */

        $qb = $this->theseService->getRepository()->createQueryBuilder("t");
        $qb = $qb
            ->select("t,d,i")
            ->join("t.doctorant", "d")
            ->join("d.individu", "i");


        $structureType = $this->params()->fromQuery("structure_type");
        $structureId = $this->params()->fromQuery("structure_id");
        $qb = $this->decorateWithStructure($qb, $structureType, $structureId);

        $dateType = $this->params()->fromQuery("date_type");
        $dateDebut = $this->params()->fromQuery("date_min");
        $dateFin = $this->params()->fromQuery("date_max");
        $qb = $this->decorateWithDate($qb, $dateType, $dateDebut, $dateFin);

        $theses = $qb->getQuery()->execute();
        $ecoles = $this->ecoleDoctoraleService->getEcolesDoctorales();
        $unites = $this->uniteRechercheService->getUnitesRecherches();
        $etablissements = $this->etablissementService->getEtablissements();
        return new ViewModel([
            'theses' => $theses,
            'ecoles' => $ecoles,
            'unites' => $unites,
            'etablissements' => $etablissements,
            'structure_id' => $structureId,
        ]);
    }

    /**
     * @param QueryBuilder $qb
     * @param string $type (parmi ED, UR, Etab)
     * @param string $id l'identifiant d'une structure
     * @return QueryBuilder
     */
    private function decorateWithStructure(QueryBuilder $qb, $type = null, $id = null)
    {
        if ($type !== null && $id !== null) {
            switch($type) {
                case "ED" :
                    $ecole = $this->ecoleDoctoraleService->getEcoleDoctoraleById($id);
                    return  $qb->andWhere("t.ecoleDoctorale = :ed")
                                ->setParameter(":ed", $ecole);
                case "UR" :
                    $unite = $this->uniteRechercheService->getUniteRechercheById($id);
                    return $qb->andWhere("t.uniteRecherche = :ur")
                                ->setParameter(":ur", $unite);
                case "Etab" :
                    $etablissement = $this->etablissementService->getEtablissementById($id);
                    return $qb->andWhere("t.etablissement = :etab")
                                ->setParameter("etab", $etablissement);
            }
        }
        return $qb;
    }

    /**
     * @param QueryBuilder $qb
     * @param string $type (parmi soutenance, inscription)
     * @param string $debut (YYYY)
     * @param string $fin (YYYY)
     * @return QueryBuilder
     */
    private function decorateWithDate(QueryBuilder $qb, $type = null, $debut = null, $fin = null)
    {
        if ($debut !== null) $debut .= "-01-01";
        if ($fin !== null) $fin .= "-12-31";

        if ($type !== null) {
            switch($type) {
                case "soutenance" :
                    if ($debut !== null) {
                        $qb->andWhere("t.dateSoutenance >= :debut")
                            ->setParameter("debut", new DateTime($debut));
                    }
                    if ($fin !== null) {
                        $qb->andWhere("t.dateSoutenance <= :fin")
                            ->setParameter("fin", new DateTime($fin));
                    }
                    return $qb;
                case "inscription" :
                    if ($debut !== null) {
                        $qb->andWhere("t.datePremiereInscription >= :debut")
                            ->setParameter("debut", new DateTime($debut));
                    }
                    if ($fin !== null) {
                        $qb->andWhere("t.datePremiereInscription <= :fin")
                            ->setParameter("fin", new DateTime($fin));
                    }
                    return $qb;
            }
        }
        return $qb;
    }


}