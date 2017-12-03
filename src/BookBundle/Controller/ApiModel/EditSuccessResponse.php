<?php

namespace BookBundle\Controller\ApiModel;

class EditSuccessResponse extends AbstractResponse
{
    public $success = true;

    public $id;

    public function __construct(int $id)
    {
        $this->id = $id;
    }
}