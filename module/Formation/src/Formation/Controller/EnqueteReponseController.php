<?php

namespace Formation\Controller;

use Application\Controller\AbstractController;
use Formation\Entity\Db\EnqueteCategorie;
use Formation\Entity\Db\EnqueteQuestion;
use Formation\Entity\Db\EnqueteReponse;
use Formation\Entity\Db\Session;
use Formation\Form\EnqueteCategorie\EnqueteCategorieFormAwareTrait;
use Formation\Form\EnqueteQuestion\EnqueteQuestionFormAwareTrait;
use Formation\Form\EnqueteReponse\EnqueteReponseFormAwareTrait;
use Formation\Service\EnqueteCategorie\EnqueteCategorieServiceAwareTrait;
use Formation\Service\EnqueteQuestion\EnqueteQuestionServiceAwareTrait;
use Formation\Service\EnqueteReponse\EnqueteReponseServiceAwareTrait;
use Formation\Service\Session\SessionServiceAwareTrait;
use Laminas\View\Model\ViewModel;
use UnicaenApp\Service\EntityManagerAwareTrait;

class EnqueteReponseController extends AbstractController {
    use EntityManagerAwareTrait;
    use EnqueteCategorieServiceAwareTrait;
    use EnqueteQuestionServiceAwareTrait;
    use EnqueteReponseServiceAwareTrait;
    use SessionServiceAwareTrait;
    use EnqueteCategorieFormAwareTrait;
    use EnqueteQuestionFormAwareTrait;
    use EnqueteReponseFormAwareTrait;

}