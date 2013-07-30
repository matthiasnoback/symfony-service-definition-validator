<?php

namespace Matthias\SymfonyServiceDefinitionValidator;

use Matthias\SymfonyServiceDefinitionValidator\Exception\ClassNotFoundException;
use Matthias\SymfonyServiceDefinitionValidator\Exception\MethodNotFoundException;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class ConstructorResolver implements ConstructorResolverInterface
{
    private $containerBuilder;
    private $resultingClassResolver;

    public function __construct(
        ContainerBuilder $containerBuilder,
        ResultingClassResolverInterface $resultingClassResolver
    )
    {
        $this->containerBuilder = $containerBuilder;
        $this->resultingClassResolver = $resultingClassResolver;
    }

    public function resolve(Definition $definition)
    {
        if ($definition->getFactoryClass() && $definition->getFactoryMethod()) {
            return $this->resolveFactoryClassWithMethod(
                $definition->getFactoryClass(),
                $definition->getFactoryMethod()
            );
        } elseif ($definition->getFactoryService() && $definition->getFactoryMethod()) {
            return $this->resolveFactoryServiceWithMethod(
                $definition->getFactoryService(),
                $definition->getFactoryMethod()
            );
        } elseif ($definition->getClass()) {
            return $this->resolveClassWithConstructor($definition->getClass());
        }

        return null;
    }

    private function resolveFactoryClassWithMethod($factoryClass, $factoryMethod)
    {
        if (!class_exists($factoryClass)) {
            throw new ClassNotFoundException($factoryClass);
        }

        if (!method_exists($factoryClass, $factoryMethod)) {
            throw new MethodNotFoundException($factoryClass, $factoryMethod);
        }

        return new \ReflectionMethod($factoryClass, $factoryMethod);
    }

    private function resolveFactoryServiceWithMethod($factoryServiceId, $factoryMethod)
    {
        $factoryDefinition = $this->containerBuilder->findDefinition($factoryServiceId);

        $factoryClass = $this->resultingClassResolver->resolve($factoryDefinition);

        if (!method_exists($factoryClass, $factoryMethod)) {
            throw new MethodNotFoundException($factoryClass, $factoryMethod);
        }

        return new \ReflectionMethod($factoryClass, $factoryMethod);
    }

    private function resolveClassWithConstructor($class)
    {
        $class = $this->resolvePlaceholders($class);

        $reflectionClass = new \ReflectionClass($class);

        if ($reflectionClass->hasMethod('__construct')) {
            $constructMethod = $reflectionClass->getMethod('__construct');
            if ($constructMethod->isPublic()) {
                return $constructMethod;
            }
        }

        return null;
    }

    private function resolvePlaceholders($value)
    {
        return $this->containerBuilder->getParameterBag()->resolveValue($value);
    }
}
