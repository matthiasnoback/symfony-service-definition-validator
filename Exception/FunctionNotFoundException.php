<?php

namespace Matthias\SymfonyServiceDefinitionValidator\Exception;

class FunctionNotFoundException extends \RuntimeException implements DefinitionValidationExceptionInterface
{
    public function __construct($functionName)
    {
        parent::__construct(sprintf(
            'Function "%s" does not exist',
            $functionName
        ));
    }
}
