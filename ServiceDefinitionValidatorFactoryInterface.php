<?php

namespace Matthias\SymfonyServiceDefinitionValidator;

use Symfony\Component\DependencyInjection\ContainerBuilder;

interface ServiceDefinitionValidatorFactoryInterface
{
    /**
     * @param ContainerBuilder $containerBuilder
     * @return ServiceDefinitionValidatorInterface
     */
    public function create(ContainerBuilder $containerBuilder);
}
