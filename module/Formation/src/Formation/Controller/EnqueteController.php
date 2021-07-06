<?php

namespace Formation\Controller;

use Application\Controller\AbstractController;
use Formation\Entity\Db\EnqueteQuestion;
use Formation\Entity\Db\EnqueteReponse;
use Formation\Entity\Db\Inscription;
use Formation\Form\EnqueteReponse\EnqueteReponseFormAwareTrait;
use UnicaenApp\Service\EntityManagerAwareTrait;
use Zend\View\Model\ViewModel;

class EnqueteController extends AbstractController {
    use EntityManagerAwareTrait;
    use EnqueteReponseFormAwareTrait;

    public function afficherQuestionsAction() {

        $questions = $this->getEntityManager()->getRepository(EnqueteQuestion::class)->findAll();
        $questions = array_filter($questions, function (EnqueteQuestion $a) { return $a->estNonHistorise();});
        usort($questions, function (EnqueteQuestion $a, EnqueteQuestion $b) { return $a->getOrdre() > $b->getOrdre();});

        return new ViewModel([
            'questions' => $questions,
        ]);
    }

    public function repondreQuestionsAction()
    {
        /** @var Inscription $inscripiton */
        $inscription = $this->getEntityManager()->getRepository(Inscription::class)->getRequestedInscription($this);
        /** @var EnqueteQuestion[] $questions */
        $questions = $this->getEntityManager()->getRepository(EnqueteQuestion::class)->findAll();
        $questions = array_filter($questions, function (EnqueteQuestion $a) { return $a->estNonHistorise();});
        usort($questions, function (EnqueteQuestion $a, EnqueteQuestion $b) { return $a->getOrdre() > $b->getOrdre();});

        $form = $this->getEnqueteReponseForm();



        return new ViewModel([
            'inscription' => $inscription,
            'questions' => $questions,
            'form' => $form,
        ]);
    }
}