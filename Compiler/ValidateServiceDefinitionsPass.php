<?php

namespace Matthias\SymfonyServiceDefinitionValidator\Compiler;

use Matthias\SymfonyServiceDefinitionValidator\ServiceDefinitionValidatorInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ValidateServiceDefinitionsPass implements CompilerPassInterface
{
    private $validator;

    public function __construct(ServiceDefinitionValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    public function process(ContainerBuilder $container)
    {
        foreach ($container->getDefinitions() as $definition) {
            $this->validator->validate($definition);
        }
    }
}
