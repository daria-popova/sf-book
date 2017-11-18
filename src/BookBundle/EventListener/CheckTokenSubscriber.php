<?php

namespace BookBundle\EventListener;

use BookBundle\Controller\TokenAuthenticatedController;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;

class CheckTokenSubscriber implements EventSubscriberInterface
{
    private $token;

    public function __construct(string $token)
    {
        $this->token = $token;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::CONTROLLER => 'onKernelController',
        ];
    }

    public function onKernelController(FilterControllerEvent $event) : void
    {
        $controller = $event->getController();
        $request = $event->getRequest();

        if (!is_array($controller)) {
            return;
        }

        if ($controller[0] instanceof TokenAuthenticatedController) {
            if (!$request->query->has('token')) {
                throw new AccessDeniedHttpException('Token is missing');
            } elseif ($request->query->get('token') !== $this->token) {
                throw new AccessDeniedHttpException('Invalid token');
            }
        }
    }
}
