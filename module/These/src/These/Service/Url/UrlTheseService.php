<?php

namespace These\Service\Url;

use Application\Service\Url\UrlService;
use These\Entity\Db\These;

class UrlTheseService extends UrlService
{
    /**
     * @param These  $these
     * @param string $redirect URL éventuelle où rediriger ensuite
     * @return string
     */
    public function refreshTheseUrl(These $these, $redirect = null)
    {
        $options = $this->options;

        if ($redirect !== null) {
            $options = array_merge_recursive($options, ['query' => ['redirect' => $redirect] ]);
        }

        return $this->fromRoute('these/refresh-these',
            ['these' => $this->idify($these)],
            $options
        );
    }
}