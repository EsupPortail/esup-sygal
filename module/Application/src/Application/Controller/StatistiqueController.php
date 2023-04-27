<?php

namespace Application\Controller;

use Structure\Entity\Db\TypeStructure;
use Structure\Service\EcoleDoctorale\EcoleDoctoraleServiceAwareTrait;
use Structure\Service\Etablissement\EtablissementServiceAwareTrait;
use Structure\Service\Structure\StructureServiceAwareTrait;
use These\Service\These\TheseServiceAwareTrait;
use Structure\Service\UniteRecherche\UniteRechercheServiceAwareTrait;
use DateTime;
use Doctrine\ORM\QueryBuilder;
use Laminas\View\Model\ViewModel;

class StatistiqueController extends AbstractController
{
    use TheseServiceAwareTrait;
    use EcoleDoctoraleServiceAwareTrait;
    use UniteRechercheServiceAwareTrait;
    use EtablissementServiceAwareTrait;
    use StructureServiceAwareTrait;

    public function indexAction()
    {
        $theses = null;

        /** TODO prendre les non substitué **/
        $ecoles = $this->getStructureService()->findAllStructuresAffichablesByType(TypeStructure::CODE_ECOLE_DOCTORALE, 'libelle');
        $unites = $this->getStructureService()->findAllStructuresAffichablesByType(TypeStructure::CODE_UNITE_RECHERCHE, 'libelle');
        $etablissements = $this->getEtablissementService()->getRepository()->findAllEtablissementsInscriptions();

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
        $structure = null;
        switch ($structureType) {
            case "ED" :
                $structure = $this->getEcoleDoctoraleService()->getRepository()->findOneBy(['id'=>$structureId]);
                break;
            case "UR" :
                $structure = $this->getUniteRechercheService()->getRepository()->findOneBy(['id'=>$structureId]);
                break;
            case "ETAB" :
                $structure = $this->getEtablissementService()->getRepository()->findOneBy(['id'=>$structureId]);
                break;
        }
        $qb = $this->decorateWithStructure($qb, $structureType, $structureId);

        $dateType = $this->params()->fromQuery("date_type");
        $dateDebut = $this->params()->fromQuery("date_min");
        $dateFin = $this->params()->fromQuery("date_max");
        $qb = $this->decorateWithDate($qb, $dateType, $dateDebut, $dateFin);


        $theses = $qb->getQuery()->execute();



        return new ViewModel([
            'theses' => $theses,
            'ecoles' => $ecoles,
            'unites' => $unites,
            'etablissements' => $etablissements,

            'type' => $structureType,
            'structure' => $structure,
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
                    $ecole = $this->getEcoleDoctoraleService()->getRepository()->findOneBy(['id'=>$id]);
                    return  $qb->andWhere("t.ecoleDoctorale = :ed")
                                ->setParameter(":ed", $ecole);
                case "UR" :
                    $unite = $this->getUniteRechercheService()->getRepository()->findOneBy(['id'=>$id]);
                    return $qb->andWhere("t.uniteRecherche = :ur")
                                ->setParameter(":ur", $unite);
                case "ETAB" :
                    $etablissement = $this->getEtablissementService()->getRepository()->findOneBy(['id'=>$id]);
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