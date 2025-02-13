<?php

namespace Soutenance\Controller;

use Depot\Service\FichierHDR\FichierHDRServiceAwareTrait;
use Depot\Service\FichierThese\FichierTheseServiceAwareTrait;
use Fichier\Entity\Db\Fichier;
use Fichier\Entity\Db\NatureFichier;
use Fichier\Entity\Db\VersionFichier;
use Fichier\Service\Fichier\FichierServiceAwareTrait;
use Fichier\Service\Fichier\FichierStorageServiceAwareTrait;
use Fichier\Service\Storage\Adapter\Exception\StorageAdapterException;
use Laminas\Http\Request;
use Laminas\View\Model\ViewModel;
use Notification\Service\NotifierServiceAwareTrait;
use Soutenance\Entity\Avis;
use Soutenance\Entity\Proposition;
use Soutenance\Form\Avis\AvisForm;
use Soutenance\Form\Avis\AvisFormAwareTrait;
use Soutenance\Service\Avis\AvisServiceAwareTrait;
use Soutenance\Service\Membre\MembreServiceAwareTrait;
use Soutenance\Service\Notification\SoutenanceNotificationFactoryAwareTrait;
use These\Entity\Db\These;
use UnicaenApp\Exception\RuntimeException;

class AvisController extends AbstractSoutenanceController
{
    use AvisServiceAwareTrait;
    use MembreServiceAwareTrait;
    use NotifierServiceAwareTrait;
    use SoutenanceNotificationFactoryAwareTrait;

    use FichierStorageServiceAwareTrait;
    use FichierServiceAwareTrait;
    use FichierTheseServiceAwareTrait;
    use FichierHDRServiceAwareTrait;

    use AvisFormAwareTrait;

    public function indexAction()
    {
        $this->initializeFromType(true, false);
        $membre = $this->getMembreService()->getRequestedMembre($this, 'rapporteur');
        $acteur = $this->acteurService->getRepository()->findActeurForSoutenanceMembre($membre);
        
        $avis = $this->getAvisService()->getAvisByMembre($membre);

        if ($avis !== null) {
            $this->redirect()->toRoute("soutenance_{$this->type}/avis-soutenance/afficher", ['id' => $this->entity->getId(), 'rapporteur' => $membre->getId()], [], true);
        }

        /** @var AvisForm $form */
        $form = $this->getAvisForm();

        /** @var Request $request */
        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            $files = ['files' => $request->getFiles()->toArray()];

            if ($files['files']['rapport']['size'] === 0) {
                $this->flashMessenger()->addErrorMessage("Pas de pré-rapport de soutenance !");
                $this->redirect()->toRoute("soutenance_{$this->type}/avis-soutenance", ['id' => $this->entity->getId(), 'rapporteur' => $membre->getId()], [], true);
            }
            if ($data['avis'] === "Défavorable" && trim($data['motif']) == '') {
                $this->flashMessenger()->addErrorMessage("Vous devez motivez votre avis défavorable en quelques mots.");
                $this->redirect()->toRoute("soutenance_{$this->type}/avis-soutenance", ['id' => $this->entity->getId(), 'rapporteur' => $membre->getId()], [], true);
            }

            $form->setData($data);
            if ($form->isValid()) {

//                $fichier = $this->getAvisService()->createAvisFromUpload($files, $membre);


                $fichiers = [];
                if (!empty($files)) {

                    $file = new Fichier();
                    $fichierService = $this->proposition->getObject() instanceof These ?
                        $this->fichierTheseService :
                        $this->fichierHDRService;
                    $nature = $fichierService->fetchNatureFichier(NatureFichier::CODE_PRE_RAPPORT_SOUTENANCE);
                    $file->setNature($nature);
                    $nfiles['files'][0] =  $files['files']['rapport'] ;


                    $version = $fichierService->fetchVersionFichier(VersionFichier::CODE_ORIG);
                    $fichiers = $this->proposition->getObject() instanceof These ?
                        $fichierService->createFichierThesesFromUpload($this->entity, $nfiles , $nature, $version) :
                        $fichierService->createFichierHDRsFromUpload($this->entity, $nfiles , $nature, $version);
                }
//                var_dump($fichiers);
//                die();
                $validation = $this->validationService->signerAvisSoutenance($this->entity, $acteur->getIndividu());
                $avis = new Avis();
                $avis->setProposition($this->proposition);
                $avis->setMembre($membre);
                $avis->setFichier($fichiers[0]);
                $avis->setValidation($validation);
                $avis->setAvis($data['avis']);
                $avis->setMotif($data['motif']);
                $this->getAvisService()->create($avis);

                /**
                 * N.B. :  Après un dépôt penser à vérifier :
                 *   - peu importe l'avis il faut notifier à chaque dépot d'un avis ;
                 *   - si tous les avis sont déposés penser à notifier le bureau des doctorats.
                 */

                $allAvis = $this->getAvisService()->getAvisByProposition($this->proposition);
                $allRapporteurs = $this->getMembreService()->getRapporteursByProposition($this->proposition);

                if ($avis->getAvis() === Avis::FAVORABLE) {
                    try {
                        $notif = $this->soutenanceNotificationFactory->createNotificationAvisFavorable($this->entity, $avis);
                        $this->notifierService->trigger($notif);
                    } catch (\Notification\Exception\RuntimeException $e) {
                        // aucun destinataire, todo : cas à gérer !
                    }
                }
                if ($avis->getAvis() === Avis::DEFAVORABLE) {
                    try {
                        $notif = $this->soutenanceNotificationFactory->createNotificationAvisDefavorable($this->entity, $avis);
                        $this->notifierService->trigger($notif);
                    } catch (\Notification\Exception\RuntimeException $e) {
                        // aucun destinataire, todo : cas à gérer !
                    }
                }

                /** TODO ajouter un prédicat dans thèse ou soutenance ??? */
                if (count($allAvis) === count($allRapporteurs)) {
                    try {
                        $notif1 = $this->soutenanceNotificationFactory->createNotificationAvisRendus($this->entity);
                        $this->notifierService->trigger($notif1);
                        $notif2 = $this->soutenanceNotificationFactory->createNotificationAvisRendusDirection($this->entity);
                        $this->notifierService->trigger($notif2);
                    } catch (\Notification\Exception\RuntimeException $e) {
                        // aucun destinataire, todo : cas à gérer !
                    }
                }

                $this->redirect()->toRoute("soutenance_{$this->type}/avis-soutenance/afficher", ['id' => $this->entity->getId(), 'membre' => $membre->getId()], [], true);
            }
        }

