<?php

namespace BookBundle\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use BookBundle\Entity\Book;

class DeleteUnusedFilesSubscriber implements EventSubscriber
{
    private $uploadDir;

    public function __construct($uploadDir)
    {
        $this->uploadDir = $uploadDir;
    }

    public function getSubscribedEvents()
    {
        return array(
            'preUpdate',
            'preRemove',
        );
    }

    public function preUpdate(LifecycleEventArgs $args)
    {
        $book = $args->getEntity();
        $changeSet = $args->getEntityManager()->getUnitOfWork()->getEntityChangeSet($book);

        if (isset($changeSet['cover'])) {
            if ($changeSet['cover'][0]) {
                $coverFullPath = $this->uploadDir . '/' . $changeSet['cover'][0];
                if (file_exists($coverFullPath)) {
                    unlink($coverFullPath);
                }
            }
        }

        if (isset($changeSet['source'])) {
            if ($changeSet['source'][0]) {
                $sourceFullPath = $this->uploadDir . '/' . $changeSet['source'][0];
                if (file_exists($sourceFullPath)) {
                    unlink($sourceFullPath);
                }
            }
        }
    }

    public function preRemove(LifecycleEventArgs $args)
    {
        $book = $args->getEntity();

        if ($book->getCover()) {
            $coverFullPath = $this->uploadDir . '/' . $book->getCover();
            if (file_exists($coverFullPath)) {
                unlink($coverFullPath);
            }
        }

        if ($book->getSource()) {
            $sourceFullPath = $this->uploadDir . '/' . $book->getSource();
            if (file_exists($sourceFullPath)) {
                unlink($sourceFullPath);
            }
        }
    }
}
