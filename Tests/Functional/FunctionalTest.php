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

        $compilerPass = new ValidateServiceDefinitionsPass();

        $this->container->addCompilerPass($compilerPass, PassConfig::TYPE_AFTER_REMOVING);
    }

    public function testIfTheServiceDefinitionsAreCorrectTheContainerWillBeCompiled()
    {
        $loader = new XmlFileLoader($this->container, new FileLocator(__DIR__.'/Fixtures'));
        $loader->load('correct_service_definitions.xml');

        $this->container->compile();
    }

    public function testIfAServiceDefinitionIsNotCorrectAnExceptionWillBeThrown()
    {
        $loader = new XmlFileLoader($this->container, new FileLocator(__DIR__.'/Fixtures'));
        $loader->load('incorrect_service_definitions.xml');

        $this->setExpectedException('Matthias\SymfonyServiceDefinitionValidator\Exception\InvalidServiceDefinitionsException');
        $this->container->compile();
    }
}
