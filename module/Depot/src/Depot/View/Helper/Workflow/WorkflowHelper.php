<?php

namespace Depot\View\Helper\Workflow;

use Depot\View\Helper\Workflow\WorkflowStepHelper;
use Depot\View\Helper\Workflow\RoadmapHelper;
use These\Entity\Db\These;
use Depot\Entity\Db\WfEtape;
use Depot\Service\Workflow\WorkflowServiceAwareInterface;
use Depot\Service\Workflow\WorkflowServiceAwareTrait;
use Application\View\Helper\AbstractHelper;

class WorkflowHelper extends AbstractHelper implements \Depot\Service\Workflow\WorkflowServiceAwareInterface
{
    use WorkflowServiceAwareTrait;

    /**
     * @var These
     */
    protected $these;

    /**
     * @param These $these
     * @return RoadmapHelper|string
     */
    function __invoke($these = null)
    {
        $this->these = $these;

        return $this;
    }

    /**
     * Délègue à l'aide de vue "roadmap".
     *
     * @return string HTML
     * @see RoadmapHelper
     */
    public function render()
    {
        return $this->getView()->roadmap()->render($this->these);
    }

    /**
     * @param These|null $these
     * @return WorkflowStepHelper
     */
    public function current(These $these = null)
    {
        $step = $this->workflowService->getCurrent($these ?: $this->these);

        return $this->getView()->wfs($step);
    }

    /**
     * @param WfEtape|string $etape
     * @param These|null     $these
     * @return WorkflowStepHelper
     */
    public function next($etape, These $these = null)
    {
        $step = $this->workflowService->getNext($these ?: $this->these, $etape);

        return $this->getView()->wfs($step);
    }

    /**
     * @param string     $etape
     * @param These|null $these
     * @return WorkflowStepHelper
     */
    public function one($etape, These $these = null)
    {
        $step = $this->workflowService->findOneByEtape($these ?: $this->these, $etape);

        return $this->getView()->wfs($step);
    }
}