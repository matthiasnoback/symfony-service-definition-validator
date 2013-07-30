<?php

namespace Matthias\SymfonyServiceDefinitionValidator;

use Symfony\Component\DependencyInjection\Definition;

interface DefinitionArgumentsValidatorInterface
{
    public function validate(Definition $definition);
}
