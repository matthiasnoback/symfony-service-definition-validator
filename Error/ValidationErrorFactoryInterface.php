<?php

namespace Matthias\SymfonyServiceDefinitionValidator\Error;

use Symfony\Component\DependencyInjection\Definition;

interface ValidationErrorFactoryInterface
{
    /**
     * @return ValidationErrorListInterface
     */
    public function createValidationErrorList();

    /**
     * @return ValidationErrorInterface
     */
    public function createValidationError($serviceId, Definition $definition, \Exception $exception);
}
