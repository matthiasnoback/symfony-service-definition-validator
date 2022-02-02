<?php

namespace Matthias\SymfonyServiceDefinitionValidator\Tests;

use Matthias\SymfonyServiceDefinitionValidator\ArgumentsValidator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ArgumentsValidatorTest extends TestCase
{
    /**
     * @test
     */
    public function ifRequiredArgumentIsMissingFails()
    {
        $validator = new ArgumentsValidator($this->createMockArgumentValidator());
        $class = 'Matthias\SymfonyServiceDefinitionValidator\Tests\Fixtures\ClassWithRequiredConstructorArguments';
        $method = new \ReflectionMethod($class, '__construct');

        $this->expectException('Matthias\SymfonyServiceDefinitionValidator\Exception\MissingRequiredArgumentException');

        $validator->validate($method, array('argument1'));
    }

    private function createMockArgumentValidator()
    {
        return $this->createMock('Matthias\SymfonyServiceDefinitionValidator\ArgumentValidatorInterface');
    }
}
