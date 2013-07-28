<?php

namespace Matthias\SymfonyServiceDefinitionValidator;

use Symfony\Component\DependencyInjection\Definition;

interface ServiceDefinitionValidatorInterface
{
    /**
     * @param Definition $definition
     * @throws \Matthias\SymfonyServiceDefinitionValidator\Exception\DefinitionValidationExceptionInterface
     */
    public function validate(Definition $definition);
}
