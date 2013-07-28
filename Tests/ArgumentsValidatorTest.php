<?php

namespace Matthias\SymfonyServiceDefinitionValidator\Tests;

use Matthias\SymfonyServiceDefinitionValidator\ArgumentsValidator;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ArgumentsValidatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function ifRequiredArgumentIsMissingFails()
    {
        $validator = new ArgumentsValidator($this->getMockArgumentValidator());
        $class = 'Matthias\SymfonyServiceDefinitionValidator\Tests\Fixtures\ClassWithRequiredConstructorArguments';
        $method = new \ReflectionMethod($class, '__construct');

        $this->setExpectedException('Matthias\SymfonyServiceDefinitionValidator\Exception\MissingRequiredArgumentException');

        $validator->validate($method, array('argument1'));
    }

    private function getMockArgumentValidator()
    {
        return $this->getMock('Matthias\SymfonyServiceDefinitionValidator\ArgumentValidatorInterface');
    }
}
