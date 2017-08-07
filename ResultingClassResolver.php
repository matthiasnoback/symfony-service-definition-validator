<?php

namespace Matthias\SymfonyServiceDefinitionValidator;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Parameter;

class ResultingClassResolver implements ResultingClassResolverInterface
{
    private $containerBuilder;

    public function __construct(ContainerBuilder $containerBuilder)
    {
        $this->containerBuilder = $containerBuilder;
    }

    public function resolve(Definition $definition)
    {
        $class = $definition->getClass();

        if ($class instanceof Parameter) {
            $class = $this->containerBuilder->getParameter($class);
        }

        return $this->resolvePlaceholders($class);
    }

    private function resolvePlaceholders($value)
    {
        return $this->containerBuilder->getParameterBag()->resolveValue($value);
    }
}
