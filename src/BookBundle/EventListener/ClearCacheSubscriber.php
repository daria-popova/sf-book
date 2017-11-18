<?php

namespace BookBundle\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\PostFlushEventArgs;

class ClearCacheSubscriber implements EventSubscriber
{
    private $cacheKeys;

    public function __construct(array $cacheKeys)
    {
        $this->cacheKeys = $cacheKeys;
    }

    public function getSubscribedEvents()
    {
        return [
            'postFlush',
        ];
    }

    public function postFlush(PostFlushEventArgs $args) : void
    {
        foreach ($this->cacheKeys as $key) {
            $args
                ->getEntityManager()
                ->getConfiguration()
                ->getResultCacheImpl()
                ->delete($key);
        }
    }
}
