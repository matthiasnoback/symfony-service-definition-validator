<?php

namespace Matthias\SymfonyServiceDefinitionValidator\Error;

interface ValidationErrorListInterface extends \Countable, \Traversable
{
    /**
     * @param ValidationErrorInterface $error
     * @return null
     */
    public function add(ValidationErrorInterface $error);
}
