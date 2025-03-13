<?php

namespace Soutenance\Service\Proposition\PropositionHDR;

use Acteur\Service\ActeurHDR\ActeurHDRServiceAwareTrait;
use Application\Entity\Db\Repository\DefaultEntityRepository;
use Application\Entity\Db\Role;
use Application\QueryBuilder\DefaultQueryBuilder;
use Application\Service\UserContextServiceAwareTrait;
use Application\Service\Variable\VariableServiceAwareTrait;
use DateTime;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\ORMException;
use Fichier\Service\Fichier\FichierStorageServiceAwareTrait;
use HDR\Entity\Db\HDR;
use Individu\Entity\Db\Individu;
use Laminas\Cache\Exception\LogicException;
use Laminas\Mvc\Controller\AbstractActionController;
use Notification\Service\NotifierServiceAwareTrait;
use Soutenance\Entity\Etat;
use Soutenance\Entity\PropositionHDR;
use Soutenance\Provider\Validation\TypeValidation as TypeValidationSoutenance;
use Soutenance\Service\Membre\MembreServiceAwareTrait;
use Soutenance\Service\Notification\SoutenanceNotificationFactoryAwareTrait;
use Soutenance\Service\Proposition\PropositionService;
use Soutenance\Service\Validation\ValidationHDR\ValidationHDRServiceAwareTrait;
use Structure\Entity\Db\EcoleDoctorale;
use Structure\Service\Etablissement\EtablissementServiceAwareTrait;
use UnicaenApp\Exception\RuntimeException;
use UnicaenApp\Service\EntityManagerAwareTrait;
use UnicaenParametre\Service\Parametre\ParametreServiceAwareTrait;
use Validation\Entity\Db\TypeValidation;
use Validation\Entity\Db\ValidationHDR;

class PropositionHDRService extends PropositionService
{
    use EntityManagerAwareTrait;
    use ActeurHDRServiceAwareTrait;
    use ValidationHDRServiceAwareTrait;
    use NotifierServiceAwareTrait;
    use ParametreServiceAwareTrait;
    use SoutenanceNotificationFactoryAwareTrait;
    use VariableServiceAwareTrait;
    use FichierStorageServiceAwareTrait;
    use EtablissementServiceAwareTrait;
    use MembreServiceAwareTrait;
    use UserContextServiceAwareTrait;

    public function getRepository(): DefaultEntityRepository
    {
        /** @var DefaultEntityRepository $qb */
        $qb = $this->getEntityManager()->getRepository(PropositionHDR::class);

        return $qb;
    }

    /** GESTION DES ENTITES *******************************************************************************************/

    public function create(HDR $hdr): PropositionHDR
    {
        $propositionHDR = new PropositionHDR($hdr);
        $propositionHDR->setEtat($this->findPropositionEtatByCode(Etat::EN_COURS_SAISIE));

        try {
            $this->getEntityManager()->persist($propositionHDR);
            $this->getEntityManager()->flush($propositionHDR);
        } catch (ORMException $e) {
            throw new RuntimeException("Un erreur s'est produite lors de l'enregistrment en BD de la proposition de HDR !");
        }
        return $propositionHDR;
    }

    /** REQUETES ******************************************************************************************************/

    protected function createQueryBuilder(): DefaultQueryBuilder
    {
        return $this->getRepository()->createQueryBuilder("proposition")
            ->addSelect('etat')->join('proposition.etat', 'etat')
            ->addSelect('hdr')->join('proposition.hdr', 'hdr')
            ->addSelect('unite')->leftJoin('hdr.uniteRecherche', 'unite')
            ->addSelect('structure_ur')->leftJoin('unite.structure', 'structure_ur')
            ->addSelect('ecole')->leftJoin('hdr.ecoleDoctorale', 'ecole')
            ->addSelect('structure_ed')->leftJoin('ecole.structure', 'structure_ed')
            ->addSelect('etablissement')->leftJoin('hdr.etablissement', 'etablissement')
            ->addSelect('structure_etab')->leftJoin('etablissement.structure', 'structure_etab')
            ->addSelect('membre')->leftJoin('proposition.membres', 'membre')
            ->addSelect('qualite')->leftJoin('membre.qualite', 'qualite')
//            ->addSelect('acteur')->leftJoin('membre.acteur', 'acteur') // n'existe plus
//            ->addSelect('amembre')->leftJoin('acteur.membre', 'amembre')
            ->addSelect('justificatif')->leftJoin('proposition.justificatifs', 'justificatif')
            ->addSelect('avis')->leftJoin('proposition.avis', 'avis')
            ->andWhere('proposition.histoDestruction is null')//->addSelect('validation')->leftJoin('proposition.validations', 'validation')
            ;
    }

