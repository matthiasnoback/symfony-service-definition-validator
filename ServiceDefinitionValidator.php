<?php

namespace Matthias\SymfonyServiceDefinitionValidator;

use Matthias\SymfonyServiceDefinitionValidator\Exception\ClassNotFoundException;
use Matthias\SymfonyServiceDefinitionValidator\Exception\DefinitionHasNoClassException;
use Matthias\SymfonyServiceDefinitionValidator\Exception\MethodNotFoundException;
use Matthias\SymfonyServiceDefinitionValidator\Exception\MissingFactoryMethodException;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

class ServiceDefinitionValidator implements ServiceDefinitionValidatorInterface
{
    private $containerBuilder;
    private $constructorResolver;

    public function __construct(ContainerBuilder $containerBuilder)
    {
        $this->containerBuilder = $containerBuilder;

        $argumentValidator = new ArgumentValidator($containerBuilder, new ResultingClassResolver());
        $this->argumentsValidator = new ArgumentsValidator($argumentValidator);
        $this->constructorResolver = new ConstructorResolver($containerBuilder);
    }

    public function validate(Definition $definition)
    {
        $this->validateAttributes($definition);

        $this->validateArguments($definition);
    }

    public function validateAttributes(Definition $definition)
    {
        $this->validateClass($definition);

        $this->validateFactoryClass($definition);

        $this->validateFactoryService($definition);
    }

    public function validateArguments(Definition $definition)
    {
        if ($definition->isAbstract()) {
            return;
        }

        if ($definition->isSynthetic()) {
            return;
        }

        $constructor = $this->constructorResolver->resolve($definition);
        if ($constructor === null) {
            return;
        }

        $definitionArguments = $definition->getArguments();

        $this->argumentsValidator->validate($constructor, $definitionArguments);
    }

    private function validateClass(Definition $definition)
    {
        $class = $definition->getClass();

        if ($class) {
            $class = $this->containerBuilder->getParameterBag()->resolveValue($class);

            if (!class_exists($class)) {
                throw new ClassNotFoundException($class);
            }
        } elseif ($this->shouldDefinitionHaveAClass($definition)) {
            throw new DefinitionHasNoClassException();
        }
    }

    private function validateFactoryClass(Definition $definition)
    {
        $factoryClass = $definition->getFactoryClass();

        if (!$factoryClass) {
            return;
        }

        $factoryMethod = $definition->getFactoryMethod();

        if ($factoryClass && !$factoryMethod) {
            throw new MissingFactoryMethodException();
        }

        $factoryClass = $this->resolveValue($factoryClass);

        $this->validateFactoryClassAndMethod($factoryClass, $factoryMethod);
    }

    private function validateFactoryService(Definition $definition)
    {
        $factoryServiceId = $definition->getFactoryService();

        if (!$factoryServiceId) {
            return;
        }

        $factoryMethod = $definition->getFactoryMethod();
        if (!$factoryMethod) {
            throw new MissingFactoryMethodException();
        }

        if (!$this->containerBuilder->has($factoryServiceId)) {
            throw new ServiceNotFoundException($factoryServiceId);
        }

        $factoryServiceDefinition = $this->containerBuilder->getDefinition($factoryServiceId);
        $factoryClass = $factoryServiceDefinition->getClass();

        $this->validateFactoryClassAndMethod($factoryClass, $factoryMethod);
    }

    private function validateFactoryClassAndMethod($factoryClass, $factoryMethod)
    {
        if (!class_exists($factoryClass)) {
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
}