        $rapporteur = $this->acteurService->getRepository()->findActeurForSoutenanceMembre($membre);

        return new ViewModel([
            'form' => $form,
            'object' => $this->entity,
//            'rapporteur' => $membre->getActeur(),
            'rapporteur' => $rapporteur,
            'typeProposition' => $this->type
        ]);
    }

    public function afficherAction()
    {
        $this->initializeFromType(false,false);
        $membre = $this->getMembreService()->getRequestedMembre($this, 'rapporteur');
//        $rapporteur = $membre->getActeur();
        $rapporteur = $this->acteurService->getRepository()->findActeurForSoutenanceMembre($membre);

        /** @var Avis $avis */
        $avis = $this->getAvisService()->getAvisByMembre($membre);

        return new ViewModel([
            'object' => $this->entity,
            'rapporteur' => $rapporteur,
            'membre' => $membre,
            'avis' => $avis,
            'url' => $this->type === Proposition::ROUTE_PARAM_PROPOSITION_THESE ?
                $this->urlFichierThese()->telechargerFichierThese($this->entity, $avis->getFichier()) :
                $this->urlFichierHDR()->telechargerFichierHDR($this->entity, $avis->getFichier()),
            'typeProposition' => $this->type
        ]);
    }

    public function annulerAction()
    {
        $this->initializeFromType(false,false, false);
        $membre = $this->getMembreService()->getRequestedMembre($this, 'rapporteur');
        /** @var Avis $avis */
        $avis = $this->getAvisService()->getAvisByMembre($membre);
        $this->getAvisService()->historiser($avis);

        $this->redirect()->toRoute("soutenance_{$this->type}/index-rapporteur", ['id' => $this->entity->getId()], [], true);
    }

    public function telechargerAction()
    {
        $membre = $this->getMembreService()->getRequestedMembre($this, 'rapporteur');
        $avis = $this->getAvisService()->getAvisByMembre($membre);
        $fichier = $avis->getFichier()?->getFichier();

        // injection préalable du contenu du fichier pour pouvoir utiliser le plugin Uploader
        try {
            $contenuFichier = $this->fichierStorageService->getFileContentForFichier($fichier);
        } catch (StorageAdapterException $e) {
            throw new RuntimeException("Impossible d'obtenir le contenu du fichier", null, $e);
        }
        $fichier->setContenuFichierData($contenuFichier);

        $this->uploader()->download($fichier);
    }
}