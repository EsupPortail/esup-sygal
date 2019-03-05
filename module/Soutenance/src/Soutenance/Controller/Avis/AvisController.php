<?php

namespace Soutenance\Controller\Avis;

use Application\Controller\AbstractController;
use Application\Entity\Db\Individu;
use Application\Entity\Db\NatureFichier;
use Application\Entity\Db\These;
use Application\Entity\Db\VersionFichier;
use Application\Service\Acteur\ActeurServiceAwareTrait;
use Application\Service\Fichier\FichierServiceAwareTrait;
use Application\Service\These\TheseServiceAwareTrait;
use Application\Service\UserContextServiceAwareTrait;
use Application\Service\Utilisateur\UtilisateurServiceAwareTrait;
use Notification\Service\NotifierServiceAwareTrait;
use Soutenance\Entity\Avis;
use Soutenance\Entity\Membre;
use Soutenance\Filter\NomAvisFormatter;
use Soutenance\Form\Avis\AvisForm;
use Soutenance\Form\Avis\AvisFormAwareTrait;
use Soutenance\Service\Avis\AvisServiceAwareTrait;
use Soutenance\Service\Membre\MembreServiceAwareTrait;
use Soutenance\Service\Validation\ValidatationServiceAwareTrait;
use Zend\Http\Request;
use Zend\View\Model\ViewModel;

class AvisController extends AbstractController {
    use TheseServiceAwareTrait;
    use ActeurServiceAwareTrait;
    use NotifierServiceAwareTrait;
    use UserContextServiceAwareTrait;
    use FichierServiceAwareTrait;
    use UtilisateurServiceAwareTrait;
    use AvisServiceAwareTrait;
    use MembreServiceAwareTrait;
    use ValidatationServiceAwareTrait;
    use AvisFormAwareTrait;

    public function indexAction()
    {
        /** @var These $these */
        $theseId    = $this->params()->fromRoute('these');
        $these      = $this->getTheseService()->getRepository()->find($theseId);
        /** @var Membre $membre */
        $idMembre = $this->params()->fromRoute('rapporteur');
        $membre = $this->getMembreService()->find($idMembre);
        /** @var Individu $rapporteur */
        $rapporteur = $membre->getIndividu();

        $avis = $this->getAvisService()->getAvisByMembre($these, $membre);

        if ($avis !== null) {
            $this->redirect()->toRoute('soutenance/avis-soutenance/afficher', ['these' => $these->getId(), 'rapporteur' => $membre->getId()]);
        }

        /** @var AvisForm $form */
        $form = $this->getAvisForm();

        /** @var Request $request */
        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            $files = ['files' => $request->getFiles()->toArray()];

            if ($files['files']['rapport']['size'] === 0) {
                $this->flashMessenger()->addErrorMessage("Pas de prérapport de soutenance !");
                $this->redirect()->toRoute('soutenance/avis-soutenance', ['these' => $these->getId(), 'rapporteur' => $membre->getId()]);
            }
            if ($data['avis'] === "Défavorable" && trim($data['motif']) == '') {
                $this->flashMessenger()->addErrorMessage("Vous devez motivez votre avis défavorable en quelques mots.");
                $this->redirect()->toRoute('soutenance/avis-soutenance', ['these' => $these->getId(), 'rapporteur' => $membre->getId()]);
            }

            $form->setData($data);
            if ($form->isValid()) {

                $nature = $this->fichierService->fetchNatureFichier(NatureFichier::CODE_PRE_RAPPORT_SOUTENANCE);
                $version = $this->fichierService->fetchVersionFichier(VersionFichier::CODE_ORIG);
                $fichiers = $this->fichierService->createFichiersFromUpload($these, $files, $nature, $version, null, new NomAvisFormatter($membre->getIndividu()));
                $fichier = current($fichiers);

                $validation = $this->getValidationService()->signerAvisSoutenance($these, $membre->getIndividu());

                $avis = new Avis();
                $avis->setThese($these);
                $avis->setRapporteur($rapporteur);
                $avis->setFichier($fichier);
                $avis->setValidation($validation);
                $avis->setAvis($data['avis']);
                $avis->setMotif($data['motif']);
                $this->getAvisService()->create($avis);

                $this->redirect()->toRoute('soutenance/avis-soutenance/afficher', ['these' => $these->getId(), 'membre' => $membre->getId()], [], true);
            }
        }
        return new ViewModel([
            'form' => $form,
        ]);
    }

    public function afficherAction() {
        /** @var These $these */
        $theseId = $this->params()->fromRoute('these');
        $these = $this->getTheseService()->getRepository()->find($theseId);
        /** @var Membre $membre */
        $membreId = $this->params()->fromRoute('rapporteur');
        $membre = $this->getMembreService()->find($membreId);
        $rapporteur = $this->getActeurService()->getRepository()->findActeurByIndividuAndThese($membre->getIndividu(), $these);
        /** @var Avis $avis */
        $avis = $this->getAvisService()->getAvisByMembre($these, $membre);

        return new ViewModel([
            'these' => $these,
            'rapporteur' => $rapporteur,
            'avis' => $avis,
            'url' => $this->urlFichierThese()->telechargerFichierThese($these, $avis->getFichier()),
        ]);
    }

    public function annulerAction() {
        /** @var These $these */
        $theseId = $this->params()->fromRoute('these');
        $these = $this->getTheseService()->getRepository()->find($theseId);
        /** @var Membre $membre */
        $membreId = $this->params()->fromRoute('rapporteur');
        $membre = $this->getMembreService()->find($membreId);
        /** @var Avis $avis */
        $avis = $this->getAvisService()->getAvisByMembre($these, $membre);

        //historisation de la validation associée et du prérapport
        $avis->getValidation()->historiser();
        $this->fichierService->getEntityManager()->flush($avis->getFichier());
        $avis->getFichier()->historiser();
        $this->getValidationService()->getEntityManager()->flush($avis->getValidation());
        $avis->historiser();
        $this->getAvisService()->update($avis);


        $this->redirect()->toRoute('soutenance/avis-soutenance', ['these' => $these->getId(), 'rapporteur' => $membreId], [], true);
    }

}