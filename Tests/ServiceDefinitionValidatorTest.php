<?php

namespace Matthias\SymfonyServiceDefinitionValidator\Tests;

use Matthias\SymfonyServiceDefinitionValidator\Exception\ClassNotFoundException;
use Matthias\SymfonyServiceDefinitionValidator\Exception\DefinitionHasNoClassException;
use Matthias\SymfonyServiceDefinitionValidator\ServiceDefinitionValidator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class ServiceDefinitionValidatorTest extends \PHPUnit_Framework_TestCase
{
    public function testRecognizesMissingClass()
    {
        $definition = new Definition();
        $containerBuilder = new ContainerBuilder();
        $validator = new ServiceDefinitionValidator(
            $containerBuilder,
            $this->createMockDefinitionArgumentsValidator(),
            $this->createMockMethodCallsValidator()
        );

        $this->setExpectedException('Matthias\SymfonyServiceDefinitionValidator\Exception\DefinitionHasNoClassException');
        $validator->validate($definition);
    }

    public function testResolvesClassWithParameterName()
    {
        $definition = new Definition('%class_name%');
        $containerBuilder = new ContainerBuilder();
        $containerBuilder->setParameter('class_name', '\stdClass');
        $validator = new ServiceDefinitionValidator(
            $containerBuilder,
            $this->createMockDefinitionArgumentsValidator(),
            $this->createMockMethodCallsValidator()
        );

        $validator->validate($definition);
    }

    public function testSyntheticDefinitionCanHaveNoClass()
    {
        $definition = new Definition();
        $definition->setSynthetic(true);
        $containerBuilder = new ContainerBuilder();
        $validator = new ServiceDefinitionValidator(
            $containerBuilder,
            $this->createMockDefinitionArgumentsValidator(),
            $this->createMockMethodCallsValidator()
        );

        try {
            $validator->validate($definition);
        } catch (DefinitionHasNoClassException $e) {
            $this->fail('Synthetic definitions should be allowed to have no class');
        }
    }

    public function testAbstractDefinitionCanHaveNoClass()
    {
        $definition = new Definition();
        $definition->setAbstract(true);
        $containerBuilder = new ContainerBuilder();
        $validator = new ServiceDefinitionValidator(
            $containerBuilder,
            $this->createMockDefinitionArgumentsValidator(),
            $this->createMockMethodCallsValidator()
        );

        try {
            $validator->validate($definition);
        } catch (DefinitionHasNoClassException $e) {
            $this->fail('Abstract definitions should be allowed to have no class');
        }
    }

    public function testDefinedClassCanBeInterface()
    {
        $validator = new ServiceDefinitionValidator(
            new ContainerBuilder(),
            $this->createMockDefinitionArgumentsValidator(),
            $this->createMockMethodCallsValidator()
        );

        try {
            // The choice for Serializable is arbitrary, any PHP interface would do
            $validator->validate(new Definition('Serializable'));
        } catch (ClassNotFoundException $e) {
            $this->fail('Definition should be allowed to have an interface as class');
        }
    }

    public function testRecognizesNonExistingClass()
    {
        $definition = new Definition($this->getNonExistingClassName());
        $containerBuilder = new ContainerBuilder();
        $validator = new ServiceDefinitionValidator(
            $containerBuilder,
            $this->createMockDefinitionArgumentsValidator(),
            $this->createMockMethodCallsValidator()
        );

        $this->setExpectedException('Matthias\SymfonyServiceDefinitionValidator\Exception\ClassNotFoundException');
        $validator->validate($definition);
    }

    public function testRecognizesNonExistingFactoryClass()
    {
        $definition = new Definition('stdClass');
        $definition->setFactoryClass($this->getNonExistingClassName());
        $definition->setFactoryMethod('create');
        $containerBuilder = new ContainerBuilder();
        $validator = new ServiceDefinitionValidator(
            $containerBuilder,
            $this->createMockDefinitionArgumentsValidator(),
            $this->createMockMethodCallsValidator()
        );

        $this->setExpectedException('Matthias\SymfonyServiceDefinitionValidator\Exception\ClassNotFoundException');
        $validator->validate($definition);
    }

    public function testRecognizesNonExistingFactoryMethod()
    {
        $definition = new Definition('stdClass');
        $definition->setFactoryClass('Matthias\SymfonyServiceDefinitionValidator\Tests\Fixtures\FactoryClass');
        $definition->setFactoryMethod('nonExistingFactoryMethod');
        $containerBuilder = new ContainerBuilder();
        $validator = new ServiceDefinitionValidator(
            $containerBuilder,
            $this->createMockDefinitionArgumentsValidator(),
            $this->createMockMethodCallsValidator()
        );

        $this->setExpectedException('Matthias\SymfonyServiceDefinitionValidator\Exception\MethodNotFoundException');
        $validator->validate($definition);
    }

    /**
     * @test
     */
    public function ifFactoryServiceIsSpecifiedWithoutFactoryMethodFails()
    {
        $definition = new Definition('stdClass');
        $definition->setFactoryService('factory_service');

        $containerBuilder = new ContainerBuilder();
        $validator = new ServiceDefinitionValidator(
            $containerBuilder,
            $this->createMockDefinitionArgumentsValidator(),
            $this->createMockMethodCallsValidator()
        );

        $this->setExpectedException('Matthias\SymfonyServiceDefinitionValidator\Exception\MissingFactoryMethodException');
        $validator->validate($definition);
    }

    /**
     * @test
     */
    public function ifFactoryServiceDoesNotExistFails()
    {
        $definition = new Definition('stdClass');
        $definition->setFactoryService('factory_service');
        $definition->setFactoryMethod('factoryMethod');

        $containerBuilder = new ContainerBuilder();

        $validator = new ServiceDefinitionValidator(
            $containerBuilder,
            $this->createMockDefinitionArgumentsValidator(),
            $this->createMockMethodCallsValidator()
        );

        $this->setExpectedException('Matthias\SymfonyServiceDefinitionValidator\Exception\ServiceNotFoundException');
        $validator->validate($definition);
    }

    /**
     * @test
     */
    public function ifFactoryMethodDoesNotExistOnFactoryServiceFails()
    {
        $containerBuilder = new ContainerBuilder();
        $factoryDefinition = new Definition('stdClass');
        $containerBuilder->setDefinition('factory_service', $factoryDefinition);

        $definition = new Definition('stdClass');
        $definition->setFactoryService('factory_service');
        $definition->setFactoryMethod('nonExistingFactoryMethod');

        $validator = new ServiceDefinitionValidator(
            $containerBuilder,
            $this->createMockDefinitionArgumentsValidator(),
            $this->createMockMethodCallsValidator()
        );

        $this->setExpectedException('Matthias\SymfonyServiceDefinitionValidator\Exception\MethodNotFoundException');
        $validator->validate($definition);
    }

    private function getNonExistingClassName()
    {
        return md5(rand(1, 999));
    }

    private function createMockDefinitionArgumentsValidator()
    {
        return $this->getMock('Matthias\SymfonyServiceDefinitionValidator\DefinitionArgumentsValidatorInterface');
    }

    private function createMockMethodCallsValidator()
    {
        return $this->getMock('Matthias\SymfonyServiceDefinitionValidator\MethodCallsValidatorInterface');
    }
}
