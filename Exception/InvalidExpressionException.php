<?php

namespace Matthias\SymfonyServiceDefinitionValidator\Exception;

class InvalidExpressionException extends \RuntimeException implements DefinitionValidationExceptionInterface
{
    public function __construct($expression, \Exception $exception)
    {
        parent::__construct(
            sprintf(
                'Expression "%s" could not be evaluated: %s',
                $expression,
                $exception->getMessage()
            ),
            0,
            $exception
        );
    }
}
