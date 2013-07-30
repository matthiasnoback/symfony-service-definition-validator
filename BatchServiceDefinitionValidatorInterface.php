<?php

namespace Matthias\SymfonyServiceDefinitionValidator;

use Matthias\SymfonyServiceDefinitionValidator\Error\ValidationErrorListInterface;

interface BatchServiceDefinitionValidatorInterface
{
    /**
     * Each key should be a service id, each value should be a Definition object
     *
     * @param array $serviceDefinitions
     * @return ValidationErrorListInterface
     */
    public function validate(array $serviceDefinitions);
}
