<?php

namespace Matthias\SymfonyServiceDefinitionValidator\Error;

use Symfony\Component\DependencyInjection\Definition;

class ValidationErrorFactory implements ValidationErrorFactoryInterface
{
    public function createValidationErrorList()
    {
        return new ValidationErrorList();
    }

    public function createValidationError($serviceId, Definition $definition, \Exception $exception)
    {
        return new ValidationError($serviceId, $definition, $exception);
    }
}
