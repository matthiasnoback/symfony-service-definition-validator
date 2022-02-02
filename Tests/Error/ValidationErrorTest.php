<?php

namespace Matthias\SymfonyServiceDefinitionValidator\Tests\Error;

use Matthias\SymfonyServiceDefinitionValidator\Error\ValidationError;
use PHPUnit\Framework\TestCase;

class ValidationErrorTest extends TestCase
{
    public function testConstructor()
    {
        $serviceId = 'service_id';
        $definition = $this->createMockDefinition();
        $exception = $this->createMockException();

        $error = new ValidationError($serviceId, $definition, $exception);

        $this->assertSame($serviceId, $error->getServiceId());
        $this->assertSame($definition, $error->getDefinition());
        $this->assertSame($exception, $error->getException());
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
        return $this->createMock('\Exception');
    }
}
