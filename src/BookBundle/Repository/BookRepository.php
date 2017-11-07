<?php

namespace BookBundle\Repository;

use BookBundle\Entity\Book;
use Doctrine\ORM\EntityRepository;

class BookRepository extends EntityRepository
{
    const CACHE_TIME = 86400;

    public function findAllOrderedByDateDesc()
    {
        return $this
            ->getEntityManager()
            ->getRepository(Book::class)
            ->createQueryBuilder('b')
            ->orderBy('b.readDate', 'DESC')
            ->getQuery()
            ->useResultCache(true)
            ->setResultCacheLifetime(self::CACHE_TIME)
            ->setResultCacheId('book_list_desc')
            ->getResult();
    }
}
