<?php

namespace Soutenance\Service\Proposition\PropositionThese;

use Acteur\Service\ActeurThese\ActeurTheseServiceAwareTrait;
use Application\Entity\Db\Repository\DefaultEntityRepository;
use Application\Entity\Db\Role;
use Application\QueryBuilder\DefaultQueryBuilder;
use Application\Service\UserContextServiceAwareTrait;
use Application\Service\Variable\VariableServiceAwareTrait;
use DateTime;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\ORMException;
use Exception;
use Fichier\Service\Fichier\FichierStorageServiceAwareTrait;
use Individu\Entity\Db\Individu;
use Laminas\Cache\Exception\LogicException;
use Notification\Service\NotifierServiceAwareTrait;
use Soutenance\Entity\Etat;
use Soutenance\Entity\PropositionThese;
use Soutenance\Provider\Validation\TypeValidation as TypeValidationSoutenance;
use Soutenance\Service\Membre\MembreServiceAwareTrait;
use Soutenance\Service\Notification\SoutenanceNotificationFactoryAwareTrait;
use Soutenance\Service\Proposition\PropositionService;
use Soutenance\Service\Validation\ValidationThese\ValidationTheseServiceAwareTrait;
use Structure\Entity\Db\EcoleDoctorale;
use Structure\Service\Etablissement\EtablissementServiceAwareTrait;
use These\Entity\Db\These;
use UnicaenApp\Exception\RuntimeException;
use UnicaenApp\Service\EntityManagerAwareTrait;
use UnicaenParametre\Service\Parametre\ParametreServiceAwareTrait;
use Validation\Entity\Db\TypeValidation;
use Validation\Entity\Db\ValidationThese;

class PropositionTheseService extends PropositionService
{
    use EntityManagerAwareTrait;
    use ActeurTheseServiceAwareTrait;
    use ValidationTheseServiceAwareTrait;
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
        $qb = $this->getEntityManager()->getRepository(PropositionThese::class);

