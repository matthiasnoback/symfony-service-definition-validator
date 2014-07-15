<?php

namespace Matthias\SymfonyServiceDefinitionValidator\Compiler\Compatibility;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class FixSymfonyValidatorDefinitionPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if ($container->hasDefinition('validator')) {
            $container->getDefinition('validator')->setClass('Symfony\Component\Validator\Validator');
        }
    }
}
