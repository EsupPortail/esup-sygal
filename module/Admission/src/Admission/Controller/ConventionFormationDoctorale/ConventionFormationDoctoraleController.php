<?php

namespace Admission\Controller\ConventionFormationDoctorale;

use Admission\Entity\Db\ConventionFormationDoctorale;
use Admission\Form\ConventionFormationDoctorale\ConventionFormationDoctoraleFormAwareTrait;
use Admission\Service\Admission\AdmissionServiceAwareTrait;
use Admission\Service\ConventionFormationDoctorale\ConventionFormationDoctoraleServiceAwareTrait;
use Admission\Service\Exporter\ConventionFormationDoctorale\ConventionFormationDoctoraleExporterAwareTrait;
use Fichier\Service\Fichier\FichierStorageServiceAwareTrait;
use Fichier\Service\Storage\Adapter\Exception\StorageAdapterException;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use Structure\Service\Etablissement\EtablissementServiceAwareTrait;

class ConventionFormationDoctoraleController extends AbstractActionController
{
    use AdmissionServiceAwareTrait;
    use FichierStorageServiceAwareTrait;
    use ConventionFormationDoctoraleServiceAwareTrait;
    use ConventionFormationDoctoraleFormAwareTrait;
    use ConventionFormationDoctoraleExporterAwareTrait;
    use EtablissementServiceAwareTrait;

    public function ajouterConventionFormationAction()
    {
        $admission = $this->admissionService->getRepository()->findRequestedAdmission($this);

        $form = $this->conventionFormationDoctoraleForm;

        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost()->toArray();
            $form->setData($data);
            if ($form->isValid()) {
                /** @var ConventionFormationDoctorale $conventionFormationDoctorale */
                $conventionFormationDoctorale = $form->getData();
                $conventionFormationDoctorale->setAdmission($admission);

                $conventionAlreadyInBdd = $this->conventionFormationDoctoraleService->getRepository()->findOneBy(["admission" => $admission]);
                if (!$conventionAlreadyInBdd) {
                    $this->conventionFormationDoctoraleService->save($conventionFormationDoctorale);
                    $this->flashMessenger()->addSuccessMessage("Convention de formation doctorale enregistrée avec succès.");

                    $individu = $admission->getIndividu()->getId();
                    return $this->redirect()->toRoute('admission/ajouter', ['action' => 'document', 'individu' => $individu]);
                } else {
                    $this->flashMessenger()->addErrorMessage(
                        "La convention doctorale est déjà en enregistrée, vous ne pouvez pas en créer une nouvelle."
                    );
                }
            }
        }
        return (new ViewModel([
            'admission' => $admission,
            'form' => $form,
        ]))->setTemplate('admission/admission/convention-formation-doctorale/modifier');
    }

    public function modifierConventionFormationAction(){
        $admission = $this->admissionService->getRepository()->findRequestedAdmission($this);
        $conventionFormationDoctorale = $this->conventionFormationDoctoraleService->getRepository()->findOneBy(["admission" => $admission]);
        $form = $this->conventionFormationDoctoraleForm;
        $form->bind($conventionFormationDoctorale);

        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost()->toArray();
            $form->setData($data);
            if ($form->isValid()) {
                /** @var ConventionFormationDoctorale $conventionFormationDoctorale */
                $conventionFormationDoctorale = $form->getData();
                $this->conventionFormationDoctoraleService->save($conventionFormationDoctorale);

                $this->flashMessenger()->addSuccessMessage("Convention de formation doctorale modifiée avec succès.");

                $individu = $admission->getIndividu()->getId();
                return $this->redirect()->toRoute('admission/ajouter', ['action' => 'document', 'individu' => $individu]);
            }
        }

        return (new ViewModel([
            'admission' => $admission,
            'form' => $form,
        ]))->setTemplate('admission/admission/convention-formation-doctorale/modifier');
    }

    public function genererConventionFormationAction(){
        $admission = $this->admissionService->getRepository()->findRequestedAdmission($this);
        $conventionFormationDoctorale = $this->conventionFormationDoctoraleService->getRepository()->findOneBy(["admission" => $admission]);

        $logos = [];
        try {
            $site = $admission->getInscription()->first()->getEtablissementInscription() ? $admission->getInscription()->first()->getEtablissementInscription()->getStructure() : null;
            $logos['site'] = $site ? $this->fichierStorageService->getFileForLogoStructure($site) : null;
        } catch (StorageAdapterException $e) {
            $logos['site'] = null;
        }
        if ($comue = $this->etablissementService->fetchEtablissementComue()) {
            try {
                $logos['comue'] = $this->fichierStorageService->getFileForLogoStructure($comue->getStructure());
            } catch (StorageAdapterException $e) {
                $logos['comue'] = null;
            }
        }

        $export = $this->conventionFormationDoctoraleExporter;
        $export->setWatermark("CONFIDENTIEL");
        $export->setVars([
            'admission' => $admission,
            'conventionFormationDoctorale' => $conventionFormationDoctorale,
            'logos' => $logos,
        ]);
        $export->export('SYGAL_admission_convention_formation_doctorale_' . $admission->getId() . ".pdf");
    }
}