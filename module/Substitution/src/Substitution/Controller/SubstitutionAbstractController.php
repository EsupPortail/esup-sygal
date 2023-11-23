<?php

namespace Substitution\Controller;

use BadMethodCallException;
use Doctorant\Controller\DoctorantController;
use Doctrine\DBAL\Result;
use Exception;
use Individu\Controller\IndividuController;
use InvalidArgumentException;
use Laminas\Filter\Word\UnderscoreToCamelCase;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\Mvc\Plugin\FlashMessenger\FlashMessenger;
use Laminas\View\Model\ViewModel;
use Structure\Controller\EcoleDoctoraleController;
use Structure\Controller\EtablissementController;
use Structure\Controller\StructureController;
use Structure\Controller\UniteRechercheController;
use Substitution\Constants;
use Substitution\Service\SubstitutionServiceAwareTrait;
use Webmozart\Assert\Assert;

/**
 * @method FlashMessenger flashMessenger()
 */
class SubstitutionAbstractController extends AbstractActionController
{
    use SubstitutionServiceAwareTrait;

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function listeAction(): ViewModel
    {
        $type = $this->params()->fromRoute('type');
        Assert::inArray($type, Constants::TYPES);

        $vm = new ViewModel([
            'type' => $type,
            'result' => $this->findAllSubstitutionsForType($type),
        ]);

        return $vm->setTemplate("substitution/substitution/$type/liste");
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function voirAction(): ViewModel
    {
        $type = $this->params()->fromRoute('type');
        $substituantId = $this->params()->fromRoute('id');

        Assert::inArray($type, Constants::TYPES);
        Assert::notNull($substituantId, "Un id doit être spécifié.");

        $result = $this->findOneSubstitutionForTypeAndId($type, $substituantId);

        $substitution = $result->fetchAssociative();
        $substituesIds = explode('|', $substitution['from_ids']);

        try {
            $substituant = $this->fetchEntity($type, $substituantId);
        } catch (InvalidArgumentException $e) {
            throw new InvalidArgumentException("Substituant spécifié introuvable.");
        }
        $substitues = [];
        foreach ($substituesIds as $substitueId) {
            try {
                $entity = $this->fetchEntity($type, $substitueId);
            } catch (InvalidArgumentException $e) {
                $entity = null;
            }
            $substitues[$substitueId] = $entity;
        }

        $vm = new ViewModel();
        $vm
            ->setVariables([
                'type' => $type,
                'substitution' => $substitution,
                'substituant' => $substituant,
                'substitues' => $substitues,
            ])
            ->setTemplate("substitution/substitution/$type/voir");

        return $vm;
    }

    /**
     * @see \Substitution\Service\SubstitutionService::findAllSubstitutionsStructure()
     * @see \Substitution\Service\SubstitutionService::findAllSubstitutionsEtablissement()
     * @see \Substitution\Service\SubstitutionService::findAllSubstitutionsEcoleDoct()
     * @see \Substitution\Service\SubstitutionService::findAllSubstitutionsUniteRech()
     * @see \Substitution\Service\SubstitutionService::findAllSubstitutionsIndividu()
     * @see \Substitution\Service\SubstitutionService::findAllSubstitutionsDoctorant()
     * @throws \Doctrine\DBAL\Exception
     */
    protected function findAllSubstitutionsForType(string $type): Result
    {
        $method = 'findAllSubstitutions' . ucfirst((new UnderscoreToCamelCase())->filter($type));

        return $this->substitutionService->$method();
    }

    /**
     * @see \Substitution\Service\SubstitutionService::findOneSubstitutionStructure()
     * @see \Substitution\Service\SubstitutionService::findOneSubstitutionEtablissement()
     * @see \Substitution\Service\SubstitutionService::findOneSubstitutionEcoleDoct()
     * @see \Substitution\Service\SubstitutionService::findOneSubstitutionUniteRech()
     * @see \Substitution\Service\SubstitutionService::findOneSubstitutionIndividu()
     * @see \Substitution\Service\SubstitutionService::findOneSubstitutionDoctorant()
     * @throws \Doctrine\DBAL\Exception
     */
    protected function findOneSubstitutionForTypeAndId(string $type, int $id): Result
    {
        $method = 'findOneSubstitution' . ucfirst((new UnderscoreToCamelCase())->filter($type));

        return $this->substitutionService->$method($id);
    }

    /**
     * @throws \InvalidArgumentException Entité introuvable
     */
    protected function fetchEntity(string $type, int $id): object
    {
        /** @var ViewModel $vm */
        switch ($type) {
            case Constants::TYPE_structure:
                /** @see StructureController::voirAction() */
                $entityViewModel = $this->forward()->dispatch(StructureController::class, ['action' => 'voir', 'structure' => $id]);
                return $entityViewModel->getVariable('structure');
            case Constants::TYPE_etablissement:
                /** @see EtablissementController::voirAction() */
                $entityViewModel = $this->forward()->dispatch(EtablissementController::class, ['action' => 'voir', 'etablissement' => $id]);
                return $entityViewModel->getVariable('etablissement');
            case Constants::TYPE_ecole_doct:
                /** @see EcoleDoctoraleController::voirAction() */
                $entityViewModel = $this->forward()->dispatch(EcoleDoctoraleController::class, ['action' => 'voir', 'ecole-doctorale' => $id]);
                return $entityViewModel->getVariable('ecole');
            case Constants::TYPE_unite_rech:
                /** @see UniteRechercheController::voirAction() */
                $entityViewModel = $this->forward()->dispatch(UniteRechercheController::class, ['action' => 'voir', 'unite-recherche' => $id]);
                return $entityViewModel->getVariable('unite');

            case Constants::TYPE_individu:
                /** @see IndividuController::voirAction() */
                $entityViewModel = $this->forward()->dispatch(IndividuController::class, ['action' => 'voir', 'individu' => $id]);
                return $entityViewModel->getVariable('individu');
            case Constants::TYPE_doctorant:
                /** @see DoctorantController::voirAction() */
                $entityViewModel = $this->forward()->dispatch(DoctorantController::class, ['action' => 'voir', 'doctorant' => $id]);
                return $entityViewModel->getVariable('doctorant');

            default:
                throw new InvalidArgumentException("Type non prévu !");
        }
    }
}