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
    public function creerAction(): Response
    {
        $type = $this->getRequestedType();
        $substituableId = $this->params()->fromRoute('substituableId');
        $npd = $this->params()->fromRoute('npd');

        $result = $this->substitutionService->createSubstitutionForTypeAndSubstituable($type, $substituableId, $npd);
        $substitution = $result->fetchAssociative();
        $substituantId = $substitution['to_id'];

        $this->flashMessenger()->addSuccessMessage("Le substitué $substituableId a été ajouté avec succès à la substitution par $substituantId.");

        return $this->redirect()->toRoute('substitution/substitution/voir', ['type' => $type, 'id' => $substituantId], [], true);
    }

    /**
     * Formulaire de création manuelle d'une substitution : un substituable de référence est pré-sélectionné et
     * l'utilisateur peut sélectionner un 2e substituable pour créer la substitution.
     *
     * @throws \Doctrine\DBAL\Exception
     */
    public function creerManuAction(): ViewModel
    {
        $type = $this->getRequestedType();
        $substituableId = $this->params()->fromRoute('substituableId');

        try {
            $substituable = $this->substitutionService->findOneEntityByTypeAndId($type, $substituableId);
        } catch (InvalidArgumentException $e) {
            throw new InvalidArgumentException("Substituable spécifié introuvable.");
        }

        // le NPD du substituable spécifié sera utilisé comme NPD forcé des autres
        $npdForce = $this->substitutionService->computeEntityNpd($type, $substituableId);

        $vm = new ViewModel();
        $vm
            ->setVariables([
                'type' => $type,
                'substituable' => $substituable,
                'npd' => $npdForce,
                'npdAttributes' => $this->substitutionService->computeEntityNpdAttributesForType($type),
                'informationPartial' => $this->computeEntityPartialPathForType($type),
            ]);

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
        $substituableId = $post['substituable']['id'];

        $substitutionData = $this->fetchSubstitutionData($type, $substituantId);
        $npd = $substitutionData['substitution']['npd'];

        $this->substitutionService->addSubstitueToSubstitutionForType($type, $substituableId, $npd);
        $this->flashMessenger()->addSuccessMessage("Enregistrement $substituableId ajouté avec succès à la substitution '$npd'.");

        return $this->redirect()->toRoute('substitution/substitution/voir', $this->params()->fromRoute(), [], true);
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function ajouterSubstitueManuAction(): Response
    {
        $request = $this->getRequest();
        if (!$request->isPost()) {
            return $this->redirect()->toRoute('home');
        }

        $type = $this->getRequestedType();
        $npd = $this->getRequestedNpd();

        $post = $request->getPost();
        Assert::keyExists($post, 'substituable');
        Assert::keyExists($post['substituable'], 'id');
        $substituableId = $post['substituable']['id'];

        $this->substitutionService->addSubstitueToSubstitutionForType($type, $substituableId, $npd);
        $this->flashMessenger()->addSuccessMessage("Substitution '$npd' créée avec succès.");

        $result = $this->substitutionService->findOneSubstitutionByTypeAndSubstitue($type, $substituableId);
        $substitution = $result->fetchAssociative();
        $substituantId = $substitution['to_id'];

        return $this->redirect()->toRoute('substitution/substitution/voir', ['type' => $type, 'id' => $substituantId], [], true);
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
        Assert::minCount($substitutionData['substitues'], 2,
            "Impossible de retirer de cette substitution l'unique enregistrement substitué");

        $npd = $substitutionData['substitution']['npd'];

        $this->substitutionService->removeSubstitueFromSubstitutionForType($type, $substitueId, $npd);
        $this->flashMessenger()->addSuccessMessage("Enregistrement $substitueId retiré avec succès de la substitution '$npd'.");

        return $this->redirect()->toRoute('substitution/substitution/voir', $this->params()->fromRoute(), [], true);
    }

    /**
     * Ajax.
     * Recherche d'un enregistrement à ajouter à une substitution existante.
     *
     * @throws \Doctrine\DBAL\Exception
     */
    public function rechercherSubstituableAction(): JsonModel
    {
        if ($text = $this->params()->fromQuery('term')) {
            $type = $this->getRequestedType();
            $substituantId = $this->getRequestedId(false);
            if ($substituantId !== null) {
                $substitutionData = $this->fetchSubstitutionData($type, $substituantId);
                $npd = $substitutionData['substitution']['npd'];
            } else {
                $npd = $this->getRequestedNpd();
            }

            return new JsonModel(
                $this->substitutionService->findSubstituableForTypeByText($type, $text, $npd)
            );
        }
        exit;
    }

    /**
     * Ajax.
     * Recherche d'un enregistrement à ajouter à une substitution n'existant pas encore mais qui aura le NPD spécifié.
     */
    public function rechercherSubstituableManuAction(): JsonModel
    {
        if ($text = $this->params()->fromQuery('term')) {
            $type = $this->getRequestedType();
            $npd = $this->getRequestedNpd();

            return new JsonModel(
                $this->substitutionService->findSubstituableForTypeByText($type, $text, $npd)
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
        $substituableId = $this->params()->fromRoute('substituableId');

        $substituable = $this->substitutionService->findOneEntityByTypeAndId($type, $substituableId);

        $vm = new ViewModel();
        $vm
            ->setVariables([
                'type' => $type,
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
        $result = $this->substitutionService->findOneSubstitutionByTypeAndSubstituant($type, $substituantId);
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

    protected function getRequestedId(bool $mandatory = true): ?int
    {
        $id = $this->params()->fromRoute('id');
        if ($mandatory) {
            Assert::notNull($id, "Un id doit être spécifié dans la requête.");
        }

        return $id;
    }

    protected function getRequestedNpd(): string
    {
        $npd = $this->params()->fromRoute('npd');
        Assert::notNull($npd, "Un NPD doit être spécifié dans la requête.");

        return $npd;
    }
}