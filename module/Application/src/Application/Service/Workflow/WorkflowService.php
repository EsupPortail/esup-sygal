<?php

namespace Application\Service\Workflow;

use Application\Entity\Db\These;
use Application\Entity\Db\VWorkflow;
use Application\Entity\Db\WfEtape;
use Application\Entity\VWorkflowNull;
use Application\Service\BaseService;
use Application\Entity\Db\Repository\DefaultEntityRepository;
use Doctrine\ORM\Query;

/**
 * Class WorkflowService
 *
 * @package Application\Service\Fichier\Workflow
 */
class WorkflowService extends BaseService
{
    private $loaded = [];

    /**
     * @return DefaultEntityRepository
     */
    public function getRepository()
    {
        /** @var DefaultEntityRepository $repo */
        $repo = $this->entityManager->getRepository(VWorkflow::class);

        return $repo;
    }

    /**
     * @return DefaultEntityRepository
     */
    public function getEtapeRepository()
    {
        /** @var DefaultEntityRepository $repo */
        $repo = $this->entityManager->getRepository(WfEtape::class);

        return $repo;
    }

    /**
     * Retourne tous les items du workflow.
     *
     * @param These $these
     * @return VWorkflow[]
     */
    public function getWorkflow(These $these)
    {
        return $this->loadWorkflow($these);
    }

    /**
     * @param These $these
     * @return VWorkflow[]
     */
    public function reloadWorkflow(These $these)
    {
        return $this->loadWorkflow($these, true);
    }

    /**
     * @param These $these
     * @param bool  $forceRefresh
     * @return VWorkflow[]
     */
    private function loadWorkflow(These $these, $forceRefresh = false)
    {
        if (!$forceRefresh && isset($this->loaded[$these->getId()])) {
            return $this->loaded[$these->getId()];
        }

        $qb = $this->getRepository()->createQueryBuilder('v');
        $qb
            ->addSelect('e, t')
            ->join('v.these', 't')
            ->join('v.etape', 'e')
            ->orderBy('v.ordre')
            ->andWhere('v.these = :these')
            ->setParameter('these', $these);

        $workflow = [];
        /** @var WfEtape $prec */
        $prec = null;

        $q = $qb->getQuery()->setHint(Query::HINT_REFRESH, true); // setHint indispensable pour les tests fonctionnels

        /** @var VWorkflow $vwf */
        foreach ($q->getResult() as $vwf) {
            // injection de l'étape précédente
            $e = $vwf->getEtape();
            $e->setPrecedente($prec);
            $prec = $e;

            // injection des témoins d'état
            $e->setAtteignable($vwf->getAtteignable());
            $e->setFranchie($vwf->getFranchie());

            $workflow[] = $vwf;
        }

        $this->loaded[$these->getId()] = $workflow;

        return $workflow;
    }

    /**
     * Retourne l'item correspondant à l'étape spécifiée.
     *
     * @param These          $these
     * @param WfEtape|string $etape
     * @return VWorkflow|null
     */
    public function findOneByEtape(These $these, $etape)
    {
        if (! $etape) {
            return null;
        }
        if ($etape instanceof WfEtape) {
            $etape = $etape->getCode();
        }

        $workflow = $this->loadWorkflow($these);

        foreach ($workflow as $r) {
            if ($r->getEtape()->getCode() === $etape) {
                return $r;
            }
        }

        return VWorkflowNull::inst($these);
    }

    /**
     * Retourne l'item correspondant à l'étape courante, autrement dit la première étape non franchie.
     *
     * @param These $these
     * @return VWorkflow|null
     */
    public function getCurrent(These $these)
    {
        $workflow = $this->loadWorkflow($these);

        foreach ($workflow as $r) {
            if (! $r->getFranchie()) {
                return $r;
            }
        }

        return VWorkflow::pseudoEtapeFinale();
    }

    /**
     * Retourne l'item correspondant à l'étape située juste après l'étape spécifiée.
     *
     * @param These               $these
     * @param WfEtape|string|null $etape null <=> pseudo-étape située avant la première étape
     * @return VWorkflow|null
     */
    public function getNext(These $these, $etape = null)
    {
        $workflow = $this->loadWorkflow($these);

        if ($etape === null) {
            $r = (new \ArrayIterator($workflow))->current();
            if ($r === null) {
                return VWorkflow::pseudoEtapeFinale();
            }
            return $r;
        }

        $r = $this->findOneByEtape($these, $etape);

        if ($r->estNull()) {
            return VWorkflow::pseudoEtapeFinale();
        }

        return $this->findOneByEtape($these, $r->getEtape()->getSuivante());
    }

    /**
     * Retourne les items correspondant à toutes les étapes situées AVANT celle spécifiée.
     *
     * @param These               $these
     * @param WfEtape|string|null $etape null <=> pseudo-étape située après la dernière étape
     * @return VWorkflow[]
     */
    public function getBefore(These $these, $etape)
    {
        if ($etape instanceof WfEtape) {
            $etape = $etape->getCode();
        }

        $wf = $this->getWorkflow($these);

        $result = [];
        $found = false;

        foreach ($wf as $r) {
            // bingo
            if ($r->getEtape()->getCode() === $etape) {
                $found = true;
            }
            // après
            elseif ($found) {
            }
            // avant
            else {
                $result[] = $r;
            }
        }

        return $result;
    }

    /**
     * Retourne les items correspondant à toutes les étapes situées APRÈS celle spécifiée.
     *
     * @param These          $these
     * @param string|WfEtape $etape
     * @return VWorkflow[]
     */
    public function getAfter(These $these, $etape)
    {
        if ($etape instanceof WfEtape) {
            $etape = $etape->getCode();
        }

        $wf = $this->getWorkflow($these);

        $result = [];
        $found = false;

        foreach ($wf as $r) {
            // bingo
            if ($r->getEtape()->getCode() === $etape) {
                $found = true;
            }
            // après
            elseif ($found) {
                $result[] = $r;
            }
            // avant
            else {
            }
        }

        return $result;
    }
}