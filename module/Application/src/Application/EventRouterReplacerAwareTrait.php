<?php

namespace Application;

trait EventRouterReplacerAwareTrait
{
    /**
     * @var EventRouterReplacer
     */
    protected $eventRouterReplacer;

    /**
     * ImportNotificationController constructor.
     *
     * @param EventRouterReplacer $eventRouterReplacer
     * @return void
     */
    public function setEventRouterReplacer(EventRouterReplacer $eventRouterReplacer)
    {
        $this->eventRouterReplacer = $eventRouterReplacer;
    }

}