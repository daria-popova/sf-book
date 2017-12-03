<?php

namespace BookBundle\Controller\ApiModel;

class BookListResponse extends AbstractResponse
{
    public $success = true;

    public $books;

    public function __construct(array $books)
    {
        $this->books = $books;
    }

}