<?php

namespace Matthias\SymfonyServiceDefinitionValidator\Tests;

use Matthias\SymfonyServiceDefinitionValidator\ServiceDefinitionValidator;
use Matthias\SymfonyServiceDefinitionValidator\Exception\DefinitionHasNoClassException;
use Matthias\SymfonyServiceDefinitionValidator\Exception\MissingRequiredArgumentException;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class ServiceDefinitionValidatorTest extends \PHPUnit_Framework_TestCase
{
    public function testRecognizesMissingClass()
    {
        $definition = new Definition();
        $containerBuilder = new ContainerBuilder();
        $validator = new ServiceDefinitionValidator($containerBuilder);

        $this->setExpectedException('Matthias\SymfonyServiceDefinitionValidator\Exception\DefinitionHasNoClassException');
        $validator->validate($definition);
    }

    public function testResolvesClassWithParameterName()
    {
        $definition = new Definition('%class_name%');
        $containerBuilder = new ContainerBuilder();
        $containerBuilder->setParameter('class_name', '\stdClass');
        $validator = new ServiceDefinitionValidator($containerBuilder);

        $validator->validate($definition);
    }

    public function testSyntheticDefinitionCanHaveNoClass()
    {
        $definition = new Definition();
        $definition->setSynthetic(true);
        $containerBuilder = new ContainerBuilder();
        $validator = new ServiceDefinitionValidator($containerBuilder);

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
        $validator = new ServiceDefinitionValidator($containerBuilder);

        try {
            $validator->validate($definition);
        } catch (DefinitionHasNoClassException $e) {
            $this->fail('Abstract definitions should be allowed to have no class');
        }
    }

    public function testRecognizesNonExistingClass()
    {
        $definition = new Definition($this->getNonExistingClassName());
        $containerBuilder = new ContainerBuilder();
        $validator = new ServiceDefinitionValidator($containerBuilder);

        $this->setExpectedException('Matthias\SymfonyServiceDefinitionValidator\Exception\ClassNotFoundException');
        $validator->validate($definition);
    }

    public function testRecognizesNonExistingFactoryClass()
    {
        $definition = new Definition('stdClass');
        $definition->setFactoryClass($this->getNonExistingClassName());
        $definition->setFactoryMethod('create');
        $containerBuilder = new ContainerBuilder();
        $validator = new ServiceDefinitionValidator($containerBuilder);

        $this->setExpectedException('Matthias\SymfonyServiceDefinitionValidator\Exception\ClassNotFoundException');
        $validator->validate($definition);
    }

    public function testRecognizesNonExistingFactoryMethod()
    {
        $definition = new Definition('stdClass');
        $definition->setFactoryClass('Matthias\SymfonyServiceDefinitionValidator\Tests\Fixtures\FactoryClass');
        $definition->setFactoryMethod('nonExistingFactoryMethod');
        $containerBuilder = new ContainerBuilder();
        $validator = new ServiceDefinitionValidator($containerBuilder);

        $this->setExpectedException('Matthias\SymfonyServiceDefinitionValidator\Exception\MethodNotFoundException');
        $validator->validate($definition);
    }

    public function testSkipsArgumentValidationForAbstractDefinition()
    {
        $definition = new Definition('Matthias\SymfonyServiceDefinitionValidator\Tests\Fixtures\ClassWithRequiredConstructorArguments');
        $definition->setAbstract(true);

        $containerBuilder = new ContainerBuilder();
        $validator = new ServiceDefinitionValidator($containerBuilder);

        try {
            $validator->validateArguments($definition);
        } catch (MissingRequiredArgumentException $exception) {
            $this->fail('Should not have failed');
        }
    }

    public function testValidatesArgumentsForFactoryClassAndMethod()
    {
        $definition = new Definition();
        $definition->setFactoryClass('Matthias\SymfonyServiceDefinitionValidator\Tests\Fixtures\FactoryClass');
        $definition->setFactoryMethod('createWithRequiredArgument');

        $containerBuilder = new ContainerBuilder();
        $validator = new ServiceDefinitionValidator($containerBuilder);

        $this->setExpectedException('Matthias\SymfonyServiceDefinitionValidator\Exception\MissingRequiredArgumentException');
        $validator->validateArguments($definition);
    }

    private function getNonExistingClassName()
    {
        return md5(rand(1, 999));
    }
}
