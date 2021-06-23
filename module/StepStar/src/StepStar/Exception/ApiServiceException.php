<?php

namespace StepStar\Exception;

class ApiServiceException extends \Exception
{
    /**
     * @var string
     */
    protected $response;

    /**
     * @param string $response
     * @return self
     */
    public function setResponse(string $response): self
    {
        $this->response = $response;
        return $this;
    }

    /**
     * @return string
     */
    public function getResponse(): ?string
    {
        return $this->response;
    }
}