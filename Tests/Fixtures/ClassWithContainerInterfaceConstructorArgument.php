<?php

namespace Matthias\SymfonyServiceDefinitionValidator\Tests\Fixtures;

use Symfony\Component\DependencyInjection\ContainerInterface;

class ClassWithContainerInterfaceConstructorArgument
{
    public function __construct(ContainerInterface $container)
    {
    }
}
