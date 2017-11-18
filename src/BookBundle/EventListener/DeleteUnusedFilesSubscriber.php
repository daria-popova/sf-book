<?php

namespace BookBundle\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use BookBundle\Entity\Book;

class DeleteUnusedFilesSubscriber implements EventSubscriber
{
    private $uploadDir;

    private $fileList;

    public function __construct(string $uploadDir)
    {
        $this->uploadDir = $uploadDir;
        $this->fileList = [];
    }

    public function getSubscribedEvents() : array
    {
        return [
            'preUpdate',
            'preRemove',
            'postFlush',
        ];
    }

    public function preUpdate(LifecycleEventArgs $args) : void
    {
        $book = $args->getEntity();
        $changeSet = $args->getEntityManager()->getUnitOfWork()->getEntityChangeSet($book);

        if (isset($changeSet['cover'][0])) {
            $this->fileList[] = $this->uploadDir . $changeSet['cover'][0];
        }

        if (isset($changeSet['source'][0])) {
            $this->fileList[] = $this->uploadDir . $changeSet['source'][0];
        }
    }

    public function preRemove(LifecycleEventArgs $args) : void
    {
        $book = $args->getEntity();

        if ($book->getCover()) {
            $this->fileList[] =  $this->uploadDir . $book->getCover();
        }

        if ($book->getSource()) {
            $this->fileList[] = $this->uploadDir . $book->getSource();
        }
    }

    public function postFlush(PostFlushEventArgs $args) : void
    {
        foreach ($this->fileList as $filePath) {
            if (is_file($filePath)) {
                unlink($filePath);
            }
        }
    }
}
