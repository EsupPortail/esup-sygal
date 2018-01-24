<?php

namespace Application\Service\Doctorant;

interface DoctorantServiceAwareInterface
{
    public function setDoctorantService(DoctorantService $doctorantService);
}