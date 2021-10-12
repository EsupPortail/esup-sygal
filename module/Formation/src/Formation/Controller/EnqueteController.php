<?php

namespace Formation\Controller;

use Application\Controller\AbstractController;
use Doctrine\Common\Collections\ArrayCollection;
use Formation\Entity\Db\EnqueteCategorie;
use Formation\Entity\Db\EnqueteQuestion;
use Formation\Entity\Db\EnqueteReponse;
use Formation\Entity\Db\Inscription;
use Formation\Form\EnqueteCategorie\EnqueteCategorieFormAwareTrait;
use Formation\Form\EnqueteQuestion\EnqueteQuestionFormAwareTrait;
use Formation\Form\EnqueteReponse\EnqueteReponseFormAwareTrait;
use Formation\Service\EnqueteCategorie\EnqueteCategorieServiceAwareTrait;
use Formation\Service\EnqueteQuestion\EnqueteQuestionServiceAwareTrait;
use Formation\Service\EnqueteReponse\EnqueteReponseServiceAwareTrait;
use UnicaenApp\Service\EntityManagerAwareTrait;
use Laminas\View\Model\ViewModel;

class EnqueteController extends AbstractController {
    use EntityManagerAwareTrait;
    use EnqueteCategorieServiceAwareTrait;
    use EnqueteQuestionServiceAwareTrait;
    use EnqueteReponseServiceAwareTrait;
    use EnqueteCategorieFormAwareTrait;
    use EnqueteQuestionFormAwareTrait;
    use EnqueteReponseFormAwareTrait;

    /** ENQUETE *******************************************************************************************************/

    public function afficherResultatsAction()
    {
        $questions = $this->getEntityManager()->getRepository(EnqueteQuestion::class)->findAll();
        $questions = array_filter($questions, function (EnqueteQuestion $a) { return $a->estNonHistorise();});
        usort($questions, function (EnqueteQuestion $a, EnqueteQuestion $b) { return $a->getOrdre() > $b->getOrdre();});

        //todo exploiter le filtre pour réduire
        $reponses = $this->getEntityManager()->getRepository(EnqueteReponse::class)->findAll();
        $reponses = array_filter($reponses, function (EnqueteReponse $a) { return $a->estNonHistorise();});
        usort($reponses, function (EnqueteReponse $a, EnqueteReponse $b) { return $a->getQuestion()->getId() > $b->getQuestion()->getId();});

        /** PREP HISTOGRAMME $histogramme */
        $histogramme = [];
        foreach ($questions as $question) {
            $histogramme[$question->getId()] = [];
            foreach (EnqueteReponse::NIVEAUX as $clef => $value) $histogramme[$question->getId()][$clef] = 0;
        }

        $array = [];

        /** @var EnqueteReponse $reponse */
        foreach ($reponses as $reponse) {
            if ($reponse->getQuestion()->estNonHistorise()) {
                $question = $reponse->getQuestion()->getId();
                $inscription = $reponse->getInscription()->getId();

                $niveau = $reponse->getNiveau();
                $description = $reponse->getDescription();

                $array[$inscription]["Niveau_" . $question] = EnqueteReponse::NIVEAUX[$niveau];
                $array[$inscription]["Commentaire_" . $question] = $description;
                $histogramme[$question][$niveau]++;
            }
        }

        $categories = $this->getEntityManager()->getRepository(EnqueteCategorie::class)->findAll();
        return new ViewModel([
            "array" => $array,
            "histogramme" => $histogramme,
            "nbReponses" => count($array),
            "questions" => $questions,
            "categories" => $categories,
        ]);
    }

    /** QUESTIONS *****************************************************************************************************/

    public function afficherQuestionsAction() {

        $questions = $this->getEntityManager()->getRepository(EnqueteQuestion::class)->findAll();
        $categories = $this->getEntityManager()->getRepository(EnqueteCategorie::class)->findAll();

        return new ViewModel([
            'categories' => $categories,
            'questions' => $questions,
        ]);
    }

    public function ajouterCategorieAction() {

        $question = new EnqueteCategorie();

        $form = $this->getEnqueteCategorieForm();
        $form->setAttribute('action', $this->url()->fromRoute('formation/enquete/categorie/ajouter', [], [], true));
        $form->bind($question);

        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            $form->setData($data);
            if ($form->isValid()) {
                $this->getEnqueteCategorieService()->create($question);
            }
        }

