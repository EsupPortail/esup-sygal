<?php

namespace Soutenance\Controller;

use Application\Controller\AbstractController;
use Application\Entity\Db\Acteur;
use Application\Entity\Db\NatureFichier;
use Application\Entity\Db\These;
use Application\Entity\Db\Utilisateur;
use Application\Entity\Db\VersionFichier;
use Application\Service\Acteur\ActeurServiceAwareTrait;
use Application\Service\Fichier\FichierServiceAwareTrait;
use Application\Service\These\TheseServiceAwareTrait;
use Application\Service\UserContextServiceAwareTrait;
use Application\Service\Utilisateur\UtilisateurServiceAwareTrait;
use Application\Service\Validation\ValidationServiceAwareTrait;
use BjyAuthorize\Exception\UnAuthorizedException;
use Notification\Service\NotifierServiceAwareTrait;
use Soutenance\Entity\Avis;
use Soutenance\Entity\Membre;
use Soutenance\Form\Avis\AvisForm;
use Soutenance\Provider\Privilege\AvisSoutenancePrivileges;
use Soutenance\Service\Avis\AvisServiceAwareTrait;
use Soutenance\Service\Membre\MembreServiceAwareTrait;
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
    use AvisServiceAwareTrait;
    use MembreServiceAwareTrait;

    public function indexAction()
    {
        /** @var These $these */
        $idThese = $this->params()->fromRoute('these');
        $these = $this->getTheseService()->getRepository()->find($idThese);
        /** @var Membre $membre */
        $idMembre = $this->params()->fromRoute('rapporteur');
        $membre = $this->getMembreService()->find($idMembre);
        /** @var Acteur $rapporteur */
        $rapporteur = $this->getActeurService()->getRepository()->findActeurByIndividu($membre->getIndividu());

        $isAllowed = $this->isAllowed($rapporteur, AvisSoutenancePrivileges::SOUTENANCE_AVIS_VISUALISER);
        if (!$isAllowed) {
            throw new UnAuthorizedException("Vous êtes non authorisé(e) à visualiser l'avis de soutenance de ".$rapporteur->getIndividu()->getNomComplet().".");
        }

        $avis = $this->getAvisService()->getAvisByRapporteur($rapporteur,$these);
        if ($avis === null) {
            $avis = new Avis();
            $avis->setRapporteur($rapporteur);
            $avis->setThese($these);
        }

        /** @var AvisForm $form */
        $form = $this->getServiceLocator()->get('FormElementManager')->get(AvisForm::class);
        $form->bind($avis);

        /** @var Request $request */
        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            $form->setData($data);
            if ($form->isValid()) {
                $validation = $this->getValidationService()->signerAvisSoutenance($these, $membre->getIndividu());
                $avis->setValidation($validation);
                if ($avis->getId()) {
                    $this->getAvisService()->update($avis);
                }
                else $this->getAvisService()->create($avis);
            }
        }

        $view = $this->createViewForFichierAction( $rapporteur);
        $view->setVariable('isVisible', true);
        $view->setVariable('maxUploadableFilesCount', 1);
        $view->setVariable('these', $these);
        $view->setVariable('rapporteur', $rapporteur);
        $view->setVariable('form', $form);
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