<?php

namespace Matthias\SymfonyServiceDefinitionValidator\Tests;

use Matthias\SymfonyServiceDefinitionValidator\ConstructorResolver;
use Matthias\SymfonyServiceDefinitionValidator\ResultingClassResolver;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class ConstructorResolverTest extends \PHPUnit_Framework_TestCase
{
    public function testIfConstructorDoesNotExistResolvedConstructorIsNull()
    {
        $containerBuilder = new ContainerBuilder();
        $resolver = new ConstructorResolver($containerBuilder, new ResultingClassResolver($containerBuilder));

        // stdClass has no constructor
        $definition = new Definition('stdClass');

        $this->assertNull($resolver->resolve($definition));
    }

    public function testIfConstructorExistsResolvedConstructorIsConstructorMethod()
    {
        $containerBuilder = new ContainerBuilder();
        $resolver = new ConstructorResolver($containerBuilder, new ResultingClassResolver($containerBuilder));

        // DateTime has a constructor
        $definition = new Definition('\DateTime');

        $expectedConstructor = new \ReflectionMethod('\DateTime', '__construct');
        $this->assertEquals($expectedConstructor, $resolver->resolve($definition));
    }

    public function testIfConstructorIsNotPublicItFails()
    {
        $containerBuilder = new ContainerBuilder();
        $resolver = new ConstructorResolver($containerBuilder, new ResultingClassResolver($containerBuilder));

        // ClassWithNonPublicConstructor has a non-public constructor
        $definition = new Definition('Matthias\SymfonyServiceDefinitionValidator\Tests\Fixtures\ClassWithNonPublicConstructor');

        $this->setExpectedException('Matthias\SymfonyServiceDefinitionValidator\Exception\NonPublicConstructorException');
        $this->assertNull($resolver->resolve($definition));
    }

    /**
     * @dataProvider getFactoryServiceAndFactoryMethodAreDefinedData
     */
    public function testIfFactoryServiceAndFactoryMethodAreDefinedResolvedConstructorIsFactoryMethod(
        $factoryClass,
        Definition $definition,
        \ReflectionMethod $expectedConstructor
    ) {
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

        $data = array();

        if (method_exists('Symfony\Component\DependencyInjection\Definition', 'setFactoryService')) {
            $definition = new Definition();
            $definition->setFactoryService($factoryService);
            $definition->setFactoryMethod($factoryMethod);

            $data[] = array($factoryClass, $definition, $expectedConstructor);
        }

        if (method_exists('Symfony\Component\DependencyInjection\Definition', 'setFactory')) {
            $definition = new Definition();
            $definition->setFactory(array(new Reference($factoryService), $factoryMethod));

            $data[] = array($factoryClass, $definition, $expectedConstructor);
        }

        return $data;
    }

    /**
     * @dataProvider getFactoryClassAndFactoryMethodAreDefinedData
     */
    public function testIfFactoryClassAndFactoryMethodAreDefinedResolvedConstructorIsFactoryMethod(
        Definition $definition,
        \ReflectionMethod $expectedConstructor
    ) {
        $containerBuilder = new ContainerBuilder();
        $resolver = new ConstructorResolver($containerBuilder, new ResultingClassResolver($containerBuilder));

        $this->assertEquals($expectedConstructor, $resolver->resolve($definition));
    }

    public function getFactoryClassAndFactoryMethodAreDefinedData()
    {
        $factoryClass = 'Matthias\SymfonyServiceDefinitionValidator\Tests\Fixtures\FactoryClass';
        $factoryMethod = 'create';
        $expectedConstructor = new \ReflectionMethod($factoryClass, $factoryMethod);

        $data = array();

        if (method_exists('Symfony\Component\DependencyInjection\Definition', 'setFactoryClass')) {
            $definition = new Definition();
            $definition->setFactoryClass($factoryClass);
            $definition->setFactoryMethod($factoryMethod);

            $data[] = array($definition, $expectedConstructor);
        }

        if (method_exists('Symfony\Component\DependencyInjection\Definition', 'setFactory')) {
            $definition = new Definition();
            $definition->setFactory(array($factoryClass, $factoryMethod));

            $data[] = array($definition, $expectedConstructor);
        }

        return $data;
    }

    /**
     * @dataProvider getFactoryClassDoesNotExistData
     */
    public function testIfFactoryClassDoesNotExistFails(Definition $definition)
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

        $data = array();

        if (method_exists('Symfony\Component\DependencyInjection\Definition', 'setFactoryClass')) {
            $definition = new Definition();
            $definition->setFactoryClass($factoryClass);
            $definition->setFactoryMethod($factoryMethod);

            $data[] = array($definition);
        }

        if (method_exists('Symfony\Component\DependencyInjection\Definition', 'setFactory')) {
            $definition = new Definition();
            $definition->setFactory(array($factoryClass, $factoryMethod));

            $data[] = array($definition);
        }

        return $data;
    }

    /**
     * @dataProvider getFactoryMethodIsNotStaticData
     */
    public function testIfFactoryMethodIsNotStaticItFails(Definition $definition)
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

        $data = array();

        if (method_exists('Symfony\Component\DependencyInjection\Definition', 'setFactoryClass')) {
            $definition = new Definition();
            $definition->setFactoryClass($factoryClass);
            $definition->setFactoryMethod($factoryMethod);

            $data[] = array($definition);
        }

        if (method_exists('Symfony\Component\DependencyInjection\Definition', 'setFactory')) {
            $definition = new Definition();
            $definition->setFactory(array($factoryClass, $factoryMethod));

            $data[] = array($definition);
        }

        return $data;
    }

    public function testIfFactoryIsStringIsFactoryCallback()
    {
        if (!method_exists('Symfony\Component\DependencyInjection\Definition', 'getFactory')) {
            $this->markTestSkipped('Support for callables as factories was introduced in Symfony 2.6');
        }

        $containerBuilder = new ContainerBuilder();
        $resolver = new ConstructorResolver($containerBuilder, new ResultingClassResolver($containerBuilder));

        $definition = new Definition();
        $definition->setFactory('factoryCallback');

        $this->assertEquals(new \ReflectionFunction('factoryCallback'), $resolver->resolve($definition));
    }

    public function testIfFactoryFunctionDoesNotExistFails()
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
