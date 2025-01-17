<?php

namespace Admission\Service\Url;

use Admission\Entity\Db\Admission;

class UrlService extends \Application\Service\Url\UrlService
{
    protected ?array $allowedVariables = [
        'admission',
    ];

    /**
     * @noinspection
     * @return string
     */
    public function getAdmission() : string
    {
        $admission = $this->variables['admission'];
        if($admission instanceof Admission){
            $individu = $admission->getIndividu() ;
        }else {
            $individu = $admission->getAdmission()->getIndividu();
        }
        $link = $this->fromRoute('admission/ajouter', ['action' => 'etudiant', 'individu' => $individu->getId()], ['force_canonical' => true, 'query' => ['refresh' => 'true']], true);
        $url = "<a href='" . $link . "'>lien</a>";

        return $url;
    }

    /**
     * @noinspection
     * @return string
     */
    public function getAccueilAdmission() : string
    {
        $link = $this->fromRoute('admission', ['action' => 'index'], ['force_canonical' => true], true);
        $url = "<a href='" . $link . "'>lien</a>";

        return $url;
    }
}