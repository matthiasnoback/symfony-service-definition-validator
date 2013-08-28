<?php

namespace Matthias\SymfonyServiceDefinitionValidator\Exception;

class NonStaticFactoryMethodException extends \RuntimeException implements DefinitionValidationExceptionInterface
{
    public function __construct($className, $methodName)
    {
        parent::__construct(sprintf(
            'Factory method "%s::%s" is not static',
            $className,
            $methodName
        ));
    }
}
