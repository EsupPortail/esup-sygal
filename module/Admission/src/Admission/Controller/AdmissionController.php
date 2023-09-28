<?php /** @noinspection PhpUnusedAliasInspection */

namespace Admission\Controller;

use Admission\Form\Etudiant\EtudiantForm;
use Laminas\Http\Response;
use Laminas\View\Model\ViewModel;

class AdmissionController extends AdmissionAbstractController {

    public function __construct(EtudiantForm $form)
    {
        $this->form = $form;
    }

    public function ajouterAction()
    {
        return $this->multipageForm($this->form)
            ->setUsePostRedirectGet()
            ->setReuseRequestQueryParams() // la redir vers la 1ere étape conservera les nom, prenom issus de la recherche
            ->start(); // réinit du plugin et redirection vers la 1ère étape
    }

    public function etudiantAction()
    {
        $this->form->get('etudiant')->get('infosEtudiant')->setUrlPaysNationalite($this->url()->fromRoute('pays/rechercher-pays', [], [], true));
        $this->form->get('etudiant')->get('infosEtudiant')->setUrlNationalite($this->url()->fromRoute('pays/rechercher-nationalite', [], [], true));

        $response = $this->processMultipageForm($this->form);
        if ($response instanceof Response) {
            return $response;
        }

        $response->setTemplate('admission/ajouter-etudiant');

        return $response;
    }

    public function inscriptionAction()
    {
        $this->form->get('inscription')->get('specifitesEnvisagees')->setUrlPaysNationalite($this->url()->fromRoute('pays/rechercher-pays', [], [], true));
        $data = $this->multipageForm($this->form)->getFormSessionData();
        $this->form->bindValues($data);
        $response = $this->processMultipageForm($this->form);
        if ($response instanceof Response) {
            return $response;
        }

        $response->setTemplate('admission/ajouter-inscription');

        return $response;
    }

    public function financementAction()
    {
        $response = $this->processMultipageForm($this->form);
        if ($response instanceof Response) {
            return $response;
        }

        $response->setTemplate('admission/ajouter-financement');

        return $response;
    }

    public function validationAction()
    {
        $response = $this->processMultipageForm($this->form);
        if ($response instanceof Response) {
            return $response;
        }
        $response->setVariable('data_form', "coucou");
        $response->setTemplate('admission/ajouter-validation');

        return $response;
    }



    public function annulerAction()
    {
        $this->multipageForm()->clearSession();

        // todo: fournir une page de demande de confirmation d'annulation via le plugin multipageForm() ?
        return $this->redirect()->toRoute('admission/ajouter/etudiant');
    }

    public function confirmerAction()
    {
        $response = $this->multipageForm($this->form)->process();
        if ($response instanceof Response) {
            return $response;
        }
        return array('form' => $this->form);
    }

//    public function ajouterEnregistrerAction()
//    {
//        $data = $this->multipageForm($this->getForm())->getFormSessionData();
//        // ...
//        // enregistrement en base de données (par exemple)
//        // ...
//        return $this->redirect()->toRoute('home');
//    }


//    public function addInformationsEtudiantAction() : ViewModel
//    {
//        $this->form->get('etudiant')->get('infosEtudiant')->setUrlPaysNationalite($this->url()->fromRoute('pays/rechercher-pays', [], [], true));
//        $this->form->get('etudiant')->get('infosEtudiant')->setUrlNationalite($this->url()->fromRoute('pays/rechercher-nationalite', [], [], true));
//        return new ViewModel([
//            'form' => $this->form,
//        ]);
//    }
//    public function addInformationsInscriptionAction() : ViewModel
//    {
//        $this->form->get('inscription')->get('specifitesEnvisagees')->setUrlPaysNationalite($this->url()->fromRoute('pays/rechercher-pays', [], [], true));
//        return new ViewModel([
//            'form' => $this->form,
//        ]);
//    }
//
//    public function addInformationsFinancementAction() : ViewModel
//    {
//        return new ViewModel([
//            'form' => $this->form,
//        ]);
//    }
//
//    public function addInformationsJustificatifsAction() : ViewModel
//    {
//        return new ViewModel([
//            'form' => $this->form,
//        ]);
//    }
}