<?php

namespace Matthias\SymfonyServiceDefinitionValidator;

use Matthias\SymfonyServiceDefinitionValidator\Exception\ClassNotFoundException;
use Matthias\SymfonyServiceDefinitionValidator\Exception\DefinitionHasNoClassException;
use Matthias\SymfonyServiceDefinitionValidator\Exception\MethodNotFoundException;
use Matthias\SymfonyServiceDefinitionValidator\Exception\MissingFactoryMethodException;
use Matthias\SymfonyServiceDefinitionValidator\Exception\ServiceNotFoundException;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class ServiceDefinitionValidator implements ServiceDefinitionValidatorInterface
{
    private $containerBuilder;
    private $definitionArgumentsValidator;
    private $methodCallsValidator;

    public function __construct(
        ContainerBuilder $containerBuilder,
        DefinitionArgumentsValidatorInterface $definitionArgumentsValidator,
        MethodCallsValidatorInterface $methodCallsValidator
    ) {
        $this->containerBuilder = $containerBuilder;
        $this->definitionArgumentsValidator = $definitionArgumentsValidator;
        $this->methodCallsValidator = $methodCallsValidator;
    }

    public function validate(Definition $definition)
    {
        $this->validateAttributes($definition);

        $this->validateArguments($definition);

        $this->validateMethodCalls($definition);
    }

    public function validateAttributes(Definition $definition)
    {
        $this->validateClass($definition);

        $this->validateFactory($definition);
    }

    public function validateArguments(Definition $definition)
    {
        $this->definitionArgumentsValidator->validate($definition);
    }

    private function validateMethodCalls(Definition $definition)
    {
        $this->methodCallsValidator->validate($definition);
    }

    private function validateClass(Definition $definition)
    {
        $class = $definition->getClass();

        if ($class) {
            $class = $this->containerBuilder->getParameterBag()->resolveValue($class);

            // TODO only services created using a factory can have an interface
            if (!class_exists($class) && !interface_exists($class)) {
                throw new ClassNotFoundException($class);
            }
        } elseif ($this->shouldDefinitionHaveAClass($definition)) {
            throw new DefinitionHasNoClassException();
        }
    }

    private function validateFactory(Definition $definition)
    {
        if (method_exists($definition, 'getFactoryClass')) {
            // Symfony <= 3.0.0
            $factoryClass = $definition->getFactoryClass();
            if ($factoryClass) {
                $this->validateFactoryClassAndMethod($factoryClass, $definition->getFactoryMethod());
            }
        }

        if (method_exists($definition, 'getFactoryService')) {
            $factoryService = $definition->getFactoryService();
            if ($factoryService) {
                $this->validateFactoryServiceAndMethod($factoryService, $definition->getFactoryMethod());
            }
        }

        if (method_exists($definition, 'getFactory')) {
            $factory = $definition->getFactory();
            if (!is_array($factory) || count($factory) !== 2) {
                return;
            }

            list($factoryClassOrService, $method) = $factory;
            if (is_string($factoryClassOrService)) {
                $this->validateFactoryClassAndMethod($factoryClassOrService, $method);
            } else {
                $this->validateFactoryServiceAndMethod((string) $factoryClassOrService, $method);
            }
        }
    }

    private function validateFactoryClassAndMethod($factoryClass, $factoryMethod)
    {
        if ($factoryClass && !$factoryMethod) {
            throw new MissingFactoryMethodException();
        }

        $factoryClass = $this->resolveValue($factoryClass);

        if (!class_exists($factoryClass) && !interface_exists($factoryClass)) {
            throw new ClassNotFoundException($factoryClass);
        }

        if (!method_exists($factoryClass, $factoryMethod)) {
            throw new MethodNotFoundException($factoryClass, $factoryMethod);
        }
    }

    /**
     * Find out whether or not the given definition should have a class (i.e. not when it is a synthetic or abstract
     * definition)
     *
     * @param Definition $definition
     * @return bool
     */
    private function shouldDefinitionHaveAClass(Definition $definition)
    {
        if ($definition->isSynthetic()) {
            return false;
        }

        if ($definition->isAbstract()) {
            return false;
        }

        return true;
    }

    /**
     * Resolve a value with placeholders for container parameters
     *
     * @param $value
     * @return string
     */
    private function resolveValue($value)
    {
        return $this->containerBuilder->getParameterBag()->resolveValue($value);
    }

    private function validateFactoryServiceAndMethod($factoryServiceId, $factoryMethod)
    {
        if (!$factoryMethod) {
            throw new MissingFactoryMethodException();
        }

        if (!$this->containerBuilder->has($factoryServiceId)) {
            throw new ServiceNotFoundException($factoryServiceId);
        }

        $factoryServiceDefinition = $this->containerBuilder->findDefinition($factoryServiceId);
        $factoryClass = $factoryServiceDefinition->getClass();

        $this->validateFactoryClassAndMethod($factoryClass, $factoryMethod);
    }
}
