<?php

namespace Matthias\SymfonyServiceDefinitionValidator\Exception;

use Symfony\Component\ExpressionLanguage\SyntaxError;

class InvalidExpressionSyntaxException extends \RuntimeException implements DefinitionValidationExceptionInterface
{
    public function __construct($expression, SyntaxError $exception)
    {
        parent::__construct(
            sprintf(
                'The syntax of expression "%s" is invalid: %s',
                $expression,
                $exception->getMessage()
            ),
            0,
            $exception
        );
    }
}
