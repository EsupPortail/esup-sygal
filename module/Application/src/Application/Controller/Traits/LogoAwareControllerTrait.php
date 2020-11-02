<?php

namespace Application\Controller\Traits;

use Application\Entity\Db\StructureConcreteInterface;
use Application\Service\Structure\StructureService;
use UnicaenApp\Exception\RuntimeException;
use Zend\Mvc\Plugin\FlashMessenger\FlashMessenger;
use Zend\Mvc\Controller\Plugin\Params;

/**
 * Trait LogoAwareControllerTrait
 *
 * @method Params params()
 * @method FlashMessenger flashMessenger()
 *
 * @property StructureService $structureService

 * @package Application\Controller\Traits
 *
 * @deprecated Mis dans StructureConcreteController
 */
trait LogoAwareControllerTrait
{
    /**
     * Retire le logo associé à une structure :
     * - effacement du chemin en bdd,
     * - effacement du fichier stocké sur le serveur.
     *
     * @param StructureConcreteInterface $structure
     */
    public function supprimerLogoStructure(StructureConcreteInterface $structure)
    {
        try {
            $fileDeleted = $this->structureService->deleteLogoStructure($structure);
        } catch (RuntimeException $e) {
            $this->flashMessenger()->addErrorMessage(
                "Erreur lors de l'effacement du logo de la structure '$structure' : " . $e->getMessage());
            return;
        }

        if ($fileDeleted) {
            $this->flashMessenger()->addSuccessMessage("Le logo de la structure '$structure' vient d'être supprimé.");
        } else {
            $this->flashMessenger()->addWarningMessage("Aucun logo à supprimer pour la structure '$structure'.");
        }
    }

    /**
     * Ajoute le logo associé à une structure :
     * - suppression du précédent logo éventuel,
     * - modification du chemin en bdd
     * - création du fichier sur le serveur.
     *
     * @param StructureConcreteInterface $structure
     * @param string                     $cheminLogoUploade chemin vers le fichier temporaire associé au logo
     */
    public function ajouterLogoStructure(StructureConcreteInterface $structure, $cheminLogoUploade)
    {
        try {
            $this->structureService->updateLogoStructure($structure, $cheminLogoUploade);
        } catch (RuntimeException $e) {
            $this->flashMessenger()->addErrorMessage(
                "Erreur lors de l'enregistrement du logo de la structure '$structure' : " . $e->getMessage());
        }

        $this->flashMessenger()->addSuccessMessage("Le logo de la structure '$structure' vient d'être ajouté.");
    }
}