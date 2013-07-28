<?php

namespace Matthias\SymfonyServiceDefinitionValidator\Exception;

class MethodNotFoundException extends \RuntimeException implements DefinitionValidationExceptionInterface
{
    public function __construct($className, $methodName)
    {
        parent::__construct(sprintf(
            'Method "%s::%s" does not exist',
            $className,
            $methodName
        ));
    }
}
