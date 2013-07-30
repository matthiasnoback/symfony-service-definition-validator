<?php

namespace Matthias\SymfonyServiceDefinitionValidator\Tests;

use Matthias\SymfonyServiceDefinitionValidator\BatchServiceDefinitionValidator;
use Matthias\SymfonyServiceDefinitionValidator\Tests\Fixtures\InvalidServiceDefinitionException;

class BatchServiceDefinitionValidatorTest extends \PHPUnit_Framework_TestCase
{
    public function testCreatesErrorListAndTransformsValidationExceptionIntoErrors()
    {
        $goodDefinition = $this->createMockDefinition();
        $badDefinition = $this->createMockDefinition();

        $definitions = array(
            'good_service' => $goodDefinition,
            'bad_service' => $badDefinition
        );

        $error = $this->createMockError();

        $errorList = $this->createMockErrorList();
        $errorList
            ->expects($this->once())
            ->method('add')
            ->with($error);

        $errorFactory = $this->createMockErrorFactory();
        $errorFactory
            ->expects($this->once())
            ->method('createValidationErrorList')
            ->will($this->returnValue($errorList));

        $exception = $this->createException();



        $errorFactory
            ->expects($this->once())
            ->method('createValidationError')
            ->with('bad_service', $badDefinition, $exception)
            ->will($this->returnValue($error));

        $validator = $this->createMockValidator();
        $validator
            ->expects($this->at(0))
            ->method('validate')
            ->with($goodDefinition);
        $validator
            ->expects($this->at(1))
            ->method('validate')
            ->with($badDefinition)
            ->will($this->throwException($exception));

        $batchValidator = new BatchServiceDefinitionValidator($validator, $errorFactory);
        $result = $batchValidator->validate($definitions);

        $this->assertSame($errorList, $result);
    }

    private function createMockErrorFactory()
    {
        return $this->getMock('Matthias\SymfonyServiceDefinitionValidator\Error\ValidationErrorFactoryInterface');
    }

    private function createMockErrorList()
    {
        return $this->getMock('Matthias\SymfonyServiceDefinitionValidator\Tests\Error\ValidationErrorListInterface');
    }

    private function createMockDefinition()
    {
        return $this
            ->getMockBuilder('Symfony\Component\DependencyInjection\Definition')
            ->disableOriginalConstructor()
            ->getMock();
    }

    private function createMockValidator()
    {
        return $this->getMock('Matthias\SymfonyServiceDefinitionValidator\ServiceDefinitionValidatorInterface');
    }

    private function createException()
    {
        return new InvalidServiceDefinitionException();
    }

    private function createMockError()
    {
        return $this->getMock('Matthias\SymfonyServiceDefinitionValidator\Error\ValidationErrorInterface');
    }
}
