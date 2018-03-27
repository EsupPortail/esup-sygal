<?php

namespace Application\Form\Hydrator;

use Application\Entity\Db\Individu;
use Application\Entity\Db\Utilisateur;
use Application\Entity\Db\Diffusion;
use Application\Entity\Db\RecapBu;
use Doctrine\ORM\OptimisticLockException;
use UnicaenApp\Service\EntityManagerAwareInterface;
use UnicaenApp\Service\EntityManagerAwareTrait;
use Zend\Stdlib\Hydrator\HydratorInterface;

class CreationUtilisateurHydrator implements HydratorInterface, EntityManagerAwareInterface
{
    use EntityManagerAwareTrait;

    /**
     * @param array $data
     * @param RecapBu $recap
     * @return RecapBu
     * @throws OptimisticLockException
     */
    public function hydrate(array $data, $recap) {

        /** @var Diffusion $diffusion */
        $repoUtilisateur = $this->entityManager->getRepository(Utilisateur::class);
        $repoIndividu = $this->entityManager->getRepository(Individu::class);

//        $diffusion = $repoDiffusion->findOneBy(["these" => $recap->getThese()]);
//
//        $recap->setOrcid($data['orcid']);
//        if ($diffusion !== null) {
//            $diffusion->setIdOrcid($data['orcid']);
//            $this->entityManager->flush($diffusion);
//        }
//        $recap->setNNT($data['nnt']);
//        $recap->setVigilance($data['vigilance']);
//        return $recap;
          return null;
    }

    /**
     * @param RecapBu $recap
     * @return array
     */
    public function extract($recap) {

//        /** @var Diffusion $diffusion */
//        $repoDiffusion = $this->entityManager->getRepository(Diffusion::class);
//        $diffusion = $repoDiffusion->findOneBy(["these" => $recap->getThese()]);
//        $orcid = ($diffusion === null) ? null : $diffusion->getIdOrcid();
//
//        $data['orcid']      = $orcid;
//        $data['nnt']        = $recap->getNNT();
//        $data['vigilance']  = $recap->getVigilance();
//
//        return $data;
        return [];
    }
}
