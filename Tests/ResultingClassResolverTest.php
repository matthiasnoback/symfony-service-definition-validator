<?php

namespace Matthias\SymfonyServiceDefinitionValidator\Tests;

use Matthias\SymfonyServiceDefinitionValidator\ResultingClassResolver;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class ResultingClassResolverTest extends TestCase
{
    public function testReturnsClassOfDefinition()
    {
        $resolver = new ResultingClassResolver(new ContainerBuilder());

        $definition = new Definition('stdClass');
        $resolvedClass = $resolver->resolve($definition);
        $this->assertSame('stdClass', $resolvedClass);
    }

    public function testResolvesClassOfDefinition()
    {
        $containerBuilder = new ContainerBuilder();
        $containerBuilder->setParameter('definition.class', 'stdClass');

        $resolver = new ResultingClassResolver($containerBuilder);

        $definition = new Definition('%definition.class%');

        $resolvedClass = $resolver->resolve($definition);

        $this->assertSame('stdClass', $resolvedClass);
    }
}
