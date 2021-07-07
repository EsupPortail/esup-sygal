<?php

namespace Formation\Controller;

use Application\Controller\AbstractController;
use Doctrine\Common\Collections\ArrayCollection;
use Formation\Entity\Db\EnqueteQuestion;
use Formation\Entity\Db\EnqueteReponse;
use Formation\Entity\Db\Inscription;
use Formation\Form\EnqueteReponse\EnqueteReponseFormAwareTrait;
use Formation\Service\EnqueteReponse\EnqueteReponseServiceAwareTrait;
use UnicaenApp\Service\EntityManagerAwareTrait;
use Zend\View\Model\ViewModel;

class EnqueteController extends AbstractController {
    use EntityManagerAwareTrait;
    use EnqueteReponseServiceAwareTrait;
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
        /** @var Inscription $inscription */
        $inscription = $this->getEntityManager()->getRepository(Inscription::class)->getRequestedInscription($this);
        /** @var EnqueteQuestion[] $questions */
        $questions = $this->getEntityManager()->getRepository(EnqueteQuestion::class)->findAll();
        $questions = array_filter($questions, function (EnqueteQuestion $a) { return $a->estNonHistorise();});
        $dictionnaireQuestion = [];
        foreach ($questions as $question) $dictionnaireQuestion[$question->getId()] = $question;
        usort($questions, function (EnqueteQuestion $a, EnqueteQuestion $b) { return $a->getOrdre() > $b->getOrdre();});
        /** @var EnqueteReponse[] $reponses */
        $reponses = $this->getEntityManager()->getRepository(EnqueteReponse::class)->findEnqueteReponseByInscription($inscription);
        $reponses = array_filter($reponses, function (EnqueteReponse $a) { return $a->estNonHistorise();});
        $dictionnaireReponse = [];
        foreach ($reponses as $reponse) $dictionnaireReponse[$reponse->getQuestion()->getId()] = $reponse;
        $enquete = new ArrayCollection();
        foreach ($dictionnaireQuestion as $id => $question) {
            $reponse = $dictionnaireReponse[$id];
            if ($reponse === null) {
                $reponse = new EnqueteReponse();
                $reponse->setInscription($inscription);
                $reponse->setQuestion($question);
            }
            $element = [$question, $reponse];
            $enquete->add($element);
        }

        $form = $this->getEnqueteReponseForm();
        $form->setAttribute('action', $this->url()->fromRoute('formation/enquete/repondre-questions', ['isncription' => $inscription->getId()], [], true));
        $form->bind($enquete);

        $request = $this->getRequest();
        if($request->isPost()) {
            $data = $request->getPost();
            $form->setData($data);
            if ($form->isValid()) {
                foreach ($enquete as $element) {
                    [$question, $reponse] = $element;
                    if ($reponse->getHistoCreation()) $this->getEnqueteReponseService()->update($reponse);
                    else $this->getEnqueteReponseService()->create($reponse);
                }
            }
        }

        return new ViewModel([
            'inscription' => $inscription,
            'questions' => $questions,
            'form' => $form,
        ]);
    }
}