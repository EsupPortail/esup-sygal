<?php

namespace Soutenance\Controller;

use Application\Controller\AbstractController;
use Application\Entity\Db\Acteur;
use Application\Entity\Db\NatureFichier;
use Application\Entity\Db\These;
use Application\Entity\Db\TypeValidation;
use Application\Entity\Db\Utilisateur;
use Application\Entity\Db\VersionFichier;
use Application\Service\Acteur\ActeurServiceAwareTrait;
use Application\Service\Fichier\FichierServiceAwareTrait;
use Application\Service\These\TheseServiceAwareTrait;
use Application\Service\UserContextServiceAwareTrait;
use Application\Service\Utilisateur\UtilisateurServiceAwareTrait;
use Application\Service\Validation\ValidationServiceAwareTrait;
use Notification\Service\NotifierServiceAwareTrait;
use Soutenance\Entity\Avis;
use Soutenance\Form\Avis\AvisForm;
use UnicaenApp\Exception\RuntimeException;
use Zend\Form\Element\Hidden;
use Zend\Http\Request;
use Zend\View\Model\ViewModel;

class AvisSoutenanceController extends AbstractController {
    use TheseServiceAwareTrait;
    use ActeurServiceAwareTrait;
    use NotifierServiceAwareTrait;
    use ValidationServiceAwareTrait;
    use UserContextServiceAwareTrait;
    use FichierServiceAwareTrait;
    use UtilisateurServiceAwareTrait;

    public function indexAction()
    {
        /** @var These $these */
        $idThese = $this->params()->fromRoute('these');
        $these = $this->getTheseService()->getRepository()->find($idThese);
        /** @var Acteur $rapporteur */
        $idRapporteur = $this->params()->fromRoute('rapporteur');
        $rapporteur = $this->getActeurService()->getRepository()->findActeurByIndividu($idRapporteur);

        $avis = new Avis();

        $form = $this->getServiceLocator()->get('FormElementManager')->get(AvisForm::class);

//        return new ViewModel([
//            'form'          => $form,
//            'fichier'       => $fichier,
//            'these'         => $these,
//            'rapporteur'    => $rapporteur,
//        ]);

//        $validation = current($this->getValidationService()->getRepository()->findValidationByCodeAndThese(TypeValidation::CODE_AVIS_SOUTENANCE, $these));
//
//
//
        /** @var Request $request */
        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            var_dump($data);
        }

        $view = $this->createViewForFichierAction( $rapporteur);
        $view->setVariable('isVisible', true);
        $view->setVariable('maxUploadableFilesCount', 1);
        $view->setVariable('these', $these);
        $view->setVariable('rapporteur', $rapporteur);
        $view->setVariable('form', $form);
//        $view->setVariable('validation', $validation);
        return $view;

    }

    /**
     * @param Acteur $rapporteur
     * @return ViewModel
     */
    private function createViewForFichierAction(Acteur $rapporteur)
    {
        $these = $this->requestedThese();
        $nature = $this->fichierService->fetchNatureFichier(NatureFichier::CODE_PRE_RAPPORT_SOUTENANCE);
        $version = $this->fichierService->fetchVersionFichier(VersionFichier::CODE_ORIG);

        if (!$nature) {
            throw new RuntimeException("Nature de fichier introuvable: " . NatureFichier::CODE_PRE_RAPPORT_SOUTENANCE);
        }

        $form = $this->uploader()->getForm();
        $form->setAttribute('id', uniqid('form-'));
//        $form->setUploadMaxFilesize('50M');
        $form->addElement((new Hidden('nature'))->setValue($nature->getCode()));
        $form->addElement((new Hidden('version'))->setValue($version->getCode()));
        $form->get('files')->setLabel("")->setAttribute('multiple', false)/*->setAttribute('accept', '.pdf')*/;

        $fichierStuff = null;
//        if ($fichier)
//            $fichierStuff = [
//                'listUrl'   => $this->urlFichierThese()->listerFichiers($these, $nature, $version, false, ['inclureValidite' => false]),
//                'downloadUrl' => $this->urlFichierThese()->telechargerFichierThese($these, $fichier),
//                'deleteUrl' => $this->urlFichierThese()->supprimerFichierThese($these, $fichier),
//            ];


        $utilisateurs = $this->utilisateurService->getRepository()->findByIndividu($rapporteur->getIndividu());
        if (empty($utilisateurs)) {
            throw new RuntimeException("Aucun utilisateur trouvé correspond au rapporteur [".$rapporteur->getId()." - " . $rapporteur->getIndividu()->getNomComplet()."]");
        }

        //TODO Que faire lorsque plusieurs utilisateurs sont remontés pour un même individu. (shib est favorisé)
        /** @var Utilisateur $utilisateur */
        $utilisateur = null;
        $utilisateursShib = array_filter($utilisateurs, function (Utilisateur $u) { return $u->getPassword() === Utilisateur::PASSWORD_SHIB;});
        if (!empty($utilisateursShib)) {
            $utilisateur = current($utilisateursShib);
        } else {
            $utilisateur = current($utilisateurs);
        }

        $view = new ViewModel([
            'these'           => $these,
            'uploadUrl'       => $this->urlFichierThese()->televerserFichierThese($these),
            'listUrl'         => $this->urlFichierThese()->listerFichiersPreRapportByUtilisateur($these, $utilisateur),
            'nature'          => $nature,
            'version'         => $version,
        ]);

        return $view;
    }
}