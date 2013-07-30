<?php

namespace Matthias\SymfonyServiceDefinitionValidator;

use Symfony\Component\DependencyInjection\Definition;

interface MethodCallsValidatorInterface
{
    public function validate(Definition $definition);
}
