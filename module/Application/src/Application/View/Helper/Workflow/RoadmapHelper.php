<?php

namespace Application\View\Helper\Workflow;

use Application\Entity\Db\These;
use Application\Entity\Db\VWorkflow;
use Application\Service\Workflow\WorkflowServiceAwareInterface;
use Application\Service\Workflow\WorkflowServiceAwareTrait;
use Application\View\Helper\AbstractHelper;

class RoadmapHelper extends AbstractHelper implements WorkflowServiceAwareInterface
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
        if ($these === null) {
            return $this;
        }

        $this->these = $these;

        return $this->render($these);
    }

    /**
     * @param These $these
     * @return string
     */
    public function render(These $these = null)
    {
        $workflow = $this->workflowService->getWorkflow($these);

        if (empty($workflow)) {
            return '';
        }

        $parts = [];
        /** @var VWorkflow $r */
        foreach ($workflow as $i => $r) {
            $parts[] = sprintf('<tr>%s</tr>', $this->renderRow($r));
        }
        $html =  sprintf('<table class="roadmap table table-sm table-hover">%s</table>', implode('', $parts));

        return $html;
    }

    /**
     * @param VWorkflow $r
     * @return string HTML
     */
    private function renderRow(VWorkflow $r)
    {
        $e = $r->getEtape();

        $iconChecked = '<span title="Franchie" class="fas fa-check-circle text-success"></span>';
        $iconUnchecked = '<span title="Non franchie" class="fas fa-times-circle"></span>';

        return sprintf("<td>%s %s %s</td><!--<td>%s/%s</td>-->",
            str_repeat("&nbsp;", ($e->getChemin() - 1) * 7),
            $r->getFranchie() ? $iconChecked : $iconUnchecked,
            $this->renderLinkEtape($r),
            $r->getResultat(),
            $r->getObjectif()
        );
    }

    /**
     * @param VWorkflow $r
     * @param string    $class
     * @return string HTML
     */
    private function renderLinkEtape(VWorkflow $r, $class = null)
    {
        $e = $r->getEtape();

        if (! $r->getAtteignable()) {
            return $e->getLibelleActeur();
        }

        $url = $this->getView()->url($e->getRoute(), ['these' => $this->these->getId()], ['force_canonical' => true], true);

        return sprintf('<a class="%s" href="%s">%s</a>', $class, $url, $e->getLibelleActeur());
    }

//    /**
//     * @param These  $these
//     * @param string $class
//     * @return string HTML
//     */
//    public function current(These $these, $class = null)
//    {
//        $r = $this->workflowService->getCurrent($these);
//        if (! $r) {
//            return '';
//        }
//
//        return $this->renderLinkEtape($r, $class);
//    }
//
//    /**
//     * @param string $codeEtape
//     * @param These  $these
//     * @param string $class
//     * @return string HTML
//     */
//    public function get($codeEtape, These $these, $class = null)
//    {
//        $r = $this->workflowService->findOneByEtape($codeEtape, $these);
//        if (! $r) {
//            return '';
//        }
//
//        return $this->renderLinkEtape($r, $class);
//    }
//
//    /**
//     * @param string $codeEtape
//     * @param These  $these
//     * @param string $class
//     * @return string HTML
//     */
//    public function next($codeEtape, These $these, $class = null)
//    {
//        $r = $this->workflowService->getNext($codeEtape, $these);
//        if (! $r) {
//            return '';
//        }
//
//        return $this->renderLinkEtape($r, $class);
//    }
}