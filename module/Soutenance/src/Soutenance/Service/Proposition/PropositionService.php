<?php

namespace Soutenance\Service\Proposition;

use Acteur\Service\ActeurThese\ActeurTheseServiceAwareTrait;
use Application\Entity\Db\Repository\DefaultEntityRepository;
use Application\Entity\Db\Role;
use Application\Entity\Db\Variable;
use Application\QueryBuilder\DefaultQueryBuilder;
use Application\Service\BaseService;
use Application\Service\UserContextServiceAwareTrait;
use Application\Service\Variable\VariableServiceAwareTrait;
use DateInterval;
use DateTime;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\ORMException;
use Exception;
use Fichier\Service\Fichier\FichierStorageServiceAwareTrait;
use Fichier\Service\Storage\Adapter\Exception\StorageAdapterException;
use HDR\Entity\Db\HDR;
use Laminas\Mvc\Controller\AbstractActionController;
use Notification\Service\NotifierServiceAwareTrait;
use Soutenance\Entity\Etat;
use Soutenance\Entity\Membre;
use Soutenance\Entity\Proposition;
use Soutenance\Entity\PropositionThese;
use Soutenance\Provider\Parametre\These\SoutenanceParametres;
use Soutenance\Rule\PropositionJuryRuleAwareTrait;
use Soutenance\Service\Membre\MembreServiceAwareTrait;
use Soutenance\Service\Notification\SoutenanceNotificationFactoryAwareTrait;
use Soutenance\Service\Proposition\PropositionHDR\PropositionHDRServiceAwareTrait;
use Soutenance\Service\Proposition\PropositionThese\PropositionTheseServiceAwareTrait;
use Soutenance\Service\Validation\ValidationThese\ValidationTheseServiceAwareTrait;
use Structure\Service\Etablissement\EtablissementServiceAwareTrait;
use These\Entity\Db\These;
use UnicaenApp\Exception\RuntimeException;
use UnicaenApp\Service\EntityManagerAwareTrait;
use UnicaenParametre\Service\Parametre\ParametreServiceAwareTrait;

class PropositionService extends BaseService
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
    use PropositionTheseServiceAwareTrait;
    use PropositionHDRServiceAwareTrait;
    use PropositionJuryRuleAwareTrait;

    public function getRepository(): DefaultEntityRepository
    {
        /** @var DefaultEntityRepository $qb */
        $qb = $this->getEntityManager()->getRepository(Proposition::class);

        return $qb;
    }

    /** GESTION DES ENTITES *******************************************************************************************/

    public function update(Proposition $proposition) : Proposition
    {
        try {
            $date = new DateTime();
            $user = $this->userContextService->getIdentityDb();
        } catch(Exception $e) {
            throw new RuntimeException("Un problème est survenu lors de la récupération des données liées à l'historisation", 0 , $e);
        }

        $proposition->setHistoModificateur($user);
        $proposition->setHistoModification($date);

        try {
            $this->getEntityManager()->flush($proposition);
        } catch (ORMException $e) {
            throw new RuntimeException("Une erreur s'est produite lors de la mise à jour de la proposition de soutenance !");
        }
        return $proposition;
    }

    public function historise(Proposition $proposition) : Proposition
    {
        $proposition->historiser();

        try {
            $this->getEntityManager()->flush($proposition);
        } catch (ORMException $e) {
            throw new RuntimeException("Un problème est survenue lors de l'enregistrement en BDD.", $e);
        }
        return $proposition;
    }

    public function restore(Proposition $proposition) : Proposition
    {
        $proposition->dehistoriser();

        try {
            $this->getEntityManager()->flush($proposition);
        } catch (ORMException $e) {
            throw new RuntimeException("Un problème est survenue lors de l'enregistrement en BDD.", $e);
        }
        return $proposition;
    }

    public function delete(Proposition $proposition) : Proposition
    {
        try {
            $this->getEntityManager()->remove($proposition);
            $this->getEntityManager()->flush($proposition);
        } catch (ORMException $e) {
            throw new RuntimeException("Une erreur s'est produite lors de la mise à jour de la proposition de soutenance !");
        }
        return $proposition;
    }

    /** REQUETES ******************************************************************************************************/

    protected function createQueryBuilder(): DefaultQueryBuilder
    {
        return $this->getRepository()->createQueryBuilder("proposition")
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
            //->addSelect('validation')->leftJoin('proposition.validations', 'validation')
        ;
    }

    public function getRequestedProposition(AbstractActionController $controller, string $param = 'proposition') : ?Proposition
    {
        $id = $controller->params()->fromRoute($param);

        return $this->find($id);
    }

    public function findOneForObject(These|HDR $object): ?Proposition
    {
        try {
            if($object instanceof These){
                $proposition = $this->propositionTheseService->getRepository()->findOneBy(['these' => $object]);
            }else{
                $proposition = $this->propositionHDRService->getRepository()->findOneBy(['hdr' => $object]);
            }
        } catch (NonUniqueResultException $e) {
            throw new RuntimeException("De multiples propositions associé à la thèse/HDR [".$object->getId()."] ont été trouvées !");
        }
        return $proposition ?: null;
    }

    /**
     * @param Proposition $proposition
     * @return Membre[]
     */
    public function getRapporteurs(Proposition $proposition): array
    {
        $rapporteurs = [];
        /** @var Membre $membre */
        foreach ($proposition->getMembres() as $membre) {
            if($membre->estRapporteur()) $rapporteurs[] = $membre;
        }

        return $rapporteurs;
    }

    /**
     * Fonction annulant toutes les validations associés à la proposition de soutenances
     *
     * @param Proposition $proposition
     */
