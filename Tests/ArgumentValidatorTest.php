<?php

namespace Matthias\SymfonyServiceDefinitionValidator\Tests;

use Matthias\SymfonyServiceDefinitionValidator\ArgumentValidator;
use Matthias\SymfonyServiceDefinitionValidator\Exception\TypeHintMismatchException;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\ExpressionLanguage\Expression;

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

    public function testFailsWhenOptionalParameterHasArrayTypeHintAndResultOfExpressionIsNullButNullIsNotAllowed()
    {
        $class = 'Matthias\SymfonyServiceDefinitionValidator\Tests\Fixtures\ClassWithOptionalArrayConstructorArgument';

        $parameter = new \ReflectionParameter(array($class, '__construct'), 'options');
        $argument = null;

        $validator = new ArgumentValidator(new ContainerBuilder(), $this->createMockResultingClassResolver());

        try {
            $validator->validate($parameter, $argument);
        } catch (TypeHintMismatchException $exception) {
            $this->fail('null argument should be allowed');
        }
    }

    public function testFailsWhenResultOfExpressionIsNotAnObjectOfTheExpectedClass()
    {
        $this->skipTestIfExpressionsAreNotAvailable();

        $class = 'Matthias\SymfonyServiceDefinitionValidator\Tests\Fixtures\ClassWithTypeHintedConstructorArgument';

        $parameter = new \ReflectionParameter(array($class, '__construct'), 'expected');
        $argument = new Expression('service("service_wrong_class")');

        $containerBuilder = new ContainerBuilder();
        $containerBuilder->setDefinition('service_wrong_class', new Definition('stdClass'));

        $validator = new ArgumentValidator($containerBuilder, $this->createMockResultingClassResolver(), true);

        $this->setExpectedException('Matthias\SymfonyServiceDefinitionValidator\Exception\TypeHintMismatchException', 'ExpectedClass');

        $validator->validate($parameter, $argument);
    }

    public function testFailsWhenResultOfExpressionIsNotAnObject()
    {
        $this->skipTestIfExpressionsAreNotAvailable();

        $class = 'Matthias\SymfonyServiceDefinitionValidator\Tests\Fixtures\ClassWithTypeHintedConstructorArgument';

        $parameter = new \ReflectionParameter(array($class, '__construct'), 'expected');
        $argument = new Expression('"a string"');

        $validator = new ArgumentValidator(new ContainerBuilder(), $this->createMockResultingClassResolver(), true);

        $this->setExpectedException('Matthias\SymfonyServiceDefinitionValidator\Exception\TypeHintMismatchException', 'ExpectedClass');

        $validator->validate($parameter, $argument);
    }

    public function testFailsWhenResultOfExpressionIsNullButNullIsNotAllowed()
    {
        $this->skipTestIfExpressionsAreNotAvailable();

        $class = 'Matthias\SymfonyServiceDefinitionValidator\Tests\Fixtures\ClassWithTypeHintedOptionalConstructorArgument';

        $parameter = new \ReflectionParameter(array($class, '__construct'), 'expected');
        $argument = new Expression('null');

        $validator = new ArgumentValidator(new ContainerBuilder(), $this->createMockResultingClassResolver());

        try {
            $validator->validate($parameter, $argument);
        } catch (TypeHintMismatchException $exception) {
            $this->fail('null argument should be allowed');
        }
    }

    public function testFailsIfSyntaxOfExpressionIsInvalid()
    {
        $this->skipTestIfExpressionsAreNotAvailable();

        $class = 'Matthias\SymfonyServiceDefinitionValidator\Tests\Fixtures\ClassWithTypeHintedConstructorArgument';

        $parameter = new \ReflectionParameter(array($class, '__construct'), 'expected');
        $argument = new Expression('*invalid expression');

        $validator = new ArgumentValidator(new ContainerBuilder(), $this->createMockResultingClassResolver());

        $this->setExpectedException('Matthias\SymfonyServiceDefinitionValidator\Exception\InvalidExpressionSyntaxException');

        $validator->validate($parameter, $argument);
    }

    public function testFailsIfExpressionCouldNotBeEvaluated()
    {
        $this->skipTestIfExpressionsAreNotAvailable();

        $class = 'Matthias\SymfonyServiceDefinitionValidator\Tests\Fixtures\ClassWithTypeHintedConstructorArgument';

        $parameter = new \ReflectionParameter(array($class, '__construct'), 'expected');
        $argument = new Expression('service("invalid service")');

        $validator = new ArgumentValidator(new ContainerBuilder(), $this->createMockResultingClassResolver(), true);

        $this->setExpectedException('Matthias\SymfonyServiceDefinitionValidator\Exception\InvalidExpressionException');

        $validator->validate($parameter, $argument);
    }

    public function testContainerInterfaceReferenceArgumentDoesNotFail()
    {
        $class = 'Matthias\SymfonyServiceDefinitionValidator\Tests\Fixtures\ClassWithContainerInterfaceConstructorArgument';

        $parameter = new \ReflectionParameter(array($class, '__construct'), 'container');
        $argument = new Reference('service_container');

        $classResolver = $this->createMockResultingClassResolver();
        $classResolver
            ->expects($this->any())
            ->method('resolve')
            ->willReturn('Symfony\Component\DependencyInjection\ContainerBuilder');

        $validator = new ArgumentValidator(new ContainerBuilder(), $classResolver);

        $validator->validate($parameter, $argument);
    }

    public function testContainerBuilderReferenceArgumentDoesNotFail()
    {
        $class = 'Matthias\SymfonyServiceDefinitionValidator\Tests\Fixtures\ClassWithContainerBuilderConstructorArgument';

        $parameter = new \ReflectionParameter(array($class, '__construct'), 'container');
        $argument = new Reference('service_container');

        $classResolver = $this->createMockResultingClassResolver();
        $classResolver
            ->expects($this->any())
            ->method('resolve')
            ->willReturn('Symfony\Component\DependencyInjection\ContainerBuilder');

        $validator = new ArgumentValidator(new ContainerBuilder(), $classResolver);

        $validator->validate($parameter, $argument);
    }

    public function testPassesWhenArgumentIsClassAlias()
    {
        class_alias('Matthias\SymfonyServiceDefinitionValidator\Tests\Fixtures\ExpectedClass', 'AliasedExpectedClass');
        $class = 'Matthias\SymfonyServiceDefinitionValidator\Tests\Fixtures\ClassWithTypeHintedAliasConstructorArgument';
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
            ->will($this->returnValue('\AliasedExpectedClass'));
        $validator = new ArgumentValidator($this->containerBuilder, $resultingClassResolver);
        $validator->validate($parameter, $argument);
    }

    public function testFailsIfContainerReferenceArgumentIsInjectedForParameterWithIncompatibleTypeHint()
    {
        $class = 'Matthias\SymfonyServiceDefinitionValidator\Tests\Fixtures\ClassWithTypeHintedConstructorArgument';

        $parameter = new \ReflectionParameter(array($class, '__construct'), 'expected');
        $argument = new Reference('service_container');

        $classResolver = $this->createMockResultingClassResolver();
        $classResolver
            ->expects($this->any())
            ->method('resolve')
            ->willReturn('Symfony\Component\DependencyInjection\ContainerBuilder');
        $validator = new ArgumentValidator(new ContainerBuilder(), $classResolver);

        $this->setExpectedException('Matthias\SymfonyServiceDefinitionValidator\Exception\TypeHintMismatchException', 'ExpectedClass');

        $validator->validate($parameter, $argument);
    }

    private function createMockResultingClassResolver()
    {
        return $this->getMock('Matthias\SymfonyServiceDefinitionValidator\ResultingClassResolverInterface');
    }

    private function skipTestIfExpressionsAreNotAvailable()
    {
        if (!class_exists('Symfony\Component\DependencyInjection\ExpressionLanguage')) {
            $this->markTestSkipped(
                'Expressions are not supported by this version of the DependencyInjection component'
            );
        }
    }
}
