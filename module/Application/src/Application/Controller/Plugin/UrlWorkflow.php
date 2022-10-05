<?php

namespace Application\Controller\Plugin;

use These\Entity\Db\These;
use Laminas\Mvc\Controller\Plugin\Url as UrlPlugin;

class UrlWorkflow extends UrlPlugin
{
    /**
     * @param These           $these
     * @param string          $etape
     * @param string|string[] $except
     * @param array           $queryParams
     * @return string
     */
    public function nextStepBox(These $these, $etape = null, $except = null, array $queryParams = [])
    {
        if ($etape !== null) {
            $queryParams['etape'] = $etape;
        }
        if ($except !== null) {
            $queryParams['except'] = $except;
        }

        return $this->fromRoute('workflow/next-step-box',
            ['these' => $these->getId()],
            ['query' => $queryParams]
        );
    }
}