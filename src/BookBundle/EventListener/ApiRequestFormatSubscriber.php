<?php

namespace BookBundle\EventListener;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;

class ApiRequestFormatSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            // must be registered after the default Locale listener
            KernelEvents::REQUEST => array(array('onRequest', 10)),
        ];
    }

    public function onRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();

        if (substr($request->getPathInfo(), 0, 4) == '/api') {
            $request->setRequestFormat('json');
            $request->setLocale('en');
        }
    }
}
