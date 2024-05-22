<?php

namespace Individu\Service;

use Structure\Entity\Db\Etablissement;
use Application\Entity\Db\Utilisateur;
use Application\Service\BaseService;
use Application\SourceCodeStringHelperAwareTrait;
use Doctrine\ORM\ORMException;
use Individu\Entity\Db\Individu;
use Individu\Entity\Db\Repository\IndividuRepository;
use Laminas\Mvc\Controller\AbstractActionController;
use UnicaenApp\Exception\RuntimeException;

class IndividuService extends BaseService
{
    use SourceCodeStringHelperAwareTrait;

    /**
     * @return IndividuRepository
     */
    public function getRepository(): IndividuRepository
    {
        /** @var IndividuRepository $repo */
        $repo = $this->entityManager->getRepository(Individu::class);

        $repo->setSourceCodeStringHelper($this->sourceCodeStringHelper);

        return $repo;
    }

//    /**
//     * Instancie un Individu, à partir des données de formulaire spécifiées.
//     *
//     * @param array $formData
//     * @return Individu
//     */
//    public function createIndividuFromFormData(array $formData): Individu
//    {
//        $source = $this->sourceService->fetchApplicationSource();
//
//        $individu = new Individu();
//        $individu->setCivilite($formData['civilite']);
//        $individu->setNomUsuel($formData['nomUsuel']);
//        $individu->setNomPatronymique($formData['nomPatronymique']);
//        $individu->setPrenom1($formData['prenom']);
//        $individu->setEmail($formData['email']);
//        $individu->setSource($source);
//        $individu->setSourceCode(uniqid()); // NB: sera remplacé par "COMUE::{INDIVIDU.ID}"
//
//        try {
//            $this->getEntityManager()->persist($individu);
//            $this->getEntityManager()->flush($individu);
//        } catch (ORMException $e) {
//            throw new RuntimeException("Erreur lors de l'enregistrement du nouvel individu", null, $e);
//        }
//
//        // source code définitif, ex : "COMUE::{INDIVIDU.ID}"
//        $sourceCodeIndividu = $this->sourceCodeStringHelper->addDefaultPrefixTo($individu->getId());
//        $individu->setSourceCode($sourceCodeIndividu);
//
//        try {
//            $this->getEntityManager()->flush($individu);
//        } catch (ORMException $e) {
//            throw new RuntimeException("Erreur lors de l'enregistrement de l'individu", null, $e);
//        }
//
//        return $individu;
//    }

    /**
     * Instancie un Individu, à partir de l'Utilisateur spécifié.
     *
     * @param \Application\Entity\Db\Utilisateur $utilisateur
     * @return \Individu\Entity\Db\Individu
     */
    public function newIndividuFromUtilisateur(Utilisateur $utilisateur): Individu
    {
        $nom = $utilisateur->getNom();
        $prenom = $utilisateur->getPrenom();
        if ($nom === null || $prenom === null) {
            // tentative d'extraire les nom et prénom, en présupposant que le nom de famille est devant et en majuscule
            if (preg_match("/([A-Z]{2,}\s)+/", $dn = $utilisateur->getDisplayName(), $matches)) {
                $nom = trim($matches[0]);
                $prenom = trim(substr($dn, strlen($nom)));
            }
        }

        return $this->newIndividuFromData([
            'civilite' => null,
            'nomUsuel' => $nom,
            'nomPatronymique' => $nom,
            'prenom' => $prenom,
            'email' => $utilisateur->getEmail(),
            'sourceCode' => $this->sourceCodeStringHelper->addDefaultPrefixTo($utilisateur->getUsername()),
        ]);
    }

    /**
     * Instancie un Individu, à partir des données fournies.
     *
     * @param array $data
     * @return Individu
     */
    public function newIndividuFromData(array $data): Individu
    {
        $sourceCode = $data['sourceCode'] ?? uniqid('', true);

        $individu = new Individu();
        $individu->setCivilite($data['civilite'] ?? null);
        $individu->setNomUsuel($data['nomUsuel']);
        $individu->setNomPatronymique($data['nomPatronymique'] ?? null);
        $individu->setPrenom1($data['prenom1'] ?? $data['prenom']);
        $individu->setEmailPro($data['email']);
        $individu->setSourceCode($sourceCode);

        return $individu;
    }

    /**
     * Enregistre l'Individu en base de données.
     *
     * @param \Individu\Entity\Db\Individu $individu
     */
    public function saveIndividu(Individu $individu)
    {
        try {
            $this->entityManager->persist($individu);
            $this->entityManager->flush($individu);
        } catch (ORMException $e) {
            throw new RuntimeException("Erreur lors de l'enregistrement du nouvel individu", null, $e);
        }
    }

    public function historiser(Individu $individu, Utilisateur $destructeur)
    {
        $individu->historiser($destructeur);

        try {
            $this->entityManager->flush($individu);
        } catch (ORMException $e) {
            throw new RuntimeException("Erreur rencontrée en BDD lors de l'historisation", null, $e);
        }
    }

    public function dehistoriser(Individu $individu)
    {
        $individu->dehistoriser();

        try {
            $this->entityManager->flush($individu);
        } catch (ORMException $e) {
            throw new RuntimeException("Erreur rencontrée en BDD lors de la restauration", null, $e);
        }
    }

    /**
     * Met à jour le SOURCE_CODE d'un individu à partir de l'établissement spécifié.
     *
     * @param Individu $entity
     * @param Etablissement $etablissement
     * @param Utilisateur $modificateur
     */
    public function updateIndividuSourceCodeFromEtab(Individu      $entity,
                                                     Etablissement $etablissement,
                                                     Utilisateur   $modificateur)
    {
        $sourceCode = $this->sourceCodeStringHelper->addEtablissementPrefixTo($entity->getSupannId(), $etablissement);

        $entity->setSourceCode($sourceCode);
        $entity->setHistoModificateur($modificateur);

        try {
            $this->getEntityManager()->flush($entity);
        } catch (ORMException $e) {
            throw new RuntimeException("Impossible d'enregistrer l'Individu", null, $e);
        }
    }

    /**
     * @param AbstractActionController $controller
     * @param string $param
     * @return Individu
     */
    public function getRequestedIndividu(AbstractActionController $controller, string $param = 'individu'): ?Individu
    {
        $id = $controller->params()->fromRoute($param);
        if ($id === null) {
            return null;
        }

        /** @var Individu $individu */
        $individu = $this->getRepository()->find($id);

        return $individu;
    }
}