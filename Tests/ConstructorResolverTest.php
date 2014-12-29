<?php

namespace Matthias\SymfonyServiceDefinitionValidator\Tests;

use Matthias\SymfonyServiceDefinitionValidator\ConstructorResolver;
use Matthias\SymfonyServiceDefinitionValidator\ResultingClassResolver;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

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
     * @dataProvider getFactoryServiceAndFactoryMethodAreDefinedData
     */
    public function ifFactoryServiceAndFactoryMethodAreDefinedResolvedConstructorIsFactoryMethod($factoryClass, Definition $definition, \ReflectionMethod $expectedConstructor)
    {
        $containerBuilder = new ContainerBuilder();
        $containerBuilder->register('factory', $factoryClass);
        $resolver = new ConstructorResolver($containerBuilder, new ResultingClassResolver($containerBuilder));

        $this->assertEquals($expectedConstructor, $resolver->resolve($definition));
    }

    public function getFactoryServiceAndFactoryMethodAreDefinedData()
    {
        $factoryService = 'factory';
        $factoryClass = 'Matthias\SymfonyServiceDefinitionValidator\Tests\Fixtures\FactoryClass';
        $factoryMethod = 'create';
        $expectedConstructor = new \ReflectionMethod($factoryClass, $factoryMethod);

        $definition = new Definition();
        $definition->setFactoryService($factoryService);
        $definition->setFactoryMethod($factoryMethod);

        $data = array(array($factoryClass, $definition, $expectedConstructor));

        if (method_exists($definition, 'setFactory')) {
            $definition = new Definition();
            $definition->setFactory(array(new Reference($factoryService), $factoryMethod));
            $data[] = array($factoryClass, $definition, $expectedConstructor);
        }

        return $data;
    }

    /**
     * @test
     * @dataProvider getFactoryClassAndFactoryMethodAreDefinedData
     */
    public function ifFactoryClassAndFactoryMethodAreDefinedResolvedConstructorIsFactoryMethod(Definition $definition, \ReflectionMethod $expectedConstructor)
    {
        $containerBuilder = new ContainerBuilder();
        $resolver = new ConstructorResolver($containerBuilder, new ResultingClassResolver($containerBuilder));

        $this->assertEquals($expectedConstructor, $resolver->resolve($definition));
    }

    public function getFactoryClassAndFactoryMethodAreDefinedData()
    {
        $factoryClass = 'Matthias\SymfonyServiceDefinitionValidator\Tests\Fixtures\FactoryClass';
        $factoryMethod = 'create';
        $expectedConstructor = new \ReflectionMethod($factoryClass, $factoryMethod);

        $definition = new Definition();
        $definition->setFactoryClass($factoryClass);
        $definition->setFactoryMethod($factoryMethod);

        $data = array(array($definition, $expectedConstructor));

        if (method_exists($definition, 'setFactory')) {
            $definition = new Definition();
            $definition->setFactory(array($factoryClass, $factoryMethod));
            $data[] = array($definition, $expectedConstructor);
        }

        return $data;
    }

    /**
     * @test
     * @dataProvider getFactoryClassDoesNotExistData
     */
    public function ifFactoryClassDoesNotExistFails(Definition $definition)
    {
        $containerBuilder = new ContainerBuilder();
        $resolver = new ConstructorResolver($containerBuilder, new ResultingClassResolver($containerBuilder));

        $this->setExpectedException('Matthias\SymfonyServiceDefinitionValidator\Exception\ClassNotFoundException');
        $resolver->resolve($definition);
    }

    public function getFactoryClassDoesNotExistData()
    {
        $factoryClass = 'NonExistingClass';
        $factoryMethod = 'create';

        $definition = new Definition();
        $definition->setFactoryClass($factoryClass);
        $definition->setFactoryMethod($factoryMethod);

        $data = array(array($definition));

        if (method_exists($definition, 'setFactory')) {
            $definition = new Definition();
            $definition->setFactory(array($factoryClass, $factoryMethod));
            $data[] = array($definition);
        }

        return $data;
    }

    /**
     * @test
     * @dataProvider getFactoryMethodIsNotStaticData
     */
    public function ifFactoryMethodIsNotStaticItFails(Definition $definition)
    {
        $containerBuilder = new ContainerBuilder();
        $resolver = new ConstructorResolver($containerBuilder, new ResultingClassResolver($containerBuilder));

        $this->setExpectedException('Matthias\SymfonyServiceDefinitionValidator\Exception\NonStaticFactoryMethodException');
        $resolver->resolve($definition);
    }

    public function getFactoryMethodIsNotStaticData()
    {
        $factoryClass = 'Matthias\SymfonyServiceDefinitionValidator\Tests\Fixtures\FactoryClass';
        $factoryMethod = 'createNonStatic';

        $definition = new Definition();
        $definition->setFactoryClass($factoryClass);
        $definition->setFactoryMethod($factoryMethod);

        $data = array(array($definition));

        if (method_exists($definition, 'setFactory')) {
            $definition = new Definition();
            $definition->setFactory(array($factoryClass, $factoryMethod));
            $data[] = array($definition);
        }

        return $data;
    }

    /**
     * @test
     */
    public function ifFactoryIsStringIsFactoryCallback()
    {
        if (!method_exists('Symfony\Component\DependencyInjection\Definition', 'getFactory')) {
            $this->markTestSkipped('Support for callables as factories was introduced in Symfony 2.6');
        }

        $containerBuilder = new ContainerBuilder();
        $resolver = new ConstructorResolver($containerBuilder, new ResultingClassResolver($containerBuilder));

        $definition = new Definition();
        $definition->setFactory('factoryCallback');

        $this->assertSame('factoryCallback', $resolver->resolve($definition));
    }

    /**
     * @test
     */
    public function ifFactoryFunctionDoesNotExistFails()
    {
        if (!method_exists('Symfony\Component\DependencyInjection\Definition', 'getFactory')) {
            $this->markTestSkipped('Support for callables as factories was introduced in Symfony 2.6');
        }

        $containerBuilder = new ContainerBuilder();
        $resolver = new ConstructorResolver($containerBuilder, new ResultingClassResolver($containerBuilder));

        $definition = new Definition();
        $definition->setFactory('NotExistingFactoryCallback');

        $this->setExpectedException('Matthias\SymfonyServiceDefinitionValidator\Exception\FunctionNotFoundException');
        $resolver->resolve($definition);
    }
}