        $vm =  new ViewModel([
            'title' => "Ajout d'une catégorie",
            'form' => $form,
        ]);
        $vm->setTemplate('formation/default/default-form');
        return $vm;
    }

    public function modifierCategorieAction() {

        /** @var EnqueteCategorie $categorie */
        $categorie = $this->getEntityManager()->getRepository(EnqueteCategorie::class)->getRequestedEnqueteCategorie($this);

        $form = $this->getEnqueteCategorieForm();
        $form->setAttribute('action', $this->url()->fromRoute('formation/enquete/categorie/modifier', ['categorie' => $categorie->getId()], [], true));
        $form->bind($categorie);

        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            $form->setData($data);
            if ($form->isValid()) {
                $this->getEnqueteCategorieService()->update($categorie);
            }
        }

        $vm =  new ViewModel([
            'title' => "Modification de la catégorie",
            'form' => $form,
        ]);
        $vm->setTemplate('formation/default/default-form');
        return $vm;
    }

    public function historiserCategorieAction()
    {
        /** @var EnqueteCategorie $categorie */
        $categorie = $this->getEntityManager()->getRepository(EnqueteCategorie::class)->getRequestedEnqueteCategorie($this);
        $retour = $this->params()->fromQuery('retour');

        $this->getEnqueteCategorieService()->historise($categorie);

        if ($retour) return $this->redirect()->toUrl($retour);
        return $this->redirect()->toRoute('formation/enquete/question', [], [], true);
    }

    public function restaurerCategorieAction()
    {
        /** @var EnqueteCategorie $categorie */
        $categorie = $this->getEntityManager()->getRepository(EnqueteCategorie::class)->getRequestedEnqueteCategorie($this);
        $retour = $this->params()->fromQuery('retour');

        $this->getEnqueteCategorieService()->restore($categorie);

        if ($retour) return $this->redirect()->toUrl($retour);
        return $this->redirect()->toRoute('formation/enquete/question', [], [], true);
    }

    public function supprimerCategorieAction()
    {
        /** @var EnqueteCategorie|null $categorie */
        $categorie = $this->getEntityManager()->getRepository(EnqueteCategorie::class)->getRequestedEnqueteCategorie($this);

        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            if ($data["reponse"] === "oui") $this->getEnqueteCategorieService()->delete($categorie);
            exit();
        }

        $vm = new ViewModel();
        if ($categorie !== null) {
            $vm->setTemplate('formation/default/confirmation');
            $vm->setVariables([
                'title' => "Suppression de la catégorie",
                'text' => "La suppression est définitive êtes-vous sûr&middot;e de vouloir continuer ?",
                'action' => $this->url()->fromRoute('formation/enquete/categorie/supprimer', ["categorie" => $categorie->getId()], [], true),
            ]);
        }
        return $vm;
    }

    public function ajouterQuestionAction() {

        $question = new EnqueteQuestion();

        $form = $this->getEnqueteQuestionForm();
        $form->setAttribute('action', $this->url()->fromRoute('formation/enquete/question/ajouter', [], [], true));
        $form->bind($question);

        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            $form->setData($data);
            if ($form->isValid()) {
                $this->getEnqueteQuestionService()->create($question);
            }
        }

        $vm =  new ViewModel([
            'title' => "Ajout d'une question",
            'form' => $form,
        ]);
        $vm->setTemplate('formation/default/default-form');
        return $vm;
    }

    public function modifierQuestionAction() {

        /** @var EnqueteQuestion $question */
        $question = $this->getEntityManager()->getRepository(EnqueteQuestion::class)->getRequestedEnqueteQuestion($this);

        $form = $this->getEnqueteQuestionForm();
        $form->setAttribute('action', $this->url()->fromRoute('formation/enquete/question/modifier', ['question' => $question->getId()], [], true));
        $form->bind($question);

        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            $form->setData($data);
            if ($form->isValid()) {
                $this->getEnqueteQuestionService()->update($question);
            }
        }

        $vm =  new ViewModel([
            'title' => "Modification de la question",
            'form' => $form,
        ]);
        $vm->setTemplate('formation/default/default-form');
        return $vm;
    }

    public function historiserQuestionAction()
    {
        /** @var EnqueteQuestion $question */
        $question = $this->getEntityManager()->getRepository(EnqueteQuestion::class)->getRequestedEnqueteQuestion($this);
        $retour = $this->params()->fromQuery('retour');

        $this->getEnqueteQuestionService()->historise($question);

        if ($retour) return $this->redirect()->toUrl($retour);
        return $this->redirect()->toRoute('formation/enquete/question', [], [], true);
    }

    public function restaurerQuestionAction()
    {
        /** @var EnqueteQuestion $question */
        $question = $this->getEntityManager()->getRepository(EnqueteQuestion::class)->getRequestedEnqueteQuestion($this);
        $retour = $this->params()->fromQuery('retour');

        $this->getEnqueteQuestionService()->restore($question);

        if ($retour) return $this->redirect()->toUrl($retour);
        return $this->redirect()->toRoute('formation/enquete/question', [], [], true);
    }

    public function supprimerQuestionAction()
    {
        /** @var EnqueteQuestion|null $question */
        $question = $this->getEntityManager()->getRepository(EnqueteQuestion::class)->getRequestedEnqueteQuestion($this);

        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            if ($data["reponse"] === "oui") $this->getEnqueteQuestionService()->delete($question);
            exit();
        }

        $vm = new ViewModel();
        if ($question !== null) {
            $vm->setTemplate('formation/default/confirmation');
            $vm->setVariables([
                'title' => "Suppression de la question",
                'text' => "La suppression est définitive êtes-vous sûr&middot;e de vouloir continuer ?",
                'action' => $this->url()->fromRoute('formation/enquete/question/supprimer', ["question" => $question->getId()], [], true),
            ]);
        }
        return $vm;
    }

    /** REPONSES ******************************************************************************************************/
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

        $categories = $this->getEntityManager()->getRepository(EnqueteCategorie::class)->findAll();

        return new ViewModel([
            'inscription' => $inscription,
            'questions' => $questions,
            'categories' => $categories,
            'form' => $form,
        ]);
    }
}