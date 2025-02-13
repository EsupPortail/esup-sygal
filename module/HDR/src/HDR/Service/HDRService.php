<?php

namespace HDR\Service;

use Acteur\Entity\Db\ActeurHDR;
use Acteur\Service\ActeurHDR\ActeurHDRServiceAwareTrait;
use Application\Entity\Db\Role;
use Application\Service\BaseService;
use Application\Service\Source\SourceServiceAwareTrait;
use Application\Service\UserContextServiceAwareTrait;
use Application\Service\Variable\VariableServiceAwareTrait;
use DateTime;
use Doctrine\ORM\ORMException;
use Exception;
use Fichier\Service\Fichier\FichierStorageServiceAwareTrait;
use HDR\Entity\Db\HDR;
use HDR\Entity\Db\Repository\HDRRepository;
use Individu\Entity\Db\Individu;
use Notification\Service\NotifierServiceAwareTrait;
use Soutenance\Service\Membre\MembreServiceAwareTrait;
use Soutenance\Service\Notification\SoutenanceNotificationFactoryAwareTrait;
use Structure\Service\Etablissement\EtablissementServiceAwareTrait;
use UnicaenApp\Exception\RuntimeException;
use UnicaenApp\Service\EntityManagerAwareTrait;
use UnicaenParametre\Service\Parametre\ParametreServiceAwareTrait;
use Validation\Service\ValidationHDR\ValidationHDRServiceAwareTrait;

class HDRService extends BaseService
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
    use SourceServiceAwareTrait;

    public function getRepository(): HDRRepository
    {
        /** @var HDRRepository $qb */
        $qb = $this->getEntityManager()->getRepository(HDR::class);

        return $qb;
    }

    public function newHDR() : HDR
    {
        $hdr = new HDR();
        $hdr->setSource($this->sourceService->fetchApplicationSource());
        $hdr->setSourceCode($this->sourceService->genereateSourceCode());

        return $hdr;
    }

    /** GESTION DES ENTITES *******************************************************************************************/

    public function create(HDR $hdr) : HDR
    {
        try {
            $this->getEntityManager()->persist($hdr);
            $this->getEntityManager()->flush($hdr);
        } catch (ORMException $e) {
            throw new RuntimeException("Un erreur s'est produite lors de l'enregistrment en BD de la HDR !");
        }
        return $hdr;
    }

    public function update(HDR $hdr) : HDR
    {
        try {
            $date = new DateTime();
            $user = $this->userContextService->getIdentityDb();
        } catch(Exception $e) {
            throw new RuntimeException("Un problème est survenu lors de la récupération des données liées à l'historisation", 0 , $e);
        }

        $hdr->setHistoModificateur($user);
        $hdr->setHistoModification($date);

        try {
            $this->getEntityManager()->flush($hdr);
        } catch (ORMException $e) {
            throw new RuntimeException("Une erreur s'est produite lors de la mise à jour de la HDR !");
        }
        return $hdr;
    }

    public function saveHDR(HDR $hdr): HDR
    {
        /** @var ActeurHDR[] $direction */
        $direction = $hdr->getActeursByRoleCode([
            Role::CODE_HDR_GARANT
        ]);

        foreach ($direction as $acteur) {
            try {
                $this->getEntityManager()->persist($acteur);
            } catch (ORMException $e) {
                throw new RuntimeException("Un problème est survenu lors de l'enregistrement en BD !",0,$e);
            }
        }

        $candidat = $hdr->getCandidat();
        if($candidat){
            try {
                $this->getEntityManager()->persist($candidat);
            } catch (ORMException $e) {
                throw new RuntimeException("Un problème est survenu lors de l'enregistrement en BD !",0,$e);
            }
        }

        if($hdr->getId() !== null){
            return $this->updateAll($hdr);
        }else{
            return $this->create($hdr);
        }
    }

    public function updateAll(HDR $hdr, $serviceEntityClass = null): HDR
    {
        $entityClass = get_class($hdr);
        $serviceEntityClass = $serviceEntityClass ?: HDR::class;
        if ($serviceEntityClass != $entityClass && !is_subclass_of($hdr, $serviceEntityClass)) {
            throw new \RuntimeException("L'entité transmise doit être de la classe $serviceEntityClass.");
        }
        try {
            $this->getEntityManager()->flush();
        } catch (ORMException $e) {
            throw new RuntimeException("Un problème est survenu lors de l'enregistrement en BD !",0,$e);
        }
        return $hdr;
    }

    public function historise(HDR $hdr) : HDR
    {
        $hdr->historiser();

        try {
            $this->getEntityManager()->flush($hdr);
        } catch (ORMException $e) {
            throw new RuntimeException("Un problème est survenue lors de l'enregistrement en BDD.", $e);
        }
        return $hdr;
    }

    public function restore(HDR $hdr) : HDR
    {
        $hdr->dehistoriser();

        try {
            $this->getEntityManager()->flush($hdr);
        } catch (ORMException $e) {
            throw new RuntimeException("Un problème est survenue lors de l'enregistrement en BDD.", $e);
        }
        return $hdr;
    }

    public function delete(HDR $hdr) : HDR
    {
        try {
            $this->getEntityManager()->remove($hdr);
            $this->getEntityManager()->flush($hdr);
        } catch (ORMException $e) {
            throw new RuntimeException("Une erreur s'est produite lors de la mise à jour de la HDR !");
        }
        return $hdr;
    }

    /** PREDICATS *****************************************************************************************************/

    /**
     * @param HDR $hdr
     * @param Individu $individu
     * @return bool
     */
    public function isCandidat(HDR $hdr, Individu $individu): bool
    {
        return ($hdr->getCandidat()->getIndividu() === $individu);
    }

    /**
     * @param HDR $hdr
     * @param Individu $individu
     * @return bool
     */
    public function isGarant(HDR $hdr, Individu $individu): bool
    {
        $garants = $this->getActeurHDRService()->getRepository()->findActeursByHDRAndRole($hdr, 'D');
        foreach ($garants as $garant) {
            if ($garant->getIndividu() === $individu) return true;
        }
        return false;
    }
}