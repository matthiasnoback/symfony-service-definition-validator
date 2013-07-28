<?php

namespace Matthias\SymfonyServiceDefinitionValidator;

use Symfony\Component\DependencyInjection\Definition;

interface ConstructorResolverInterface
{
    public function resolve(Definition $definition);
}
