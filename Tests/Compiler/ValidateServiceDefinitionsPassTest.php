<?php


namespace Matthias\SymfonyServiceDefinitionValidator\Tests;


use Matthias\SymfonyServiceDefinitionValidator\Compiler\ValidateServiceDefinitionsPass;
use Symfony\Component\DependencyInjection\Definition;

class ValidateServiceDefinitionsPassTest extends \PHPUnit_Framework_TestCase
{
    public function testValidatesAllServiceDefinitions()
    {
        $containerBuilder = $this->createMockContainerBuilder();

        $definition1 = new Definition();
        $definition2 = new Definition();

        $containerBuilder
            ->expects($this->at(0))
            ->method('getDefinitions')
            ->will($this->returnValue(array(
                'service1' => $definition1,
                'service2'=> $definition2
            )));

        $validator = $this->createMockValidator();

        $validator
            ->expects($this->at(0))
            ->method('validate')
            ->with($definition1);
        $validator
            ->expects($this->at(1))
            ->method('validate')
            ->with($definition2);

        $compilerPass = new ValidateServiceDefinitionsPass($validator);
        $compilerPass->process($containerBuilder);
    }

    private function createMockContainerBuilder()
    {
        return $this
            ->getMockBuilder('Symfony\Component\DependencyInjection\ContainerBuilder')
            ->disableOriginalConstructor()
            ->getMock();
    }

    private function createMockValidator()
    {
        return $this->getMock('Matthias\SymfonyServiceDefinitionValidator\ServiceDefinitionValidatorInterface');
    }
}
