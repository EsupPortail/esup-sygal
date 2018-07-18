<?php

namespace Application\View\Helper\Workflow;

use Application\Entity\Db\VWorkflow;
use Application\Entity\Db\WfEtape;
use Application\View\Helper\AbstractHelper;

class WorkflowStepHelper extends AbstractHelper
{
    /**
     * @var VWorkflow
     */
    protected $step;

    /**
     * @param VWorkflow|null $step
     * @return WorkflowStepHelper
     */
    function __invoke($step = null)
    {
        if ($step !== null) {
            $this->step = $step;
        }

        return $this;
    }

    /**
     * @return VWorkflow|null
     */
    public function step()
    {
        return $this->step;
    }

    /**
     *
     * @return string HTML
     */
    public function render()
    {
        if ($this->step === null) {
            return '';
        }

        $e = $this->step->getEtape();

        if (! $e->getRoute()) {
            return $this->renderAsText();
        }

        return $this->renderAsLink();
    }

    /**
     * @return bool
     */
    public function isStepAtteignable()
    {
        return $this->step ? (bool)$this->step->getAtteignable() : false;
    }

    /**
     * @param string $class
     * @return string HTML
     */
    protected function renderAsLink($class = null)
    {
        $e = $this->step->getEtape();

        if (! $this->step->getAtteignable()) {
            return $e->getLibelleActeur();
        }

        $url = $this->getView()->url($e->getRoute(), ['these' => $this->step->getThese()->getId() ], ['force_canonical' => true], true);

        $tpl = <<<EOS
<a class="roadmap-step-link %s" href="%s" title="Cliquez pour accéder à cette étape"
    ><span class="glyphicon glyphicon-circle-arrow-right"></span>%s</a>
EOS;
        return sprintf($tpl,
            $class,
            $url,
            $e->getLibelleActeur());
    }

    /**
     * @return string HTML
     */
    protected function renderAsText()
    {
        $e = $this->step->getEtape();

        return $e->getLibelleActeur();
    }

    /**
     * Détermine si la valeur actuelle du step correspond à l'étape spécifiée.
     *
     * @param WfEtape|string $etape
     * @return bool
     */
    public function isStep($etape)
    {
        if ($etape instanceof WfEtape) {
            $etape = $etape->getCode();
        }

        return $this->step ? $this->step->getEtape()->getCode() === $etape : false;
    }
}