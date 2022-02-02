<?php

namespace Matthias\SymfonyServiceDefinitionValidator\Tests;

use Matthias\SymfonyServiceDefinitionValidator\DefinitionArgumentsValidator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Definition;

class DefinitionArgumentsValidatorTest extends TestCase
{
    /**
     * @test
     */
    public function ifDefinitionIsAbstractDefinitionSkipsValidation()
    {
        $definition = new Definition();
        $definition->setAbstract(true);

        $argumentValidator = $this->createMockArgumentsValidator();
        $argumentValidator
            ->expects($this->never())
            ->method('validate');

        $validator = new DefinitionArgumentsValidator($this->createMockConstructorResolver(), $argumentValidator);

        $validator->validate($definition);
    }

    /**
     * @test
     */
    public function ifDefinitionIsSyntheticSkipsValidation()
    {
        $definition = new Definition();
        $definition->setSynthetic(true);

        $argumentsValidator = $this->createMockArgumentsValidator();
        $argumentsValidator
            ->expects($this->never())
            ->method('validate');

        $validator = new DefinitionArgumentsValidator($this->createMockConstructorResolver(), $argumentsValidator);

        $validator->validate($definition);
    }

    /**
     * @test
     */
    public function ifNoConstructorCouldBeFoundSkipsValidation()
    {
        $class = 'stdClass';
        $definition = new Definition($class);

        $argumentsValidator = $this->createMockArgumentsValidator();
        $argumentsValidator
            ->expects($this->never())
            ->method('validate');

        $constructorResolver = $this->createMockConstructorResolver();
        $constructorResolver
            ->expects($this->once())
            ->method('resolve')
            ->with($definition)
            ->will($this->returnValue(null));

        $validator = new DefinitionArgumentsValidator($constructorResolver, $argumentsValidator);

        $validator->validate($definition);
    }

    /**
     * @test
     */
    public function ifConstructorIsFoundValidatesUsingArgumentsValidator()
    {
        $definition = new Definition();
        $arguments = array(0 => 'argument1', 1 => 'argument2');
        $definition->setArguments($arguments);

        $constructorMethod = new \ReflectionMethod('Matthias\SymfonyServiceDefinitionValidator\Tests\Fixtures\ClassWithConstructor', '__construct');
        $argumentsValidator = $this->createMockArgumentsValidator();
        $argumentsValidator
            ->expects($this->once())
            ->method('validate')
            ->with($this->equalTo($constructorMethod), $arguments);

        $constructorResolver = $this->createMockConstructorResolver();
        $constructorResolver
            ->expects($this->once())
            ->method('resolve')
            ->with($definition)
            ->will($this->returnValue($constructorMethod));

        $validator = new DefinitionArgumentsValidator($constructorResolver, $argumentsValidator);

        $validator->validate($definition);
    }

    /**
     * @test
     */
    public function itConvertsAnAssociativeArrayOfArgumentsToANumericallyIndexedOrderedArray()
    {
        $definition = new Definition();
        $arguments = array('named_argument1' => 'argument1', 'named_argument2' => 'argument2');
        $definition->setArguments($arguments);

        $numericallyIndexedArguments = array('argument1', 'argument2');

        $constructorMethod = new \ReflectionMethod('Matthias\SymfonyServiceDefinitionValidator\Tests\Fixtures\ClassWithConstructor', '__construct');
        $argumentsValidator = $this->createMockArgumentsValidator();
        $argumentsValidator
            ->expects($this->once())
            ->method('validate')
            ->with($this->equalTo($constructorMethod), $this->identicalTo($numericallyIndexedArguments));

        $constructorResolver = $this->createMockConstructorResolver();
        $constructorResolver
            ->expects($this->once())
            ->method('resolve')
            ->with($definition)
            ->will($this->returnValue($constructorMethod));

        $validator = new DefinitionArgumentsValidator($constructorResolver, $argumentsValidator);

        $validator->validate($definition);
    }

    private function createMockConstructorResolver()
    {
        return $this->createMock('Matthias\SymfonyServiceDefinitionValidator\ConstructorResolverInterface');
    }

    private function createMockArgumentsValidator()
    {
        return $this->createMock('Matthias\SymfonyServiceDefinitionValidator\ArgumentsValidatorInterface');
    }
}