    public function find(?int $id): ?PropositionHDR
    {
        $qb = $this->createQueryBuilder()
            ->andWhere("proposition.id = :id")
            ->setParameter("id", $id);
        try {
            $result = $qb->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            throw new RuntimeException("De multiples propositions identifiées [" . $id . "] ont été trouvées !");
        }

        return $result;
    }

    public function getRequestedProposition(AbstractActionController $controller, string $param = 'proposition'): ?PropositionHDR
    {
        $id = $controller->params()->fromRoute($param);

        return $this->find($id);
    }

    public function findOneForHDR(HDR $hdr): ?PropositionHDR
    {
        $qb = $this->createQueryBuilder()
            ->andWhere("proposition.hdr = :hdr")
            ->setParameter("hdr", $hdr);

        try {
            $result = $qb->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            throw new RuntimeException("De multiples propositions associé à la HDR [" . $hdr->getId() . "] ont été trouvées !");
        }

        return $result;
    }

    /**
     * Fonction annulant toutes les validations associés à la proposition de soutenances
     *
     * @param PropositionHDR $proposition
     */
    public function annulerValidationsForProposition(PropositionHDR $proposition)
    {
        $hdr = $proposition->getHDR();
        $validations = $this->getValidationHDRService()->findValidationPropositionSoutenanceByHDR($hdr);
        foreach ($validations as $validation) {
            $this->getValidationHDRService()->historise($validation);
            try {
                $notif = $this->soutenanceNotificationFactory->createNotificationDevalidationProposition($hdr, $validation);
                $this->notifierService->trigger($notif);
            } catch (\Notification\Exception\RuntimeException $e) {
                // aucun destinataire, todo : cas à gérer !
            }
        }
        $validationUR = current($this->getValidationHDRService()->getRepository()->findValidationByCodeAndHDR(TypeValidation::CODE_VALIDATION_PROPOSITION_UR, $hdr));
        if ($validationUR) {
            $this->getValidationHDRService()->historise($validationUR);
            try {
                $notif = $this->soutenanceNotificationFactory->createNotificationDevalidationProposition($hdr, $validationUR);
                $this->notifierService->trigger($notif);
            } catch (\Notification\Exception\RuntimeException $e) {
                // aucun destinataire, todo : cas à gérer !
            }
        }
        $validationBDD = current($this->getValidationHDRService()->getRepository()->findValidationByCodeAndHDR(TypeValidation::CODE_VALIDATION_PROPOSITION_BDD, $hdr));
        if ($validationBDD) {
            $this->getValidationHDRService()->historise($validationBDD);
            try {
                $notif = $this->soutenanceNotificationFactory->createNotificationDevalidationProposition($hdr, $validationBDD);
                $this->notifierService->trigger($notif);
            } catch (\Notification\Exception\RuntimeException $e) {
                // aucun destinataire, todo : cas à gérer !
            }
        }
    }

