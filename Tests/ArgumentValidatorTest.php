<?php

namespace Matthias\SymfonyServiceDefinitionValidator\Tests;

use Matthias\SymfonyServiceDefinitionValidator\ArgumentValidator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class ArgumentValidatorTest extends \PHPUnit_Framework_TestCase
{
    private $containerBuilder;

    protected function setUp()
    {
        $this->containerBuilder = new ContainerBuilder();
    }

    public function testFailsWhenParameterHasTypeHintButNoReferenceOrDefinitionWasProvidedAsArgument()
    {
        $class = 'Matthias\SymfonyServiceDefinitionValidator\Tests\Fixtures\ClassWithTypeHintedConstructorArgument';

        $validator = new ArgumentValidator($this->containerBuilder, $this->createMockResultingClassResolver());

        $this->setExpectedException('Matthias\SymfonyServiceDefinitionValidator\Exception\TypeHintMismatchException', 'reference');
        $validator->validate(new \ReflectionParameter(array($class, '__construct'), 'expected'), new \stdClass());
    }

    public function testFailsWhenParameterHasTypeHintForObjectButArgumentIsDefinitionForServiceOfWrongType()
    {
        $class = 'Matthias\SymfonyServiceDefinitionValidator\Tests\Fixtures\ClassWithTypeHintedConstructorArgument';
        $this->containerBuilder = new ContainerBuilder();

        $inlineDefinition = new Definition();

        $resultingClassResolver = $this->createMockResultingClassResolver();
        $resultingClassResolver
            ->expects($this->once())
            ->method('resolve')
            ->with($inlineDefinition)
            ->will($this->returnValue('Matthias\SymfonyServiceDefinitionValidator\Tests\Fixtures\WrongClass'));
        $validator = new ArgumentValidator($this->containerBuilder, $resultingClassResolver);

        $this->setExpectedException('Matthias\SymfonyServiceDefinitionValidator\Exception\TypeHintMismatchException', 'ExpectedClass');
        $validator->validate(new \ReflectionParameter(array($class, '__construct'), 'expected'), $inlineDefinition);
    }

    public function testFailsWhenParameterHasTypeHintForObjectButArgumentIsReferenceToServiceOfWrongType()
    {
        $class = 'Matthias\SymfonyServiceDefinitionValidator\Tests\Fixtures\ClassWithTypeHintedConstructorArgument';
        $this->containerBuilder = new ContainerBuilder();
        $definition = new Definition();
        $this->containerBuilder->setDefinition('referenced_service', $definition);

        $parameter = new \ReflectionParameter(array($class, '__construct'), 'expected');
        $argument = new Reference('referenced_service');

        $resultingClassResolver = $this->createMockResultingClassResolver();
        $resultingClassResolver
            ->expects($this->once())
            ->method('resolve')
            ->with($definition)
            ->will($this->returnValue('stdClass'));
        $validator = new ArgumentValidator($this->containerBuilder, $resultingClassResolver);

        $this->setExpectedException('Matthias\SymfonyServiceDefinitionValidator\Exception\TypeHintMismatchException', 'ExpectedClass');

        $validator->validate($parameter, $argument);
    }

    public function testFailsWhenParameterHasArrayTypeHintButArgumentIsNotArray()
    {
        $class = 'Matthias\SymfonyServiceDefinitionValidator\Tests\Fixtures\ClassWithRequiredArrayConstructorArgument';

        $parameter = new \ReflectionParameter(array($class, '__construct'), 'options');
        $argument = 'string';

        $validator = new ArgumentValidator(new ContainerBuilder(), $this->createMockResultingClassResolver());

        $this->setExpectedException('Matthias\SymfonyServiceDefinitionValidator\Exception\TypeHintMismatchException', 'array');

        $validator->validate($parameter, $argument);
    }

    private function createMockResultingClassResolver()
    {
        return $this->getMock('Matthias\SymfonyServiceDefinitionValidator\ResultingClassResolverInterface');
    }
}
