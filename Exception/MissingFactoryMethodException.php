<?php

namespace Matthias\SymfonyServiceDefinitionValidator\Exception;

class MissingFactoryMethodException extends \InvalidArgumentException implements DefinitionValidationExceptionInterface
{
    public function __construct()
    {
        parent::__construct('The factory method name is missing');
    }
}
