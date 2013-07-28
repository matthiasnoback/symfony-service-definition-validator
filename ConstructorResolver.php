<?php

namespace Matthias\SymfonyServiceDefinitionValidator;

use Matthias\SymfonyServiceDefinitionValidator\Exception\MethodNotFoundException;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Matthias\SymfonyServiceDefinitionValidator\Exception\ClassNotFoundException;

class ConstructorResolver implements ConstructorResolverInterface
{
    private $containerBuilder;

    public function __construct(ContainerBuilder $containerBuilder)
    {
        $this->containerBuilder = $containerBuilder;
    }

    public function resolve(Definition $definition)
    {
        if ($definition->getFactoryClass() && $definition->getFactoryMethod()) {
            $factoryClass = $this->resolvePlaceholders($definition->getFactoryClass());
            if (!class_exists($factoryClass)) {
                throw new ClassNotFoundException($factoryClass);
            }

            $factoryMethod = $this->resolvePlaceholders($definition->getFactoryMethod());
            if (!method_exists($factoryClass, $factoryMethod)) {
                throw new MethodNotFoundException($factoryClass, $factoryMethod);
            }

            return new \ReflectionMethod($factoryClass, $factoryMethod);
        } else {
            $class = $this->resolvePlaceholders($definition->getClass());

            $reflectionClass = new \ReflectionClass($class);

            if ($reflectionClass->hasMethod('__construct')) {
                $constructMethod = $reflectionClass->getMethod('__construct');
                if ($constructMethod->isPublic()) {
                    return $constructMethod;
                }
            }
        }

        return null;
    }

    private function resolvePlaceholders($value)
    {
        return $this->containerBuilder->getParameterBag()->resolveValue($value);
    }
}
