<?php

namespace Soutenance\Service\Horodatage;

use Horodatage\Service\Horodatage\HorodatageServiceAwareTrait;
use Soutenance\Entity\Proposition;
use Soutenance\Service\Proposition\PropositionServiceAwareTrait;

class HorodatageService {
    use HorodatageServiceAwareTrait;
    use PropositionServiceAwareTrait;

    const TYPE_EDITION      = "Édition";
    const TYPE_ETAT         = "État";
    const TYPE_MODIFICATION = "Modification";
    const TYPE_NOTIFICATION = "Notification";
    const TYPE_VALIDATION   = "Validation";

    public function addHorodatage(Proposition $proposition, string $type, ?string $complement = null)
    {
        $horodatage = $this->getHorodatageService()->createHorodatage($type,$complement);
        $proposition->addHorodatage($horodatage);
        $this->getPropositionService()->update($proposition);
    }
}