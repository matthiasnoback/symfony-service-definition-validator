<?php

namespace Matthias\SymfonyServiceDefinitionValidator\Tests;

use Matthias\SymfonyServiceDefinitionValidator\ArgumentValidator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class ArgumentValidatorTest extends \PHPUnit_Framework_TestCase
{
    public function testFailsWhenParameterHasTypeHintButNoReferenceWasProvidedAsArgument()
    {
        $class = 'Matthias\SymfonyServiceDefinitionValidator\Tests\Fixtures\ClassWithTypeHintedDateTimeConstructorArgument';
        $containerBuilder = new ContainerBuilder();

        $validator = new ArgumentValidator($containerBuilder, $this->createMockResultingClassResolver());

        $this->setExpectedException('Matthias\SymfonyServiceDefinitionValidator\Exception\TypeHintMismatchException', 'Reference');
        $validator->validate(new \ReflectionParameter(array($class, '__construct'), 'date'), new \stdClass());
    }

    public function testFailsWhenParameterHasTypeHintButArgumentIsReferenceToServiceOfWrongTypeWasProvided()
    {
        $class = 'Matthias\SymfonyServiceDefinitionValidator\Tests\Fixtures\ClassWithTypeHintedDateTimeConstructorArgument';
        $containerBuilder = new ContainerBuilder();
        $definition = new Definition();
        $containerBuilder->setDefinition('referenced_service', $definition);

        $parameter = new \ReflectionParameter(array($class, '__construct'), 'date');
        $argument = new Reference('referenced_service');

        $resultingClassResolver = $this->createMockResultingClassResolver();
        $resultingClassResolver
            ->expects($this->once())
            ->method('resolve')
            ->with($definition)
            ->will($this->returnValue('stdClass'));
        $validator = new ArgumentValidator($containerBuilder, $resultingClassResolver);

        $this->setExpectedException('Matthias\SymfonyServiceDefinitionValidator\Exception\TypeHintMismatchException', 'DateTime');

        $validator->validate($parameter, $argument);
    }

    public function testFailsWhenParameterHasArrayTypeHintButArgumentIsNotArray()
    {
        $class = 'Matthias\SymfonyServiceDefinitionValidator\Tests\Fixtures\ClassWithArrayConstructorArgument';

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
