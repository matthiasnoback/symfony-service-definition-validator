<?php

namespace Matthias\SymfonyServiceDefinitionValidator\Functional;

use Matthias\SymfonyServiceDefinitionValidator\ArgumentsValidator;
use Matthias\SymfonyServiceDefinitionValidator\ArgumentValidator;
use Matthias\SymfonyServiceDefinitionValidator\Compiler\ValidateServiceDefinitionsPass;
use Matthias\SymfonyServiceDefinitionValidator\ConstructorResolver;
use Matthias\SymfonyServiceDefinitionValidator\DefinitionArgumentsValidator;
use Matthias\SymfonyServiceDefinitionValidator\MethodCallsValidator;
use Matthias\SymfonyServiceDefinitionValidator\ResultingClassResolver;
use Matthias\SymfonyServiceDefinitionValidator\ServiceDefinitionValidator;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

class FunctionalTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ContainerBuilder
     */
    private $container;

    protected function setUp()
    {
        $this->container = new ContainerBuilder();

        $resultingClassResolver = new ResultingClassResolver();
        $constructorResolver = new ConstructorResolver($this->container, $resultingClassResolver);
        $argumentValidator = new ArgumentValidator($this->container, $resultingClassResolver);
        $argumentsValidator = new ArgumentsValidator($argumentValidator);
        $definitionArgumentsValidator = new DefinitionArgumentsValidator($constructorResolver, $argumentsValidator);
        $methodCallsValidator = new MethodCallsValidator($resultingClassResolver, $argumentsValidator);
        $validator = new ServiceDefinitionValidator($this->container, $definitionArgumentsValidator, $methodCallsValidator);

        $compilerPass = new ValidateServiceDefinitionsPass($validator);

        $this->container->addCompilerPass($compilerPass, PassConfig::TYPE_AFTER_REMOVING);
    }

    /**
     * @test
     */
    public function ifTheServiceDefinitionsAreCorrectTheContainerWillBeCompiled()
    {
        $loader = new XmlFileLoader($this->container, new FileLocator(__DIR__.'/Fixtures'));
        $loader->load('correct_service_definitions.xml');

        $this->container->compile();
    }
}
