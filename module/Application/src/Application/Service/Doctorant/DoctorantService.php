<?php

namespace Application\Service\Doctorant;

use Application\Entity\Db\Doctorant;
use Application\Entity\Db\DoctorantCompl;
use Application\Entity\Db\Repository\DoctorantRepository;
use Application\Entity\UserWrapper;
use Application\Service\BaseService;
use Application\Service\Etablissement\EtablissementServiceAwareTrait;
use Doctrine\ORM\NonUniqueResultException;
use UnicaenApp\Exception\RuntimeException;

class DoctorantService extends BaseService
{
    use EtablissementServiceAwareTrait;

    /**
     * @return DoctorantRepository
     */
    public function getRepository()
    {
        /** @var DoctorantRepository $repo */
        $repo = $this->entityManager->getRepository(Doctorant::class);

        return $repo;
    }

    /**
     * @param Doctorant $doctorant
     * @param array     $data
     * @return DoctorantCompl
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function updateDoctorant(Doctorant $doctorant, $data)
    {
        if (! ($complement = $doctorant->getComplement())) {
            $complement = new DoctorantCompl();
            $complement->setDoctorant($doctorant);
            $doctorant->setComplement($complement);

            $this->entityManager->persist($complement);
        }

        if (isset($data['identity'])) {
            $complement->setPersopass($data['identity']);
        }
        if (isset($data['mail'])) {
            $complement->setEmailPro($data['mail']);
        }

        $this->entityManager->flush($complement);

        return $complement;
    }

    /**
     * @param UserWrapper $user
     * @return Doctorant|null
     */
    public function findOneByUserWrapper(UserWrapper $user)
    {
        $id = $user->getSupannId();
        if (! $id) {
            throw new RuntimeException("Aucun id supann disponible.");
        }

        $domaineEtab = $user->getDomainFromEppn();
        if (! $domaineEtab) {
            return null;
        }

        $etablissement = $this->getEtablissementService()->getRepository()->findOneByDomaine($domaineEtab);
        if (! $etablissement) {
            throw new RuntimeException("Aucun établissement trouvé avec ce domaine: " . $domaineEtab);
        }

        $sourceCode = $etablissement->prependPrefixTo($id);

        try {
            $doctorant = $this->getRepository()->findOneBySourceCode($sourceCode);
        } catch (NonUniqueResultException $e) {
            throw new RuntimeException("Anomalie: plusieurs doctorants ont été trouvés avec le même source code: " . $sourceCode);
        }

        return $doctorant;
    }
}