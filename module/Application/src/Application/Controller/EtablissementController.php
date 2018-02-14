<?php

namespace Application\Controller;

class EtablissementController extends AbstractController
{

    public function indexAction()
    {
        $selected = $this->params()->fromQuery('selected');
        $etablissements = null;

        /** la pagination est desactivée pour le moment */
//        /** @var DefaultQueryBuilder $qb */
//        $qb = $this->etablissementService->getRepository()->createQueryBuilder('ed');
//        $qb->addOrderBy('ed.libelle');
//
//        $paginator = new \Zend\Paginator\Paginator(new DoctrinePaginator(new Paginator($qb, true)));
//        $paginator
//            ->setPageRange(30)
//            ->setItemCountPerPage(20)
//            ->setCurrentPageNumber(1);

        /** les individus sont desactivés pour le moment */
//        $ecoleDoctoraleIndividus = null;
//        if ($selected) {
//            /** @var EcoleDoctorale $ed */
//            $ed = $this->ecoleDoctoraleService->getRepository()->find($selected);
//            $ecoleDoctoraleIndividus = $ed->getEcoleDoctoraleIndividus();
//        }

        return new ViewModel([
            'etablissements' => $etablissements,
            'selected' => $selected,

        ]);
    }
}