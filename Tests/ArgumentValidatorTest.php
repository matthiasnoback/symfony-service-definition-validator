<?php

namespace Matthias\SymfonyServiceDefinitionValidator\Tests;

use Matthias\SymfonyServiceDefinitionValidator\ArgumentValidator;
use Matthias\SymfonyServiceDefinitionValidator\ResultingClassResolver;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class ArgumentValidatorTest extends \PHPUnit_Framework_TestCase
{

    public function testFailsWhenParameterHasTypeHintButNoReferenceWasProvidedAsArgument()
    {
        $class = 'Matthias\SymfonyServiceDefinitionValidator\Tests\Fixtures\ClassWithTypeHintedDateTimeConstructorArgument';
        $containerBuilder = new ContainerBuilder();

        $validator = new ArgumentValidator($containerBuilder, new ResultingClassResolver());

        $this->setExpectedException('Matthias\SymfonyServiceDefinitionValidator\Exception\TypeHintMismatchException', 'Reference');
        $validator->validate(new \ReflectionParameter(array($class, '__construct'), 'date'), new \stdClass());
    }

    public function testFailsWhenParameterHasTypeHintButArgumentIsReferenceToServiceOfWrongTypeWasProvided()
    {
        $class = 'Matthias\SymfonyServiceDefinitionValidator\Tests\Fixtures\ClassWithTypeHintedDateTimeConstructorArgument';
        $containerBuilder = new ContainerBuilder();
        $mismatchingClass = 'stdClass';
        $containerBuilder->setDefinition('referenced_service', new Definition($mismatchingClass));

        $parameter = new \ReflectionParameter(array($class, '__construct'), 'date');
        $argument = new Reference('referenced_service');

        $validator = new ArgumentValidator($containerBuilder, new ResultingClassResolver());

        $this->setExpectedException('Matthias\SymfonyServiceDefinitionValidator\Exception\TypeHintMismatchException', 'DateTime');

        $validator->validate($parameter, $argument);
    }
}
