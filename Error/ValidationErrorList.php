<?php

namespace Matthias\SymfonyServiceDefinitionValidator\Error;

class ValidationErrorList implements \IteratorAggregate, ValidationErrorListInterface
{
    private $errors = array();

    public function add(ValidationErrorInterface $error)
    {
        $this->errors[] = $error;
    }

    public function count()
    {
        return count($this->errors);
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->errors);
    }
}
