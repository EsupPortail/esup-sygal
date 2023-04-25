<?php

namespace Formation\Controller;

use Application\Controller\AbstractController;
use Formation\Form\EnqueteCategorie\EnqueteCategorieFormAwareTrait;
use Formation\Form\EnqueteQuestion\EnqueteQuestionFormAwareTrait;
use Formation\Form\EnqueteReponse\EnqueteReponseFormAwareTrait;
use Formation\Service\EnqueteCategorie\EnqueteCategorieServiceAwareTrait;
use Formation\Service\EnqueteQuestion\EnqueteQuestionServiceAwareTrait;
use Formation\Service\EnqueteReponse\EnqueteReponseServiceAwareTrait;
use Formation\Service\Session\SessionServiceAwareTrait;
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