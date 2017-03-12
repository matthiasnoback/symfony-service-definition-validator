<?php

namespace Matthias\SymfonyServiceDefinitionValidator\Tests\Fixtures;

use Symfony\Component\DependencyInjection\ContainerBuilder;

class ClassWithContainerBuilderConstructorArgument
{
    public function __construct(ContainerBuilder $container)
    {
    }
}
