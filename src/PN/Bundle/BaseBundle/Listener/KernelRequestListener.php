<?php

namespace PN\Bundle\BaseBundle\Listener;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class KernelRequestListener
{

    public function onKernelRequest(GetResponseEvent $event)
    {
        if ($event->getRequestType() == HttpKernelInterface::MASTER_REQUEST) {
            $session = $event->getRequest()->getSession();
            if (!$session->isStarted()) {
                $session->start();
            }
        }
    }

}
