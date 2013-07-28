<?php

namespace Matthias\SymfonyServiceDefinitionValidator;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class ResultingClassResolver implements ResultingClassResolverInterface
{
    public function resolve(Definition $definition)
    {
        return $definition->getClass();
    }
}