    /**
     * @param HDR $hdr
     * @return \Validation\Entity\Db\ValidationHDR[]
     */
    public function findValidationSoutenanceForHDR(HDR $hdr): array
    {
        $validations = [];

        /** Recuperation de la validation du candidat */
        $candidats = [$hdr->getCandidat()];
        $validations[Role::CODE_HDR_CANDIDAT] = [];
        foreach ($candidats as $candidat) {
            $validation = $this->getValidationHDRService()->getRepository()->findValidationByHDRAndCodeAndIndividu($hdr,TypeValidation::CODE_PROPOSITION_SOUTENANCE, $candidat->getIndividu());
            if ($validation) $validations[Role::CODE_HDR_CANDIDAT][] = $validation;
        }


        /** Recuperation de la validation du garant de HDR */
        $garants = $hdr->getActeursByRoleCode(Role::CODE_HDR_GARANT);
        $validations[Role::CODE_HDR_GARANT] = [];
        foreach ($garants as $garant) {
            $validation = $this->getValidationHDRService()->getRepository()->findValidationByHDRAndCodeAndIndividu($hdr,TypeValidation::CODE_PROPOSITION_SOUTENANCE, $garant->getIndividu());
            if ($validation) $validations[Role::CODE_HDR_GARANT][] = $validation;
        }

        /** Recuperation de la validation de l'unite de recherche */
        $validations[Role::CODE_RESP_UR] = [];
        $validation = $this->getValidationHDRService()->getRepository()->findValidationByCodeAndHDR(TypeValidation::CODE_VALIDATION_PROPOSITION_UR, $hdr);
        if (!empty($validation)) $validations[Role::CODE_RESP_UR][] = current($validation);

        /** Recuperation de la validation du gestionnaire HDR */
        $validations[Role::CODE_GEST_HDR] = [];
        $validation = $this->getValidationHDRService()->getRepository()->findValidationByCodeAndHDR(TypeValidation::CODE_VALIDATION_PROPOSITION_BDD, $hdr);
        if (!empty($validation)) $validations[Role::CODE_GEST_HDR][] = current($validation);

        /** Recuperation des engagement d'impartialite */
        $validations['Impartialite'] = $this->getValidationHDRService()->getRepository()->findValidationByCodeAndHDR(TypeValidation::CODE_ENGAGEMENT_IMPARTIALITE, $hdr);
        $validations['Avis']        = $this->getValidationHDRService()->getRepository()->findValidationByCodeAndHDR(TypeValidation::CODE_AVIS_SOUTENANCE, $hdr);

        return $validations;
    }

    /**
     * @param HDR $hdr
     * @param Individu $currentIndividu
     * @param Role $currentRole
     * @return boolean
     */
    public function isValidated(HDR $hdr, Individu $currentIndividu, Role $currentRole): bool
    {
        $validations = [];
        switch ($currentRole->getCode()) {
            case Role::CODE_DOCTORANT :
            case Role::CODE_HDR_GARANT :
                $validations = $this->getValidationHDRService()->getRepository()->findValidationByCodeAndHDR(TypeValidation::CODE_PROPOSITION_SOUTENANCE, $hdr);
                $validations = array_filter($validations, function (ValidationHDR $v) use ($currentIndividu) { return $v->getIndividu() === $currentIndividu;});
                break;
            case Role::CODE_RESP_UR :
                $validations = $this->getValidationHDRService()->getRepository()->findValidationByCodeAndHDR(TypeValidation::CODE_VALIDATION_PROPOSITION_UR, $hdr);
                break;
            case Role::CODE_GEST_HDR:
                $validations = $this->getValidationHDRService()->getRepository()->findValidationByCodeAndHDR(TypeValidation::CODE_VALIDATION_PROPOSITION_BDD, $hdr);
                break;
        }
        return !(empty($validations));
    }

    /**
     * @param Role $role
     * @return PropositionHDR[]
     */
    public function findPropositionsByRole(Role $role): array
    {
        $qb = $this->createQueryBuilder()
            ->andWhere('hdr.etatHDR = :encours')
            ->setParameter('encours', HDR::ETAT_EN_COURS)
            ->orderBy('proposition.date', 'ASC');

        switch ($role->getCode()) {
            case Role::CODE_RESP_UR :
                $qb = $qb
                    ->andWhere('structure_ur.id = :structure')
                    ->setParameter('structure', $role->getStructure(/*false*/)->getId());
                break;
            case Role::CODE_GEST_HDR:
                $qb = $qb
                    ->andWhere('structure_etab.id = :structure')
                    ->setParameter('structure', $role->getStructure(/*false*/)->getId());
                break;
            default:
                break;
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Le garant est un membre par défaut du jury d'une HDR. Cette fonction permet d'ajouter
     * ceux-ci à une proposition.
     * NB: La proposition doit être liée à une HDR.
     * NB: Le garant sans mail n'est pas ajouté automatiquement
     *
     * @param PropositionHDR $proposition
     */
    public function addGarantsAsMembres(PropositionHDR $proposition)
    {
        $hdr = $proposition->getHDR();
        if ($hdr === null) throw new LogicException("Impossible d'ajout le garant comme membre : Aucune HDR de liée à la proposition id:" . $proposition->getId());

        $encadrements = $this->acteurHDRService->getRepository()->findEncadrementHDR($hdr);
        foreach ($encadrements as $encadrement) {
            $membre = $this->getMembreService()->createMembre($proposition, $encadrement);

            $encadrement->setMembre($membre);
            $this->acteurHDRService->save($encadrement);
        }
    }
}