<?php

namespace Matthias\SymfonyServiceDefinitionValidator\Exception;

class NonPublicConstructorException extends \RuntimeException implements DefinitionValidationExceptionInterface
{
    public function __construct($className)
    {
        parent::__construct(sprintf(
            'Class "%s" has no public constructor',
            $className
        ));
    }
}
