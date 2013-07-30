<?php

namespace Matthias\SymfonyServiceDefinitionValidator\Tests\Error;

use Matthias\SymfonyServiceDefinitionValidator\Error\ValidationErrorFactory;

class ValidationErrorFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testCreatesValidationError()
    {
        $serviceId = 'service_id';
        $definition = $this->createMockDefinition();
        $exception = $this->createMockException();

        $factory = new ValidationErrorFactory();

        $error = $factory->createValidationError($serviceId, $definition, $exception);

        $this->assertInstanceOf('Matthias\SymfonyServiceDefinitionValidator\Error\ValidationErrorInterface', $error);
        $this->assertSame($serviceId, $error->getServiceId());
        $this->assertSame($definition, $error->getDefinition());
        $this->assertSame($exception, $error->getException());
    }

    public function testCreatesEmptyValidationErrorList()
    {
        $factory = new ValidationErrorFactory();

        $list = $factory->createValidationErrorList();

        $this->assertInstanceOf('Matthias\SymfonyServiceDefinitionValidator\Error\ValidationErrorListInterface', $list);
        $this->assertCount(0, $list);
    }

    private function createMockDefinition()
    {
        return $this
            ->getMockBuilder('Symfony\Component\DependencyInjection\Definition')
            ->disableOriginalConstructor()
            ->getMock();
    }

    private function createMockException()
    {
        return $this
            ->getMockBuilder('\Exception')
            ->getMock();
    }
}
