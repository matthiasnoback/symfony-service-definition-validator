<?php

namespace Matthias\SymfonyServiceDefinitionValidator;

use Matthias\SymfonyServiceDefinitionValidator\Exception\ClassNotFoundException;
use Matthias\SymfonyServiceDefinitionValidator\Exception\FunctionNotFoundException;
use Matthias\SymfonyServiceDefinitionValidator\Exception\MethodNotFoundException;
use Matthias\SymfonyServiceDefinitionValidator\Exception\NonPublicConstructorException;
use Matthias\SymfonyServiceDefinitionValidator\Exception\NonStaticFactoryMethodException;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class ConstructorResolver implements ConstructorResolverInterface
{
    private $containerBuilder;
    private $resultingClassResolver;

    public function __construct(
        ContainerBuilder $containerBuilder,
        ResultingClassResolverInterface $resultingClassResolver
    ) {
        $this->containerBuilder = $containerBuilder;
        $this->resultingClassResolver = $resultingClassResolver;
    }

    public function resolve(Definition $definition)
    {
        $factory = $this->resolveFactory($definition);

        if (is_string($factory)) {
            return $this->resolveFactoryFunction($factory);
        }

        if (is_array($factory) && $factory[0] instanceof Reference) {
            return $this->resolveFactoryServiceWithMethod((string) $factory[0], $factory[1]);
        }

        if (is_array($factory)) {
            return $this->resolveFactoryClassWithMethod($factory[0], $factory[1]);
        }

        if ($definition->getClass()) {
            return $this->resolveClassWithConstructor($definition->getClass());
        }

        return null;
    }

    private function resolveFactoryClassWithMethod($factoryClass, $factoryMethod)
    {
        $factoryClass = $this->resolvePlaceholders($factoryClass);

        if (!class_exists($factoryClass)) {
            throw new ClassNotFoundException($factoryClass);
        }

        if (!method_exists($factoryClass, $factoryMethod)) {
            throw new MethodNotFoundException($factoryClass, $factoryMethod);
        }

        $reflectionMethod = new \ReflectionMethod($factoryClass, $factoryMethod);

        if (!$reflectionMethod->isStatic()) {
            throw new NonStaticFactoryMethodException($factoryClass, $factoryMethod);
        }

        return $reflectionMethod;
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
            if (!$constructMethod->isPublic()) {
                throw new NonPublicConstructorException($class);
            }

            return $constructMethod;
        }

        return null;
    }

    private function resolvePlaceholders($value)
    {
        return $this->containerBuilder->getParameterBag()->resolveValue($value);
    }

    private function resolveFactory(Definition $definition)
    {
        if (method_exists($definition, 'getFactory') && $definition->getFactory() !== null) {
            return $definition->getFactory();
        }

        if (method_exists($definition, 'getFactoryClass')
            && $definition->getFactoryClass()
            && $definition->getFactoryMethod()) {
            return array($definition->getFactoryClass(), $definition->getFactoryMethod());
        }

        if (method_exists($definition, 'getFactoryService')
            && $definition->getFactoryService()
            && $definition->getFactoryMethod()) {
            return array(new Reference($definition->getFactoryService()), $definition->getFactoryMethod());
        }

        return null;
    }

    private function resolveFactoryFunction($factory)
    {
        if (!function_exists($factory)) {
            throw new FunctionNotFoundException($factory);
        }

        return new \ReflectionFunction($factory);
    }
}
