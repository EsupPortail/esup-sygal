<?php

namespace Application\Controller;

use Application\Service\EcoleDoctorale\EcoleDoctoraleServiceAwareInterface;
use Application\Service\EcoleDoctorale\EcoleDoctoraleServiceAwareTrait;
use Application\Service\Etablissement\EtablissementServiceAwareInterface;
use Application\Service\Etablissement\EtablissementServiceAwareTrait;
use Application\Service\These\TheseServiceAwareInterface;
use Application\Service\These\TheseServiceAwareTrait;
use Application\Service\UniteRecherche\UniteRechercheServiceAwareInterface;
use Application\Service\UniteRecherche\UniteRechercheServiceAwareTrait;
use Doctrine\ORM\QueryBuilder;
use DateTime;
use Zend\Validator\Date;
use Zend\View\Model\ViewModel;

class StatistiqueController extends AbstractController
    implements TheseServiceAwareInterface,
        EcoleDoctoraleServiceAwareInterface, UniteRechercheServiceAwareInterface, EtablissementServiceAwareInterface
{
    use TheseServiceAwareTrait;
    use EcoleDoctoraleServiceAwareTrait;
    use UniteRechercheServiceAwareTrait;
    use EtablissementServiceAwareTrait;

    public function indexAction()
    {

        $qb = $this->theseService->getRepository()->createQueryBuilder("t");
        $qb = $qb
            ->select("t,d,i")
            ->join("t.doctorant", "d")
            ->join("d.individu", "i");


        $structureType = $this->params()->fromQuery("structure_type");
        $structureId = $this->params()->fromQuery("structure_id");
        $qb = $this->decorateWithStructure($qb, $structureType, $structureId);

        $dateType = $this->params()->fromQuery("date");
        $dateDebut = $this->params()->fromQuery("debut");
        $dateFin = $this->params()->fromQuery("fin");
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
        ]);
    }

    private function decorateWithStructure(QueryBuilder $qb, $type = null, $id = null)
    {
        if ($type !== null && $id !== null) {
            switch($type) {
                case "ED" :
                    $ecole = $this->ecoleDoctoraleService->getEcoleDoctoraleById($id);
//                    var_dump($ecole->getLibelle());
                    return  $qb->andWhere("t.ecoleDoctorale = :ed")
                                ->setParameter(":ed", $ecole);
                case "UR" :
                    $unite = $this->uniteRechercheService->getUniteRechercheById($id);
//                    var_dump($unite->getLibelle());
                    return $qb->andWhere("t.uniteRecherche = :ur")
                                ->setParameter(":ur", $unite);
                case "Etab" :
                    $etablissement = $this->etablissementService->getEtablissementById($id);
//                    var_dump($etablissement->getLibelle());
                    return $qb->andWhere("t.etablissement = :etab")
                                ->setParameter("etab", $etablissement);
            }
        }
        return $qb;
    }

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