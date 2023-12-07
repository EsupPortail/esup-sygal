<?php

namespace Substitution\Controller;

use InvalidArgumentException;
use Laminas\Http\Response;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\Mvc\Plugin\FlashMessenger\FlashMessenger;
use Laminas\View\Model\JsonModel;
use Laminas\View\Model\ViewModel;
use Substitution\Constants;
use Substitution\Service\Substitution\SubstitutionServiceAwareTrait;
use Substitution\TypeAwareTrait;
use Webmozart\Assert\Assert;

/**
 * @method FlashMessenger flashMessenger()
 */
class SubstitutionController extends AbstractActionController
{
    use TypeAwareTrait;
    use SubstitutionServiceAwareTrait;

    public function accueilAction(): array
    {
        return [];
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function listerAction(): ViewModel
    {
        $type = $this->getRequestedType();

        return new ViewModel([
            'type' => $type,
            'result' => $this->substitutionService->findAllSubstitutionsForType($type),
            'count' => $this->substitutionService->countAllSubstitutionsForType($type),
            'npdAttributes' => $this->substitutionService->computeEntityNpdAttributesForType($type),
        ]);
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function voirAction(): ViewModel
    {
        $type = $this->getRequestedType();
        $substituantId = $this->getRequestedId();

        $data  = $this->fetchSubstitutionData($type, $substituantId);

        $vm = new ViewModel();
        $vm
            ->setVariables($data)
            ->setVariable('informationPartial', $this->computeEntityPartialPathForType($type))
            ->setVariable('npdAttributes', $this->substitutionService->computeEntityNpdAttributesForType($type))
            ->setTemplate("substitution/substitution/voir");

        return $vm;
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function modifierAction(): ViewModel
    {
        $type = $this->getRequestedType();

        $voirViewModel = $this->voirAction();

        $vm = new ViewModel();
        $vm
            ->setVariables([
                'type' => $type,
                'substitution' => $voirViewModel->getVariable('substitution'),
                'substituant' => $voirViewModel->getVariable('substituant'),
                'substitues' => $voirViewModel->getVariable('substitues'),
                'npdAttributes' => $this->substitutionService->computeEntityNpdAttributesForType($type),
                'informationPartial' => $this->computeEntityPartialPathForType($type),
            ]);

        return $vm;
    }

    public function modifierSubstituantAction(): Response
    {
        $request = $this->getRequest();
        if (!$request->isPost()) {
            return $this->redirect()->toRoute('home');
        }

        $type = $this->getRequestedType();
        $substituantId = $this->getRequestedId();

        $post = $request->getPost();
        Assert::keyExists($post, 'majAutomatiqueSubstituant');
        Assert::inArray($post['majAutomatiqueSubstituant'], [false, true, 0, 1, '0', '1']);

        $estSubstituantModifiable = (bool) $post['majAutomatiqueSubstituant'];
        $data = ['estSubstituantModifiable' => $estSubstituantModifiable];

        $this->substitutionService->updateSubstituantByTypeAndId($type, $substituantId, $data);
        $this->flashMessenger()->addSuccessMessage(sprintf(
            "La mise à jour automatique du substituant %s a été %s avec succès.",
            $substituantId, $estSubstituantModifiable ? 'activée' : 'désactivée'
        ));

        return $this->redirect()->toRoute('substitution/substitution/voir', $this->params()->fromRoute(), [], true);
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function ajouterSubstitueAction(): Response
    {
        $request = $this->getRequest();
        if (!$request->isPost()) {
            return $this->redirect()->toRoute('home');
        }

        $type = $this->getRequestedType();
        $substituantId = $this->getRequestedId();

        $post = $request->getPost();
        Assert::keyExists($post, 'substituable');
        Assert::keyExists($post['substituable'], 'id');

        $substitutionData = $this->fetchSubstitutionData($type, $substituantId);
        $substitueId = $post['substituable']['id'];
        $npd = $substitutionData['substitution']['npd'];

        $this->substitutionService->addSubstitueToSubstitutionForType($type, $substitueId, $npd);
        $this->flashMessenger()->addSuccessMessage("Enregistrement $substitueId ajouté avec succès à la substitution '$npd'.");

        return $this->redirect()->toRoute('substitution/substitution/voir', $this->params()->fromRoute(), [], true);
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function retirerSubstitueAction(): Response
    {
        $request = $this->getRequest();
        if (!$request->isPost()) {
            return $this->redirect()->toRoute('home');
        }

        $type = $this->getRequestedType();
        $substituantId = $this->getRequestedId();
        $post = $request->getPost();
        Assert::keyExists($post, 'substitue');

        $substitueId = $post['substitue'];
        $substitutionData = $this->fetchSubstitutionData($type, $substituantId);
        $npd = $substitutionData['substitution']['npd'];

        $this->substitutionService->removeSubstitueFromSubstitutionForType($type, $substitueId, $npd);
        $this->flashMessenger()->addSuccessMessage("Enregistrement $substitueId retiré avec succès de la substitution '$npd'.");

        return $this->redirect()->toRoute('substitution/substitution/voir', $this->params()->fromRoute(), [], true);
    }

    /**
     * Ajax.
     * Recherche d'un enregistrement à ajouter à une substitution.
     *
     * @throws \Doctrine\DBAL\Exception
     */
    public function rechercherSubstituableAction(): JsonModel
    {
        if ($text = $this->params()->fromQuery('term')) {
            $type = $this->getRequestedType();
            $substituantId = $this->getRequestedId();
            $substitutionData = $this->fetchSubstitutionData($type, $substituantId);
            $npd = $substitutionData['substitution']['npd'];

            return new JsonModel(
                $this->substitutionService->findSubstituableForTypeByText($this->getRequestedType(), $text, $npd)
            );
        }
        exit;
    }

    /**
     * Ajax.
     * Visualisation d'un enregistrement à ajouter à une substitution.
     */
    public function voirSubstituableAction(): ViewModel
    {
        $type = $this->getRequestedType();
        $substituantId = $this->getRequestedId();
        $substituableId = $this->params()->fromRoute('substituableId');

        $substituable = $this->substitutionService->findOneEntityByTypeAndId($type, $substituableId);

        $vm = new ViewModel();
        $vm
            ->setVariables([
                'type' => $type,
                'substituantId' => $substituantId,
                'substituable' => $substituable,
                'npdAttributes' => $this->substitutionService->computeEntityNpdAttributesForType($type),
                'informationPartial' => $this->computeEntityPartialPathForType($type),
            ]);

        return $vm;
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    protected function fetchSubstitutionData(string $type, int $substituantId): array
    {
        $result = $this->substitutionService->findOneSubstitutionForType($type, $substituantId);
        $substitution = $result->fetchAssociative();

        $substituesIds = explode('|', $substitution['from_ids']);

        try {
            $substituant = $this->substitutionService->findOneEntityByTypeAndId($type, $substituantId);
        } catch (InvalidArgumentException $e) {
            throw new InvalidArgumentException("Substituant spécifié introuvable.");
        }
        $substitues = [];
        foreach ($substituesIds as $substitueId) {
            try {
                $entity = $this->substitutionService->findOneEntityByTypeAndId($type, $substitueId);
            } catch (InvalidArgumentException $e) {
                $entity = null;
            }
            $substitues[$substitueId] = $entity;
        }

        return [
            'type' => $type,
            'substitution' => $substitution,
            'substituant' => $substituant,
            'substitues' => $substitues,
        ];
    }

    protected function computeEntityPartialPathForType(string $type): string
    {
        return match ($type) {
            Constants::TYPE_structure => 'structure/structure/partial/information',
            Constants::TYPE_etablissement => 'structure/etablissement/partial/information',
            Constants::TYPE_ecole_doct => 'structure/ecole-doctorale/partial/information',
            Constants::TYPE_unite_rech => 'structure/unite-recherche/partial/information',
            Constants::TYPE_individu => 'individu/individu/partial/dl',
            Constants::TYPE_doctorant => 'doctorant/doctorant/partial/information',
            default => throw new InvalidArgumentException("Type non supporté")
        };
    }

    protected function getRequestedId(): int
    {
        $id = $this->params()->fromRoute('id');
        Assert::notNull($id, "Un id doit être spécifié dans la requête.");

        return $id;
    }
}