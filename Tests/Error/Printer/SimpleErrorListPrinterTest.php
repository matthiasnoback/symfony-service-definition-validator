<?php

namespace Matthias\SymfonyServiceDefinitionValidator\Tests\Error\Printer;

use Matthias\SymfonyServiceDefinitionValidator\Error\Printer\SimpleErrorListPrinter;
use Matthias\SymfonyServiceDefinitionValidator\Error\ValidationErrorList;

class SimpleErrorListPrinterTest extends \PHPUnit_Framework_TestCase
{
    public function testPrintsErrorMessageInAList()
    {
        $serviceId1 = 'service_1';
        $message1 = 'Message 1';
        $exception1 = $this->createException($message1);
        $error1 = $this->createMockError($serviceId1, $exception1);

        $serviceId2 = 'service_2';
        $message2 = 'Message 2';
        $exception2 = $this->createException($message2);
        $error2 = $this->createMockError($serviceId2, $exception2);

        $list = new ValidationErrorList();
        $list->add($error1);
        $list->add($error2);

        $expectedMessage = <<<EOT
Service definition validation errors (2):
- $serviceId1: $message1
- $serviceId2: $message2
EOT;
        $printer = new SimpleErrorListPrinter();
        $this->assertSame($expectedMessage, $printer->printErrorList($list));
    }

    private function createMockError($serviceId, \Exception $exception)
    {
        $error = $this->getMock('Matthias\SymfonyServiceDefinitionValidator\Error\ValidationErrorInterface');

        $error
            ->expects($this->any())
            ->method('getServiceId')
            ->will($this->returnValue($serviceId));

        $error
            ->expects($this->any())
            ->method('getException')
            ->will($this->returnValue($exception));

        return $error;
    }

    private function createException($message)
    {
        return new \Exception($message);
    }
}
