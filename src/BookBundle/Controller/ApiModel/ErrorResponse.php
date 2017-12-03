<?php

namespace BookBundle\Controller\ApiModel;

use Symfony\Component\Validator as Validator;

class ErrorResponse extends AbstractResponse
{
    public $success = false;

    public $errors;

    public function __construct($errors)
    {
        if ($errors instanceof Validator\ConstraintViolationListInterface) {
            foreach ($errors as $error) {
                $this->errors[] = (string)$error;
            }
        } else {
            $this->errors[] = $errors;
        }
    }
}