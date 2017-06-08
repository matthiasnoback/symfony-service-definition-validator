<?php

namespace Matthias\SymfonyServiceDefinitionValidator\Tests;

use Matthias\SymfonyServiceDefinitionValidator\ArgumentsValidator;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ArgumentsValidatorTest extends \PHPUnit_Framework_TestCase
{
    public function testIfRequiredArgumentIsMissingFails()
    {
        $validator = new ArgumentsValidator($this->createMockArgumentValidator());
        $class = 'Matthias\SymfonyServiceDefinitionValidator\Tests\Fixtures\ClassWithRequiredConstructorArguments';
        $method = new \ReflectionMethod($class, '__construct');

        $this->setExpectedException('Matthias\SymfonyServiceDefinitionValidator\Exception\MissingRequiredArgumentException');

        $validator->validate($method, array('argument1'));
    }

    private function createMockArgumentValidator()
    {
        return $this->getMockBuilder('Matthias\SymfonyServiceDefinitionValidator\ArgumentValidatorInterface')->getMock();
    }
}
