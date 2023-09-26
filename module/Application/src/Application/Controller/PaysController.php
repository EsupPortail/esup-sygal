<?php

namespace Application\Controller;

use Application\Entity\Db\Pays;
use Application\Service\Pays\PaysServiceAwareTrait;
use Laminas\View\Model\JsonModel;
use Laminas\View\Model\ViewModel;

class PaysController extends AbstractController
{
    use PaysServiceAwareTrait;
    public function indexAction() : ViewModel
    {
        return new ViewModel([

        ]);
    }

    public function rechercherPaysAction(?string $type = null): JsonModel
    {

        if (($term = $this->params()->fromQuery('term'))) {
            /** @var Pays[] $pays */
            $qb = $this->paysService->getRepository()->createQueryBuilder('p')
                ->where('p.libelle is not null')
                ->where('lower(p.libelle) LIKE lower(:term)')->setParameter("term", '%'.$term.'%')
                ->orderBy('p.libelle');
            $pays = $qb->getQuery()->getResult();
            $result = [];
            foreach ($pays as $p) {
                // mise en forme attendue par l'aide de vue FormSearchAndSelect
                $label = $p->getLibelle();
                $result[$p->getId()] = array(
                    'id' => $p->getId(),   // identifiant unique de l'item
                    'label' => $label,          // libellé de l'item
                );
            }

            return new JsonModel($result);
        }
        exit;
    }
    public function rechercherNationaliteAction(?string $type = null): JsonModel
    {

        if (($term = $this->params()->fromQuery('term'))) {
            /** @var Pays[] $pays */
            $qb = $this->paysService->getRepository()->createQueryBuilder('p')
                ->where('p.libelleNationalite is not null')
                ->where('lower(p.libelleNationalite) LIKE lower(:term)')->setParameter("term", '%'.$term.'%')
                ->orderBy('p.libelle');
            $pays = $qb->getQuery()->getResult();
            $result = [];
            foreach ($pays as $p) {
                $label = $p->getLibelleNationalite();
                // Vérifiez si la nationalité n'est pas déjà présente dans le tableau
                $isNationaliteAlreadyPresent = false;
                foreach ($result as $item) {
                    if ($item['label'] === $label) {
                        $isNationaliteAlreadyPresent = true;
                        break;
                    }
                }

                // Si la nationalité n'est pas déjà présente, ajoutez une nouvelle entrée
                // mise en forme attendue par l'aide de vue FormSearchAndSelect
                if (!$isNationaliteAlreadyPresent) {
                    $result[$p->getId()] = [
                        'id' => $p->getId(),
                        'label' => $label,
                    ];
                }
            }
            return new JsonModel($result);
        }
        exit;
    }
}