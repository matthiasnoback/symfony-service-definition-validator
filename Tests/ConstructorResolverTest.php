<?php

namespace Matthias\SymfonyServiceDefinitionValidator\Tests;

use Matthias\SymfonyServiceDefinitionValidator\ConstructorResolver;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class ConstructorResolverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function ifConstructorDoesNotExistResolvedConstructorIsNull()
    {
        $resolver = new ConstructorResolver(new ContainerBuilder());

        // stdClass has no constructor
        $definition = new Definition('stdClass');

        $this->assertNull($resolver->resolve($definition));
    }

    /**
     * @test
     */
    public function ifConstructorExistsResolvedConstructorIsConstructorMethod()
    {
        $resolver = new ConstructorResolver(new ContainerBuilder());

        // stdClass has a constructor
        $definition = new Definition('\DateTime');

        $expectedConstructor = new \ReflectionMethod('\DateTime', '__construct');
        $this->assertEquals($expectedConstructor, $resolver->resolve($definition));
    }

    /**
     * @test
     */
    public function ifConstructorIsNotPublicResolvedConstructorIsNull()
    {
        $resolver = new ConstructorResolver(new ContainerBuilder());

        // stdClass has a constructor
        $definition = new Definition('Matthias\SymfonyServiceDefinitionValidator\Tests\Fixtures\ClassWithNonPublicConstructor');

        $this->assertSame(null, $resolver->resolve($definition));
    }

    /**
     * @test
     */
    public function ifFactoryClassAndFactoryMethodAreDefinedResolvedConstructorIsFactoryMethod()
    {
        $resolver = new ConstructorResolver(new ContainerBuilder());

        $definition = new Definition();
        $factoryClass = 'Matthias\SymfonyServiceDefinitionValidator\Tests\Fixtures\FactoryClass';
        $definition->setFactoryClass($factoryClass);
        $factoryMethod = 'create';
        $definition->setFactoryMethod($factoryMethod);

        $expectedConstructor = new \ReflectionMethod($factoryClass, $factoryMethod);
        $this->assertEquals($expectedConstructor, $resolver->resolve($definition));
    }

    /**
     * @test
     */
    public function ifFactoryClassDoesNotExistFails()
    {
        $resolver = new ConstructorResolver(new ContainerBuilder());

        $definition = new Definition();
        $factoryClass = 'NonExistingClass';
        $definition->setFactoryClass($factoryClass);
        $factoryMethod = 'create';
        $definition->setFactoryMethod($factoryMethod);

        $this->setExpectedException('Matthias\SymfonyServiceDefinitionValidator\Exception\ClassNotFoundException');
        $resolver->resolve($definition);
    }
}
