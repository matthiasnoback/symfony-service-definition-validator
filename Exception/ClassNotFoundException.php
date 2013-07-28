<?php

namespace Matthias\SymfonyServiceDefinitionValidator\Exception;

class ClassNotFoundException extends \RuntimeException implements DefinitionValidationExceptionInterface
{
    public function __construct($className)
    {
        parent::__construct(sprintf(
            'Class "%s" does not exist',
            $className
        ));
    }
}
