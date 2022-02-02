<?php

namespace Matthias\SymfonyServiceDefinitionValidator\Error;

class ValidationErrorList implements \IteratorAggregate, ValidationErrorListInterface
{
    private $errors = array();

    public function add(ValidationErrorInterface $error)
    {
        $this->errors[] = $error;
    }

    public function count(): int
    {
        return count($this->errors);
    }

    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->errors);
    }
}
