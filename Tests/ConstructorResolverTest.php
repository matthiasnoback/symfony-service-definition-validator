<?php

namespace Matthias\SymfonyServiceDefinitionValidator\Tests;

use Matthias\SymfonyServiceDefinitionValidator\ConstructorResolver;
use Matthias\SymfonyServiceDefinitionValidator\ResultingClassResolver;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class ConstructorResolverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function ifConstructorDoesNotExistResolvedConstructorIsNull()
    {
        $containerBuilder = new ContainerBuilder();
        $resolver = new ConstructorResolver($containerBuilder, new ResultingClassResolver($containerBuilder));

        // stdClass has no constructor
        $definition = new Definition('stdClass');

        $this->assertNull($resolver->resolve($definition));
    }

    /**
     * @test
     */
    public function ifConstructorExistsResolvedConstructorIsConstructorMethod()
    {
        $containerBuilder = new ContainerBuilder();
        $resolver = new ConstructorResolver($containerBuilder, new ResultingClassResolver($containerBuilder));

        // DateTime has a constructor
        $definition = new Definition('\DateTime');

        $expectedConstructor = new \ReflectionMethod('\DateTime', '__construct');
        $this->assertEquals($expectedConstructor, $resolver->resolve($definition));
    }

    /**
     * @test
     */
    public function ifConstructorIsNotPublicItFails()
    {
        $containerBuilder = new ContainerBuilder();
        $resolver = new ConstructorResolver($containerBuilder, new ResultingClassResolver($containerBuilder));

        // ClassWithNonPublicConstructor has a non-public constructor
        $definition = new Definition('Matthias\SymfonyServiceDefinitionValidator\Tests\Fixtures\ClassWithNonPublicConstructor');

        $this->setExpectedException('Matthias\SymfonyServiceDefinitionValidator\Exception\NonPublicConstructorException');
        $this->assertSame(null, $resolver->resolve($definition));
    }

    /**
     * @test
     */
    public function ifFactoryClassAndFactoryMethodAreDefinedResolvedConstructorIsFactoryMethod()
    {
        $containerBuilder = new ContainerBuilder();
        $resolver = new ConstructorResolver($containerBuilder, new ResultingClassResolver($containerBuilder));

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
        $containerBuilder = new ContainerBuilder();
        $resolver = new ConstructorResolver($containerBuilder, new ResultingClassResolver($containerBuilder));

        $definition = new Definition();
        $factoryClass = 'NonExistingClass';
        $definition->setFactoryClass($factoryClass);
        $factoryMethod = 'create';
        $definition->setFactoryMethod($factoryMethod);

        $this->setExpectedException('Matthias\SymfonyServiceDefinitionValidator\Exception\ClassNotFoundException');
        $resolver->resolve($definition);
    }

    /**
     * @test
     */
    public function ifFactoryMethodIsNotStaticItFails()
    {
        $containerBuilder = new ContainerBuilder();
        $resolver = new ConstructorResolver($containerBuilder, new ResultingClassResolver($containerBuilder));

        $definition = new Definition();
        $factoryClass = 'Matthias\SymfonyServiceDefinitionValidator\Tests\Fixtures\FactoryClass';
        $definition->setFactoryClass($factoryClass);
        $factoryMethod = 'createNonStatic';
        $definition->setFactoryMethod($factoryMethod);

        $this->setExpectedException('Matthias\SymfonyServiceDefinitionValidator\Exception\NonStaticFactoryMethodException');
        $resolver->resolve($definition);
    }
}
