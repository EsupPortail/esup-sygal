<?php

namespace Application\Controller;

class WorkflowController extends AbstractController
{
    /**
     * @return array
     */
    public function nextStepBoxAction()
    {
        $these = $this->requestedThese();
        $etape = $this->params()->fromQuery('etape');
        $except = $this->params()->fromQuery('except');
        $message = $this->params()->fromQuery('message');

        return [
            'these' => $these,
            'etape' => $etape,
            'except' => $except,
            'message' => $message,
        ];
    }
}