        return $qb;
    }

    /** GESTION DES ENTITES *******************************************************************************************/

    public function create(These $these) : PropositionThese
    {
        $propositionThese = new PropositionThese($these);
        $propositionThese->setEtat($this->findPropositionEtatByCode(Etat::EN_COURS));

        try {
            $date = new DateTime();
            $user = $this->userContextService->getIdentityDb();
        } catch(Exception $e) {
            throw new RuntimeException("Un problème est survenu lors de la récupération des données liées à l'historisation", 0 , $e);
        }

        $propositionThese->setHistoCreateur($user);
        $propositionThese->setHistoCreation($date);
        $propositionThese->setHistoModificateur($user);
        $propositionThese->setHistoModification($date);

        try {
            $this->getEntityManager()->persist($propositionThese);
            $this->getEntityManager()->flush($propositionThese);
        } catch (ORMException $e) {
            throw new RuntimeException("Un erreur s'est produite lors de l'enregistrment en BD de la proposition de thèse !");
        }
        return $propositionThese;
    }

    /** REQUETES ******************************************************************************************************/

    protected function createQueryBuilder(): DefaultQueryBuilder
    {
        return $this->getRepository()->createQueryBuilder("propositionThese")
            ->addSelect('proposition')->join('propositionThese.proposition', 'proposition')
            ->addSelect('etat')->join('proposition.etat', 'etat')
            ->addSelect('these')->join('proposition.these', 'these')
            ->addSelect('unite')->leftJoin('these.uniteRecherche', 'unite')
            ->addSelect('structure_ur')->leftJoin('unite.structure', 'structure_ur')
            ->addSelect('ecole')->leftJoin('these.ecoleDoctorale', 'ecole')
            ->addSelect('structure_ed')->leftJoin('ecole.structure', 'structure_ed')
            ->addSelect('etablissement')->leftJoin('these.etablissement', 'etablissement')
            ->addSelect('structure_etab')->leftJoin('etablissement.structure', 'structure_etab')
            ->addSelect('membre')->leftJoin('proposition.membres', 'membre')
            ->addSelect('qualite')->leftJoin('membre.qualite', 'qualite')
//            ->addSelect('acteur')->leftJoin('membre.acteur', 'acteur') // n'existe plus
//            ->addSelect('amembre')->leftJoin('acteur.membre', 'amembre')
            ->addSelect('justificatif')->leftJoin('proposition.justificatifs', 'justificatif')
            ->addSelect('avis')->leftJoin('proposition.avis', 'avis')
            ->andWhere('proposition.histoDestruction is null')
        ;
    }

    public function find(?int $id): ?PropositionThese
    {
        $qb = $this->createQueryBuilder()
            ->andWhere("propositionThese.id = :id")
            ->setParameter("id", $id)
        ;
        try {
            $result = $qb->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            throw new RuntimeException("De multiples propositions identifiées [".$id."] ont été trouvées !");
        }

        return $result;
    }

    public function findOneForThese(These $these): ?PropositionThese
    {
        $qb = $this->createQueryBuilder()
            ->andWhere("propositionThese.these = :these")
            ->setParameter("these", $these)
        ;

        try {
            $result = $qb->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            throw new RuntimeException("De multiples propositions associé à la thèse [".$these->getId()."] ont été trouvées !");
        }

        return $result;
    }

    /**
     * Fonction annulant toutes les validations associés à la proposition de soutenances
     *
     * @param PropositionThese $propositionThese
     */
    public function annulerValidationsForProposition(PropositionThese $propositionThese)
    {
        $these = $propositionThese->getThese();
        $validations = $this->getValidationTheseService()->findValidationPropositionSoutenanceByThese($these);
        foreach ($validations as $validation) {
            $this->getValidationTheseService()->historise($validation);
            try {
                $notif = $this->soutenanceNotificationFactory->createNotificationDevalidationProposition($these, $validation);
                $this->notifierService->trigger($notif);
            } catch (\Notification\Exception\RuntimeException $e) {
                // aucun destinataire, todo : cas à gérer !
            }
        }
        $validationED = current($this->getValidationTheseService()->getRepository()->findValidationByCodeAndThese(TypeValidation::CODE_VALIDATION_PROPOSITION_ED, $these));
        if ($validationED) {
            $this->getValidationTheseService()->historise($validationED);
            try {
                $notif = $this->soutenanceNotificationFactory->createNotificationDevalidationProposition($these, $validationED);
                $this->notifierService->trigger($notif);
            } catch (\Notification\Exception\RuntimeException $e) {
                // aucun destinataire, todo : cas à gérer !
            }
        }
        $validationUR = current($this->getValidationTheseService()->getRepository()->findValidationByCodeAndThese(TypeValidation::CODE_VALIDATION_PROPOSITION_UR, $these));
        if ($validationUR) {
            $this->getValidationTheseService()->historise($validationUR);
            try {
                $notif = $this->soutenanceNotificationFactory->createNotificationDevalidationProposition($these, $validationUR);
                $this->notifierService->trigger($notif);
            } catch (\Notification\Exception\RuntimeException $e) {
                // aucun destinataire, todo : cas à gérer !
            }
        }
        $validationBDD = current($this->getValidationTheseService()->getRepository()->findValidationByCodeAndThese(TypeValidation::CODE_VALIDATION_PROPOSITION_BDD, $these));
        if ($validationBDD) {
            $this->getValidationTheseService()->historise($validationBDD);
            try {
                $notif = $this->soutenanceNotificationFactory->createNotificationDevalidationProposition($these, $validationBDD);
                $this->notifierService->trigger($notif);
            } catch (\Notification\Exception\RuntimeException $e) {
                // aucun destinataire, todo : cas à gérer !
            }
        }
    }

    /**
     * @param These $these
     * @return \Validation\Entity\Db\ValidationThese[]
     */
    public function findValidationSoutenanceForThese(These $these): array
    {
        $validations = [];

        /** Recuperation de la validation du directeur de thèse */
        $doctorants = [ $these->getApprenant() ];
        $validations[Role::CODE_DOCTORANT] = [];
        foreach ($doctorants as $doctorant) {
            $validation = $this->getValidationTheseService()->getRepository()->findValidationByTheseAndCodeAndIndividu($these,TypeValidation::CODE_PROPOSITION_SOUTENANCE, $doctorant->getIndividu());
            if ($validation) $validations[Role::CODE_DOCTORANT][] = $validation;
        }


        /** Recuperation de la validation du directeur de thèse */
        $directeurs = $these->getActeursByRoleCode(Role::CODE_DIRECTEUR_THESE);
        $validations[Role::CODE_DIRECTEUR_THESE] = [];
        foreach ($directeurs as $directeur) {
            $validation = $this->getValidationTheseService()->getRepository()->findValidationByTheseAndCodeAndIndividu($these,TypeValidation::CODE_PROPOSITION_SOUTENANCE, $directeur->getIndividu());
            if ($validation) $validations[Role::CODE_DIRECTEUR_THESE][] = $validation;
        }

        /** Recuperation de la validation du codirecteur de thèse */
        $codirecteurs = $these->getActeursByRoleCode(Role::CODE_CODIRECTEUR_THESE);
        $validations[Role::CODE_CODIRECTEUR_THESE] = [];
        foreach ($codirecteurs as $codirecteur) {
            $validation = $this->getValidationTheseService()->getRepository()->findValidationByTheseAndCodeAndIndividu($these,TypeValidation::CODE_PROPOSITION_SOUTENANCE, $codirecteur->getIndividu());
            if ($validation) $validations[Role::CODE_CODIRECTEUR_THESE][] = $validation;
        }

        /** Recuperation de la validation de l'unite de recherche */
        $validations[Role::CODE_RESP_UR] = [];
        $validation = $this->getValidationTheseService()->getRepository()->findValidationByCodeAndThese(TypeValidation::CODE_VALIDATION_PROPOSITION_UR, $these);
        if (!empty($validation)) $validations[Role::CODE_RESP_UR][] = current($validation);

        /** Recuperation de la validation de l'école doctorale */
        $validations[Role::CODE_RESP_ED] = [];
        $validation = $this->getValidationTheseService()->getRepository()->findValidationByCodeAndThese(TypeValidation::CODE_VALIDATION_PROPOSITION_ED, $these);
        if (!empty($validation)) $validations[Role::CODE_RESP_ED][] = current($validation);

        /** Recuperation de la validation du bureau des doctorats */
        $validations[Role::CODE_BDD] = [];
        $validation = $this->getValidationTheseService()->getRepository()->findValidationByCodeAndThese(TypeValidation::CODE_VALIDATION_PROPOSITION_BDD, $these);
        if (!empty($validation)) $validations[Role::CODE_BDD][] = current($validation);

        /** Recuperation des engagement d'impartialite */
        $validations['Impartialite'] = $this->getValidationTheseService()->getRepository()->findValidationByCodeAndThese(TypeValidation::CODE_ENGAGEMENT_IMPARTIALITE, $these);
        $validations['Avis']        = $this->getValidationTheseService()->getRepository()->findValidationByCodeAndThese(TypeValidation::CODE_AVIS_SOUTENANCE, $these);

        $validations[TypeValidationSoutenance::CODE_VALIDATION_DECLARATION_HONNEUR] = $this->getValidationTheseService()->getRepository()->findValidationByCodeAndThese(TypeValidationSoutenance::CODE_VALIDATION_DECLARATION_HONNEUR, $these);
        $validations[TypeValidationSoutenance::CODE_REFUS_DECLARATION_HONNEUR] = $this->getValidationTheseService()->getRepository()->findValidationByCodeAndThese(TypeValidationSoutenance::CODE_REFUS_DECLARATION_HONNEUR, $these);

        return $validations;
    }

    /**
     * @param These $these
     * @param Individu $currentIndividu
     * @param Role $currentRole
     * @return boolean
     */
    public function isValidated(These $these, Individu $currentIndividu, Role $currentRole): bool
    {
        $validations = [];
        switch($currentRole->getCode()) {
            case Role::CODE_DOCTORANT :
            case Role::CODE_DIRECTEUR_THESE :
            case Role::CODE_CODIRECTEUR_THESE :
                $validations = $this->getValidationTheseService()->getRepository()->findValidationByCodeAndThese(TypeValidation::CODE_PROPOSITION_SOUTENANCE, $these);
                $validations = array_filter($validations, function (ValidationThese $v) use ($currentIndividu) { return $v->getIndividu() === $currentIndividu;});
                break;
            case Role::CODE_RESP_UR :
                $validations = $this->getValidationTheseService()->getRepository()->findValidationByCodeAndThese(TypeValidation::CODE_VALIDATION_PROPOSITION_UR, $these);
                break;
            case Role::CODE_RESP_ED :
            case Role::CODE_GEST_ED :
                $validations = $this->getValidationTheseService()->getRepository()->findValidationByCodeAndThese(TypeValidation::CODE_VALIDATION_PROPOSITION_ED, $these);
                break;
            case Role::CODE_BDD :
                $validations = $this->getValidationTheseService()->getRepository()->findValidationByCodeAndThese(TypeValidation::CODE_VALIDATION_PROPOSITION_BDD, $these);
                break;
        }
        return !(empty($validations));
    }

    /**
     * @param Role $role
     * @return PropositionThese[]
     */
    public function findPropositionsByRole(Role $role): array
    {
        $qb = $this->createQueryBuilder()
            ->andWhere('these.etatThese = :encours')
            ->setParameter('encours', These::ETAT_EN_COURS)
            ->orderBy('proposition.date', 'ASC')
        ;

        switch ($role->getCode()) {
            case Role::CODE_RESP_UR :
                $qb = $qb
                    ->andWhere('structure_ur.id = :structure')
                    ->setParameter('structure', $role->getStructure(/*false*/)->getId())
                ;
                break;
            case Role::CODE_RESP_ED :
            case Role::CODE_GEST_ED :
                $qb = $qb
                    ->andWhere('structure_ed.id = :structure')
                    ->setParameter('structure', $role->getStructure(/*false*/)->getId())
                ;
                break;
            case Role::CODE_BDD :
                $qb = $qb
                    ->andWhere('structure_etab.id = :structure')
                    ->setParameter('structure', $role->getStructure(/*false*/)->getId())
                ;
                break;
            default:
                break;
        }

        return $qb->getQuery()->getResult();
    }


    /**
     * @param EcoleDoctorale $ecole
     * @return \Soutenance\Entity\PropositionThese[]
     */
    public function findSoutenancesAutoriseesByEcoleDoctorale(EcoleDoctorale $ecole) : array
    {
        $qb = $this->getRepository()->createQueryBuilder("proposition")
            ->addSelect('etat')->join('proposition.etat', 'etat')
            ->addSelect('these')->join('proposition.these', 'these')
            ->addSelect('ecole')->leftJoin('these.ecoleDoctorale', 'ecole')
            ->addSelect('structure_ed')->leftJoin('ecole.structure', 'structure_ed')
            ->andWhereStructureIs($ecole->getStructure(), 'structure_ed')
            ->andWhere('etat.code = :autorise')
            ->andWhere('DATE_ADD(these.dateSoutenance, 1, \'YEAR\') > :date')
            ->setParameter('autorise', Etat::VALIDEE)
            ->setParameter('date', new DateTime())
            ->orderBy('these.dateSoutenance', 'DESC');

        return $qb->getQuery()->getResult();
    }

    /**
     * Les directeurs et co-directeurs sont des membres par défauts du jury d'une thèse. Cette fonction permet d'ajouter
     * ceux-ci à une proposition.
     * NB: La proposition doit être liée à une thèse.
     * NB: Les directeurs et codirecteurs sans mail ne sont pas ajoutés automatiquement
     *
     * @param PropositionThese $propositionThese
     */
    public function addDirecteursAsMembres(PropositionThese $propositionThese)
    {
        $these = $propositionThese->getThese();
        if ($these === null) throw new LogicException("Impossible d'ajout les directeurs comme membres : Aucun thèse de lié à la proposition id:" . $propositionThese->getId());

        $encadrements = $this->getActeurTheseService()->getRepository()->findEncadrementThese($these);

        foreach ($encadrements as $encadrement) {
            $membre = $this->getMembreService()->createMembre($propositionThese, $encadrement);

            $encadrement->setMembre($membre);
            $this->acteurTheseService->update($encadrement);
        }
    }
}