//    public function annulerValidationsForProposition(Proposition $proposition)
//    {
//        $these = $proposition->getThese();
//        $validations = $this->getValidationService()->findValidationPropositionSoutenanceByThese($these);
//        foreach ($validations as $validation) {
//            $this->getValidationService()->historise($validation);
//            try {
//                $notif = $this->soutenanceNotificationFactory->createNotificationDevalidationProposition($these, $validation);
//                $this->notifierService->trigger($notif);
//            } catch (\Notification\Exception\RuntimeException $e) {
//                // aucun destinataire, todo : cas à gérer !
//            }
//        }
//        $validationED = current($this->getValidationService()->getRepository()->findValidationByCodeAndThese(TypeValidation::CODE_VALIDATION_PROPOSITION_ED, $these));
//        if ($validationED) {
//            $this->getValidationService()->historise($validationED);
//            try {
//                $notif = $this->soutenanceNotificationFactory->createNotificationDevalidationProposition($these, $validationED);
//                $this->notifierService->trigger($notif);
//            } catch (\Notification\Exception\RuntimeException $e) {
//                // aucun destinataire, todo : cas à gérer !
//            }
//        }
//        $validationUR = current($this->getValidationService()->getRepository()->findValidationByCodeAndThese(TypeValidation::CODE_VALIDATION_PROPOSITION_UR, $these));
//        if ($validationUR) {
//            $this->getValidationService()->historise($validationUR);
//            try {
//                $notif = $this->soutenanceNotificationFactory->createNotificationDevalidationProposition($these, $validationUR);
//                $this->notifierService->trigger($notif);
//            } catch (\Notification\Exception\RuntimeException $e) {
//                // aucun destinataire, todo : cas à gérer !
//            }
//        }
//        $validationBDD = current($this->getValidationService()->getRepository()->findValidationByCodeAndThese(TypeValidation::CODE_VALIDATION_PROPOSITION_BDD, $these));
//        if ($validationBDD) {
//            $this->getValidationService()->historise($validationBDD);
//            try {
//                $notif = $this->soutenanceNotificationFactory->createNotificationDevalidationProposition($these, $validationBDD);
//                $this->notifierService->trigger($notif);
//            } catch (\Notification\Exception\RuntimeException $e) {
//                // aucun destinataire, todo : cas à gérer !
//            }
//        }
//    }

    /**
     * @param Proposition|null $proposition
     * @return array
     */
    public function computeIndicateurForProposition(?Proposition $proposition): array
    {
        if ($proposition === null) return [];

        $this->propositionJuryRule->setProposition($proposition);
        $this->propositionJuryRule->execute();

        return $this->propositionJuryRule->getIndicateurs();
    }

    /**
     * @param Proposition|null $proposition
     * @param array $indicateurs
     * @return boolean
     */
    public function isJuryPropositionOk(?Proposition $proposition, array $indicateurs = []): bool
    {
        if ($proposition === null) return false;
        if ($indicateurs === []) $indicateurs = $this->computeIndicateurForProposition($proposition);
        /** @var Membre $membre */
        foreach ($proposition->getMembres() as $membre) {
            $email = $membre->getEmail();
            $res = ($membre->getEmail() === null);
            if ($membre->getEmail() === null) {
                return false;
            }
        }
        if (!$indicateurs["valide"]) return false;
        return true;
    }

    /**
     * @param Proposition $proposition
     * @param array $indicateurs
     * @return boolean
     */
    public function isPropositionOk(Proposition $proposition, array $indicateurs = []) : bool
    {
        if ($indicateurs === []) $indicateurs = $this->computeIndicateurForProposition($proposition);
        if (!$indicateurs["valide"]) return false;
        if(! $proposition->getDate() || ! $proposition->getLieu()) return false;
        return true;
    }

    /**
     * Genere la texte "M Pierre Denis, Le président de l'Universite de Xxxxx"
     * @var These|HDR $object
     * @return string
     */
    public function generateLibelleSignaturePresidence(These|HDR $object): string
    {
        $ETB_LIB_NOM_RESP = $this->variableService->getRepository()->findOneByCodeAndEtab(Variable::CODE_ETB_LIB_NOM_RESP, $object->getEtablissement());
        $ETB_LIB_TIT_RESP = $this->variableService->getRepository()->findOneByCodeAndEtab(Variable::CODE_ETB_LIB_TIT_RESP, $object->getEtablissement());
        $ETB_ART_ETB_LIB = $this->variableService->getRepository()->findOneByCodeAndEtab(Variable::CODE_ETB_ART_ETB_LIB, $object->getEtablissement());
        $ETB_LIB = $this->variableService->getRepository()->findOneByCodeAndEtab(Variable::CODE_ETB_LIB, $object->getEtablissement());

        $libelle  = "";
        $libelle .= $ETB_LIB_NOM_RESP ? $ETB_LIB_NOM_RESP->getValeur() : "";
        $libelle .= ", ";
        $libelle .= $ETB_LIB_TIT_RESP ? $ETB_LIB_TIT_RESP->getValeur() : "(Variable ETB_LIB_TIT_RESP introuvable)";
        $libelle .= " de ";
        $libelle .= $ETB_ART_ETB_LIB ? $ETB_ART_ETB_LIB->getValeur() : "";
        $libelle .= $ETB_LIB ? $ETB_LIB->getValeur() : "(Variable ETB_LIB introuvable)";

        return $libelle;
    }

    /**
     * @var These|HDR $object
     * @return string[]
     */
    public function findLogos(These|HDR $object): array
    {
        $logos = [];
        $logos['COMUE'] = null;
        if ($comue = $this->getEtablissementService()->fetchEtablissementComue()) {
            try {
                $logos['COMUE'] = $this->fichierStorageService->getFileForLogoStructure($comue->getStructure());
            } catch (StorageAdapterException $e) {
                $logos['COMUE'] = null;
            }
        }

        try {
            $logos['ETAB'] = $this->fichierStorageService->getFileForLogoStructure($object->getEtablissement()->getStructure());
        } catch (StorageAdapterException $e) {
            $logos['ETAB'] = null;
        }

        return $logos;
    }

    /**
     * @param Role $role
     * @return Proposition[]
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
            case Role::CODE_GEST_HDR :
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
     * @param \Soutenance\Entity\Proposition|null $proposition
     * @return Membre[]
     */
    public function extractMembresAsOptionsFromProposition(?Proposition $proposition): array
    {
        $array = [];

        if ($proposition !== null) {
            /** @var Membre[] $membres */
            $membres = $proposition->getMembres();
            foreach ($membres as $membre) {
                $array[$membre->getId()] = $membre->getDenomination();
            }
        }

        return $array;
    }

    /** PROPOSTITION ETAT  ********************************************************************************************/

    /**
     * @return Etat[]
     */
    public function findPropositionEtats() : array
    {
        $qb = $this->getEntityManager()->getRepository(Etat::class)->createQueryBuilder('etat')
            ->orderBy('etat.id');

        return $qb->getQuery()->getResult();
    }

    /**
     * @param string $code
     * @return \Soutenance\Entity\Etat
     */
    public function findPropositionEtatByCode(string $code): Etat
    {
        $qb = $this->getEntityManager()->getRepository(Etat::class)->createQueryBuilder('etat')
            ->andWhere('etat.code = :code')
            ->setParameter('code', $code);

        try {
            return $qb->getQuery()->getOneOrNullResult();
        } catch (ORMException $e) {
            throw new RuntimeException("Plusieurs ".Etat::class." partagent le même CODE [".$code."]", $e);
        }
    }

    /**
     * Les directeurs et co-directeurs sont des membres par défauts du jury d'une thèse. Cette fonction permet d'ajouter
     * ceux-ci à une proposition.
     * NB: La proposition doit être liée à une thèse.
     * NB: Les directeurs et codirecteurs sans mail ne sont pas ajoutés automatiquement
     *
     * @param Proposition $proposition
     */
