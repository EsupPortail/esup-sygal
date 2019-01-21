<?php

namespace Information\Controller;

use Application\Entity\Db\Fichier;
use Application\Entity\Db\NatureFichier;
use Application\Entity\Db\VersionFichier;
use Application\Service\Fichier\FichierServiceAwareTrait;
use Application\Service\Utilisateur\UtilisateurServiceAwareTrait;
use Zend\Form\Element\Hidden;
use Zend\Form\Form;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class FichierController extends AbstractActionController {
    use FichierServiceAwareTrait;
    use UtilisateurServiceAwareTrait;

    public function indexAction()
    {
        $nature = $this->fichierService->fetchNatureFichier(NatureFichier::CODE_INFORMATION);
        $fichiers = $this->fichierService->getRepository()->fetchFichiersByNature($nature);
        return new ViewModel([
            'fichiers' => $fichiers,
        ]);
    }

    public function ajouterAction()
    {
        $nature = $this->fichierService->fetchNatureFichier(NatureFichier::CODE_INFORMATION);
        $version = $this->fichierService->fetchVersionFichier(VersionFichier::CODE_ORIG);

        /** @var Form $form */
        $form = $this->uploader()->getForm();
        $form->setAttribute('id', uniqid('form-'));
//        $form->setUploadMaxFilesize('50M');
        $form->addElement((new Hidden('nature'))->setValue($nature->getCode()));
        $form->addElement((new Hidden('version'))->setValue($version->getCode()));
        $form->get('files')->setLabel("")->setAttribute('multiple', false)/*->setAttribute('accept', '.pdf')*/;

        $fichierStuff = null;
        $utilisateurs = $this->utilisateurService->getRepository()->findByIndividu($rapporteur->getIndividu());

        //TODO Que faire lorsque plusieurs utilisateurs sont remontés pour un même individu. (shib est favorisé)
        /** @var Utilisateur $utilisateur */
        $utilisateur = null;
        $utilisateursShib = array_filter($utilisateurs, function (Utilisateur $u) { return $u->getPassword() === Utilisateur::PASSWORD_SHIB;});
        if (!empty($utilisateursShib)) {
            $utilisateur = current($utilisateursShib);
        } else {
            $utilisateur = current($utilisateurs);
        }

    }

    public function supprimerAction()
    {
        /** @var Fichier $fichier */
        $id = $this->params()->fromRoute('id');
        $fichier = $this->fichierService->getRepository()->find($id);

        $fichier->historiser();
        $this->fichierService->update($fichier);
        $this->redirect()->toRoute('informations/fichiers', [], [], true);
    }

}