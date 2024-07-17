<?php

namespace Structure\Controller;

use Application\Entity\Db\Variable;
use Application\Service\Variable\VariableServiceAwareTrait;
use Laminas\Http\Request;
use Laminas\Http\Response;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use Structure\Entity\Db\Etablissement;
use Structure\Form\VariableForm;
use Structure\Service\Etablissement\EtablissementServiceAwareTrait;
use Structure\Service\Structure\StructureServiceAwareTrait;

class VariableController extends AbstractActionController {
    use EtablissementServiceAwareTrait;
    use StructureServiceAwareTrait;
    use VariableServiceAwareTrait;
    /**
     * @var VariableForm
     */
    private $variableForm;

    /**
     * @param VariableForm $variableForm
     */
    public function setVariableForm(VariableForm $variableForm)
    {
        $this->variableForm = $variableForm;
    }

    public function saisirVariableAction() {
        /** @var VariableForm $form */
        $form = $this->variableForm;

        $etabId = $this->params()->fromRoute('etablissement');
        /** @var Etablissement $etablissement */
        $etablissement = $this->etablissementService->getRepository()->find($etabId);
        if ($etablissement === null) {
            throw new \InvalidArgumentException("Etablissement introuvable avec cet id");
        }

        $form->setAttribute('action', $this->url()->fromRoute('etablissement/saisir-variable', ['etablissement'=> $etablissement->getId()], [], true));

        $vm = new ViewModel([
            'form' => $form,
            'title' => "Ajout d'une variable liée à l'établissement",
        ]);

        $variableId = $this->params()->fromRoute('id');
        $variable = null;
        if($variableId) {
            /** @var Variable $variable */
            $variable = $this->variableService->getRepository()->find($variableId);
            if ($variable === null) {
                throw new \InvalidArgumentException("Variable introuvable avec cet id");
            }
            $form->bind($variable);
            $vm->setVariable('title', "Modification de la variable ".$variable->getCode());
        }

        /** @var Request $request */
        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            $form->setData($data);
            if(!$variable){
                $variable = $form->getObject();
                $variable->setEtablissement($etablissement);
            }
            if ($form->isValid()) {
                if ($variable->getId() !== null){
                    $this->variableService->update($variable);
                }else{
                    $this->variableService->create($variable);
                }
                return $this->redirect()->toRoute('etablissement/voir', ['etablissement'=> $etablissement->getId()], ['query' => ['tab' => StructureController::TAB_variables]], true);
            }
        }

        return $vm;
    }

    public function supprimerAction(): Response
    {
        $variableId = $this->params()->fromRoute('id');
        if($variableId){
            $variable = $this->variableService->getRepository()->find($variableId);
            if ($variable === null) {
                throw new \InvalidArgumentException("Variable introuvable avec cet id");
            }
            $this->variableService->delete($variable);
        }
        $redirectUrl = $this->params()->fromQuery('redirect');
        return $this->redirect()->toUrl($redirectUrl);
    }
}