//    public function addDirecteursAsMembres(Proposition $proposition)
//    {
//        $these = $proposition->getThese();
//        if ($these === null) throw new LogicException("Impossible d'ajout les directeurs comme membres : Aucun thèse de lié à la proposition id:" . $proposition->getId());
//
//        $encadrements = $this->getActeurTheseService()->getRepository()->findEncadrementThese($these);
//        foreach ($encadrements as $encadrement) {
//            $this->getMembreService()->createMembre($proposition, $encadrement);
//        }
//    }

    public function initialisationDateRetour(Proposition $proposition)
    {
        if ($proposition->getDate() === null) throw new RuntimeException("Aucune date de soutenance renseignée !");
        try {
            $renduRapport = $proposition->getDate();
            $categorieParametre = $proposition instanceof PropositionThese ? SoutenanceParametres::CATEGORIE : \Soutenance\Provider\Parametre\HDR\SoutenanceParametres::CATEGORIE;
            $deadline = $this->getParametreService()->getValeurForParametre($categorieParametre, SoutenanceParametres::DELAI_RETOUR);
            $renduRapport = $renduRapport->sub(new DateInterval('P'. $deadline.'D'));

            $date = DateTime::createFromFormat('d/m/Y H:i:s', $renduRapport->format('d/m/Y') . " 23:59:59");
        } catch (Exception $e) {
            throw new RuntimeException("Un problème a été rencontré lors du calcul de la date de rendu des rapport.");
        }
        $proposition->setRenduRapport($date);
        $this->update($proposition);
    }

}